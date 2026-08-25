<?php
/**
 * Meta Boxes - Post/Page settings service.
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Admin;

use Developer_Starter\SEO\Industry_Schema_Engine;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Meta_Boxes_Post_Settings_Service {
    public function render_seo_meta_box( $post ) {
        wp_nonce_field( 'developer_starter_seo_nonce', 'seo_nonce' );
        $t = get_post_meta( $post->ID, '_developer_starter_seo_title', true );
        $d = get_post_meta( $post->ID, '_developer_starter_seo_description', true );
        $k = get_post_meta( $post->ID, '_developer_starter_seo_keywords', true );
        $noindex = get_post_meta( $post->ID, '_developer_starter_seo_noindex', true );
        $nofollow = get_post_meta( $post->ID, '_developer_starter_seo_nofollow', true );
        $canonical = get_post_meta( $post->ID, '_developer_starter_seo_canonical', true );
        $og_title = get_post_meta( $post->ID, '_developer_starter_og_title', true );
        $og_desc = get_post_meta( $post->ID, '_developer_starter_og_description', true );
        $og_image = get_post_meta( $post->ID, '_developer_starter_og_image', true );
        ?>
        <p><label><strong><?php esc_html_e( 'SEO标题', 'developer-starter' ); ?></strong></label><br><input type="text" name="seo_title" value="<?php echo esc_attr( $t ); ?>" class="large-text"/></p>
        <p><label><strong><?php esc_html_e( 'SEO描述', 'developer-starter' ); ?></strong></label><br><textarea name="seo_description" rows="2" class="large-text"><?php echo esc_textarea( $d ); ?></textarea></p>
        <p><label><strong><?php esc_html_e( 'SEO关键词', 'developer-starter' ); ?></strong></label><br><input type="text" name="seo_keywords" value="<?php echo esc_attr( $k ); ?>" class="large-text"/></p>
        <hr style="margin: 16px 0;">
        <p><label><strong><?php esc_html_e( 'Canonical URL（可选）', 'developer-starter' ); ?></strong></label><br><input type="url" name="seo_canonical" value="<?php echo esc_url( $canonical ); ?>" class="large-text" placeholder="https://example.com/custom-canonical/" /></p>
        <p>
            <label style="margin-right: 20px;"><input type="checkbox" name="seo_noindex" value="1" <?php checked( $noindex, '1' ); ?> /> <?php esc_html_e( '该页面设置为 noindex', 'developer-starter' ); ?></label>
            <label><input type="checkbox" name="seo_nofollow" value="1" <?php checked( $nofollow, '1' ); ?> /> <?php esc_html_e( '该页面设置为 nofollow', 'developer-starter' ); ?></label>
        </p>
        <p class="description"><?php esc_html_e( '用于专题页、活动页等精细化SEO控制。', 'developer-starter' ); ?></p>
        <hr style="margin: 16px 0;">
        <p><label><strong><?php esc_html_e( 'Open Graph 标题（可覆盖）', 'developer-starter' ); ?></strong></label><br><input type="text" name="og_title" value="<?php echo esc_attr( $og_title ); ?>" class="large-text"/></p>
        <p><label><strong><?php esc_html_e( 'Open Graph 描述（可覆盖）', 'developer-starter' ); ?></strong></label><br><textarea name="og_description" rows="2" class="large-text"><?php echo esc_textarea( $og_desc ); ?></textarea></p>
        <p><label><strong><?php esc_html_e( 'Open Graph 图片URL（可覆盖）', 'developer-starter' ); ?></strong></label><br><input type="url" name="og_image" value="<?php echo esc_url( $og_image ); ?>" class="large-text" placeholder="https://example.com/cover.jpg" /></p>
        <?php
        $this->render_seo_basic_score( $post );
        $this->render_multilingual_seo_status_matrix( $post );
        $this->render_schema_override_meta_box( $post );
    }

    /**
     * Render read-only rule-based SEO score.
     *
     * @param \WP_Post $post Current post.
     * @return void
     */
    private function render_seo_basic_score( $post ) {
        if ( ! $post instanceof \WP_Post || ! in_array( $post->post_type, array( 'post', 'page' ), true ) ) {
            return;
        }
        if ( ! class_exists( '\Developer_Starter\SEO\SEO_Health_Check' ) ) {
            return;
        }

        $score = \Developer_Starter\SEO\SEO_Health_Check::score_post( (int) $post->ID );
        if ( empty( $score ) || ! is_array( $score ) ) {
            return;
        }

        $value = isset( $score['score'] ) ? absint( $score['score'] ) : 0;
        $grade = isset( $score['grade'] ) ? (string) $score['grade'] : '';
        $tone  = $value >= 85 ? 'pass' : ( $value >= 65 ? 'warning' : 'critical' );

        echo '<hr style="margin: 18px 0;">';
        echo '<div class="qiling-seo-basic-score">';
        echo '<style>
            .qiling-seo-basic-score{border:1px solid #dcdcde;border-radius:8px;background:#fff;padding:12px;margin-top:12px}
            .qiling-seo-basic-score__head{display:flex;align-items:center;gap:12px;margin-bottom:10px}
            .qiling-seo-basic-score__badge{display:inline-flex;align-items:center;justify-content:center;min-width:54px;height:54px;border-radius:50%;font-weight:700;font-size:18px;background:#f6f7f7;border:1px solid #dcdcde}
            .qiling-seo-basic-score__badge.is-pass{background:#f0fdf4;border-color:#bbf7d0;color:#166534}
            .qiling-seo-basic-score__badge.is-warning{background:#fffbeb;border-color:#fde68a;color:#92400e}
            .qiling-seo-basic-score__badge.is-critical{background:#fef2f2;border-color:#fecaca;color:#991b1b}
            .qiling-seo-basic-score__checks{display:grid;gap:6px;margin:8px 0 0}
            .qiling-seo-basic-score__check{display:flex;gap:8px;align-items:flex-start}
            .qiling-seo-basic-score__dot{width:9px;height:9px;border-radius:999px;margin-top:6px;background:#f59e0b;flex:0 0 auto}
            .qiling-seo-basic-score__check.is-pass .qiling-seo-basic-score__dot{background:#16a34a}
        </style>';
        echo '<div class="qiling-seo-basic-score__head">';
        echo '<span class="qiling-seo-basic-score__badge is-' . esc_attr( $tone ) . '">' . esc_html( (string) $value ) . '</span>';
        echo '<div><h4 style="margin:0;">' . esc_html__( 'SEO 基础评分', 'developer-starter' ) . '</h4>';
        echo '<p class="description" style="margin:3px 0 0;">' . esc_html( sprintf( __( '%s。基于规则检查，不调用 AI，不保存额外数据。', 'developer-starter' ), $grade ) ) . '</p></div>';
        echo '</div>';

        $checks = isset( $score['checks'] ) && is_array( $score['checks'] ) ? $score['checks'] : array();
        if ( ! empty( $checks ) ) {
            echo '<div class="qiling-seo-basic-score__checks">';
            foreach ( $checks as $check ) {
                if ( ! is_array( $check ) ) {
                    continue;
                }
                $status = isset( $check['status'] ) && 'pass' === $check['status'] ? 'pass' : 'warning';
                $label = isset( $check['label'] ) ? (string) $check['label'] : '';
                $message = isset( $check['message'] ) ? (string) $check['message'] : '';
                echo '<div class="qiling-seo-basic-score__check is-' . esc_attr( $status ) . '">';
                echo '<span class="qiling-seo-basic-score__dot" aria-hidden="true"></span>';
                echo '<span><strong>' . esc_html( $label ) . '：</strong>' . esc_html( $message ) . '</span>';
                echo '</div>';
            }
            echo '</div>';
        }

        echo '</div>';
    }

    /**
     * Render the multilingual SEO status matrix powered by 启灵AI多语言.
     *
     * @param \WP_Post $post Current post.
     * @return void
     */
    private function render_multilingual_seo_status_matrix( $post ) {
        if ( ! $post instanceof \WP_Post || ! in_array( $post->post_type, array( 'post', 'page' ), true ) ) {
            return;
        }

        $this->render_multilingual_seo_status_matrix_assets();

        echo '<hr style="margin: 18px 0;">';
        echo '<div class="qiling-ml-seo-matrix">';
        echo '<h4>' . esc_html__( '多语言 SEO 状态矩阵', 'developer-starter' ) . '</h4>';

        if ( ! function_exists( 'xb_aifanyi_get_post_seo_diagnostics' ) ) {
            echo '<p class="description">' . esc_html__( '未检测到启灵AI多语言插件；当前仅显示主题本页 SEO 设置。', 'developer-starter' ) . '</p>';
            echo '</div>';
            return;
        }

        $diagnostics = xb_aifanyi_get_post_seo_diagnostics( (int) $post->ID );
        $languages = is_array( $diagnostics ) && isset( $diagnostics['languages'] ) && is_array( $diagnostics['languages'] )
            ? $diagnostics['languages']
            : array();

        if ( empty( $languages ) ) {
            echo '<p class="description">' . esc_html__( '启灵AI多语言暂未提供语言诊断数据。', 'developer-starter' ) . '</p>';
            echo '</div>';
            return;
        }

        echo '<p class="description">' . esc_html__( 'canonical 与 hreflang 按启灵AI多语言 URL 规则判断；OG image、noindex 和 nofollow 默认继承原页面，不计入译文缺失项。', 'developer-starter' ) . '</p>';
        echo '<div class="qiling-ml-seo-matrix__scroll">';
        echo '<table class="widefat striped qiling-ml-seo-matrix__table">';
        echo '<thead><tr>';
        echo '<th>' . esc_html__( '语言', 'developer-starter' ) . '</th>';
        echo '<th>' . esc_html__( '译文状态', 'developer-starter' ) . '</th>';
        echo '<th>' . esc_html__( 'SEO 状态', 'developer-starter' ) . '</th>';
        echo '<th>' . esc_html__( 'OG 状态', 'developer-starter' ) . '</th>';
        echo '<th>' . esc_html__( 'canonical', 'developer-starter' ) . '</th>';
        echo '<th>' . esc_html__( 'hreflang', 'developer-starter' ) . '</th>';
        echo '<th>' . esc_html__( '前台查看', 'developer-starter' ) . '</th>';
        echo '</tr></thead><tbody>';

        foreach ( $languages as $language ) {
            if ( ! is_array( $language ) ) {
                continue;
            }

            $name = isset( $language['name'] ) ? (string) $language['name'] : '';
            $code = isset( $language['code'] ) ? (string) $language['code'] : '';
            $locale = isset( $language['locale'] ) ? (string) $language['locale'] : '';
            $hreflang = isset( $language['hreflang'] ) ? (string) $language['hreflang'] : $code;
            $url = isset( $language['current_url'] ) ? (string) $language['current_url'] : '';
            $canonical_url = isset( $language['canonical_url'] ) ? (string) $language['canonical_url'] : $url;
            $hreflang_url = isset( $language['hreflang_url'] ) ? (string) $language['hreflang_url'] : $url;
            $is_default = ! empty( $language['is_default'] );
            $has_public_translation = ! empty( $language['has_public_translation'] );
            $seo = isset( $language['seo'] ) && is_array( $language['seo'] ) ? $language['seo'] : array();
            $og = isset( $language['og'] ) && is_array( $language['og'] ) ? $language['og'] : array();

            echo '<tr>';
            echo '<td><strong>' . esc_html( $name ) . '</strong><br><span>' . esc_html( $code . ' / ' . $locale ) . '</span></td>';
            echo '<td>' . $this->get_multilingual_seo_matrix_badge( $is_default || $has_public_translation ? 'pass' : 'warning', $is_default ? __( '原文', 'developer-starter' ) : ( $has_public_translation ? __( '已公开', 'developer-starter' ) : __( '缺失', 'developer-starter' ) ) ) . '</td>';
            echo '<td>' . wp_kses_post( $this->get_multilingual_seo_status_label( $seo, array( 'title', 'description', 'keywords' ) ) ) . '</td>';
            echo '<td>' . wp_kses_post( $this->get_multilingual_seo_status_label( $og, array( 'title', 'description' ) ) ) . '</td>';
            echo '<td>' . ( '' !== $canonical_url ? '<a href="' . esc_url( $canonical_url ) . '" target="_blank" rel="noopener noreferrer">' . esc_html( $this->truncate_multilingual_seo_url( $canonical_url ) ) . '</a>' : esc_html__( '未生成', 'developer-starter' ) ) . '</td>';
            echo '<td><code>' . esc_html( $hreflang ) . '</code><br>' . ( '' !== $hreflang_url ? '<a href="' . esc_url( $hreflang_url ) . '" target="_blank" rel="noopener noreferrer">' . esc_html( $this->truncate_multilingual_seo_url( $hreflang_url ) ) . '</a>' : esc_html__( '未生成', 'developer-starter' ) ) . '</td>';
            echo '<td>' . ( '' !== $url ? '<a class="button button-small" href="' . esc_url( $url ) . '" target="_blank" rel="noopener noreferrer">' . esc_html__( '查看', 'developer-starter' ) . '</a>' : '-' ) . '</td>';
            echo '</tr>';
        }

        echo '</tbody></table>';
        echo '</div>';
        echo '</div>';
    }

    /**
     * Render matrix CSS once.
     *
     * @return void
     */
    private function render_multilingual_seo_status_matrix_assets() {
        static $printed = false;
        if ( $printed ) {
            return;
        }
        $printed = true;
        ?>
        <style>
            .qiling-ml-seo-matrix h4 {
                margin: 0 0 8px;
            }
            .qiling-ml-seo-matrix__scroll {
                overflow-x: auto;
                margin-top: 10px;
            }
            .qiling-ml-seo-matrix__table {
                min-width: 920px;
            }
            .qiling-ml-seo-matrix__table td {
                vertical-align: top;
            }
            .qiling-ml-seo-badge {
                display: inline-flex;
                align-items: center;
                min-height: 24px;
                padding: 0 8px;
                border-radius: 999px;
                font-size: 12px;
                font-weight: 600;
                white-space: nowrap;
            }
            .qiling-ml-seo-badge.is-pass {
                background: #dcfce7;
                color: #166534;
            }
            .qiling-ml-seo-badge.is-warning {
                background: #fef3c7;
                color: #92400e;
            }
            .qiling-ml-seo-badge.is-info {
                background: #e0f2fe;
                color: #075985;
            }
            .qiling-ml-seo-status-detail {
                display: block;
                margin-top: 5px;
                color: #646970;
                font-size: 12px;
            }
        </style>
        <?php
    }

    /**
     * Build SEO/OG status label.
     *
     * @param array<string,mixed> $group Field group diagnostics.
     * @param array<int,string>   $fields Fields to inspect.
     * @return string
     */
    private function get_multilingual_seo_status_label( $group, $fields ) {
        $total = count( $fields );
        $filled = 0;
        foreach ( $fields as $field ) {
            if ( ! empty( $group[ $field ] ) && is_array( $group[ $field ] ) && ! empty( $group[ $field ]['has_value'] ) ) {
                $filled++;
            }
        }

        if ( $filled >= $total ) {
            return $this->get_multilingual_seo_matrix_badge( 'pass', __( '完整', 'developer-starter' ) )
                . '<span class="qiling-ml-seo-status-detail">' . esc_html( sprintf( __( '%1$d/%2$d 已填写', 'developer-starter' ), $filled, $total ) ) . '</span>';
        }

        if ( $filled > 0 ) {
            return $this->get_multilingual_seo_matrix_badge( 'warning', __( '部分', 'developer-starter' ) )
                . '<span class="qiling-ml-seo-status-detail">' . esc_html( sprintf( __( '%1$d/%2$d 已填写', 'developer-starter' ), $filled, $total ) ) . '</span>';
        }

        return $this->get_multilingual_seo_matrix_badge( 'warning', __( '缺失', 'developer-starter' ) )
            . '<span class="qiling-ml-seo-status-detail">' . esc_html( sprintf( __( '%1$d/%2$d 已填写', 'developer-starter' ), $filled, $total ) ) . '</span>';
    }

    /**
     * Build a status badge.
     *
     * @param string $status Status key.
     * @param string $label Label.
     * @return string
     */
    private function get_multilingual_seo_matrix_badge( $status, $label ) {
        $status = in_array( $status, array( 'pass', 'warning', 'info' ), true ) ? $status : 'info';

        return '<span class="qiling-ml-seo-badge is-' . esc_attr( $status ) . '">' . esc_html( $label ) . '</span>';
    }

    /**
     * Truncate long URLs for table display.
     *
     * @param string $url URL.
     * @return string
     */
    private function truncate_multilingual_seo_url( $url ) {
        $url = (string) $url;
        if ( function_exists( 'mb_strlen' ) && function_exists( 'mb_substr' ) ) {
            return mb_strlen( $url, 'UTF-8' ) > 54 ? mb_substr( $url, 0, 51, 'UTF-8' ) . '...' : $url;
        }

        return strlen( $url ) > 54 ? substr( $url, 0, 51 ) . '...' : $url;
    }

    /**
     * Render page-level Schema override controls.
     *
     * @param \WP_Post $post Current post.
     * @return void
     */
    private function render_schema_override_meta_box( $post ) {
        if ( ! $post instanceof \WP_Post || ! class_exists( '\Developer_Starter\SEO\Industry_Schema_Engine' ) ) {
            return;
        }

        $this->render_schema_override_assets();
        wp_nonce_field( 'qiling_schema_override_nonce', 'qiling_schema_override_nonce' );

        $override = Industry_Schema_Engine::get_page_schema_override( (int) $post->ID );
        $choices = Industry_Schema_Engine::get_page_schema_type_choices();
        $data = isset( $override['data'] ) && is_array( $override['data'] ) ? $override['data'] : array();
        $type = isset( $override['type'] ) ? (string) $override['type'] : '';
        $enabled = ! empty( $override['enabled'] );
        $preview = Industry_Schema_Engine::get_instance()->get_preview_data( (int) $post->ID );
        $diagnostics = isset( $preview['diagnostics'] ) && is_array( $preview['diagnostics'] ) ? $preview['diagnostics'] : array();

        echo '<hr style="margin: 18px 0;">';
        echo '<div class="qiling-schema-override">';
        echo '<h4>' . esc_html__( '页面级 Schema 覆盖', 'developer-starter' ) . '</h4>';
        echo '<p class="description">' . esc_html__( '页面设置会合并到同一份 Schema 结构化数据中，不会重复输出 JSON-LD。留空字段会优先使用当前页面标题、描述、特色图或站点组织信息。', 'developer-starter' ) . '</p>';

        echo '<p><label><input type="checkbox" name="qiling_schema_override[enabled]" value="1" ' . checked( $enabled, true, false ) . '> ' . esc_html__( '启用当前页面 Schema 覆盖', 'developer-starter' ) . '</label></p>';
        echo '<p><label><strong>' . esc_html__( '页面主类型', 'developer-starter' ) . '</strong></label><br>';
        echo '<select name="qiling_schema_override[type]" class="widefat">';
        foreach ( $choices as $choice => $label ) {
            echo '<option value="' . esc_attr( $choice ) . '" ' . selected( $type, $choice, false ) . '>' . esc_html( $label ) . '</option>';
        }
        echo '</select></p>';

        echo '<div class="qiling-schema-override__grid">';
        $this->render_schema_override_text_input( 'name', __( '名称 / name', 'developer-starter' ), $data, __( '产品名、课程名、服务名等。', 'developer-starter' ) );
        $this->render_schema_override_text_input( 'headline', __( '标题 / headline', 'developer-starter' ), $data, __( 'Article、JobPosting 可使用。', 'developer-starter' ) );
        $this->render_schema_override_text_input( 'image', __( '图片 URL', 'developer-starter' ), $data, __( '用于当前主实体 image。', 'developer-starter' ), 'url' );
        $this->render_schema_override_text_input( 'url', __( '实体 URL', 'developer-starter' ), $data, __( '留空使用 canonical URL。', 'developer-starter' ), 'url' );
        echo '</div>';

        $this->render_schema_override_textarea( 'description', __( '描述 / description', 'developer-starter' ), $data, __( 'Article 摘要、Product 说明、Service 介绍、Review 正文等。', 'developer-starter' ), 2 );

        echo '<div class="qiling-schema-override__grid">';
        $this->render_schema_override_text_input( 'price', __( '价格', 'developer-starter' ), $data, __( 'Product / Service 使用，数字会生成 Offer。', 'developer-starter' ) );
        $this->render_schema_override_text_input( 'currency', __( '币种', 'developer-starter' ), $data, __( '三位 ISO 代码，如 USD、JPY。', 'developer-starter' ) );
        $this->render_schema_override_text_input( 'brand', __( '品牌', 'developer-starter' ), $data, __( 'Product 可选。', 'developer-starter' ) );
        $this->render_schema_override_text_input( 'sku', 'SKU', $data, __( 'Product 可选。', 'developer-starter' ) );
        $this->render_schema_override_text_input( 'rating_value', __( '评分', 'developer-starter' ), $data, __( '1-5，Product / Review 使用。', 'developer-starter' ) );
        $this->render_schema_override_text_input( 'rating_count', __( '评分数量', 'developer-starter' ), $data, __( 'Product aggregateRating 使用。', 'developer-starter' ) );
        $this->render_schema_override_text_input( 'item_name', __( '评价对象', 'developer-starter' ), $data, __( 'Review 使用。', 'developer-starter' ) );
        $this->render_schema_override_text_input( 'author_name', __( '作者 / 评价人', 'developer-starter' ), $data, __( 'Article / Review 使用。', 'developer-starter' ) );
        echo '</div>';

        echo '<div class="qiling-schema-override__grid">';
        $this->render_schema_override_text_input( 'course_provider', __( '课程提供方', 'developer-starter' ), $data, __( 'Course 使用，留空回退站点组织。', 'developer-starter' ) );
        $this->render_schema_override_text_input( 'service_area', __( '服务区域', 'developer-starter' ), $data, __( 'Service 使用。', 'developer-starter' ) );
        $this->render_schema_override_text_input( 'start_date', __( '活动开始时间', 'developer-starter' ), $data, __( 'Event 使用，建议 ISO 日期时间。', 'developer-starter' ) );
        $this->render_schema_override_text_input( 'end_date', __( '活动结束时间', 'developer-starter' ), $data, __( 'Event 可选。', 'developer-starter' ) );
        $this->render_schema_override_text_input( 'location_name', __( '地点名称', 'developer-starter' ), $data, __( 'Event 使用。', 'developer-starter' ) );
        $this->render_schema_override_text_input( 'event_status', __( '活动状态 URL', 'developer-starter' ), $data, __( '如 https://schema.org/EventScheduled。', 'developer-starter' ) );
        echo '</div>';
        $this->render_schema_override_textarea( 'location_address', __( '地点地址', 'developer-starter' ), $data, __( 'Event 使用。', 'developer-starter' ), 2 );

        echo '<div class="qiling-schema-override__grid">';
        $this->render_schema_override_text_input( 'title', __( '职位标题', 'developer-starter' ), $data, __( 'JobPosting 使用。', 'developer-starter' ) );
        $this->render_schema_override_text_input( 'date_posted', __( '职位发布日期', 'developer-starter' ), $data, __( 'JobPosting 使用，建议 YYYY-MM-DD。', 'developer-starter' ) );
        $this->render_schema_override_text_input( 'valid_through', __( '职位截止日期', 'developer-starter' ), $data, __( 'JobPosting 可选。', 'developer-starter' ) );
        $this->render_schema_override_text_input( 'employment_type', __( '雇佣类型', 'developer-starter' ), $data, __( '如 FULL_TIME、CONTRACTOR。', 'developer-starter' ) );
        $this->render_schema_override_text_input( 'hiring_organization', __( '招聘组织', 'developer-starter' ), $data, __( 'JobPosting 使用。', 'developer-starter' ) );
        echo '</div>';
        $this->render_schema_override_textarea( 'job_location', __( '工作地点', 'developer-starter' ), $data, __( 'JobPosting 使用。', 'developer-starter' ), 2 );

        $faq_text = $this->format_schema_override_faq_text( isset( $data['faq_items'] ) && is_array( $data['faq_items'] ) ? $data['faq_items'] : array() );
        echo '<p><label><strong>' . esc_html__( 'FAQ 问答', 'developer-starter' ) . '</strong></label><br>';
        echo '<textarea name="qiling_schema_override[data][faq_items_text]" rows="4" class="large-text" placeholder="' . esc_attr__( '每行一组：问题 | 答案', 'developer-starter' ) . '">' . esc_textarea( $faq_text ) . '</textarea>';
        echo '<span class="description">' . esc_html__( 'FAQPage 使用；FAQ 模块仍会自动生成 FAQPage，这里可手动覆盖当前页面主 FAQ。', 'developer-starter' ) . '</span></p>';

        $howto_text = $this->format_schema_override_howto_text( isset( $data['howto_steps'] ) && is_array( $data['howto_steps'] ) ? $data['howto_steps'] : array() );
        echo '<p><label><strong>HowTo</strong></label><br>';
        echo '<textarea name="qiling_schema_override[data][howto_steps_text]" rows="4" class="large-text" placeholder="' . esc_attr__( '每行一个步骤', 'developer-starter' ) . '">' . esc_textarea( $howto_text ) . '</textarea>';
        echo '<span class="description">' . esc_html__( 'HowTo 使用，系统会按行生成 HowToStep。', 'developer-starter' ) . '</span></p>';

        $this->render_schema_override_diagnostics( $diagnostics );
        echo '</div>';
    }

    /**
     * Render a Schema override text input.
     *
     * @param string              $key Field key.
     * @param string              $label Field label.
     * @param array<string,mixed> $data Stored data.
     * @param string              $description Description.
     * @param string              $type Input type.
     * @return void
     */
    private function render_schema_override_text_input( $key, $label, $data, $description = '', $type = 'text' ) {
        $value = isset( $data[ $key ] ) && ! is_array( $data[ $key ] ) ? (string) $data[ $key ] : '';
        echo '<p><label><strong>' . esc_html( $label ) . '</strong></label><br>';
        echo '<input type="' . esc_attr( $type ) . '" name="qiling_schema_override[data][' . esc_attr( $key ) . ']" value="' . esc_attr( $value ) . '" class="widefat">';
        if ( '' !== $description ) {
            echo '<span class="description">' . esc_html( $description ) . '</span>';
        }
        echo '</p>';
    }

    /**
     * Render a Schema override textarea.
     *
     * @param string              $key Field key.
     * @param string              $label Field label.
     * @param array<string,mixed> $data Stored data.
     * @param string              $description Description.
     * @param int                 $rows Row count.
     * @return void
     */
    private function render_schema_override_textarea( $key, $label, $data, $description = '', $rows = 3 ) {
        $value = isset( $data[ $key ] ) && ! is_array( $data[ $key ] ) ? (string) $data[ $key ] : '';
        echo '<p><label><strong>' . esc_html( $label ) . '</strong></label><br>';
        echo '<textarea name="qiling_schema_override[data][' . esc_attr( $key ) . ']" rows="' . absint( $rows ) . '" class="large-text">' . esc_textarea( $value ) . '</textarea>';
        if ( '' !== $description ) {
            echo '<span class="description">' . esc_html( $description ) . '</span>';
        }
        echo '</p>';
    }

    /**
     * Render visual Schema diagnostics.
     *
     * @param array<string,mixed> $diagnostics Diagnostics payload.
     * @return void
     */
    private function render_schema_override_diagnostics( $diagnostics ) {
        $issues = isset( $diagnostics['issues'] ) && is_array( $diagnostics['issues'] ) ? $diagnostics['issues'] : array();
        echo '<div class="qiling-schema-override__diagnostics">';
        echo '<strong>' . esc_html__( 'Schema 可视化诊断', 'developer-starter' ) . '</strong>';
        if ( empty( $issues ) ) {
            echo '<p class="is-success">' . esc_html__( '当前页面未发现 Schema 冲突或关键缺失项。', 'developer-starter' ) . '</p>';
        } else {
            echo '<ul>';
            foreach ( $issues as $issue ) {
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
    }

    /**
     * Format FAQ rows for textarea editing.
     *
     * @param array<int,array<string,mixed>> $items FAQ rows.
     * @return string
     */
    private function format_schema_override_faq_text( $items ) {
        $lines = array();
        foreach ( $items as $item ) {
            if ( ! is_array( $item ) ) {
                continue;
            }
            $question = isset( $item['question'] ) ? trim( (string) $item['question'] ) : '';
            $answer = isset( $item['answer'] ) ? trim( (string) $item['answer'] ) : '';
            if ( '' !== $question && '' !== $answer ) {
                $lines[] = $question . ' | ' . $answer;
            }
        }

        return implode( "\n", $lines );
    }

    /**
     * Format HowTo rows for textarea editing.
     *
     * @param array<int,array<string,mixed>> $items HowTo rows.
     * @return string
     */
    private function format_schema_override_howto_text( $items ) {
        $lines = array();
        foreach ( $items as $item ) {
            if ( ! is_array( $item ) ) {
                continue;
            }
            $text = isset( $item['text'] ) ? trim( (string) $item['text'] ) : '';
            if ( '' !== $text ) {
                $lines[] = $text;
            }
        }

        return implode( "\n", $lines );
    }

    /**
     * Render Schema override CSS once.
     *
     * @return void
     */
    private function render_schema_override_assets() {
        static $printed = false;
        if ( $printed ) {
            return;
        }
        $printed = true;
        ?>
        <style>
            .qiling-schema-override h4 {
                margin: 0 0 8px;
            }
            .qiling-schema-override__grid {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 10px 14px;
            }
            .qiling-schema-override__grid p {
                margin: 0 0 10px;
            }
            .qiling-schema-override .description {
                display: block;
                margin-top: 4px;
            }
            .qiling-schema-override__diagnostics {
                border: 1px solid #e2e8f0;
                border-radius: 8px;
                background: #f8fafc;
                padding: 10px 12px;
                margin-top: 12px;
            }
            .qiling-schema-override__diagnostics > strong {
                display: block;
                margin-bottom: 8px;
                color: #0f172a;
            }
            .qiling-schema-override__diagnostics p {
                margin: 0;
                color: #166534;
            }
            .qiling-schema-override__diagnostics ul {
                display: flex;
                flex-direction: column;
                gap: 8px;
                margin: 0;
            }
            .qiling-schema-override__diagnostics li {
                display: flex;
                gap: 8px;
                margin: 0;
                color: #64748b;
            }
            .qiling-schema-override__diagnostics li strong {
                min-width: 96px;
            }
            .qiling-schema-override__diagnostics li.is-warning strong {
                color: #92400e;
            }
            .qiling-schema-override__diagnostics li.is-error strong {
                color: #b91c1c;
            }
            @media (max-width: 782px) {
                .qiling-schema-override__grid {
                    grid-template-columns: 1fr;
                }
                .qiling-schema-override__diagnostics li {
                    flex-direction: column;
                }
            }
        </style>
        <?php
    }

    public function render_page_header_meta_box( $post ) {
        wp_nonce_field( 'qiling_page_header_nonce', 'page_header_nonce' );

        $hide_header = get_post_meta( $post->ID, '_qiling_hide_page_header', true );
        ?>
        <div class="qiling-page-header-settings">
            <p>
                <label>
                    <input type="checkbox" name="qiling_hide_page_header" value="1" <?php checked( $hide_header, '1' ); ?> />
                    <?php esc_html_e( '隐藏页面头部面包屑', 'developer-starter' ); ?>
                </label>
            </p>
            <p class="description" style="color: #666; font-size: 12px; margin-top: 8px;">
                <?php esc_html_e( '勾选后将不显示页面顶部的标题横幅区域。', 'developer-starter' ); ?><br>
                <?php esc_html_e( '此设置也支持与第三方插件协同控制。', 'developer-starter' ); ?>
            </p>

            <p style="margin-top: 15px; border-top: 1px dashed #eee; padding-top: 15px;">
                <label>
                    <input type="checkbox" name="qiling_transparent_header" value="1" <?php checked( get_post_meta( $post->ID, '_qiling_transparent_header', true ), '1' ); ?> />
                    <?php esc_html_e( '顶部菜单栏首屏透明', 'developer-starter' ); ?>
                </label>
            </p>
            <p class="description" style="color: #666; font-size: 12px; margin-top: 8px;">
                <?php esc_html_e( '开启后，首屏覆盖在 Banner/首屏模块上，滚动后恢复常规背景。系统会按首屏标题明暗自动选择深色或白色菜单文字。', 'developer-starter' ); ?><br>
                <strong><?php esc_html_e( '需要手动指定时：', 'developer-starter' ); ?></strong> <?php esc_html_e( '请在“页面视觉风格 → 顶部菜单栏 → 首屏透明状态文字色”填写颜色；该值优先于自动判断。', 'developer-starter' ); ?>
            </p>
        </div>
        <?php
    }

    public function render_page_visual_style_meta_box( $post ) {
        wp_nonce_field( 'qiling_page_visual_style_nonce', 'page_visual_style_nonce' );

        $settings = function_exists( 'developer_starter_get_post_page_visual_style' )
            ? developer_starter_get_post_page_visual_style( $post->ID )
            : array();
        $footer_settings = function_exists( 'developer_starter_get_post_footer_visual_settings' )
            ? developer_starter_get_post_footer_visual_settings( $post->ID )
            : array( 'mode' => 'inherit', 'wave' => 'inherit', 'preset' => '', 'inherit_skin_colors' => false );
        $footer_presets = array();
        if ( function_exists( 'developer_starter_get_page_visual_skins' ) ) {
            foreach ( developer_starter_get_page_visual_skins() as $footer_preset_key => $footer_preset_skin ) {
                if ( ! is_array( $footer_preset_skin ) || empty( $footer_preset_skin['footer'] ) ) {
                    continue;
                }
                $footer_presets[ sanitize_key( (string) $footer_preset_key ) ] = isset( $footer_preset_skin['label'] ) ? (string) $footer_preset_skin['label'] : (string) $footer_preset_key;
            }
        }
        if ( function_exists( 'developer_starter_get_page_visual_custom_presets' ) ) {
            foreach ( developer_starter_get_page_visual_custom_presets() as $footer_preset_key => $footer_preset ) {
                if ( ! is_array( $footer_preset ) || empty( $footer_preset['skin']['footer'] ) ) {
                    continue;
                }
                $footer_presets[ sanitize_key( (string) $footer_preset_key ) ] = sprintf(
                    /* translators: %s: user-created visual preset label */
                    __( '我的预设：%s', 'developer-starter' ),
                    ! empty( $footer_preset['label'] ) ? (string) $footer_preset['label'] : (string) $footer_preset_key
                );
            }
        }
        $settings = is_array( $settings ) ? $settings : array();
        $mode     = isset( $settings['mode'] ) ? (string) $settings['mode'] : 'inherit';
        $preset   = isset( $settings['preset'] ) ? (string) $settings['preset'] : '';
        $fields   = function_exists( 'developer_starter_get_page_visual_style_fields' )
            ? developer_starter_get_page_visual_style_fields()
            : array();
        $presets  = function_exists( 'developer_starter_get_page_visual_style_presets' )
            ? developer_starter_get_page_visual_style_presets()
            : array();
        $custom_presets = function_exists( 'developer_starter_get_page_visual_custom_presets' )
            ? developer_starter_get_page_visual_custom_presets()
            : array();
        $frontend_preview_url = function_exists( 'get_preview_post_link' )
            ? get_preview_post_link( $post )
            : get_permalink( $post );
        if ( ! is_string( $frontend_preview_url ) || '' === trim( $frontend_preview_url ) ) {
            $frontend_preview_url = get_permalink( $post );
        }
        $frontend_preview_url = is_string( $frontend_preview_url ) ? $frontend_preview_url : '';
        $export_json = function_exists( 'wp_json_encode' )
            ? wp_json_encode( $settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES )
            : json_encode( $settings );
        $export_json = is_string( $export_json ) ? $export_json : '';
        $manager_state = $this->build_page_visual_style_manager_state( $post, $settings, $presets, $custom_presets );
        $reuse_state   = $this->build_page_visual_reuse_state( $post, $settings, $presets, $custom_presets );
        $preview_data  = $this->build_page_visual_preview_data( $post, $presets );
        $preview_json  = function_exists( 'wp_json_encode' )
            ? wp_json_encode( $preview_data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES )
            : json_encode( $preview_data );
        $preview_json  = is_string( $preview_json ) ? $preview_json : '{}';
        ?>
        <div class="qiling-page-visual-style" data-qiling-page-visual-style>
            <?php wp_nonce_field( 'qiling_page_footer_nonce', 'page_footer_nonce' ); ?>
            <textarea hidden readonly data-qiling-page-visual-preview-data><?php echo esc_textarea( $preview_json ); ?></textarea>
            <input type="hidden" name="qiling_page_visual_style_json" value="" data-qiling-page-visual-style-json>
            <div class="qiling-page-visual-style__intro">
                <p><?php esc_html_e( '页面风格管家会先告诉你当前页面正在使用什么风格、哪些区域被单独改过，以及可以怎么恢复。', 'developer-starter' ); ?></p>
            </div>

            <?php $this->render_page_visual_style_manager( $manager_state ); ?>

            <div class="qiling-page-visual-style__top">
                <div class="qiling-page-visual-style__section-head">
                    <strong><?php esc_html_e( '快捷操作', 'developer-starter' ); ?></strong>
                    <span><?php esc_html_e( '先选操作，再保存页面；会覆盖当前页面的风格设置。', 'developer-starter' ); ?></span>
                </div>
                <p>
                    <label for="qiling-page-visual-style-mode"><strong><?php esc_html_e( '当前页面来源模式', 'developer-starter' ); ?></strong></label>
                    <select id="qiling-page-visual-style-mode" name="qiling_page_visual_style[mode]" class="widefat" data-qiling-page-visual-style-mode>
                        <option value="inherit" <?php selected( $mode, 'inherit' ); ?>><?php esc_html_e( '跟随页面模板预设', 'developer-starter' ); ?></option>
                        <option value="global" <?php selected( $mode, 'global' ); ?>><?php esc_html_e( '强制跟随全站默认', 'developer-starter' ); ?></option>
                        <option value="custom" <?php selected( $mode, 'custom' ); ?>><?php esc_html_e( '启用当前页面自定义', 'developer-starter' ); ?></option>
                    </select>
                </p>

                <p>
                    <label for="qiling-page-visual-style-preset"><strong><?php esc_html_e( '套用行业/我的预设', 'developer-starter' ); ?></strong></label>
                    <select id="qiling-page-visual-style-preset" name="qiling_page_visual_style[preset]" class="widefat">
                        <option value=""><?php esc_html_e( '按当前页面模板自动匹配', 'developer-starter' ); ?></option>
                        <?php foreach ( $presets as $preset_key => $preset_label ) : ?>
                            <option value="<?php echo esc_attr( $preset_key ); ?>" <?php selected( $preset, $preset_key ); ?>><?php echo esc_html( $preset_label ); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="button button-secondary qiling-page-visual-style__inline-action" name="qiling_page_visual_style_action" value="apply_preset"><?php esc_html_e( '应用所选预设', 'developer-starter' ); ?></button>
                </p>

                <p>
                    <label for="qiling-page-visual-style-copy-from"><strong><?php esc_html_e( '复制页面风格', 'developer-starter' ); ?></strong></label>
                    <input id="qiling-page-visual-style-copy-from" type="number" name="qiling_page_visual_style_copy_from" value="" class="widefat" min="1" step="1" placeholder="<?php esc_attr_e( '填写页面ID，保存时复制', 'developer-starter' ); ?>">
                    <span class="description"><?php esc_html_e( '填写后，本页会复制该页面的视觉风格设置；留空则保存下面的当前设置。', 'developer-starter' ); ?></span>
                </p>

                <p>
                    <label for="qiling-page-visual-style-save-preset"><strong><?php esc_html_e( '保存为我的预设', 'developer-starter' ); ?></strong></label>
                    <input id="qiling-page-visual-style-save-preset" type="text" name="qiling_page_visual_style_save_preset_name" value="" class="widefat" placeholder="<?php esc_attr_e( '例如：我的科技蓝绿风格', 'developer-starter' ); ?>">
                    <span class="description"><?php esc_html_e( '填写名称并保存页面，会把当前基础预设和细调颜色保存到“基础预设”下拉框，之后其它页面可直接套用。', 'developer-starter' ); ?></span>
                </p>

                <p class="qiling-page-visual-style__actions">
                    <button type="submit" class="button" name="qiling_page_visual_style_action" value="restore_template"><?php esc_html_e( '恢复导入模板默认风格', 'developer-starter' ); ?></button>
                    <button type="submit" class="button" name="qiling_page_visual_style_action" value="restore_global"><?php esc_html_e( '恢复全站默认风格', 'developer-starter' ); ?></button>
                    <button type="submit" class="button button-link-delete" name="qiling_page_visual_style_action" value="clear_custom"><?php esc_html_e( '清除当前页面自定义', 'developer-starter' ); ?></button>
                </p>
            </div>

            <div class="qiling-page-visual-style__top" data-qiling-page-footer-settings>
                <div class="qiling-page-visual-style__section-head">
                    <strong><?php esc_html_e( '当前页页脚策略', 'developer-starter' ); ?></strong>
                    <span><?php esc_html_e( '先决定页脚来源，再在“底部和波浪”中做当前页的显式覆盖。', 'developer-starter' ); ?></span>
                </div>
                <p>
                    <label><strong><?php esc_html_e( '页脚来源', 'developer-starter' ); ?></strong></label>
                    <select class="widefat" name="qiling_page_footer[mode]" data-qiling-page-footer-mode>
                        <option value="inherit" <?php selected( $footer_settings['mode'], 'inherit' ); ?>><?php esc_html_e( '完整跟随全局页脚', 'developer-starter' ); ?></option>
                        <option value="page_skin" <?php selected( $footer_settings['mode'], 'page_skin' ); ?>><?php esc_html_e( '使用当前页面皮肤页脚', 'developer-starter' ); ?></option>
                        <option value="preset" <?php selected( $footer_settings['mode'], 'preset' ); ?>><?php esc_html_e( '使用指定页脚预设', 'developer-starter' ); ?></option>
                        <option value="hidden" <?php selected( $footer_settings['mode'], 'hidden' ); ?>><?php esc_html_e( '当前页隐藏网站页脚', 'developer-starter' ); ?></option>
                    </select>
                </p>
                <p data-qiling-page-footer-preset-row>
                    <label><strong><?php esc_html_e( '指定页脚预设', 'developer-starter' ); ?></strong></label>
                    <select class="widefat" name="qiling_page_footer[preset]">
                        <option value=""><?php esc_html_e( '请选择页脚预设', 'developer-starter' ); ?></option>
                        <?php foreach ( $footer_presets as $footer_preset_key => $footer_preset_label ) : ?>
                            <option value="<?php echo esc_attr( $footer_preset_key ); ?>" <?php selected( $footer_settings['preset'], $footer_preset_key ); ?>><?php echo esc_html( $footer_preset_label ); ?></option>
                        <?php endforeach; ?>
                    </select>
                </p>
                <p data-qiling-page-footer-wave-row>
                    <label><strong><?php esc_html_e( '波浪衔接', 'developer-starter' ); ?></strong></label>
                    <select class="widefat" name="qiling_page_footer[wave]">
                        <option value="inherit" <?php selected( $footer_settings['wave'], 'inherit' ); ?>><?php esc_html_e( '跟随所选页脚', 'developer-starter' ); ?></option>
                        <option value="on" <?php selected( $footer_settings['wave'], 'on' ); ?>><?php esc_html_e( '当前页开启', 'developer-starter' ); ?></option>
                        <option value="off" <?php selected( $footer_settings['wave'], 'off' ); ?>><?php esc_html_e( '当前页关闭', 'developer-starter' ); ?></option>
                    </select>
                </p>
                <p data-qiling-page-footer-inherit-row>
                    <label><input type="checkbox" name="qiling_page_footer[inherit_skin_colors]" value="1" <?php checked( ! empty( $footer_settings['inherit_skin_colors'] ) ); ?>> <?php esc_html_e( '保留全局页脚结构，但使用当前页面皮肤配色', 'developer-starter' ); ?></label>
                </p>
                <p class="description"><?php esc_html_e( '“完整跟随全局”不会被模板自动改色；只有选择页面皮肤、指定预设或启用皮肤配色时才带入当前页面色系。', 'developer-starter' ); ?></p>
            </div>

            <div class="qiling-page-visual-style__assistant" data-qiling-page-visual-audit>
                <div class="qiling-page-visual-style__assistant-head">
                    <div>
                        <strong><?php esc_html_e( '防改乱系统', 'developer-starter' ); ?></strong>
                        <span><?php esc_html_e( '实时检查顶部、搜索、电话、按钮、CTA、底部波浪和模块卡片，避免颜色改乱后看不清。', 'developer-starter' ); ?></span>
                    </div>
                    <div class="qiling-page-visual-style__assistant-actions">
                        <button type="button" class="button button-secondary" data-qiling-page-visual-auto-text><?php esc_html_e( '只修复文字色', 'developer-starter' ); ?></button>
                        <button type="button" class="button button-primary" data-qiling-page-visual-fix-all><?php esc_html_e( '一键修复全部', 'developer-starter' ); ?></button>
                    </div>
                </div>
                <div class="qiling-page-visual-style__audit-summary" data-qiling-page-visual-audit-summary></div>
                <ul class="qiling-page-visual-style__audit-list" data-qiling-page-visual-audit-list></ul>
            </div>

            <script>
                (function () {
                    var root = document.currentScript && document.currentScript.closest('[data-qiling-page-visual-style]');
                    if (!root) return;
                    var mode = root.querySelector('[data-qiling-page-footer-mode]');
                    var presetRow = root.querySelector('[data-qiling-page-footer-preset-row]');
                    var waveRow = root.querySelector('[data-qiling-page-footer-wave-row]');
                    var inheritRow = root.querySelector('[data-qiling-page-footer-inherit-row]');
                    function syncFooterRows() {
                        var value = mode ? mode.value : 'inherit';
                        if (presetRow) presetRow.hidden = value !== 'preset';
                        if (waveRow) waveRow.hidden = value === 'hidden';
                        if (inheritRow) inheritRow.hidden = value !== 'inherit';
                    }
                    if (mode) mode.addEventListener('change', syncFooterRows);
                    syncFooterRows();
                })();
            </script>

            <div class="qiling-page-visual-style__toolbox">
                <div>
                    <strong><?php esc_html_e( '智能配色', 'developer-starter' ); ?></strong>
                    <span><?php esc_html_e( '根据页面主色自动生成顶部、搜索、按钮、底部和波浪颜色。', 'developer-starter' ); ?></span>
                </div>
                <button type="button" class="button button-secondary" data-qiling-page-visual-generate-palette><?php esc_html_e( '根据主色生成整套搭配', 'developer-starter' ); ?></button>
            </div>

            <div class="qiling-page-visual-style__preview" data-qiling-page-visual-preview>
                <div class="qiling-page-visual-style__preview-head">
                    <strong><?php esc_html_e( '实时小样预览', 'developer-starter' ); ?></strong>
                    <span><?php esc_html_e( '展示顶部、搜索、电话、按钮、底部和波浪的大致搭配。', 'developer-starter' ); ?></span>
                </div>
                <div class="qiling-page-visual-style__preview-surface" data-qiling-page-visual-preview-surface>
                    <div class="qiling-page-visual-style__preview-header" data-qiling-page-visual-preview-header>
                        <strong data-qiling-page-visual-preview-logo><?php esc_html_e( '启灵', 'developer-starter' ); ?></strong>
                        <div class="qiling-page-visual-style__preview-nav" data-qiling-page-visual-preview-nav>
                            <span><?php esc_html_e( '首页', 'developer-starter' ); ?></span>
                            <span><?php esc_html_e( '服务', 'developer-starter' ); ?></span>
                            <span><?php esc_html_e( '案例', 'developer-starter' ); ?></span>
                        </div>
                        <div class="qiling-page-visual-style__preview-search" data-qiling-page-visual-preview-search><?php esc_html_e( '搜索关键词', 'developer-starter' ); ?></div>
                        <div class="qiling-page-visual-style__preview-phone" data-qiling-page-visual-preview-phone>18888888888</div>
                    </div>
                    <div class="qiling-page-visual-style__preview-main" data-qiling-page-visual-preview-main>
                        <div class="qiling-page-visual-style__preview-copy">
                            <span><?php esc_html_e( '页面内容', 'developer-starter' ); ?></span>
                            <h4><?php esc_html_e( '页面主视觉', 'developer-starter' ); ?></h4>
                            <p><?php esc_html_e( '这里模拟正文区域、按钮和模块卡片的搭配。', 'developer-starter' ); ?></p>
                            <div class="qiling-page-visual-style__preview-buttons">
                                <button type="button" class="qiling-page-visual-style__preview-button-normal" data-qiling-page-visual-preview-button-normal><?php esc_html_e( '普通按钮', 'developer-starter' ); ?></button>
                                <button type="button" class="qiling-page-visual-style__preview-button-cta" data-qiling-page-visual-preview-button-cta><?php esc_html_e( '立即咨询', 'developer-starter' ); ?></button>
                            </div>
                        </div>
                        <div class="qiling-page-visual-style__preview-card" data-qiling-page-visual-preview-card>
                            <span><?php esc_html_e( '模块卡片', 'developer-starter' ); ?></span>
                            <strong data-qiling-page-visual-preview-card-title><?php esc_html_e( '服务亮点', 'developer-starter' ); ?></strong>
                            <p data-qiling-page-visual-preview-card-text><?php esc_html_e( '卡片背景、边框和文字会跟随页面基础色变化。', 'developer-starter' ); ?></p>
                            <button type="button" data-qiling-page-visual-preview-card-button><?php esc_html_e( '查看详情', 'developer-starter' ); ?></button>
                        </div>
                    </div>
                    <div class="qiling-page-visual-style__preview-footer" data-qiling-page-visual-preview-footer>
                        <div class="qiling-page-visual-style__preview-wave">
                            <span data-qiling-page-visual-preview-wave-layer></span>
                            <span data-qiling-page-visual-preview-wave></span>
                        </div>
                        <strong><?php esc_html_e( '底部信息', 'developer-starter' ); ?></strong>
                        <p><?php esc_html_e( '这里模拟页脚文字和链接颜色。', 'developer-starter' ); ?></p>
                    </div>
                </div>
            </div>

            <?php if ( '' !== $frontend_preview_url ) : ?>
                <div class="qiling-page-visual-style__live-preview" data-qiling-page-visual-live-preview>
                    <div class="qiling-page-visual-style__live-head">
                        <div>
                            <strong><?php esc_html_e( '真实页面预览', 'developer-starter' ); ?></strong>
                            <span><?php esc_html_e( '加载当前页面，并把未保存的颜色变量临时同步到预览窗口。', 'developer-starter' ); ?></span>
                        </div>
                        <div class="qiling-page-visual-style__live-actions">
                            <button type="button" class="button button-secondary" data-qiling-page-visual-live-refresh><?php esc_html_e( '刷新预览', 'developer-starter' ); ?></button>
                            <button type="button" class="button button-primary" data-qiling-page-visual-live-sync><?php esc_html_e( '同步当前颜色', 'developer-starter' ); ?></button>
                        </div>
                    </div>
                    <iframe title="<?php esc_attr_e( '页面视觉真实预览', 'developer-starter' ); ?>" loading="lazy" src="<?php echo esc_url( $frontend_preview_url ); ?>" data-qiling-page-visual-live-frame></iframe>
                </div>
            <?php endif; ?>

            <div class="qiling-page-visual-style__package">
                <div class="qiling-page-visual-style__package-col">
                    <div class="qiling-page-visual-style__package-head">
                        <strong><?php esc_html_e( '导出当前风格', 'developer-starter' ); ?></strong>
                        <button type="button" class="button button-secondary" data-qiling-page-visual-copy-export><?php esc_html_e( '复制风格 JSON', 'developer-starter' ); ?></button>
                    </div>
                    <textarea class="widefat code" rows="8" readonly data-qiling-page-visual-export><?php echo esc_textarea( $export_json ); ?></textarea>
                </div>
                <div class="qiling-page-visual-style__package-col">
                    <label for="qiling-page-visual-style-import"><strong><?php esc_html_e( '导入风格 JSON', 'developer-starter' ); ?></strong></label>
                    <textarea id="qiling-page-visual-style-import" class="widefat code" rows="8" name="qiling_page_visual_style_import_json" placeholder="<?php esc_attr_e( '粘贴页面视觉风格 JSON，保存页面后应用。', 'developer-starter' ); ?>"></textarea>
                    <span class="description"><?php esc_html_e( '支持直接粘贴导出的风格 JSON，也支持粘贴包含 visual_style 字段的完整页面包 JSON。', 'developer-starter' ); ?></span>
                </div>
            </div>

            <div class="qiling-page-visual-style__ops">
                <div class="qiling-page-visual-style__ops-card">
                    <strong><?php esc_html_e( '批量应用到页面', 'developer-starter' ); ?></strong>
                    <label for="qiling-page-visual-style-bulk-source"><span class="screen-reader-text"><?php esc_html_e( '选择批量应用的风格来源', 'developer-starter' ); ?></span></label>
                    <select id="qiling-page-visual-style-bulk-source" class="widefat" name="qiling_page_visual_style_bulk_source">
                        <option value="current"><?php esc_html_e( '当前页面风格', 'developer-starter' ); ?></option>
                        <option value="inherit"><?php esc_html_e( '恢复到导入模板时的默认风格', 'developer-starter' ); ?></option>
                        <option value="global"><?php esc_html_e( '全站默认风格', 'developer-starter' ); ?></option>
                        <?php if ( ! empty( $presets ) ) : ?>
                            <optgroup label="<?php esc_attr_e( '套用行业/我的预设', 'developer-starter' ); ?>">
                                <?php foreach ( $presets as $bulk_preset_key => $bulk_preset_label ) : ?>
                                    <option value="<?php echo esc_attr( 'preset:' . sanitize_key( (string) $bulk_preset_key ) ); ?>"><?php echo esc_html( (string) $bulk_preset_label ); ?></option>
                                <?php endforeach; ?>
                            </optgroup>
                        <?php endif; ?>
                    </select>
                    <textarea class="widefat" rows="3" name="qiling_page_visual_style_bulk_page_ids" placeholder="<?php esc_attr_e( '填写页面ID，多个ID用逗号、空格或换行分隔', 'developer-starter' ); ?>"></textarea>
                    <span class="description"><?php esc_html_e( '点击下方按钮后，会把所选风格保存到这些页面。当前页面本身仍会正常保存上方设置。', 'developer-starter' ); ?></span>
                    <p><button type="submit" class="button button-secondary" name="qiling_page_visual_style_action" value="bulk_apply"><?php esc_html_e( '批量应用所选风格', 'developer-starter' ); ?></button></p>
                </div>

                <div class="qiling-page-visual-style__ops-card">
                    <strong><?php esc_html_e( '我的预设管理', 'developer-starter' ); ?></strong>
                    <?php if ( ! empty( $custom_presets ) ) : ?>
                        <select class="widefat" name="qiling_page_visual_custom_preset_key">
                            <option value=""><?php esc_html_e( '选择我的预设', 'developer-starter' ); ?></option>
                            <?php foreach ( $custom_presets as $custom_preset_key => $custom_preset ) : ?>
                                <option value="<?php echo esc_attr( $custom_preset_key ); ?>"><?php echo esc_html( isset( $custom_preset['label'] ) ? (string) $custom_preset['label'] : (string) $custom_preset_key ); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <input type="text" class="widefat" name="qiling_page_visual_custom_preset_label" value="" placeholder="<?php esc_attr_e( '新名称，留空则只删除', 'developer-starter' ); ?>">
                        <p class="qiling-page-visual-style__ops-actions">
                            <button type="submit" class="button button-secondary" name="qiling_page_visual_style_action" value="rename_custom_preset"><?php esc_html_e( '重命名预设', 'developer-starter' ); ?></button>
                            <button type="submit" class="button button-link-delete" name="qiling_page_visual_style_action" value="delete_custom_preset"><?php esc_html_e( '删除预设', 'developer-starter' ); ?></button>
                        </p>
                    <?php else : ?>
                        <span class="description"><?php esc_html_e( '还没有保存过“我的预设”。', 'developer-starter' ); ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <?php $this->render_page_visual_reuse_usage( $reuse_state ); ?>

            <div class="qiling-page-visual-style__groups" data-qiling-page-visual-style-fields>
                <?php foreach ( $fields as $group_key => $group ) : ?>
                    <?php
                    $group_key = sanitize_key( (string) $group_key );
                    if ( '' === $group_key || empty( $group['fields'] ) || ! is_array( $group['fields'] ) ) {
                        continue;
                    }

                    $group_values = isset( $settings[ $group_key ] ) && is_array( $settings[ $group_key ] ) ? $settings[ $group_key ] : array();
                    ?>
                    <section class="qiling-page-visual-style__group">
                        <h4><?php echo esc_html( isset( $group['label'] ) ? (string) $group['label'] : $group_key ); ?></h4>
                        <?php if ( ! empty( $group['description'] ) ) : ?>
                            <p class="description"><?php echo esc_html( (string) $group['description'] ); ?></p>
                        <?php endif; ?>
                        <div class="qiling-page-visual-style__grid">
                            <?php foreach ( $group['fields'] as $field_key => $field ) : ?>
                                <?php
                                $field_key = sanitize_key( (string) $field_key );
                                if ( '' === $field_key ) {
                                    continue;
                                }

                                $field_type  = isset( $field['type'] ) ? sanitize_key( (string) $field['type'] ) : 'css';
                                $field_value = isset( $group_values[ $field_key ] ) && is_scalar( $group_values[ $field_key ] ) ? (string) $group_values[ $field_key ] : '';
                                $input_name  = 'qiling_page_visual_style[' . $group_key . '][' . $field_key . ']';
                                ?>
                                <p class="qiling-page-visual-style__field">
                                    <label>
                                        <span><?php echo esc_html( isset( $field['label'] ) ? (string) $field['label'] : $field_key ); ?></span>
                                        <?php if ( 'opacity' === $field_type ) : ?>
                                            <input type="number" name="<?php echo esc_attr( $input_name ); ?>" value="<?php echo esc_attr( $field_value ); ?>" class="widefat" min="0" max="1" step="0.01" placeholder="<?php echo esc_attr( isset( $field['placeholder'] ) ? (string) $field['placeholder'] : '' ); ?>" data-qiling-page-visual-input data-qiling-page-visual-group="<?php echo esc_attr( $group_key ); ?>" data-qiling-page-visual-key="<?php echo esc_attr( $field_key ); ?>">
                                        <?php elseif ( 'select' === $field_type && ! empty( $field['options'] ) && is_array( $field['options'] ) ) : ?>
                                            <select name="<?php echo esc_attr( $input_name ); ?>" class="widefat" data-qiling-page-visual-input data-qiling-page-visual-group="<?php echo esc_attr( $group_key ); ?>" data-qiling-page-visual-key="<?php echo esc_attr( $field_key ); ?>">
                                                <option value=""><?php esc_html_e( '跟随默认', 'developer-starter' ); ?></option>
                                                <?php foreach ( $field['options'] as $option_value => $option_label ) : ?>
                                                    <option value="<?php echo esc_attr( (string) $option_value ); ?>" <?php selected( $field_value, (string) $option_value ); ?>><?php echo esc_html( (string) $option_label ); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        <?php else : ?>
                                            <input type="text" name="<?php echo esc_attr( $input_name ); ?>" value="<?php echo esc_attr( $field_value ); ?>" class="widefat" placeholder="<?php echo esc_attr( isset( $field['placeholder'] ) ? (string) $field['placeholder'] : '' ); ?>" data-qiling-page-visual-input data-qiling-page-visual-group="<?php echo esc_attr( $group_key ); ?>" data-qiling-page-visual-key="<?php echo esc_attr( $field_key ); ?>">
                                        <?php endif; ?>
                                    </label>
                                </p>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endforeach; ?>
            </div>
        </div>
        <style>
            .qiling-page-visual-style__intro {
                margin-bottom: 12px;
            }
            .qiling-page-visual-style__intro p {
                margin: 0;
            }
            .qiling-page-style-manager {
                margin: 0 0 14px;
                border: 1px solid #c3c4c7;
                border-radius: 8px;
                background: #ffffff;
                overflow: hidden;
            }
            .qiling-page-style-manager__head {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 12px;
                padding: 14px 16px;
                background: #f6f7f7;
                border-bottom: 1px solid #dcdcde;
            }
            .qiling-page-style-manager__head h3 {
                margin: 0;
                font-size: 15px;
                line-height: 1.35;
            }
            .qiling-page-style-manager__head p {
                margin: 3px 0 0;
                color: #646970;
            }
            .qiling-page-style-manager__badge {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                min-height: 26px;
                padding: 3px 9px;
                border: 1px solid #c3c4c7;
                border-radius: 999px;
                background: #ffffff;
                color: #1d2327;
                font-size: 12px;
                font-weight: 600;
                white-space: nowrap;
            }
            .qiling-page-style-manager__badge--custom {
                border-color: #93c5fd;
                background: #eff6ff;
                color: #1d4ed8;
            }
            .qiling-page-style-manager__badge--preset {
                border-color: #a7f3d0;
                background: #ecfdf5;
                color: #047857;
            }
            .qiling-page-style-manager__badge--global {
                border-color: #e5e7eb;
                background: #f9fafb;
                color: #374151;
            }
            .qiling-page-style-manager__grid {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 12px;
                padding: 14px 16px 0;
            }
            .qiling-page-style-manager__card {
                min-width: 0;
                padding: 12px;
                border: 1px solid #dcdcde;
                border-radius: 8px;
                background: #ffffff;
            }
            .qiling-page-style-manager__card span,
            .qiling-page-style-manager__source span,
            .qiling-page-style-manager__overrides span {
                display: block;
                color: #646970;
                font-size: 12px;
            }
            .qiling-page-style-manager__card strong {
                display: block;
                margin-top: 4px;
                font-size: 14px;
                line-height: 1.4;
                overflow-wrap: anywhere;
            }
            .qiling-page-style-manager__sources {
                display: grid;
                grid-template-columns: repeat(4, minmax(0, 1fr));
                gap: 10px;
                padding: 14px 16px 0;
            }
            .qiling-page-style-manager__source {
                min-width: 0;
                padding: 10px;
                border: 1px solid #e5e7eb;
                border-radius: 8px;
                background: #f9fafb;
            }
            .qiling-page-style-manager__source strong {
                display: block;
                margin-top: 3px;
                color: #1d2327;
                overflow-wrap: anywhere;
            }
            .qiling-page-style-manager__overrides {
                margin: 14px 16px 16px;
                padding: 12px;
                border: 1px solid #e5e7eb;
                border-radius: 8px;
                background: #f8fafc;
            }
            .qiling-page-style-manager__override-list {
                display: flex;
                flex-wrap: wrap;
                gap: 8px;
                margin: 10px 0 0;
            }
            .qiling-page-style-manager__override-item {
                display: inline-flex;
                align-items: center;
                max-width: 100%;
                padding: 5px 8px;
                border: 1px solid #bfdbfe;
                border-radius: 999px;
                background: #eff6ff;
                color: #1d4ed8;
                font-size: 12px;
                font-weight: 600;
                overflow-wrap: anywhere;
            }
            .qiling-page-visual-style__top {
                margin: 0 0 14px;
                padding: 12px;
                border: 1px solid #dcdcde;
                border-radius: 8px;
                background: #ffffff;
            }
            .qiling-page-visual-style__section-head {
                margin: 0 0 10px;
            }
            .qiling-page-visual-style__section-head strong,
            .qiling-page-visual-style__section-head span {
                display: block;
            }
            .qiling-page-visual-style__section-head span {
                margin-top: 3px;
                color: #646970;
                font-size: 12px;
            }
            .qiling-page-visual-style__top p {
                margin: 0 0 12px;
            }
            .qiling-page-visual-style__top p:last-child {
                margin-bottom: 0;
            }
            .qiling-page-visual-style__inline-action {
                margin-top: 8px;
            }
            .qiling-page-visual-style__actions {
                display: flex;
                flex-wrap: wrap;
                gap: 8px;
            }
            .qiling-page-visual-style__assistant {
                margin: 0 0 14px;
                padding: 12px;
                border: 1px solid #c7d2fe;
                border-radius: 8px;
                background: #f8fafc;
            }
            .qiling-page-visual-style__assistant-head {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 12px;
                margin-bottom: 10px;
            }
            .qiling-page-visual-style__assistant-actions {
                display: flex;
                flex-wrap: wrap;
                justify-content: flex-end;
                gap: 8px;
            }
            .qiling-page-visual-style__assistant-head strong,
            .qiling-page-visual-style__assistant-head span {
                display: block;
            }
            .qiling-page-visual-style__assistant-head span {
                margin-top: 3px;
                color: #64748b;
                font-size: 12px;
            }
            .qiling-page-visual-style__audit-summary {
                display: flex;
                flex-wrap: wrap;
                gap: 8px;
                margin: 0 0 8px;
                color: #475569;
                font-size: 12px;
            }
            .qiling-page-visual-style__audit-summary span {
                display: inline-flex;
                align-items: center;
                min-height: 22px;
                padding: 0 8px;
                border: 1px solid #dbe3ef;
                border-radius: 999px;
                background: #ffffff;
            }
            .qiling-page-visual-style__audit-list {
                display: grid;
                gap: 6px;
                margin: 0;
            }
            .qiling-page-visual-style__audit-item {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 10px;
                margin: 0;
                padding: 9px 10px;
                border-left: 3px solid #94a3b8;
                border-radius: 5px;
                background: #ffffff;
                color: #334155;
            }
            .qiling-page-visual-style__audit-item-content {
                min-width: 0;
            }
            .qiling-page-visual-style__audit-item-content strong,
            .qiling-page-visual-style__audit-item-content span {
                display: block;
            }
            .qiling-page-visual-style__audit-item-content span {
                margin-top: 3px;
                font-size: 12px;
                line-height: 1.45;
            }
            .qiling-page-visual-style__audit-item .button {
                flex: 0 0 auto;
                min-height: 28px;
                line-height: 26px;
            }
            .qiling-page-visual-style__audit-item--danger {
                border-left-color: #dc2626;
                background: #fef2f2;
                color: #991b1b;
            }
            .qiling-page-visual-style__audit-item--warning {
                border-left-color: #f59e0b;
                background: #fffbeb;
                color: #92400e;
            }
            .qiling-page-visual-style__audit-item--tip {
                border-left-color: #2563eb;
                background: #eff6ff;
                color: #1d4ed8;
            }
            .qiling-page-visual-style__audit-item--ok {
                border-left-color: #16a34a;
                background: #f0fdf4;
                color: #166534;
            }
            .qiling-page-visual-style__toolbox,
            .qiling-page-visual-style__live-head {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 12px;
            }
            .qiling-page-visual-style__toolbox {
                margin: 0 0 14px;
                padding: 12px;
                border: 1px solid #bae6fd;
                border-radius: 8px;
                background: #f0f9ff;
            }
            .qiling-page-visual-style__toolbox strong,
            .qiling-page-visual-style__toolbox span,
            .qiling-page-visual-style__live-head strong,
            .qiling-page-visual-style__live-head span {
                display: block;
            }
            .qiling-page-visual-style__toolbox span,
            .qiling-page-visual-style__live-head span {
                margin-top: 3px;
                color: #64748b;
                font-size: 12px;
            }
            .qiling-page-visual-style__preview {
                margin: 0 0 14px;
                padding: 12px;
                border: 1px solid #dcdcde;
                border-radius: 8px;
                background: #ffffff;
            }
            .qiling-page-visual-style__preview-head {
                display: flex;
                align-items: baseline;
                gap: 8px;
                margin-bottom: 10px;
            }
            .qiling-page-visual-style__preview-head span {
                color: #64748b;
                font-size: 12px;
            }
            .qiling-page-visual-style__preview-surface {
                overflow: hidden;
                border: 1px solid #e2e8f0;
                border-radius: 10px;
                background: #f7fbff;
                color: #25365f;
            }
            .qiling-page-visual-style__preview-header {
                display: grid;
                grid-template-columns: auto 1fr minmax(120px, 180px) auto;
                align-items: center;
                gap: 10px;
                padding: 10px 12px;
                background: rgba(255, 255, 255, 0.88);
                color: #25365f;
            }
            .qiling-page-visual-style__preview-header strong {
                font-size: 18px;
                line-height: 1;
            }
            .qiling-page-visual-style__preview-nav {
                display: flex;
                gap: 8px;
                min-width: 0;
            }
            .qiling-page-visual-style__preview-nav span {
                font-size: 12px;
                font-weight: 600;
            }
            .qiling-page-visual-style__preview-search,
            .qiling-page-visual-style__preview-phone {
                overflow: hidden;
                padding: 7px 10px;
                border-radius: 999px;
                background: rgba(255, 255, 255, 0.76);
                font-size: 12px;
                font-weight: 600;
                line-height: 1.2;
                text-overflow: ellipsis;
                white-space: nowrap;
            }
            .qiling-page-visual-style__preview-main {
                display: grid;
                grid-template-columns: minmax(0, 1fr) minmax(180px, 240px);
                align-items: stretch;
                gap: 14px;
                padding: 24px;
            }
            .qiling-page-visual-style__preview-copy,
            .qiling-page-visual-style__preview-card {
                min-width: 0;
            }
            .qiling-page-visual-style__preview-copy span,
            .qiling-page-visual-style__preview-card span {
                display: inline-block;
                margin-bottom: 6px;
                font-size: 11px;
                font-weight: 700;
                text-transform: uppercase;
            }
            .qiling-page-visual-style__preview-main h4 {
                margin: 0 0 5px;
                font-size: 20px;
                line-height: 1.2;
            }
            .qiling-page-visual-style__preview-main p {
                margin: 0;
                color: inherit;
                opacity: .72;
            }
            .qiling-page-visual-style__preview-buttons {
                display: flex;
                flex-wrap: wrap;
                gap: 8px;
                margin-top: 14px;
            }
            .qiling-page-visual-style__preview-main button,
            .qiling-page-visual-style__preview-card button {
                flex: 0 0 auto;
                min-width: 104px;
                min-height: 36px;
                border: 0;
                border-radius: 999px;
                background: #4f7dff;
                color: #ffffff;
                font-weight: 700;
                cursor: default;
            }
            .qiling-page-visual-style__preview-button-normal {
                box-shadow: 0 8px 20px rgba(15, 23, 42, 0.12);
            }
            .qiling-page-visual-style__preview-button-cta {
                box-shadow: 0 12px 28px rgba(15, 23, 42, 0.18);
            }
            .qiling-page-visual-style__preview-card {
                display: grid;
                align-content: start;
                gap: 8px;
                padding: 14px;
                border: 1px solid rgba(15, 23, 42, 0.1);
                border-radius: 8px;
                background: #ffffff;
                box-shadow: 0 14px 34px rgba(15, 23, 42, 0.08);
            }
            .qiling-page-visual-style__preview-card strong {
                display: block;
                font-size: 15px;
                line-height: 1.35;
            }
            .qiling-page-visual-style__preview-card p {
                margin: 0;
                font-size: 12px;
                line-height: 1.55;
                opacity: .72;
            }
            .qiling-page-visual-style__preview-card button {
                justify-self: start;
                min-width: 92px;
                min-height: 32px;
                margin-top: 4px;
                font-size: 12px;
            }
            .qiling-page-visual-style__preview-footer {
                position: relative;
                padding: 34px 20px 18px;
                background: #4f7dff;
                color: #ffffff;
            }
            .qiling-page-visual-style__preview-footer strong,
            .qiling-page-visual-style__preview-footer p {
                position: relative;
                z-index: 1;
            }
            .qiling-page-visual-style__preview-footer p {
                margin: 5px 0 0;
                opacity: .78;
            }
            .qiling-page-visual-style__preview-wave {
                position: absolute;
                top: 0;
                right: 0;
                left: 0;
                height: 28px;
                transform: translateY(-1px);
            }
            .qiling-page-visual-style__preview-wave span {
                position: absolute;
                right: -5%;
                left: -5%;
                height: 26px;
                border-radius: 0 0 50% 50%;
                background: #dff7ff;
            }
            .qiling-page-visual-style__preview-wave span:first-child {
                top: 4px;
                background: #e8efff;
                opacity: .5;
            }
            .qiling-page-visual-style__preview-wave span:last-child {
                top: -5px;
            }
            .qiling-page-visual-style__live-preview {
                margin: 0 0 14px;
                padding: 12px;
                border: 1px solid #dcdcde;
                border-radius: 8px;
                background: #ffffff;
            }
            .qiling-page-visual-style__live-head {
                margin-bottom: 10px;
            }
            .qiling-page-visual-style__live-actions {
                display: flex;
                flex-wrap: wrap;
                gap: 8px;
            }
            .qiling-page-visual-style__live-preview iframe {
                display: block;
                width: 100%;
                min-height: 420px;
                border: 1px solid #e2e8f0;
                border-radius: 8px;
                background: #ffffff;
            }
            .qiling-page-visual-style__package {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 12px;
                margin: 0 0 14px;
            }
            .qiling-page-visual-style__package-col {
                padding: 12px;
                border: 1px solid #dcdcde;
                border-radius: 8px;
                background: #ffffff;
            }
            .qiling-page-visual-style__package-head {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 10px;
                margin-bottom: 8px;
            }
            .qiling-page-visual-style__package textarea {
                box-sizing: border-box;
                min-height: 168px;
                resize: vertical;
                font-size: 12px;
            }
            .qiling-page-visual-style__ops {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 12px;
                margin: 0 0 14px;
            }
            .qiling-page-visual-style__ops-card {
                display: grid;
                gap: 8px;
                padding: 12px;
                border: 1px solid #dcdcde;
                border-radius: 8px;
                background: #ffffff;
            }
            .qiling-page-visual-style__ops-card p {
                margin: 0;
            }
            .qiling-page-visual-style__ops-actions {
                display: flex;
                flex-wrap: wrap;
                gap: 8px;
            }
            .qiling-page-visual-style__usage {
                margin: 0 0 14px;
                padding: 12px;
                border: 1px solid #dbe3ef;
                border-radius: 8px;
                background: #f8fafc;
            }
            .qiling-page-visual-style__usage-head {
                display: flex;
                align-items: flex-start;
                justify-content: space-between;
                gap: 12px;
                margin-bottom: 10px;
            }
            .qiling-page-visual-style__usage-head strong,
            .qiling-page-visual-style__usage-head span {
                display: block;
            }
            .qiling-page-visual-style__usage-head span {
                margin-top: 3px;
                color: #64748b;
                font-size: 12px;
            }
            .qiling-page-visual-style__usage-grid {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 10px;
            }
            .qiling-page-visual-style__usage-card {
                display: grid;
                gap: 8px;
                padding: 10px;
                border: 1px solid #e2e8f0;
                border-radius: 8px;
                background: #ffffff;
            }
            .qiling-page-visual-style__usage-card.is-current {
                border-color: #4f7dff;
                box-shadow: 0 0 0 1px rgba(79, 125, 255, 0.14);
            }
            .qiling-page-visual-style__usage-card-head {
                display: flex;
                align-items: flex-start;
                justify-content: space-between;
                gap: 10px;
            }
            .qiling-page-visual-style__usage-count {
                flex: 0 0 auto;
                padding: 2px 8px;
                border-radius: 999px;
                background: #eff6ff;
                color: #1d4ed8;
                font-size: 12px;
                font-weight: 600;
            }
            .qiling-page-visual-style__usage-pages {
                display: flex;
                flex-wrap: wrap;
                gap: 6px;
            }
            .qiling-page-visual-style__usage-pages a,
            .qiling-page-visual-style__usage-pages span {
                display: inline-flex;
                align-items: center;
                min-height: 24px;
                padding: 0 8px;
                border-radius: 999px;
                background: #f1f5f9;
                color: #334155;
                font-size: 12px;
                text-decoration: none;
            }
            @media (max-width: 782px) {
                .qiling-page-visual-style__assistant-head,
                .qiling-page-visual-style__toolbox,
                .qiling-page-visual-style__live-head {
                    align-items: flex-start;
                    flex-direction: column;
                }
                .qiling-page-visual-style__assistant-actions {
                    justify-content: flex-start;
                }
                .qiling-page-visual-style__audit-item {
                    align-items: flex-start;
                    flex-direction: column;
                }
                .qiling-page-style-manager__head {
                    align-items: flex-start;
                    flex-direction: column;
                }
                .qiling-page-style-manager__grid,
                .qiling-page-style-manager__sources {
                    grid-template-columns: 1fr;
                }
                .qiling-page-visual-style__package,
                .qiling-page-visual-style__ops {
                    grid-template-columns: 1fr;
                }
                .qiling-page-visual-style__usage-grid {
                    grid-template-columns: 1fr;
                }
                .qiling-page-visual-style__preview-head,
                .qiling-page-visual-style__preview-main {
                    align-items: flex-start;
                    flex-direction: column;
                }
                .qiling-page-visual-style__preview-main {
                    grid-template-columns: 1fr;
                }
                .qiling-page-visual-style__preview-header {
                    grid-template-columns: 1fr;
                }
                .qiling-page-visual-style__preview-nav {
                    flex-wrap: wrap;
                }
            }
        </style>
        <script>
        (function() {
            var root = document.querySelector('[data-qiling-page-visual-style]');
            if (!root) {
                return;
            }
            var mode = root.querySelector('[data-qiling-page-visual-style-mode]');
            var presetSelect = root.querySelector('#qiling-page-visual-style-preset');
            var fields = root.querySelector('[data-qiling-page-visual-style-fields]');
            var settingsJsonInput = root.querySelector('[data-qiling-page-visual-style-json]');
            var auditList = root.querySelector('[data-qiling-page-visual-audit-list]');
            var auditSummary = root.querySelector('[data-qiling-page-visual-audit-summary]');
            var autoTextButton = root.querySelector('[data-qiling-page-visual-auto-text]');
            var fixAllButton = root.querySelector('[data-qiling-page-visual-fix-all]');
            var generatePaletteButton = root.querySelector('[data-qiling-page-visual-generate-palette]');
            var exportText = root.querySelector('[data-qiling-page-visual-export]');
            var copyExportButton = root.querySelector('[data-qiling-page-visual-copy-export]');
            var liveFrame = root.querySelector('[data-qiling-page-visual-live-frame]');
            var liveRefreshButton = root.querySelector('[data-qiling-page-visual-live-refresh]');
            var liveSyncButton = root.querySelector('[data-qiling-page-visual-live-sync]');
            var previewParts = {
                surface: root.querySelector('[data-qiling-page-visual-preview-surface]'),
                header: root.querySelector('[data-qiling-page-visual-preview-header]'),
                logo: root.querySelector('[data-qiling-page-visual-preview-logo]'),
                nav: root.querySelector('[data-qiling-page-visual-preview-nav]'),
                search: root.querySelector('[data-qiling-page-visual-preview-search]'),
                phone: root.querySelector('[data-qiling-page-visual-preview-phone]'),
                main: root.querySelector('[data-qiling-page-visual-preview-main]'),
                normalButton: root.querySelector('[data-qiling-page-visual-preview-button-normal]'),
                ctaButton: root.querySelector('[data-qiling-page-visual-preview-button-cta]'),
                card: root.querySelector('[data-qiling-page-visual-preview-card]'),
                cardTitle: root.querySelector('[data-qiling-page-visual-preview-card-title]'),
                cardText: root.querySelector('[data-qiling-page-visual-preview-card-text]'),
                cardButton: root.querySelector('[data-qiling-page-visual-preview-card-button]'),
                footer: root.querySelector('[data-qiling-page-visual-preview-footer]'),
                waveBox: root.querySelector('.qiling-page-visual-style__preview-wave'),
                wave: root.querySelector('[data-qiling-page-visual-preview-wave]'),
                waveLayer: root.querySelector('[data-qiling-page-visual-preview-wave-layer]')
            };
            var inputs = {};
            var previewDataNode = root.querySelector('[data-qiling-page-visual-preview-data]');
            var previewData = {};
            try {
                previewData = JSON.parse(previewDataNode ? (previewDataNode.value || previewDataNode.textContent || '{}') : '{}') || {};
            } catch (error) {
                previewData = {};
            }
            previewData.currentVars = previewData.currentVars && typeof previewData.currentVars === 'object' ? previewData.currentVars : {};
            previewData.presetVars = previewData.presetVars && typeof previewData.presetVars === 'object' ? previewData.presetVars : {};
            var lastAuditItems = [];
            var pairs = [
                { id: 'header_menu', label: '顶部菜单', bg: 'header.background', fg: 'header.text' },
                { id: 'transparent_header', label: '顶部透明菜单', bg: 'colors.background', fg: 'header.transparent_text' },
                { id: 'search_box', label: '搜索框', bg: 'header.search_bg', fg: 'header.search_text', placeholder: 'header.search_placeholder', icon: 'header.search_icon' },
                { id: 'phone_button', label: '联系电话按钮', bg: 'header.phone_bg', fg: 'header.phone_text' },
                { id: 'footer_main', label: '关于我们/联系区域', bg: 'footer.background', fg: 'footer.text' },
                { id: 'footer_friend', label: '友情链接区域', bg: 'footer.friend_background', fg: 'footer.friend_text' },
                { id: 'footer_bottom', label: '版权/ICP 备案区域', bg: 'footer.bottom_background', fg: 'footer.bottom_text' },
                { id: 'footer_filing_link', label: 'ICP/公安备案链接', bg: 'footer.bottom_background', fg: 'footer.bottom_link' },
                { id: 'normal_button', label: '普通按钮', bg: 'buttons.background', fg: 'buttons.text' },
                { id: 'cta_button', label: 'CTA 按钮', bg: 'buttons.hover_background', fg: 'buttons.hover_text' }
            ];
            var cssVarMap = {
                'colors.primary': ['--qiling-page-primary', '--qiling-page-accent', '--color-primary'],
                'colors.accent': ['--qiling-page-accent-2'],
                'colors.background': ['--qiling-page-bg', '--qiling-page-background'],
                'colors.text': ['--qiling-page-text'],
                'header.background': ['--qiling-header-bg', '--qiling-header-transparent-bg'],
                'header.text': ['--qiling-header-text', '--qiling-header-nav-link', '--qiling-header-scrolled-text', '--qiling-header-scrolled-nav-link'],
                'header.transparent_text': ['--qiling-header-transparent-text', '--qiling-header-transparent-nav-link'],
                'header.nav_hover_bg': ['--qiling-header-nav-hover-bg', '--qiling-header-scrolled-nav-hover-bg'],
                'header.nav_hover_text': ['--qiling-header-nav-hover-text', '--qiling-header-scrolled-nav-hover-text'],
                'header.search_bg': ['--qiling-header-search-bg', '--qiling-header-search-transparent-bg'],
                'header.search_text': ['--qiling-header-search-text', '--qiling-header-search-transparent-text'],
                'header.search_placeholder': ['--qiling-header-search-placeholder', '--qiling-header-search-transparent-placeholder'],
                'header.search_icon': ['--qiling-header-search-icon', '--qiling-header-search-transparent-icon'],
                'header.phone_bg': ['--qiling-header-phone-normal-bg', '--qiling-header-phone-transparent-bg'],
                'header.phone_text': ['--qiling-header-phone-normal-text', '--qiling-header-phone-transparent-text'],
                'footer.background': ['--qiling-footer-main-bg'],
                'footer.text': ['--qiling-footer-main-text', '--qiling-footer-main-heading', '--qiling-footer-main-link', '--qiling-footer-main-link-hover'],
                'footer.friend_background': ['--qiling-footer-friend-bg'],
                'footer.friend_text': ['--qiling-footer-friend-text', '--qiling-footer-friend-link', '--qiling-footer-friend-link-hover'],
                'footer.bottom_background': ['--qiling-footer-bottom-bg'],
                'footer.bottom_text': ['--qiling-footer-bottom-text'],
                'footer.bottom_link': ['--qiling-footer-bottom-link'],
                'footer.bottom_link_hover': ['--qiling-footer-bottom-link-hover'],
                'footer.bottom_border': ['--qiling-footer-bottom-border'],
                'footer.wave_backdrop': ['--qiling-footer-wave-backdrop'],
                'footer.wave_transition_from': ['--qiling-footer-wave-transition-from'],
                'footer.wave_transition_height': ['--qiling-footer-wave-transition-height'],
                'footer.wave_height': ['--qiling-footer-wave-height'],
                'footer.wave_color': ['--qiling-footer-wave-color'],
                'footer.wave_layer_color': ['--qiling-footer-wave-layer-color'],
                'footer.wave_layer_opacity': ['--qiling-footer-wave-layer-opacity'],
                'buttons.background': ['--qiling-button-bg', '--qiling-page-accent'],
                'buttons.text': ['--qiling-button-text', '--qiling-page-accent-contrast'],
                'buttons.hover_background': ['--qiling-button-hover-bg', '--color-primary-dark'],
                'buttons.hover_text': ['--qiling-button-hover-text']
            };

            function collectVisualSettings() {
                var settings = {
                    mode: mode ? String(mode.value || 'inherit') : 'inherit',
                    preset: presetSelect ? String(presetSelect.value || '') : ''
                };

                Object.keys(inputs).forEach(function(path) {
                    var input = inputs[path];
                    var group = input.getAttribute('data-qiling-page-visual-group') || '';
                    var key = input.getAttribute('data-qiling-page-visual-key') || '';
                    var value = String(input.value || '').trim();
                    if (!group || !key || !value) {
                        return;
                    }

                    if (!settings[group]) {
                        settings[group] = {};
                    }
                    settings[group][key] = value;
                });

                return settings;
            }

            function syncVisualSettingsJson() {
                if (settingsJsonInput) {
                    settingsJsonInput.value = JSON.stringify(collectVisualSettings());
                }
            }

            function syncBlockEditorMeta() {
                syncVisualSettingsJson();

                if (!window.wp || !wp.data || !wp.data.dispatch || !wp.data.select) {
                    return;
                }

                var editorDispatcher = wp.data.dispatch('core/editor');
                var editorStore = wp.data.select('core/editor');
                if (!editorDispatcher || typeof editorDispatcher.editPost !== 'function' || !editorStore || typeof editorStore.getEditedPostAttribute !== 'function') {
                    return;
                }

                var currentMeta = editorStore.getEditedPostAttribute('meta') || {};
                var hideHeader = document.querySelector('input[type="checkbox"][name="qiling_hide_page_header"]');
                var transparentHeader = document.querySelector('input[type="checkbox"][name="qiling_transparent_header"]');
                var nextMeta = Object.assign({}, currentMeta, {
                    _qiling_page_visual_style: collectVisualSettings()
                });

                if (hideHeader) {
                    nextMeta._qiling_hide_page_header = hideHeader.checked ? '1' : '';
                }
                if (transparentHeader) {
                    nextMeta._qiling_transparent_header = transparentHeader.checked ? '1' : '';
                }

                editorDispatcher.editPost({ meta: nextMeta });
            }

            root.querySelectorAll('[data-qiling-page-visual-input]').forEach(function(input) {
                var group = input.getAttribute('data-qiling-page-visual-group') || '';
                var key = input.getAttribute('data-qiling-page-visual-key') || '';
                if (group && key) {
                    inputs[group + '.' + key] = input;
                    input.addEventListener('input', syncAudit);
                    input.addEventListener('change', syncAudit);
                    input.addEventListener('input', syncPreview);
                    input.addEventListener('change', syncPreview);
                    input.addEventListener('input', syncBlockEditorMeta);
                    input.addEventListener('change', syncBlockEditorMeta);
                }
            });

            var sync = function() {
                if (!fields || !mode) {
                    return;
                }
                fields.style.opacity = mode.value === 'custom' ? '1' : '.48';
            };

            function getValue(path) {
                return inputs[path] ? String(inputs[path].value || '').trim() : '';
            }

            function safeCssValue(value, fallback) {
                value = String(value || '').trim();
                if (!value || /[;{}<>]/.test(value) || /(?:expression|javascript\s*:|url\s*\()/i.test(value)) {
                    return fallback;
                }
                return value;
            }

            function getActiveBaseVars() {
                var presetKey = presetSelect ? String(presetSelect.value || '').trim() : '';
                if (presetKey && previewData.presetVars[presetKey] && typeof previewData.presetVars[presetKey] === 'object') {
                    return previewData.presetVars[presetKey];
                }
                return previewData.currentVars || {};
            }

            function getMappedVarValue(path, fallback) {
                var vars = getActiveBaseVars();
                var varNames = cssVarMap[path] || [];
                var value = '';
                varNames.some(function(varName) {
                    value = safeCssValue(vars[varName], '');
                    return !!value;
                });
                return value || fallback;
            }

            function valueOr(path, fallback) {
                return safeCssValue(getValue(path), '') || getMappedVarValue(path, fallback);
            }

            function setStyle(element, property, value) {
                if (element) {
                    element.style[property] = value;
                }
            }

            function setValue(path, value) {
                if (!inputs[path]) {
                    return false;
                }
                inputs[path].value = value;
                inputs[path].setAttribute('value', value);
                inputs[path].dispatchEvent(new Event('input', { bubbles: true }));
                inputs[path].dispatchEvent(new Event('change', { bubbles: true }));
                if (window.jQuery) {
                    window.jQuery(inputs[path]).trigger('input').trigger('change');
                }
                return true;
            }

            function ensureCustomMode() {
                if (mode && mode.value !== 'custom') {
                    mode.value = 'custom';
                    mode.setAttribute('value', 'custom');
                    mode.dispatchEvent(new Event('change', { bubbles: true }));
                    if (window.jQuery) {
                        window.jQuery(mode).trigger('change');
                    }
                }
            }

            function parseColor(value) {
                value = String(value || '').trim();
                if (!value || value.indexOf('gradient') !== -1 || value.indexOf('var(') !== -1 || value.indexOf('color-mix') !== -1) {
                    return null;
                }

                var hex = value.match(/^#([0-9a-f]{3}|[0-9a-f]{6})$/i);
                if (hex) {
                    var raw = hex[1];
                    if (raw.length === 3) {
                        raw = raw.split('').map(function(ch) { return ch + ch; }).join('');
                    }
                    return {
                        r: parseInt(raw.slice(0, 2), 16),
                        g: parseInt(raw.slice(2, 4), 16),
                        b: parseInt(raw.slice(4, 6), 16)
                    };
                }

                var rgb = value.match(/^rgba?\(([^)]+)\)$/i);
                if (!rgb) {
                    return null;
                }

                var parts = rgb[1].split(',').map(function(part) { return part.trim(); });
                if (parts.length < 3) {
                    return null;
                }

                var color = {
                    r: Math.max(0, Math.min(255, parseFloat(parts[0]))),
                    g: Math.max(0, Math.min(255, parseFloat(parts[1]))),
                    b: Math.max(0, Math.min(255, parseFloat(parts[2])))
                };
                var alpha = parts.length > 3 ? Math.max(0, Math.min(1, parseFloat(parts[3]))) : 1;
                if (alpha < 1) {
                    color.r = Math.round((color.r * alpha) + (255 * (1 - alpha)));
                    color.g = Math.round((color.g * alpha) + (255 * (1 - alpha)));
                    color.b = Math.round((color.b * alpha) + (255 * (1 - alpha)));
                }

                return color;
            }

            function luminance(color) {
                return ['r', 'g', 'b'].map(function(channel) {
                    var value = color[channel] / 255;
                    return value <= 0.03928 ? value / 12.92 : Math.pow((value + 0.055) / 1.055, 2.4);
                }).reduce(function(total, value, index) {
                    return total + value * [0.2126, 0.7152, 0.0722][index];
                }, 0);
            }

            function contrastRatio(bg, fg) {
                var bgLum = luminance(bg);
                var fgLum = luminance(fg);
                var lighter = Math.max(bgLum, fgLum);
                var darker = Math.min(bgLum, fgLum);
                return (lighter + 0.05) / (darker + 0.05);
            }

            function preferredTextColor(bg) {
                var whiteRatio = contrastRatio(bg, { r: 255, g: 255, b: 255 });
                var darkRatio = contrastRatio(bg, { r: 17, g: 24, b: 39 });
                return whiteRatio >= darkRatio ? '#ffffff' : '#111827';
            }

            function preferredSoftTextColor(bg) {
                return preferredTextColor(bg) === '#ffffff' ? 'rgba(255,255,255,0.72)' : 'rgba(17,24,39,0.56)';
            }

            function isLightColor(color) {
                return luminance(color) > 0.72;
            }

            function isDarkColor(color) {
                return luminance(color) < 0.12;
            }

            function colorDistance(a, b) {
                return Math.sqrt(
                    Math.pow(a.r - b.r, 2) +
                    Math.pow(a.g - b.g, 2) +
                    Math.pow(a.b - b.b, 2)
                );
            }

            function colorsAreClose(a, b) {
                if (!a || !b) {
                    return false;
                }
                return contrastRatio(a, b) < 1.25 || colorDistance(a, b) < 38;
            }

            function isNearWhite(color) {
                return !!color && color.r > 232 && color.g > 232 && color.b > 232;
            }

            function isNearBlack(color) {
                return !!color && color.r < 34 && color.g < 34 && color.b < 34;
            }

            function colorEquals(a, b, tolerance) {
                tolerance = typeof tolerance === 'number' ? tolerance : 10;
                return !!a && !!b && colorDistance(a, b) <= tolerance;
            }

            function looksLikeDefaultBlue(color) {
                var defaults = [
                    { r: 79, g: 125, b: 255 },
                    { r: 34, g: 113, b: 177 },
                    { r: 0, g: 124, b: 186 },
                    { r: 37, g: 99, b: 235 }
                ];
                return defaults.some(function(defaultColor) {
                    return colorEquals(color, defaultColor, 18);
                });
            }

            function clampChannel(value) {
                return Math.max(0, Math.min(255, Math.round(value)));
            }

            function componentToHex(value) {
                var hex = clampChannel(value).toString(16);
                return hex.length === 1 ? '0' + hex : hex;
            }

            function rgbToHex(color) {
                return '#' + componentToHex(color.r) + componentToHex(color.g) + componentToHex(color.b);
            }

            function mixColors(color, target, amount) {
                amount = Math.max(0, Math.min(1, amount));
                return {
                    r: color.r + (target.r - color.r) * amount,
                    g: color.g + (target.g - color.g) * amount,
                    b: color.b + (target.b - color.b) * amount
                };
            }

            function rgbToHsl(color) {
                var r = color.r / 255;
                var g = color.g / 255;
                var b = color.b / 255;
                var max = Math.max(r, g, b);
                var min = Math.min(r, g, b);
                var h = 0;
                var s = 0;
                var l = (max + min) / 2;
                var d;

                if (max !== min) {
                    d = max - min;
                    s = l > 0.5 ? d / (2 - max - min) : d / (max + min);
                    if (max === r) {
                        h = (g - b) / d + (g < b ? 6 : 0);
                    } else if (max === g) {
                        h = (b - r) / d + 2;
                    } else {
                        h = (r - g) / d + 4;
                    }
                    h /= 6;
                }

                return { h: h, s: s, l: l };
            }

            function hslToRgb(hsl) {
                var h = ((hsl.h % 1) + 1) % 1;
                var s = Math.max(0, Math.min(1, hsl.s));
                var l = Math.max(0, Math.min(1, hsl.l));
                var r;
                var g;
                var b;

                if (s === 0) {
                    r = g = b = l;
                } else {
                    var hueToRgb = function(p, q, t) {
                        if (t < 0) {
                            t += 1;
                        }
                        if (t > 1) {
                            t -= 1;
                        }
                        if (t < 1 / 6) {
                            return p + (q - p) * 6 * t;
                        }
                        if (t < 1 / 2) {
                            return q;
                        }
                        if (t < 2 / 3) {
                            return p + (q - p) * (2 / 3 - t) * 6;
                        }
                        return p;
                    };
                    var q = l < 0.5 ? l * (1 + s) : l + s - l * s;
                    var p = 2 * l - q;
                    r = hueToRgb(p, q, h + 1 / 3);
                    g = hueToRgb(p, q, h);
                    b = hueToRgb(p, q, h - 1 / 3);
                }

                return {
                    r: clampChannel(r * 255),
                    g: clampChannel(g * 255),
                    b: clampChannel(b * 255)
                };
            }

            function buildAccentColor(primaryColor) {
                var hsl = rgbToHsl(primaryColor);
                return hslToRgb({
                    h: hsl.h + 0.42,
                    s: Math.max(0.52, Math.min(0.82, hsl.s + 0.06)),
                    l: Math.max(0.42, Math.min(0.58, hsl.l + 0.03))
                });
            }

            function getPrimaryColor() {
                return parseColor(valueOr('colors.primary', '#4f7dff')) || { r: 79, g: 125, b: 255 };
            }

            function getAccentColor() {
                return parseColor(valueOr('colors.accent', '')) || buildAccentColor(getPrimaryColor());
            }

            function repairReadablePair(pair) {
                var bgColor = parseColor(valueOr(pair.bg, ''));
                var changed = 0;
                if (!bgColor) {
                    return 0;
                }

                ensureCustomMode();
                if (setValue(pair.fg, preferredTextColor(bgColor))) {
                    changed++;
                }
                if (pair.placeholder && setValue(pair.placeholder, preferredSoftTextColor(bgColor))) {
                    changed++;
                }
                if (pair.icon && setValue(pair.icon, preferredTextColor(bgColor))) {
                    changed++;
                }

                return changed;
            }

            function repairTransparentHeader() {
                ensureCustomMode();
                return setValue('header.transparent_text', '#ffffff') ? 1 : 0;
            }

            function repairFooterWave() {
                var primaryColor = getPrimaryColor();
                var accentColor = getAccentColor();
                var pageBg = parseColor(valueOr('colors.background', '')) || { r: 247, g: 251, b: 255 };
                var white = { r: 255, g: 255, b: 255 };
                var changed = 0;

                ensureCustomMode();
                [
                    ['footer.wave_backdrop', rgbToHex(pageBg)],
                    ['footer.wave_transition_from', rgbToHex(pageBg)],
                    ['footer.wave_transition_height', '32px'],
                    ['footer.wave_height', '64px'],
                    ['footer.wave_color', rgbToHex(mixColors(primaryColor, white, 0.78))],
                    ['footer.wave_layer_color', rgbToHex(mixColors(accentColor, white, 0.82))],
                    ['footer.wave_layer_opacity', '0.5']
                ].forEach(function(pair) {
                    if (setValue(pair[0], pair[1])) {
                        changed++;
                    }
                });

                return changed;
            }

            function repairCtaButton() {
                var accentColor = getAccentColor();
                ensureCustomMode();
                return [
                    setValue('buttons.hover_background', rgbToHex(accentColor)),
                    setValue('buttons.hover_text', preferredTextColor(accentColor))
                ].filter(Boolean).length;
            }

            function repairModuleSurface() {
                var primaryColor = getPrimaryColor();
                var white = { r: 255, g: 255, b: 255 };
                ensureCustomMode();
                return setValue('colors.background', rgbToHex(mixColors(primaryColor, white, 0.76))) ? 1 : 0;
            }

            function applyGeneratedPalette() {
                var primaryColor = parseColor(valueOr('colors.primary', '#4f7dff')) || { r: 79, g: 125, b: 255 };
                var accentColor = buildAccentColor(primaryColor);
                var white = { r: 255, g: 255, b: 255 };
                var dark = { r: 17, g: 24, b: 39 };
                var primaryHex = rgbToHex(primaryColor);
                var accentHex = rgbToHex(accentColor);
                var bgHex = rgbToHex(mixColors(primaryColor, white, 0.93));
                var textHex = rgbToHex(mixColors(primaryColor, dark, 0.78));
                var footerDeep = rgbToHex(mixColors(primaryColor, dark, 0.56));
                var waveHex = rgbToHex(mixColors(primaryColor, white, 0.78));
                var waveLayerHex = rgbToHex(mixColors(accentColor, white, 0.82));

                if (mode && mode.value !== 'custom') {
                    mode.value = 'custom';
                    mode.dispatchEvent(new Event('change', { bubbles: true }));
                }

                [
                    ['colors.primary', primaryHex],
                    ['colors.accent', accentHex],
                    ['colors.background', bgHex],
                    ['colors.text', textHex],
                    ['header.background', 'rgba(255,255,255,0.88)'],
                    ['header.text', textHex],
                    ['header.transparent_text', '#ffffff'],
                    ['header.nav_hover_bg', 'linear-gradient(135deg,' + primaryHex + ',' + accentHex + ')'],
                    ['header.nav_hover_text', '#ffffff'],
                    ['header.search_bg', 'rgba(255,255,255,0.78)'],
                    ['header.search_text', textHex],
                    ['header.search_placeholder', 'rgba(17,24,39,0.52)'],
                    ['header.search_icon', primaryHex],
                    ['header.phone_bg', 'rgba(255,255,255,0.78)'],
                    ['header.phone_text', textHex],
                    ['footer.background', primaryHex],
                    ['footer.text', '#ffffff'],
                    ['footer.friend_background', footerDeep],
                    ['footer.friend_text', 'rgba(255,255,255,0.86)'],
                    ['footer.bottom_background', footerDeep],
                    ['footer.bottom_text', 'rgba(255,255,255,0.82)'],
                    ['footer.bottom_link', 'rgba(255,255,255,0.9)'],
                    ['footer.bottom_link_hover', '#ffffff'],
                    ['footer.bottom_border', 'rgba(255,255,255,0.12)'],
                    ['footer.wave_backdrop', bgHex],
                    ['footer.wave_transition_from', bgHex],
                    ['footer.wave_transition_height', '32px'],
                    ['footer.wave_height', '64px'],
                    ['footer.wave_color', waveHex],
                    ['footer.wave_layer_color', waveLayerHex],
                    ['footer.wave_layer_opacity', '0.5'],
                    ['buttons.background', primaryHex],
                    ['buttons.text', '#ffffff'],
                    ['buttons.hover_background', accentHex],
                    ['buttons.hover_text', '#ffffff']
                ].forEach(function(pair) {
                    setValue(pair[0], pair[1]);
                });

                sync();
                syncAudit();
                syncPreview();
                applyLivePreview();
            }

            function collectLivePreviewVars() {
                var baseVars = getActiveBaseVars();
                var vars = {};
                Object.keys(baseVars).forEach(function(varName) {
                    var value = safeCssValue(baseVars[varName], '');
                    if (value) {
                        vars[varName] = value;
                    }
                });
                Object.keys(cssVarMap).forEach(function(path) {
                    var value = safeCssValue(getValue(path), '');
                    if (!value) {
                        return;
                    }
                    cssVarMap[path].forEach(function(varName) {
                        vars[varName] = value;
                    });
                });
                return vars;
            }

            function applyLivePreview() {
                if (!liveFrame || !liveFrame.contentWindow) {
                    return false;
                }

                var doc;
                try {
                    doc = liveFrame.contentWindow.document;
                } catch (error) {
                    return false;
                }
                if (!doc || !doc.head) {
                    return false;
                }

                var vars = collectLivePreviewVars();
                var cssVars = Object.keys(vars).map(function(varName) {
                    return varName + ':' + vars[varName] + ';';
                }).join('');
                var css = 'body,body #page,body #masthead.site-header,body .site-header,body .site-footer,body .qiling-page-skin{' + cssVars + '}';
                css += 'body.qiling-page-visual-custom,body .site-main{background:var(--qiling-page-bg, inherit);color:var(--qiling-page-text, inherit);}';
                var style = doc.getElementById('qiling-admin-page-visual-live-preview');
                if (!style) {
                    style = doc.createElement('style');
                    style.id = 'qiling-admin-page-visual-live-preview';
                    doc.head.appendChild(style);
                }
                style.textContent = css;

                return true;
            }

            function refreshLivePreview() {
                if (!liveFrame) {
                    return;
                }
                var src = liveFrame.getAttribute('src') || '';
                if (!src) {
                    return;
                }
                liveFrame.setAttribute('src', src.replace(/([?&])qiling_visual_preview_refresh=[^&]*/g, '$1').replace(/[?&]$/, '') + (src.indexOf('?') === -1 ? '?' : '&') + 'qiling_visual_preview_refresh=' + Date.now());
            }

            function syncPreview() {
                if (!previewParts.surface) {
                    return;
                }

                var pageBg = valueOr('colors.background', '#f7fbff');
                var pageText = valueOr('colors.text', '#25365f');
                var primary = valueOr('colors.primary', '#4f7dff');
                var accent = valueOr('colors.accent', '#38c9a6');
                var headerBg = valueOr('header.background', 'rgba(255,255,255,0.88)');
                var headerText = valueOr('header.text', pageText);
                var searchBg = valueOr('header.search_bg', 'rgba(255,255,255,0.76)');
                var searchText = valueOr('header.search_text', headerText);
                var phoneBg = valueOr('header.phone_bg', 'rgba(255,255,255,0.76)');
                var phoneText = valueOr('header.phone_text', headerText);
                var buttonBg = valueOr('buttons.background', primary);
                var buttonText = valueOr('buttons.text', '#ffffff');
                var buttonHoverBg = valueOr('buttons.hover_background', accent);
                var buttonHoverText = valueOr('buttons.hover_text', buttonText);
                var footerBg = valueOr('footer.background', primary);
                var footerText = valueOr('footer.text', '#ffffff');
                var waveBackdrop = valueOr('footer.wave_backdrop', pageBg);
                var waveTransitionFrom = valueOr('footer.wave_transition_from', pageBg);
                var waveTransitionHeight = valueOr('footer.wave_transition_height', '32px');
                var waveHeight = valueOr('footer.wave_height', '64px');
                var waveColor = valueOr('footer.wave_color', '#dff7ff');
                var waveLayerColor = valueOr('footer.wave_layer_color', '#e8efff');
                var waveLayerOpacity = valueOr('footer.wave_layer_opacity', '0.5');
                var previewWaveHeight = Math.max(14, Math.min(42, (parseFloat(waveHeight) || 64) * 0.36)) + 'px';
                var cardBg = pageBg.indexOf('gradient') === -1 ? 'color-mix(in srgb, ' + pageBg + ' 18%, #ffffff 82%)' : '#ffffff';
                var cardBorder = primary.indexOf('gradient') === -1 ? 'color-mix(in srgb, ' + primary + ' 28%, transparent)' : 'rgba(15,23,42,0.1)';

                setStyle(previewParts.surface, 'background', pageBg);
                setStyle(previewParts.surface, 'color', pageText);
                setStyle(previewParts.header, 'background', headerBg);
                setStyle(previewParts.header, 'color', headerText);
                setStyle(previewParts.logo, 'color', primary);
                setStyle(previewParts.nav, 'color', headerText);
                setStyle(previewParts.search, 'background', searchBg);
                setStyle(previewParts.search, 'color', searchText);
                setStyle(previewParts.phone, 'background', phoneBg);
                setStyle(previewParts.phone, 'color', phoneText);
                setStyle(previewParts.main, 'background', pageBg);
                setStyle(previewParts.main, 'color', pageText);
                setStyle(previewParts.normalButton, 'background', buttonBg);
                setStyle(previewParts.normalButton, 'color', buttonText);
                setStyle(previewParts.ctaButton, 'background', buttonHoverBg);
                setStyle(previewParts.ctaButton, 'color', buttonHoverText);
                setStyle(previewParts.card, 'background', cardBg);
                setStyle(previewParts.card, 'borderColor', cardBorder);
                setStyle(previewParts.card, 'color', pageText);
                setStyle(previewParts.cardTitle, 'color', primary);
                setStyle(previewParts.cardText, 'color', pageText);
                setStyle(previewParts.cardButton, 'background', buttonBg);
                setStyle(previewParts.cardButton, 'color', buttonText);
                setStyle(previewParts.footer, 'background', footerBg);
                setStyle(previewParts.footer, 'color', footerText);
                setStyle(previewParts.waveBox, 'height', previewWaveHeight);
                setStyle(previewParts.waveBox, 'background', 'linear-gradient(180deg, ' + waveTransitionFrom + ' 0%, ' + waveBackdrop + ' 100%)');
                setStyle(previewParts.waveBox, 'paddingTop', Math.max(0, Math.min(18, (parseFloat(waveTransitionHeight) || 32) * 0.24)) + 'px');
                setStyle(previewParts.wave, 'height', previewWaveHeight);
                setStyle(previewParts.waveLayer, 'height', previewWaveHeight);
                setStyle(previewParts.wave, 'background', waveColor);
                setStyle(previewParts.waveLayer, 'background', waveLayerColor);
                setStyle(previewParts.waveLayer, 'opacity', waveLayerOpacity);
            }

            function getAuditTitle(pair, ratio, bgColor, fgColor) {
                if (pair.id === 'search_box' && isLightColor(bgColor) && isNearWhite(fgColor)) {
                    return '搜索框浅色背景上使用了浅色文字';
                }
                if (pair.id === 'transparent_header') {
                    return '顶部透明时菜单文字可能看不清';
                }
                if (pair.id === 'phone_button') {
                    return '电话按钮和背景对比不足';
                }
                if (pair.id === 'normal_button') {
                    return '普通按钮文字和背景对比不足';
                }
                if (pair.id === 'cta_button') {
                    return 'CTA 按钮文字和背景对比不足';
                }
                return pair.label + '文字对比不足';
            }

            function buildContrastAuditItem(pair) {
                var bgColor = parseColor(valueOr(pair.bg, ''));
                var fgValue = valueOr(pair.fg, '');
                var fgColor = parseColor(fgValue);
                var ratio;

                if (!bgColor && !fgValue) {
                    return null;
                }
                if (bgColor && !fgValue) {
                    return {
                        id: 'missing_' + pair.id,
                        type: 'warning',
                        title: pair.label + '缺少文字色',
                        detail: '已识别到背景色，但文字色为空。建议补齐为 ' + preferredTextColor(bgColor) + '，避免继承到不合适的默认色。',
                        actionLabel: '补齐文字色',
                        fix: function() {
                            return repairReadablePair(pair);
                        }
                    };
                }
                if (!bgColor || !fgColor) {
                    return null;
                }

                ratio = contrastRatio(bgColor, fgColor);
                if (ratio >= 4.5) {
                    return null;
                }

                return {
                    id: 'contrast_' + pair.id,
                    type: ratio < 3 ? 'danger' : 'warning',
                    title: getAuditTitle(pair, ratio, bgColor, fgColor),
                    detail: '当前对比度约 ' + ratio.toFixed(1) + ':1，建议改成 ' + preferredTextColor(bgColor) + '。',
                    actionLabel: '一键修复',
                    fix: function() {
                        return repairReadablePair(pair);
                    }
                };
            }

            function buildFooterWaveAuditItems() {
                var items = [];
                var footerBg = parseColor(valueOr('footer.background', ''));
                var waveColor = parseColor(valueOr('footer.wave_color', ''));
                var waveLayerColor = parseColor(valueOr('footer.wave_layer_color', ''));

                if (isNearBlack(waveColor) || isNearBlack(waveLayerColor)) {
                    items.push({
                        id: 'footer_wave_black',
                        type: 'danger',
                        title: '底部波浪还是默认黑色',
                        detail: '黑色波浪很容易和页面风格脱节，也可能压住页脚。建议改成由页面主色生成的浅色波浪。',
                        actionLabel: '修复波浪色',
                        fix: repairFooterWave
                    });
                } else if (footerBg && waveColor && colorsAreClose(footerBg, waveColor)) {
                    items.push({
                        id: 'footer_wave_close',
                        type: 'warning',
                        title: '底部波浪和页脚背景太接近',
                        detail: '波浪层次不明显，用户会误以为底部被糊成一块。建议换成更浅的主色衍生色。',
                        actionLabel: '优化波浪色',
                        fix: repairFooterWave
                    });
                }

                return items;
            }

            function buildCtaAuditItems() {
                var items = [];
                var ctaBg = parseColor(valueOr('buttons.hover_background', ''));
                var normalBg = parseColor(valueOr('buttons.background', ''));
                var primaryColor = getPrimaryColor();
                var accentColor = getAccentColor();

                if (!ctaBg) {
                    return items;
                }
                if (looksLikeDefaultBlue(ctaBg) && !colorEquals(ctaBg, primaryColor, 18) && !colorEquals(ctaBg, accentColor, 18)) {
                    items.push({
                        id: 'cta_default_color',
                        type: 'warning',
                        title: 'CTA 按钮还像默认色',
                        detail: 'CTA 颜色没有跟随页面主色或辅助色，页面风格会显得不统一。',
                        actionLabel: '同步 CTA 色',
                        fix: repairCtaButton
                    });
                }
                if (normalBg && colorEquals(ctaBg, normalBg, 8)) {
                    items.push({
                        id: 'cta_same_as_normal',
                        type: 'warning',
                        title: 'CTA 和普通按钮区分不明显',
                        detail: 'CTA 应该比普通按钮更像行动入口，建议使用页面辅助色并自动匹配文字色。',
                        actionLabel: '强化 CTA',
                        fix: repairCtaButton
                    });
                }

                return items;
            }

            function buildModuleSurfaceAuditItems() {
                var items = [];
                var pageBg = parseColor(valueOr('colors.background', ''));
                var white = { r: 255, g: 255, b: 255 };
                var simulatedCardBg;

                if (!pageBg) {
                    return items;
                }

                simulatedCardBg = mixColors(pageBg, white, 0.82);
                if (isNearWhite(pageBg) || colorsAreClose(pageBg, simulatedCardBg)) {
                    items.push({
                        id: 'module_surface_close',
                        type: 'warning',
                        title: '模块卡片和页面背景太接近',
                        detail: '模块边界会变弱，页面看起来容易发散。建议把页面背景调成主色的浅色底。',
                        actionLabel: '优化页面底色',
                        fix: repairModuleSurface
                    });
                }

                return items;
            }

            function buildAuditItems() {
                var items = [];
                pairs.forEach(function(pair) {
                    var item = buildContrastAuditItem(pair);
                    if (item) {
                        items.push(item);
                    }
                });
                return items
                    .concat(buildFooterWaveAuditItems())
                    .concat(buildCtaAuditItems())
                    .concat(buildModuleSurfaceAuditItems());
            }

            function renderAuditSummary(items) {
                if (!auditSummary) {
                    return;
                }
                var red = items.filter(function(item) { return item.type === 'danger'; }).length;
                var yellow = items.filter(function(item) { return item.type === 'warning'; }).length;
                var tips = items.filter(function(item) { return item.type === 'tip'; }).length;
                var chips = [];
                auditSummary.innerHTML = '';

                chips.push(red ? '红色 ' + red + ' 项明显看不清' : '红色 0 项');
                chips.push(yellow ? '黄色 ' + yellow + ' 项建议优化' : '黄色 0 项');
                if (tips) {
                    chips.push('提示 ' + tips + ' 项');
                }

                chips.forEach(function(text) {
                    var chip = document.createElement('span');
                    chip.textContent = text;
                    auditSummary.appendChild(chip);
                });
            }

            function renderAudit(items) {
                var displayItems = items.slice();
                if (!auditList) {
                    return;
                }
                lastAuditItems = displayItems.filter(function(item) {
                    return typeof item.fix === 'function';
                });
                renderAuditSummary(displayItems);
                auditList.innerHTML = '';

                if (!displayItems.length) {
                    displayItems.push({
                        id: 'ok',
                        type: 'ok',
                        title: '当前页面风格比较稳',
                        detail: '没有发现明显看不清或风格脱节的问题。'
                    });
                }

                displayItems.forEach(function(item) {
                    var li = document.createElement('li');
                    var content = document.createElement('div');
                    var title = document.createElement('strong');
                    var detail = document.createElement('span');
                    li.className = 'qiling-page-visual-style__audit-item qiling-page-visual-style__audit-item--' + item.type;
                    content.className = 'qiling-page-visual-style__audit-item-content';
                    title.textContent = item.title || '';
                    detail.textContent = item.detail || '';
                    content.appendChild(title);
                    content.appendChild(detail);
                    li.appendChild(content);

                    if (typeof item.fix === 'function') {
                        var button = document.createElement('button');
                        button.type = 'button';
                        button.className = 'button button-secondary';
                        button.setAttribute('data-qiling-page-visual-audit-fix', item.id);
                        button.textContent = item.actionLabel || '一键修复';
                        li.appendChild(button);
                    }

                    auditList.appendChild(li);
                });
            }

            function syncAudit() {
                renderAudit(buildAuditItems());
            }

            function applyAuditFixes(items) {
                var changed = 0;
                var seen = {};
                (items || []).forEach(function(item) {
                    if (!item || seen[item.id] || typeof item.fix !== 'function') {
                        return;
                    }
                    seen[item.id] = true;
                    changed += item.fix() || 0;
                });

                sync();
                syncAudit();
                syncPreview();
                applyLivePreview();
                syncBlockEditorMeta();
                if (!changed) {
                    renderAudit([{ type: 'ok', title: '当前颜色已经比较稳', detail: '没有找到需要自动修正的字段。' }]);
                }
            }

            function applyReadableTextColors() {
                var changed = 0;
                ensureCustomMode();

                pairs.forEach(function(pair) {
                    var bgColor = parseColor(valueOr(pair.bg, ''));
                    var current = parseColor(valueOr(pair.fg, ''));
                    if (!bgColor) {
                        return;
                    }

                    if (!current || contrastRatio(bgColor, current) < 4.5) {
                        changed += repairReadablePair(pair);
                    }
                });

                sync();
                syncAudit();
                syncPreview();
                applyLivePreview();
                syncBlockEditorMeta();
                if (!changed) {
                    renderAudit([{ type: 'ok', title: '当前文字色已经比较稳', detail: '没有发现需要自动修正的文字色。' }]);
                }
            }

            if (mode) {
                mode.addEventListener('change', sync);
                mode.addEventListener('change', syncAudit);
                mode.addEventListener('change', syncPreview);
                mode.addEventListener('change', applyLivePreview);
                mode.addEventListener('change', syncBlockEditorMeta);
            }
            if (presetSelect) {
                presetSelect.addEventListener('change', syncAudit);
                presetSelect.addEventListener('change', syncPreview);
                presetSelect.addEventListener('change', applyLivePreview);
                presetSelect.addEventListener('change', syncBlockEditorMeta);
            }
            ['qiling_hide_page_header', 'qiling_transparent_header'].forEach(function(name) {
                var checkbox = document.querySelector('input[type="checkbox"][name="' + name + '"]');
                if (checkbox) {
                    checkbox.addEventListener('change', syncBlockEditorMeta);
                }
            });
            var postForm = document.getElementById('post') || root.closest('form');
            if (postForm) {
                postForm.addEventListener('submit', syncBlockEditorMeta, true);
            }
            if (auditList) {
                auditList.addEventListener('click', function(event) {
                    var target = event.target && event.target.closest ? event.target.closest('[data-qiling-page-visual-audit-fix]') : null;
                    if (!target) {
                        return;
                    }
                    var id = target.getAttribute('data-qiling-page-visual-audit-fix') || '';
                    var item = lastAuditItems.filter(function(auditItem) {
                        return auditItem.id === id;
                    })[0];
                    if (item) {
                        applyAuditFixes([item]);
                    }
                });
            }
            if (autoTextButton) {
                autoTextButton.addEventListener('click', applyReadableTextColors);
            }
            if (fixAllButton) {
                fixAllButton.addEventListener('click', function() {
                    applyAuditFixes(lastAuditItems.slice());
                });
            }
            if (generatePaletteButton) {
                generatePaletteButton.addEventListener('click', applyGeneratedPalette);
            }
            if (liveFrame) {
                liveFrame.addEventListener('load', applyLivePreview);
            }
            if (liveRefreshButton) {
                liveRefreshButton.addEventListener('click', refreshLivePreview);
            }
            if (liveSyncButton) {
                liveSyncButton.addEventListener('click', applyLivePreview);
            }
            if (copyExportButton && exportText) {
                copyExportButton.addEventListener('click', function() {
                    var text = String(exportText.value || '');
                    var originalText = copyExportButton.textContent;
                    var done = function(success) {
                        copyExportButton.textContent = success ? '已复制' : '复制失败';
                        setTimeout(function() {
                            copyExportButton.textContent = originalText;
                        }, 1400);
                    };

                    if (navigator.clipboard && navigator.clipboard.writeText) {
                        navigator.clipboard.writeText(text).then(function() {
                            done(true);
                        }).catch(function() {
                            exportText.focus();
                            exportText.select();
                            done(document.execCommand('copy'));
                        });
                    } else {
                        exportText.focus();
                        exportText.select();
                        done(document.execCommand('copy'));
                    }
                });
            }
            sync();
            syncAudit();
            syncPreview();
            applyLivePreview();
            syncVisualSettingsJson();
        }());
        </script>
        <?php
    }

    /**
     * Build preset and current-page CSS vars for the small admin preview.
     *
     * @param \WP_Post             $post    Current post.
     * @param array<string,string> $presets Preset choices.
     * @return array<string,mixed>
     */
    private function build_page_visual_preview_data( $post, $presets ) {
        $post_id      = $post instanceof \WP_Post ? absint( $post->ID ) : 0;
        $current_vars = array();

        if ( $post_id > 0 && function_exists( 'developer_starter_resolve_page_visual_style' ) ) {
            $resolved = developer_starter_resolve_page_visual_style( $post_id );
            if ( isset( $resolved['vars'] ) && is_array( $resolved['vars'] ) ) {
                $current_vars = $resolved['vars'];
            }
        }

        $preset_vars = array();
        if ( is_array( $presets ) && function_exists( 'developer_starter_get_page_visual_preset_vars_array' ) ) {
            foreach ( $presets as $preset_key => $preset_label ) {
                unset( $preset_label );
                $preset_key = sanitize_key( (string) $preset_key );
                if ( '' === $preset_key ) {
                    continue;
                }

                $vars = developer_starter_get_page_visual_preset_vars_array( $preset_key, 'all' );
                if ( ! empty( $vars ) && is_array( $vars ) ) {
                    $preset_vars[ $preset_key ] = $this->sanitize_page_visual_preview_vars( $vars );
                }
            }
        }

        return array(
            'currentVars' => $this->sanitize_page_visual_preview_vars( $current_vars ),
            'presetVars'  => $preset_vars,
        );
    }

    /**
     * @param array<string,mixed> $vars CSS variables.
     * @return array<string,string>
     */
    private function sanitize_page_visual_preview_vars( $vars ) {
        if ( ! is_array( $vars ) ) {
            return array();
        }

        $clean = array();
        foreach ( $vars as $name => $value ) {
            $name = is_string( $name ) ? trim( $name ) : '';
            if ( ! preg_match( '/^--[a-z0-9_-]+$/i', $name ) || ! is_scalar( $value ) ) {
                continue;
            }

            $value = function_exists( 'developer_starter_sanitize_page_visual_style_css_value' )
                ? developer_starter_sanitize_page_visual_style_css_value( $value )
                : trim( wp_strip_all_tags( (string) $value ) );
            if ( '' !== $value ) {
                $clean[ $name ] = $value;
            }
        }

        return $clean;
    }

    /**
     * Build reusable style usage data for the stage-four management panel.
     *
     * @param \WP_Post                   $post           Current post.
     * @param array<string,mixed>        $settings       Sanitized page visual settings.
     * @param array<string,string>       $presets        Preset choices.
     * @param array<string,array<mixed>> $custom_presets User-created presets.
     * @return array<string,mixed>
     */
    private function build_page_visual_reuse_state( $post, $settings, $presets, $custom_presets ) {
        $post_id        = $post instanceof \WP_Post ? absint( $post->ID ) : 0;
        $settings       = is_array( $settings ) ? $settings : array();
        $presets        = is_array( $presets ) ? $presets : array();
        $custom_presets = is_array( $custom_presets ) ? $custom_presets : array();
        $usage_map      = $this->collect_page_visual_style_usage( $presets, $custom_presets );
        $current_target = $this->resolve_page_visual_usage_target( $post_id, $settings, $presets, $custom_presets );
        $current_key    = isset( $current_target['key'] ) ? (string) $current_target['key'] : '';
        $cards          = array();
        $seen           = array();

        $add_card = function ( $target ) use ( &$cards, &$seen, $usage_map, $current_key ) {
            if ( empty( $target['key'] ) ) {
                return;
            }

            $key = (string) $target['key'];
            if ( isset( $seen[ $key ] ) ) {
                return;
            }
            $seen[ $key ] = true;

            $usage = isset( $usage_map[ $key ] ) && is_array( $usage_map[ $key ] ) ? $usage_map[ $key ] : array();
            $cards[] = array(
                'key'        => $key,
                'label'      => ! empty( $usage['label'] ) ? (string) $usage['label'] : ( ! empty( $target['label'] ) ? (string) $target['label'] : $key ),
                'detail'     => ! empty( $usage['detail'] ) ? (string) $usage['detail'] : ( ! empty( $target['detail'] ) ? (string) $target['detail'] : '' ),
                'type'       => ! empty( $usage['type'] ) ? (string) $usage['type'] : ( ! empty( $target['type'] ) ? (string) $target['type'] : 'preset' ),
                'pages'      => ! empty( $usage['pages'] ) && is_array( $usage['pages'] ) ? $usage['pages'] : array(),
                'is_current' => $key === $current_key,
            );
        };

        $add_card( $current_target );

        foreach ( $custom_presets as $preset_key => $preset ) {
            unset( $preset );
            $preset_key = sanitize_key( (string) $preset_key );
            if ( '' === $preset_key ) {
                continue;
            }

            $add_card(
                array(
                    'key'    => 'preset:' . $preset_key,
                    'type'   => 'custom_preset',
                    'label'  => $this->get_page_visual_preset_label( $preset_key, $presets, $custom_presets ),
                    'detail' => __( '我的风格，可再次套用或批量应用。', 'developer-starter' ),
                )
            );
        }

        foreach ( $usage_map as $usage_key => $usage ) {
            if ( empty( $usage['pages'] ) ) {
                continue;
            }
            if ( 0 === strpos( (string) $usage_key, 'page_custom:' ) && (string) $usage_key !== $current_key ) {
                continue;
            }

            $add_card(
                array(
                    'key'    => (string) $usage_key,
                    'type'   => ! empty( $usage['type'] ) ? (string) $usage['type'] : 'preset',
                    'label'  => ! empty( $usage['label'] ) ? (string) $usage['label'] : (string) $usage_key,
                    'detail' => ! empty( $usage['detail'] ) ? (string) $usage['detail'] : '',
                )
            );
        }

        return array(
            'current_key' => $current_key,
            'cards'       => $cards,
        );
    }

    /**
     * Collect page usage grouped by visual style target.
     *
     * @param array<string,string>       $presets        Preset choices.
     * @param array<string,array<mixed>> $custom_presets User-created presets.
     * @return array<string,array<string,mixed>>
     */
    private function collect_page_visual_style_usage( $presets, $custom_presets ) {
        if ( ! function_exists( 'get_posts' ) || ! function_exists( 'developer_starter_get_post_page_visual_style' ) ) {
            return array();
        }

        $page_ids = get_posts(
            array(
                'post_type'        => 'page',
                'post_status'      => array( 'publish', 'draft', 'pending', 'private', 'future' ),
                'posts_per_page'   => -1,
                'fields'           => 'ids',
                'orderby'          => 'title',
                'order'            => 'ASC',
                'no_found_rows'    => true,
                'suppress_filters' => false,
            )
        );
        $page_ids = is_array( $page_ids ) ? $page_ids : array();
        $usage    = array();

        foreach ( $page_ids as $page_id ) {
            $page_id = absint( $page_id );
            if ( $page_id <= 0 || ! current_user_can( 'edit_post', $page_id ) ) {
                continue;
            }

            $settings = developer_starter_get_post_page_visual_style( $page_id );
            $target   = $this->resolve_page_visual_usage_target( $page_id, $settings, $presets, $custom_presets );
            if ( empty( $target['key'] ) ) {
                continue;
            }

            $key = (string) $target['key'];
            if ( empty( $usage[ $key ] ) ) {
                $usage[ $key ] = array(
                    'key'    => $key,
                    'type'   => ! empty( $target['type'] ) ? (string) $target['type'] : 'preset',
                    'label'  => ! empty( $target['label'] ) ? (string) $target['label'] : $key,
                    'detail' => ! empty( $target['detail'] ) ? (string) $target['detail'] : '',
                    'pages'  => array(),
                );
            }

            $usage[ $key ]['pages'][] = $this->get_page_visual_usage_page_item( $page_id );
        }

        return $usage;
    }

    /**
     * Resolve a page's reusable style target.
     *
     * @param int                        $post_id        Page ID.
     * @param array<string,mixed>        $settings       Sanitized page visual settings.
     * @param array<string,string>       $presets        Preset choices.
     * @param array<string,array<mixed>> $custom_presets User-created presets.
     * @return array<string,string>
     */
    private function resolve_page_visual_usage_target( $post_id, $settings, $presets, $custom_presets ) {
        $post_id  = absint( $post_id );
        $settings = is_array( $settings ) ? $settings : array();
        $mode     = isset( $settings['mode'] ) ? (string) $settings['mode'] : 'inherit';
        $preset   = isset( $settings['preset'] ) ? sanitize_key( (string) $settings['preset'] ) : '';

        if ( 'global' === $mode ) {
            return array(
                'key'    => 'global',
                'type'   => 'global',
                'label'  => __( '全站默认风格', 'developer-starter' ),
                'detail' => __( '页面强制跟随全站默认。', 'developer-starter' ),
            );
        }

        if ( 'custom' === $mode && '' !== $preset ) {
            $has_field_values = $this->page_visual_style_has_field_values( $settings );
            return array(
                'key'    => 'preset:' . $preset,
                'type'   => isset( $custom_presets[ $preset ] ) ? 'custom_preset' : 'preset',
                'label'  => $this->get_page_visual_preset_label( $preset, $presets, $custom_presets ),
                'detail' => $has_field_values ? __( '基于该预设做了页面细调。', 'developer-starter' ) : __( '直接套用该预设。', 'developer-starter' ),
            );
        }

        if ( 'custom' === $mode ) {
            return array(
                'key'    => 'page_custom:' . $post_id,
                'type'   => 'page_custom',
                'label'  => sprintf(
                    /* translators: %s: page title */
                    __( '页面自定义：%s', 'developer-starter' ),
                    $post_id > 0 ? get_the_title( $post_id ) : __( '当前页面', 'developer-starter' )
                ),
                'detail' => __( '该页面有单独颜色，未保存成我的风格。', 'developer-starter' ),
            );
        }

        return $this->resolve_page_visual_template_usage_target( $post_id );
    }

    /**
     * Resolve the template-default style target for a page.
     *
     * @param int $post_id Page ID.
     * @return array<string,string>
     */
    private function resolve_page_visual_template_usage_target( $post_id ) {
        $post_id  = absint( $post_id );
        $template = '';
        if ( $post_id > 0 && function_exists( 'get_page_template_slug' ) ) {
            $template = (string) get_page_template_slug( $post_id );
        }
        if ( '' === trim( $template ) && $post_id > 0 ) {
            $template = (string) get_post_meta( $post_id, '_wp_page_template', true );
        }

        $skin = function_exists( 'developer_starter_get_page_visual_skin_for_template' )
            ? developer_starter_get_page_visual_skin_for_template( $template )
            : null;
        $label = is_array( $skin ) && ! empty( $skin['label'] )
            ? (string) $skin['label']
            : __( '模板默认风格', 'developer-starter' );
        $skin_key = is_array( $skin ) && ! empty( $skin['key'] )
            ? sanitize_key( (string) $skin['key'] )
            : '';
        if ( '' === $skin_key ) {
            $skin_key = '' !== trim( $template ) ? sanitize_key( str_replace( array( '/', '\\', '.' ), '_', $template ) ) : 'default';
        }

        return array(
            'key'    => 'template:' . $skin_key,
            'type'   => 'template',
            'label'  => sprintf(
                /* translators: %s: template visual style label */
                __( '模板默认：%s', 'developer-starter' ),
                $label
            ),
            'detail' => __( '恢复到导入模板时的默认风格会回到这一组。', 'developer-starter' ),
        );
    }

    /**
     * Build a compact editable page item for usage cards.
     *
     * @param int $page_id Page ID.
     * @return array<string,string|int>
     */
    private function get_page_visual_usage_page_item( $page_id ) {
        $page_id = absint( $page_id );
        $title   = $page_id > 0 ? (string) get_the_title( $page_id ) : '';
        if ( '' === trim( $title ) ) {
            $title = sprintf( '#%d', $page_id );
        }

        return array(
            'id'       => $page_id,
            'title'    => $title,
            'edit_url' => $page_id > 0 ? (string) get_edit_post_link( $page_id, '' ) : '',
        );
    }

    /**
     * Render style usage cards for the stage-four management panel.
     *
     * @param array<string,mixed> $state Reuse state.
     * @return void
     */
    private function render_page_visual_reuse_usage( $state ) {
        $state = is_array( $state ) ? $state : array();
        $cards = isset( $state['cards'] ) && is_array( $state['cards'] ) ? $state['cards'] : array();
        ?>
        <section class="qiling-page-visual-style__usage" aria-label="<?php esc_attr_e( '风格使用情况', 'developer-starter' ); ?>">
            <div class="qiling-page-visual-style__usage-head">
                <div>
                    <strong><?php esc_html_e( '风格使用情况', 'developer-starter' ); ?></strong>
                    <span><?php esc_html_e( '查看当前风格、我的风格和已被页面使用的风格，快速判断批量修改会影响哪些页面。', 'developer-starter' ); ?></span>
                </div>
            </div>
            <div class="qiling-page-visual-style__usage-grid">
                <?php if ( empty( $cards ) ) : ?>
                    <div class="qiling-page-visual-style__usage-card">
                        <div class="qiling-page-visual-style__usage-card-head">
                            <strong><?php esc_html_e( '暂无使用数据', 'developer-starter' ); ?></strong>
                            <span class="qiling-page-visual-style__usage-count">0</span>
                        </div>
                        <span class="description"><?php esc_html_e( '还没有页面保存过页面风格设置。', 'developer-starter' ); ?></span>
                    </div>
                <?php endif; ?>

                <?php foreach ( $cards as $card ) : ?>
                    <?php
                    $pages      = ! empty( $card['pages'] ) && is_array( $card['pages'] ) ? $card['pages'] : array();
                    $count      = count( $pages );
                    $is_current = ! empty( $card['is_current'] );
                    ?>
                    <div class="qiling-page-visual-style__usage-card<?php echo $is_current ? ' is-current' : ''; ?>">
                        <div class="qiling-page-visual-style__usage-card-head">
                            <div>
                                <strong><?php echo esc_html( isset( $card['label'] ) ? (string) $card['label'] : '' ); ?></strong>
                                <?php if ( ! empty( $card['detail'] ) ) : ?>
                                    <span class="description"><?php echo esc_html( (string) $card['detail'] ); ?></span>
                                <?php endif; ?>
                            </div>
                            <span class="qiling-page-visual-style__usage-count"><?php echo esc_html( sprintf( __( '%d 个页面', 'developer-starter' ), $count ) ); ?></span>
                        </div>
                        <div class="qiling-page-visual-style__usage-pages">
                            <?php if ( empty( $pages ) ) : ?>
                                <span><?php esc_html_e( '暂无页面使用', 'developer-starter' ); ?></span>
                            <?php else : ?>
                                <?php foreach ( array_slice( $pages, 0, 10 ) as $page_item ) : ?>
                                    <?php
                                    $edit_url = ! empty( $page_item['edit_url'] ) ? (string) $page_item['edit_url'] : '';
                                    $title    = ! empty( $page_item['title'] ) ? (string) $page_item['title'] : sprintf( '#%d', isset( $page_item['id'] ) ? absint( $page_item['id'] ) : 0 );
                                    ?>
                                    <?php if ( '' !== $edit_url ) : ?>
                                        <a href="<?php echo esc_url( $edit_url ); ?>"><?php echo esc_html( $title ); ?></a>
                                    <?php else : ?>
                                        <span><?php echo esc_html( $title ); ?></span>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                                <?php if ( $count > 10 ) : ?>
                                    <span><?php echo esc_html( sprintf( __( '另有 %d 个', 'developer-starter' ), $count - 10 ) ); ?></span>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
        <?php
    }

    /**
     * Build the read-only state shown by the page style manager.
     *
     * @param \WP_Post                   $post           Current post.
     * @param array<string,mixed>        $settings       Sanitized page visual settings.
     * @param array<string,string>       $presets        Preset choices.
     * @param array<string,array<mixed>> $custom_presets User-created presets.
     * @return array<string,mixed>
     */
    private function build_page_visual_style_manager_state( $post, $settings, $presets, $custom_presets ) {
        $post_id        = $post instanceof \WP_Post ? absint( $post->ID ) : 0;
        $settings       = is_array( $settings ) ? $settings : array();
        $presets        = is_array( $presets ) ? $presets : array();
        $custom_presets = is_array( $custom_presets ) ? $custom_presets : array();
        $mode           = isset( $settings['mode'] ) ? (string) $settings['mode'] : 'inherit';
        $preset_key     = isset( $settings['preset'] ) ? sanitize_key( (string) $settings['preset'] ) : '';
        $preset_label   = $this->get_page_visual_preset_label( $preset_key, $presets, $custom_presets );
        $is_my_preset   = '' !== $preset_key && isset( $custom_presets[ $preset_key ] );

        $template = '';
        if ( $post_id > 0 && function_exists( 'get_page_template_slug' ) ) {
            $template = (string) get_page_template_slug( $post_id );
        }
        if ( '' === trim( $template ) && $post_id > 0 ) {
            $template = (string) get_post_meta( $post_id, '_wp_page_template', true );
        }

        $template_skin  = function_exists( 'developer_starter_get_page_visual_skin_for_template' )
            ? developer_starter_get_page_visual_skin_for_template( $template )
            : null;
        $template_label = is_array( $template_skin ) && ! empty( $template_skin['label'] )
            ? (string) $template_skin['label']
            : '';

        $has_field_values = $this->page_visual_style_has_field_values( $settings );
        $current_source   = $this->resolve_page_visual_current_source(
            $mode,
            $preset_key,
            $preset_label,
            $is_my_preset,
            $template_label,
            $has_field_values
        );

        $current_style = $this->resolve_page_visual_current_style_label(
            $mode,
            $preset_key,
            $preset_label,
            $template_label,
            $has_field_values
        );

        $source_context = array(
            'mode'           => $mode,
            'preset_key'     => $preset_key,
            'preset_label'   => $preset_label,
            'is_my_preset'   => $is_my_preset,
            'template_label' => $template_label,
        );

        return array(
            'current_style'   => $current_style,
            'current_source'  => $current_source,
            'template_label'  => '' !== $template_label ? $template_label : __( '未匹配模板预设', 'developer-starter' ),
            'preset_label'    => '' !== $preset_label ? $preset_label : __( '未选择基础预设', 'developer-starter' ),
            'area_sources'    => array(
                'header_menu' => $this->resolve_page_visual_area_source(
                    $settings,
                    'header',
                    array( 'background', 'text', 'transparent_text', 'nav_hover_bg', 'nav_hover_text' ),
                    $source_context
                ),
                'search'      => $this->resolve_page_visual_area_source(
                    $settings,
                    'header',
                    array( 'search_bg', 'search_text', 'search_placeholder', 'search_icon' ),
                    $source_context
                ),
                'footer_wave' => $this->resolve_page_visual_area_source(
                    $settings,
                    'footer',
                    array( 'wave_backdrop', 'wave_transition_from', 'wave_transition_height', 'wave_height', 'wave_color', 'wave_layer_color', 'wave_layer_opacity' ),
                    $source_context
                ),
                'buttons'     => $this->resolve_page_visual_area_source(
                    $settings,
                    'buttons',
                    array( 'background', 'text', 'hover_background', 'hover_text' ),
                    $source_context
                ),
            ),
            'module_overrides' => $this->collect_page_visual_module_overrides( $post_id ),
        );
    }

    /**
     * Render the first-stage page style manager dashboard.
     *
     * @param array<string,mixed> $state Manager state.
     * @return void
     */
    private function render_page_visual_style_manager( $state ) {
        $state          = is_array( $state ) ? $state : array();
        $current_style  = isset( $state['current_style'] ) && is_array( $state['current_style'] ) ? $state['current_style'] : array();
        $current_source = isset( $state['current_source'] ) && is_array( $state['current_source'] ) ? $state['current_source'] : array();
        $area_sources   = isset( $state['area_sources'] ) && is_array( $state['area_sources'] ) ? $state['area_sources'] : array();
        $module_items   = isset( $state['module_overrides'] ) && is_array( $state['module_overrides'] ) ? $state['module_overrides'] : array();

        $source_type  = isset( $current_source['type'] ) ? sanitize_html_class( (string) $current_source['type'] ) : 'global';
        $source_label = isset( $current_source['label'] ) ? (string) $current_source['label'] : __( '全站默认', 'developer-starter' );
        ?>
        <section class="qiling-page-style-manager" aria-label="<?php esc_attr_e( '页面风格管家', 'developer-starter' ); ?>">
            <div class="qiling-page-style-manager__head">
                <div>
                    <h3><?php esc_html_e( '页面风格管家', 'developer-starter' ); ?></h3>
                    <p><?php esc_html_e( '先看清当前来源和覆盖项，再决定要套用、复制、保存或恢复。', 'developer-starter' ); ?></p>
                </div>
                <span class="qiling-page-style-manager__badge qiling-page-style-manager__badge--<?php echo esc_attr( $source_type ); ?>"><?php echo esc_html( $source_label ); ?></span>
            </div>

            <div class="qiling-page-style-manager__grid">
                <div class="qiling-page-style-manager__card">
                    <span><?php esc_html_e( '当前页面风格', 'developer-starter' ); ?></span>
                    <strong><?php echo esc_html( isset( $current_style['label'] ) ? (string) $current_style['label'] : __( '全站默认', 'developer-starter' ) ); ?></strong>
                </div>
                <div class="qiling-page-style-manager__card">
                    <span><?php esc_html_e( '当前来源', 'developer-starter' ); ?></span>
                    <strong><?php echo esc_html( $source_label ); ?></strong>
                    <?php if ( ! empty( $current_source['detail'] ) ) : ?>
                        <span><?php echo esc_html( (string) $current_source['detail'] ); ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="qiling-page-style-manager__sources">
                <?php
                $source_labels = array(
                    'header_menu' => __( '顶部菜单来源', 'developer-starter' ),
                    'search'      => __( '搜索框来源', 'developer-starter' ),
                    'footer_wave' => __( '底部波浪来源', 'developer-starter' ),
                    'buttons'     => __( '按钮来源', 'developer-starter' ),
                );
                foreach ( $source_labels as $source_key => $source_title ) :
                    $source = isset( $area_sources[ $source_key ] ) && is_array( $area_sources[ $source_key ] ) ? $area_sources[ $source_key ] : array();
                    ?>
                    <div class="qiling-page-style-manager__source">
                        <span><?php echo esc_html( $source_title ); ?></span>
                        <strong><?php echo esc_html( isset( $source['label'] ) ? (string) $source['label'] : __( '全站默认', 'developer-starter' ) ); ?></strong>
                        <?php if ( ! empty( $source['detail'] ) ) : ?>
                            <span><?php echo esc_html( (string) $source['detail'] ); ?></span>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="qiling-page-style-manager__overrides">
                <span><?php esc_html_e( '模块级单独覆盖', 'developer-starter' ); ?></span>
                <?php if ( empty( $module_items ) ) : ?>
                    <strong><?php esc_html_e( '没有发现单独覆盖，模块会跟随页面风格。', 'developer-starter' ); ?></strong>
                <?php else : ?>
                    <strong><?php echo esc_html( sprintf( __( '发现 %d 个模块单独覆盖了页面风格', 'developer-starter' ), count( $module_items ) ) ); ?></strong>
                    <div class="qiling-page-style-manager__override-list">
                        <?php foreach ( array_slice( $module_items, 0, 12 ) as $module_item ) : ?>
                            <span class="qiling-page-style-manager__override-item"><?php echo esc_html( isset( $module_item['label'] ) ? (string) $module_item['label'] : '' ); ?></span>
                        <?php endforeach; ?>
                        <?php if ( count( $module_items ) > 12 ) : ?>
                            <span class="qiling-page-style-manager__override-item"><?php echo esc_html( sprintf( __( '另有 %d 个', 'developer-starter' ), count( $module_items ) - 12 ) ); ?></span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>
        <?php
    }

    /**
     * @param string                    $preset_key     Preset key.
     * @param array<string,string>      $presets        Preset choices.
     * @param array<string,array<mixed>> $custom_presets User presets.
     * @return string
     */
    private function get_page_visual_preset_label( $preset_key, $presets, $custom_presets ) {
        $preset_key = sanitize_key( (string) $preset_key );
        if ( '' === $preset_key ) {
            return '';
        }

        if ( isset( $presets[ $preset_key ] ) && is_scalar( $presets[ $preset_key ] ) ) {
            return (string) $presets[ $preset_key ];
        }

        if ( isset( $custom_presets[ $preset_key ]['label'] ) && is_scalar( $custom_presets[ $preset_key ]['label'] ) ) {
            return sprintf(
                /* translators: %s: custom preset label */
                __( '我的：%s', 'developer-starter' ),
                (string) $custom_presets[ $preset_key ]['label']
            );
        }

        return $preset_key;
    }

    /**
     * @param array<string,mixed> $settings Visual settings.
     * @return bool
     */
    private function page_visual_style_has_field_values( $settings ) {
        if ( ! is_array( $settings ) ) {
            return false;
        }

        foreach ( array( 'colors', 'canvas', 'header', 'footer', 'buttons' ) as $group_key ) {
            if ( $this->page_visual_style_group_has_values( $settings, $group_key ) ) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array<string,mixed> $settings   Visual settings.
     * @param string              $group_key  Group key.
     * @param array<int,string>   $field_keys Optional field allowlist.
     * @return bool
     */
    private function page_visual_style_group_has_values( $settings, $group_key, $field_keys = array() ) {
        if ( ! is_array( $settings ) || empty( $settings[ $group_key ] ) || ! is_array( $settings[ $group_key ] ) ) {
            return false;
        }

        $field_keys = is_array( $field_keys ) ? array_map( 'sanitize_key', $field_keys ) : array();
        foreach ( $settings[ $group_key ] as $field_key => $value ) {
            $field_key = sanitize_key( (string) $field_key );
            if ( ! empty( $field_keys ) && ! in_array( $field_key, $field_keys, true ) ) {
                continue;
            }

            if ( is_scalar( $value ) && '' !== trim( (string) $value ) ) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<string,string>
     */
    private function resolve_page_visual_current_source( $mode, $preset_key, $preset_label, $is_my_preset, $template_label, $has_field_values ) {
        if ( 'global' === (string) $mode ) {
            return array(
                'type'   => 'global',
                'label'  => __( '全站默认', 'developer-starter' ),
                'detail' => __( '当前页面强制使用全站设计设置。', 'developer-starter' ),
            );
        }

        if ( 'custom' === (string) $mode ) {
            if ( $has_field_values ) {
                return array(
                    'type'   => 'custom',
                    'label'  => __( '页面自定义', 'developer-starter' ),
                    'detail' => '' !== $preset_label ? sprintf( __( '基于 %s 细调。', 'developer-starter' ), $preset_label ) : __( '没有选择基础预设。', 'developer-starter' ),
                );
            }

            if ( '' !== (string) $preset_key ) {
                return array(
                    'type'   => 'preset',
                    'label'  => $is_my_preset ? __( '我的预设', 'developer-starter' ) : __( '模板预设', 'developer-starter' ),
                    'detail' => '' !== $preset_label ? $preset_label : (string) $preset_key,
                );
            }

            return array(
                'type'   => 'custom',
                'label'  => __( '页面自定义', 'developer-starter' ),
                'detail' => __( '尚未填写细调字段；保存后仍会保持当前页面自定义模式。', 'developer-starter' ),
            );
        }

        if ( '' !== (string) $template_label ) {
            return array(
                'type'   => 'preset',
                'label'  => __( '模板预设', 'developer-starter' ),
                'detail' => (string) $template_label,
            );
        }

        return array(
            'type'   => 'global',
            'label'  => __( '全站默认', 'developer-starter' ),
            'detail' => __( '当前页面没有匹配模板预设或页面自定义。', 'developer-starter' ),
        );
    }

    /**
     * @return array<string,string>
     */
    private function resolve_page_visual_current_style_label( $mode, $preset_key, $preset_label, $template_label, $has_field_values ) {
        if ( 'global' === (string) $mode ) {
            return array( 'label' => __( '全站默认', 'developer-starter' ) );
        }

        if ( 'custom' === (string) $mode ) {
            if ( '' !== (string) $preset_key && '' !== (string) $preset_label ) {
                return array( 'label' => (string) $preset_label );
            }

            if ( $has_field_values ) {
                return array( 'label' => __( '自定义', 'developer-starter' ) );
            }

            return array( 'label' => __( '自定义', 'developer-starter' ) );
        }

        if ( '' !== (string) $template_label ) {
            return array( 'label' => (string) $template_label );
        }

        return array( 'label' => __( '全站默认', 'developer-starter' ) );
    }

    /**
     * @param array<string,mixed> $settings Visual settings.
     * @param string              $group_key Group key.
     * @param array<int,string>   $field_keys Relevant fields.
     * @param array<string,mixed> $context Source context.
     * @return array<string,string>
     */
    private function resolve_page_visual_area_source( $settings, $group_key, $field_keys, $context ) {
        $mode = isset( $context['mode'] ) ? (string) $context['mode'] : 'inherit';
        if ( 'custom' === $mode && $this->page_visual_style_group_has_values( $settings, $group_key, $field_keys ) ) {
            return array(
                'label'  => __( '页面自定义', 'developer-starter' ),
                'detail' => __( '此区域有单独字段覆盖。', 'developer-starter' ),
            );
        }

        $preset_key     = isset( $context['preset_key'] ) ? (string) $context['preset_key'] : '';
        $preset_label   = isset( $context['preset_label'] ) ? (string) $context['preset_label'] : '';
        $is_my_preset   = ! empty( $context['is_my_preset'] );
        $template_label = isset( $context['template_label'] ) ? (string) $context['template_label'] : '';

        if ( 'global' === $mode ) {
            return array(
                'label'  => __( '全站默认', 'developer-starter' ),
                'detail' => __( '跟随全站设计设置。', 'developer-starter' ),
            );
        }

        if ( 'custom' === $mode && '' !== $preset_key ) {
            return array(
                'label'  => $is_my_preset ? __( '我的预设', 'developer-starter' ) : __( '模板预设', 'developer-starter' ),
                'detail' => '' !== $preset_label ? $preset_label : $preset_key,
            );
        }

        if ( '' !== $template_label ) {
            return array(
                'label'  => __( '模板预设', 'developer-starter' ),
                'detail' => $template_label,
            );
        }

        return array(
            'label'  => __( '全站默认', 'developer-starter' ),
            'detail' => __( '没有页面或模板级覆盖。', 'developer-starter' ),
        );
    }

    /**
     * @param int $post_id Post ID.
     * @return array<int,array<string,string>>
     */
    private function collect_page_visual_module_overrides( $post_id ) {
        $post_id = absint( $post_id );
        if ( $post_id <= 0 ) {
            return array();
        }

        $modules = function_exists( 'developer_starter_get_raw_page_modules_meta' )
            ? developer_starter_get_raw_page_modules_meta( $post_id )
            : get_post_meta( $post_id, '_developer_starter_modules', true );
        if ( ! is_array( $modules ) || empty( $modules ) ) {
            return array();
        }

        $items = array();
        foreach ( $modules as $index => $module ) {
            if ( ! is_array( $module ) ) {
                continue;
            }

            $type = '';
            if ( isset( $module['type'] ) && is_scalar( $module['type'] ) ) {
                $type = sanitize_key( (string) $module['type'] );
            } elseif ( isset( $module['module'] ) && is_scalar( $module['module'] ) ) {
                $type = sanitize_key( (string) $module['module'] );
            }

            $data = isset( $module['data'] ) && is_array( $module['data'] ) ? $module['data'] : $module;
            $visual_payload = isset( $data['_ds_visual'] ) && is_array( $data['_ds_visual'] ) ? $data['_ds_visual'] : array();
            if ( empty( $visual_payload ) || ! $this->page_visual_payload_has_non_empty_value( $visual_payload, true ) ) {
                continue;
            }

            $module_label = $this->get_page_visual_module_label( $type );
            $items[]      = array(
                'type'  => $type,
                'label' => sprintf(
                    /* translators: 1: module label, 2: module sequence number */
                    __( '%1$s #%2$d', 'developer-starter' ),
                    $module_label,
                    absint( $index ) + 1
                ),
            );
        }

        return $items;
    }

    /**
     * @param mixed $value               Payload value.
     * @param bool  $ignore_inherit_flag Whether to ignore inherit_page flags.
     * @return bool
     */
    private function page_visual_payload_has_non_empty_value( $value, $ignore_inherit_flag = false ) {
        if ( is_array( $value ) ) {
            foreach ( $value as $key => $item ) {
                if ( $ignore_inherit_flag && 'inherit_page' === (string) $key ) {
                    continue;
                }
                if ( $ignore_inherit_flag && 'mode' === (string) $key && in_array( trim( (string) $item ), array( '', 'follow', 'custom' ), true ) ) {
                    continue;
                }
                if ( $this->page_visual_payload_has_non_empty_value( $item, $ignore_inherit_flag ) ) {
                    return true;
                }
            }
            return false;
        }

        return is_scalar( $value ) && '' !== trim( (string) $value );
    }

    /**
     * @param string $type Module type.
     * @return string
     */
    private function get_page_visual_module_label( $type ) {
        $type = sanitize_key( (string) $type );
        if ( '' === $type ) {
            return __( '未知模块', 'developer-starter' );
        }

        if ( class_exists( '\Developer_Starter\Modules\Module_Manager' ) ) {
            $module_manager = \Developer_Starter\Modules\Module_Manager::get_instance();
            $module = method_exists( $module_manager, 'get_module' ) ? $module_manager->get_module( $type ) : null;
            if ( is_object( $module ) && method_exists( $module, 'get_name' ) ) {
                $label = (string) $module->get_name();
                if ( '' !== trim( $label ) ) {
                    return $label;
                }
            }
        }

        return $type;
    }

    /**
     * Ensure "save as my preset" can capture inherited template styles too.
     *
     * @param int                 $post_id  Post ID.
     * @param array<string,mixed> $settings Raw page visual settings.
     * @return array<string,mixed>
     */
    private function prepare_page_visual_style_settings_for_saved_preset( $post_id, $settings ) {
        $settings = is_array( $settings ) ? $settings : array();
        $mode     = isset( $settings['mode'] ) ? sanitize_key( (string) $settings['mode'] ) : 'inherit';
        $preset   = isset( $settings['preset'] ) ? sanitize_key( (string) $settings['preset'] ) : '';

        if ( 'custom' === $mode && ( '' !== $preset || $this->page_visual_style_has_field_values( $settings ) ) ) {
            return $settings;
        }

        $post_id  = absint( $post_id );
        $template = '';
        if ( $post_id > 0 && function_exists( 'get_page_template_slug' ) ) {
            $template = (string) get_page_template_slug( $post_id );
        }
        if ( '' === trim( $template ) && $post_id > 0 ) {
            $template = (string) get_post_meta( $post_id, '_wp_page_template', true );
        }

        $template_skin = function_exists( 'developer_starter_get_page_visual_skin_for_template' )
            ? developer_starter_get_page_visual_skin_for_template( $template )
            : null;
        if ( is_array( $template_skin ) && ! empty( $template_skin['key'] ) ) {
            return array(
                'mode'   => 'custom',
                'preset' => sanitize_key( (string) $template_skin['key'] ),
            );
        }

        return $settings;
    }

    public function render_gallery_mode_meta_box( $post ) {
        wp_nonce_field( 'qiling_gallery_mode_nonce', 'gallery_mode_nonce' );

        $gallery_mode = get_post_meta( $post->ID, '_qiling_gallery_mode', true );
        ?>
        <div class="qiling-gallery-mode-settings">
            <p>
                <label>
                    <input type="checkbox" name="qiling_gallery_mode" value="1" <?php checked( $gallery_mode, '1' ); ?> />
                    <?php esc_html_e( '当前文章为相册模式', 'developer-starter' ); ?>
                </label>
            </p>
            <p class="description" style="color: #666; font-size: 12px; margin-top: 8px;">
                <?php esc_html_e( '勾选后，前台文章页面将以相册模式展示（一页一张图片，左右翻页）。', 'developer-starter' ); ?>
            </p>
        </div>
        <?php
    }

    public function render_post_layout_meta_box( $post ) {
        wp_nonce_field( 'qiling_post_layout_nonce', 'post_layout_nonce' );

        $full_width_mode = get_post_meta( $post->ID, '_qiling_full_width_mode', true );
        ?>
        <div class="qiling-post-layout-settings">
            <p>
                <label>
                    <input type="checkbox" name="qiling_full_width_mode" value="1" <?php checked( $full_width_mode, '1' ); ?> />
                    <?php esc_html_e( '文章页面全宽', 'developer-starter' ); ?>
                </label>
            </p>
            <p class="description" style="color: #666; font-size: 12px; margin-top: 8px;">
                <?php esc_html_e( '开启后，该文章前台页面将全宽显示，不加载侧边栏小工具。', 'developer-starter' ); ?>
            </p>
        </div>
        <?php
    }

    public function render_featured_image_url_meta_box( $post ) {
        wp_nonce_field( 'developer_starter_featured_image_url_nonce', 'featured_image_url_nonce' );
        $value = get_post_meta( $post->ID, '_developer_starter_featured_image_url', true );
        ?>
        <p>
            <input type="url" name="developer_starter_featured_image_url" value="<?php echo esc_url( $value ); ?>" class="widefat" placeholder="https://example.com/image.jpg" />
        </p>
        <p class="description"><?php esc_html_e( '填写后将优先使用该URL作为特色图', 'developer-starter' ); ?></p>
        <?php
    }

    public function save_post_meta_boxes( $post_id, $post_data = array() ) {
        $post_data = is_array( $post_data ) ? $post_data : array();

        $this->save_seo_meta_box( $post_id, $post_data );
        $this->save_page_header_meta_box( $post_id, $post_data );
        $this->save_page_footer_meta_box( $post_id, $post_data );
        $this->save_page_visual_style_meta_box( $post_id, $post_data );
        $this->save_gallery_mode_meta_box( $post_id, $post_data );
        $this->save_post_layout_meta_box( $post_id, $post_data );
        $this->save_featured_image_url_meta_box( $post_id, $post_data );
        $this->save_schema_override_meta_box( $post_id, $post_data );
    }

    private function save_seo_meta_box( $post_id, $post_data ) {
        if ( ! isset( $post_data['seo_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $post_data['seo_nonce'] ) ), 'developer_starter_seo_nonce' ) ) {
            return;
        }

        $seo_title = isset( $post_data['seo_title'] ) ? sanitize_text_field( wp_unslash( $post_data['seo_title'] ) ) : '';
        $seo_desc = isset( $post_data['seo_description'] ) ? sanitize_textarea_field( wp_unslash( $post_data['seo_description'] ) ) : '';
        $seo_keywords = isset( $post_data['seo_keywords'] ) ? sanitize_text_field( wp_unslash( $post_data['seo_keywords'] ) ) : '';
        $seo_canonical = isset( $post_data['seo_canonical'] ) ? esc_url_raw( trim( wp_unslash( $post_data['seo_canonical'] ) ) ) : '';
        $seo_noindex = isset( $post_data['seo_noindex'] ) ? '1' : '';
        $seo_nofollow = isset( $post_data['seo_nofollow'] ) ? '1' : '';
        $og_title = isset( $post_data['og_title'] ) ? sanitize_text_field( wp_unslash( $post_data['og_title'] ) ) : '';
        $og_desc = isset( $post_data['og_description'] ) ? sanitize_textarea_field( wp_unslash( $post_data['og_description'] ) ) : '';
        $og_image = isset( $post_data['og_image'] ) ? esc_url_raw( trim( wp_unslash( $post_data['og_image'] ) ) ) : '';

        update_post_meta( $post_id, '_developer_starter_seo_title', $seo_title );
        update_post_meta( $post_id, '_developer_starter_seo_description', $seo_desc );
        update_post_meta( $post_id, '_developer_starter_seo_keywords', $seo_keywords );
        update_post_meta( $post_id, '_developer_starter_seo_canonical', $seo_canonical );
        update_post_meta( $post_id, '_developer_starter_seo_noindex', $seo_noindex );
        update_post_meta( $post_id, '_developer_starter_seo_nofollow', $seo_nofollow );
        update_post_meta( $post_id, '_developer_starter_og_title', $og_title );
        update_post_meta( $post_id, '_developer_starter_og_description', $og_desc );
        update_post_meta( $post_id, '_developer_starter_og_image', $og_image );
    }

    private function save_page_header_meta_box( $post_id, $post_data ) {
        if ( ! isset( $post_data['page_header_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $post_data['page_header_nonce'] ) ), 'qiling_page_header_nonce' ) ) {
            return;
        }

        if ( isset( $post_data['qiling_hide_page_header'] ) && '1' === (string) wp_unslash( $post_data['qiling_hide_page_header'] ) ) {
            update_post_meta( $post_id, '_qiling_hide_page_header', '1' );
        } else {
            delete_post_meta( $post_id, '_qiling_hide_page_header' );
        }

        if ( isset( $post_data['qiling_transparent_header'] ) && '1' === (string) wp_unslash( $post_data['qiling_transparent_header'] ) ) {
            update_post_meta( $post_id, '_qiling_transparent_header', '1' );
        } else {
            delete_post_meta( $post_id, '_qiling_transparent_header' );
        }
    }

    private function save_page_footer_meta_box( $post_id, $post_data ) {
        if ( ! function_exists( 'developer_starter_persist_post_footer_visual_settings' ) ) {
            return;
        }
        if ( ! isset( $post_data['page_footer_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $post_data['page_footer_nonce'] ) ), 'qiling_page_footer_nonce' ) ) {
            return;
        }

        $settings = isset( $post_data['qiling_page_footer'] ) && is_array( $post_data['qiling_page_footer'] )
            ? wp_unslash( $post_data['qiling_page_footer'] )
            : array();
        developer_starter_persist_post_footer_visual_settings( $post_id, $settings );
    }

    private function save_page_visual_style_meta_box( $post_id, $post_data ) {
        if ( ! function_exists( 'developer_starter_persist_post_page_visual_style' ) ) {
            return;
        }
        if ( ! isset( $post_data['page_visual_style_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $post_data['page_visual_style_nonce'] ) ), 'qiling_page_visual_style_nonce' ) ) {
            return;
        }

        $action = isset( $post_data['qiling_page_visual_style_action'] ) ? sanitize_key( wp_unslash( (string) $post_data['qiling_page_visual_style_action'] ) ) : '';
        if ( 'restore_template' === $action || 'clear_custom' === $action ) {
            developer_starter_persist_post_page_visual_style( $post_id, array( 'mode' => 'inherit' ) );
            return;
        }
        if ( 'restore_global' === $action ) {
            developer_starter_persist_post_page_visual_style( $post_id, array( 'mode' => 'global' ) );
            return;
        }

        $copy_from = isset( $post_data['qiling_page_visual_style_copy_from'] ) ? absint( wp_unslash( (string) $post_data['qiling_page_visual_style_copy_from'] ) ) : 0;
        if ( $copy_from > 0 && $copy_from !== absint( $post_id ) && current_user_can( 'edit_post', $copy_from ) && function_exists( 'developer_starter_get_post_page_visual_style' ) ) {
            developer_starter_persist_post_page_visual_style( $post_id, developer_starter_get_post_page_visual_style( $copy_from ) );
            return;
        }

        $raw_settings = isset( $post_data['qiling_page_visual_style'] ) && is_array( $post_data['qiling_page_visual_style'] )
            ? wp_unslash( $post_data['qiling_page_visual_style'] )
            : array();
        if ( empty( $raw_settings ) && ! empty( $post_data['qiling_page_visual_style_json'] ) ) {
            $decoded_settings = json_decode( wp_unslash( (string) $post_data['qiling_page_visual_style_json'] ), true );
            if ( is_array( $decoded_settings ) ) {
                $raw_settings = $decoded_settings;
            }
        }

        if ( 'apply_preset' === $action ) {
            $raw_settings['mode'] = ! empty( $raw_settings['preset'] ) ? 'custom' : 'inherit';
        }

        $import_json = isset( $post_data['qiling_page_visual_style_import_json'] )
            ? trim( wp_unslash( (string) $post_data['qiling_page_visual_style_import_json'] ) )
            : '';
        if ( '' !== $import_json ) {
            $imported_settings = json_decode( $import_json, true );
            if ( is_array( $imported_settings ) ) {
                if ( isset( $imported_settings['visual_style'] ) && is_array( $imported_settings['visual_style'] ) ) {
                    $imported_settings = $imported_settings['visual_style'];
                } elseif ( isset( $imported_settings['visualStyle'] ) && is_array( $imported_settings['visualStyle'] ) ) {
                    $imported_settings = $imported_settings['visualStyle'];
                }

                $raw_settings = $imported_settings;
            }
        }

        $save_preset_name = isset( $post_data['qiling_page_visual_style_save_preset_name'] )
            ? sanitize_text_field( wp_unslash( (string) $post_data['qiling_page_visual_style_save_preset_name'] ) )
            : '';
        if ( '' !== $save_preset_name && current_user_can( 'edit_theme_options' ) && function_exists( 'developer_starter_save_page_visual_custom_preset' ) ) {
            developer_starter_save_page_visual_custom_preset(
                $save_preset_name,
                $this->prepare_page_visual_style_settings_for_saved_preset( $post_id, $raw_settings )
            );
        }

        $custom_preset_key   = isset( $post_data['qiling_page_visual_custom_preset_key'] ) ? sanitize_key( wp_unslash( (string) $post_data['qiling_page_visual_custom_preset_key'] ) ) : '';
        $custom_preset_label = isset( $post_data['qiling_page_visual_custom_preset_label'] ) ? sanitize_text_field( wp_unslash( (string) $post_data['qiling_page_visual_custom_preset_label'] ) ) : '';
        if ( 'rename_custom_preset' === $action && '' !== $custom_preset_key && '' !== $custom_preset_label && current_user_can( 'edit_theme_options' ) && function_exists( 'developer_starter_update_page_visual_custom_preset_label' ) ) {
            developer_starter_update_page_visual_custom_preset_label( $custom_preset_key, $custom_preset_label );
        } elseif ( 'delete_custom_preset' === $action && '' !== $custom_preset_key && current_user_can( 'edit_theme_options' ) && function_exists( 'developer_starter_delete_page_visual_custom_preset' ) ) {
            developer_starter_delete_page_visual_custom_preset( $custom_preset_key );
        }

        developer_starter_persist_post_page_visual_style( $post_id, $raw_settings );

        if ( 'bulk_apply' === $action ) {
            $bulk_settings     = $this->resolve_page_visual_bulk_style_settings( $raw_settings, $post_data );
            $bulk_page_ids_raw = isset( $post_data['qiling_page_visual_style_bulk_page_ids'] )
                ? wp_unslash( (string) $post_data['qiling_page_visual_style_bulk_page_ids'] )
                : '';
            $bulk_page_ids = preg_split( '/[\s,，;；]+/', $bulk_page_ids_raw );
            $bulk_page_ids = is_array( $bulk_page_ids ) ? $bulk_page_ids : array();

            foreach ( $bulk_page_ids as $bulk_page_id ) {
                $bulk_page_id = absint( $bulk_page_id );
                if ( $bulk_page_id <= 0 || $bulk_page_id === absint( $post_id ) ) {
                    continue;
                }
                if ( 'page' !== get_post_type( $bulk_page_id ) || ! current_user_can( 'edit_post', $bulk_page_id ) ) {
                    continue;
                }

                developer_starter_persist_post_page_visual_style( $bulk_page_id, $bulk_settings );
            }
        }
    }

    /**
     * Resolve the selected stage-four bulk style source into persistable settings.
     *
     * @param array<string,mixed> $raw_settings Current form settings.
     * @param array<string,mixed> $post_data    Raw posted data.
     * @return array<string,mixed>
     */
    private function resolve_page_visual_bulk_style_settings( $raw_settings, $post_data ) {
        $source = isset( $post_data['qiling_page_visual_style_bulk_source'] )
            ? sanitize_text_field( wp_unslash( (string) $post_data['qiling_page_visual_style_bulk_source'] ) )
            : 'current';

        if ( 'inherit' === $source ) {
            return array( 'mode' => 'inherit' );
        }

        if ( 'global' === $source ) {
            return array( 'mode' => 'global' );
        }

        if ( 0 === strpos( $source, 'preset:' ) ) {
            $preset_key = sanitize_key( substr( $source, 7 ) );
            if ( '' !== $preset_key && ( ! function_exists( 'developer_starter_page_visual_preset_exists' ) || developer_starter_page_visual_preset_exists( $preset_key ) ) ) {
                return array(
                    'mode'   => 'custom',
                    'preset' => $preset_key,
                );
            }
        }

        return is_array( $raw_settings ) ? $raw_settings : array();
    }

    private function save_gallery_mode_meta_box( $post_id, $post_data ) {
        if ( ! isset( $post_data['gallery_mode_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $post_data['gallery_mode_nonce'] ) ), 'qiling_gallery_mode_nonce' ) ) {
            return;
        }

        $gallery_mode = isset( $post_data['qiling_gallery_mode'] ) ? '1' : '';
        update_post_meta( $post_id, '_qiling_gallery_mode', $gallery_mode );
    }

    private function save_post_layout_meta_box( $post_id, $post_data ) {
        if ( ! isset( $post_data['post_layout_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $post_data['post_layout_nonce'] ) ), 'qiling_post_layout_nonce' ) ) {
            return;
        }

        $full_width = isset( $post_data['qiling_full_width_mode'] ) ? '1' : '';
        update_post_meta( $post_id, '_qiling_full_width_mode', $full_width );
    }

    private function save_featured_image_url_meta_box( $post_id, $post_data ) {
        if ( ! isset( $post_data['featured_image_url_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $post_data['featured_image_url_nonce'] ) ), 'developer_starter_featured_image_url_nonce' ) ) {
            return;
        }

        $featured_url = isset( $post_data['developer_starter_featured_image_url'] ) ? trim( wp_unslash( $post_data['developer_starter_featured_image_url'] ) ) : '';
        $featured_url = $featured_url ? esc_url_raw( $featured_url ) : '';

        if ( $featured_url ) {
            update_post_meta( $post_id, '_developer_starter_featured_image_url', $featured_url );
        } else {
            delete_post_meta( $post_id, '_developer_starter_featured_image_url' );
        }
    }

    /**
     * Save page-level Schema override fields.
     *
     * @param int                 $post_id Post id.
     * @param array<string,mixed> $post_data Raw request data.
     * @return void
     */
    private function save_schema_override_meta_box( $post_id, $post_data ) {
        if ( ! class_exists( '\Developer_Starter\SEO\Industry_Schema_Engine' ) ) {
            return;
        }
        if ( ! isset( $post_data['qiling_schema_override_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $post_data['qiling_schema_override_nonce'] ) ), 'qiling_schema_override_nonce' ) ) {
            return;
        }

        $raw = isset( $post_data['qiling_schema_override'] ) && is_array( $post_data['qiling_schema_override'] )
            ? $post_data['qiling_schema_override']
            : array();

        Industry_Schema_Engine::persist_page_schema_override( $post_id, $raw );
    }
}
