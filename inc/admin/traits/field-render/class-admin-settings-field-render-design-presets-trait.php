<?php
/**
 * Admin settings design preset field render trait.
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Admin\Traits;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

trait Admin_Settings_Field_Render_Design_Presets_Trait {

    private function render_design_preset_manager_field( $options ) {
        echo '<tr id="setting-row-design_preset_manager" data-setting-id="design_preset_manager"><th scope="row">' . esc_html__( '自定义预设', 'developer-starter' ) . '</th><td>';

        if ( ! class_exists( '\Developer_Starter\Core\Design_Tokens' ) ) {
            echo '<p class="description">' . esc_html__( '设计预设服务未加载。', 'developer-starter' ) . '</p>';
            echo '</td></tr>';
            return;
        }

        $payload = \Developer_Starter\Core\Design_Tokens::get_client_payload( is_array( $options ) ? $options : array() );
        $custom_presets = isset( $payload['customPresets'] ) && is_array( $payload['customPresets'] ) ? array_values( $payload['customPresets'] ) : array();
        $current_preset = isset( $payload['preset'] ) ? (string) $payload['preset'] : 'default';
        $schema_version = isset( $payload['schemaVersion'] ) ? (string) $payload['schemaVersion'] : '';
        $current_preset_label = isset( $payload['presetLabel'] ) ? (string) $payload['presetLabel'] : $current_preset;
        $option_name = $this->option_name;
        $field_groups = array(
            'brand' => array(
                'title'  => __( '品牌色板', 'developer-starter' ),
                'fields' => array(
                    'primary'       => '#2563eb',
                    'primary_hover' => '#1d4ed8',
                    'secondary'     => '#0f766e',
                    'accent'        => '#f97316',
                ),
            ),
            'semantic' => array(
                'title'  => __( '语义色', 'developer-starter' ),
                'fields' => array(
                    'success' => '#16a34a',
                    'info'    => '#0ea5e9',
                    'warning' => '#f59e0b',
                    'error'   => '#dc2626',
                    'overlay' => 'rgba(15, 23, 42, 0.68)',
                ),
            ),
            'surface' => array(
                'title'  => __( '表面与文本', 'developer-starter' ),
                'fields' => array(
                    'text'        => '#1f2937',
                    'text_muted'  => '#64748b',
                    'heading'     => '#111827',
                    'background'  => '#ffffff',
                    'surface'     => '#ffffff',
                    'surface_alt' => '#f8fafc',
                    'border'      => '#e5e7eb',
                ),
            ),
            'neutral' => array(
                'title'  => __( '常用中性色', 'developer-starter' ),
                'fields' => array(
                    'neutral_0'   => '#ffffff',
                    'neutral_50'  => '#f8fafc',
                    'neutral_100' => '#f1f5f9',
                    'neutral_200' => '#e2e8f0',
                    'neutral_300' => '#cbd5e1',
                    'neutral_400' => '#94a3b8',
                    'neutral_500' => '#64748b',
                    'neutral_600' => '#475569',
                    'neutral_700' => '#334155',
                    'neutral_800' => '#1e293b',
                    'neutral_900' => '#0f172a',
                ),
            ),
            'dark' => array(
                'title'  => __( '暗色映射', 'developer-starter' ),
                'fields' => array(
                    'dark_bg'      => '#111827',
                    'dark_surface' => '#1f2937',
                    'dark_text'    => '#f3f4f6',
                    'dark_text_muted' => '#cbd5e1',
                    'dark_border'  => '#334155',
                ),
            ),
        );
        $current_tokens = isset( $payload['tokens'] ) && is_array( $payload['tokens'] ) ? $payload['tokens'] : array();
        $current_preset_seed_tokens = array();
        foreach ( $field_groups as $group ) {
            if ( empty( $group['fields'] ) || ! is_array( $group['fields'] ) ) {
                continue;
            }
            foreach ( $group['fields'] as $token_key => $placeholder ) {
                unset( $placeholder );
                $current_preset_seed_tokens[ $token_key ] = isset( $current_tokens[ $token_key ] ) ? (string) $current_tokens[ $token_key ] : '';
            }
        }
        $current_preset_seed = array(
            'id'               => $current_preset,
            'label'            => $current_preset_label,
            'tokens'           => $current_preset_seed_tokens,
            'typographySystem' => isset( $payload['typographySystem'] ) && is_array( $payload['typographySystem'] ) ? $payload['typographySystem'] : array(),
            'layoutSystem'     => isset( $payload['layoutSystem'] ) && is_array( $payload['layoutSystem'] ) ? $payload['layoutSystem'] : array(),
            'componentStyles'  => isset( $payload['componentStyles'] ) && is_array( $payload['componentStyles'] ) ? $payload['componentStyles'] : array(),
        );
        $current_token_option_map = isset( $payload['tokenOptionMap'] ) && is_array( $payload['tokenOptionMap'] ) ? $payload['tokenOptionMap'] : array();
        $manager_messages = array(
            'exportReady'   => __( '已生成可分享内容，可直接复制或发给别人。', 'developer-starter' ),
            'copySuccess'   => __( '可分享内容已复制到剪贴板。', 'developer-starter' ),
            'copyFallback'  => __( '浏览器不支持直接复制，已选中文本框内容，可手动复制。', 'developer-starter' ),
            'copyFailed'    => __( '复制失败，请手动复制文本框中的内容。', 'developer-starter' ),
            'importEmpty'   => __( '请先粘贴要导入的预设内容。', 'developer-starter' ),
            'importInvalid' => __( '内容格式不对，请确认粘贴的是完整预设内容。', 'developer-starter' ),
            'importAppend'  => __( '已放进当前列表，保存后生效。', 'developer-starter' ),
            'importReplace' => __( '已用导入内容替换当前列表，保存后生效。', 'developer-starter' ),
            'customSuffix'  => __( '（自定义）', 'developer-starter' ),
            'customUntitled' => __( '未命名自定义预设', 'developer-starter' ),
            'snapshotSummary' => __( '排版 %1$d · 布局 %2$d · 组件 %3$d', 'developer-starter' ),
            'snapshotEmpty' => __( '当前仅保存了颜色；可使用上方按钮保存完整效果。', 'developer-starter' ),
            'snapshotSynced' => __( '已收下当前站点的排版、布局和组件效果。', 'developer-starter' ),
            'captureSaved' => __( '已新建一张“当前站点风格”预设卡，保存后生效。', 'developer-starter' ),
            'defaultPresetLabel' => __( '我的站点风格', 'developer-starter' ),
        );
        $preset_select_selector = wp_json_encode( 'select[name="' . $option_name . '[design_preset]"]' );

        echo '<style>
        .ds-design-preset-manager{display:grid;gap:14px;max-width:1180px;}
        .ds-design-preset-manager__tip{margin:0;color:#475569;line-height:1.7;}
        .ds-design-preset-manager__list{display:grid;gap:16px;}
        .ds-design-preset-card{border:1px solid #dbe3f0;border-radius:16px;background:#fff;box-shadow:0 10px 26px rgba(15,23,42,.04);overflow:hidden;}
        .ds-design-preset-card__head{display:flex;justify-content:space-between;gap:12px;align-items:center;padding:16px 18px;border-bottom:1px solid #eef2f7;background:linear-gradient(135deg,#f8fafc 0%,#f1f5f9 100%);}
        .ds-design-preset-card__head strong{font-size:15px;color:#0f172a;}
        .ds-design-preset-card__head p{margin:4px 0 0;color:#64748b;}
        .ds-design-preset-card__meta{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:12px;padding:16px 18px 0;}
        .ds-design-preset-card__meta label,.ds-design-preset-card__group label{display:flex;flex-direction:column;gap:6px;font-weight:600;color:#1e293b;}
        .ds-design-preset-card__meta input,.ds-design-preset-card__group input{width:100%;}
        .ds-design-preset-card__groups{display:grid;gap:14px;padding:16px 18px 18px;}
        .ds-design-preset-card__group{border:1px solid #edf2f7;border-radius:12px;padding:14px 16px;background:#fcfdff;}
        .ds-design-preset-card__group h5{margin:0 0 12px;font-size:13px;color:#0f172a;}
        .ds-design-preset-card__grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:12px;}
        .ds-design-preset-card__snapshot{border:1px solid #edf2f7;border-radius:12px;padding:14px 16px;background:#f8fafc;display:grid;gap:10px;}
        .ds-design-preset-card__snapshot-head{display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;}
        .ds-design-preset-card__snapshot-title{font-size:13px;color:#0f172a;font-weight:700;}
        .ds-design-preset-card__snapshot-badges{display:flex;flex-wrap:wrap;gap:8px;}
        .ds-design-preset-card__snapshot-badge{display:inline-flex;align-items:center;min-height:26px;padding:0 10px;border-radius:999px;background:#e2e8f0;color:#334155;font-size:12px;font-weight:600;}
        .ds-design-preset-card__snapshot-summary{margin:0;color:#64748b;line-height:1.6;}
        .ds-design-preset-card__snapshot textarea{display:none;}
        .ds-design-preset-manager__actions{display:flex;gap:10px;align-items:center;flex-wrap:wrap;}
        .ds-design-preset-manager__badge{display:inline-flex;align-items:center;padding:2px 8px;border-radius:999px;background:#e0f2fe;color:#075985;font-size:12px;font-weight:600;}
        .ds-design-preset-exchange{border:1px solid #dbe3f0;border-radius:16px;background:#fff;padding:16px 18px;box-shadow:0 10px 26px rgba(15,23,42,.04);display:grid;gap:12px;}
        .ds-design-preset-exchange h4{margin:0;color:#0f172a;font-size:15px;}
        .ds-design-preset-exchange p{margin:0;color:#64748b;line-height:1.7;}
        .ds-design-preset-exchange textarea{width:100%;min-height:220px;font-family:ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,"Liberation Mono","Courier New",monospace;font-size:12px;line-height:1.6;}
        .ds-design-preset-exchange__actions{display:flex;gap:10px;align-items:center;flex-wrap:wrap;}
        .ds-design-preset-exchange__status{margin:0;font-size:12px;color:#64748b;}
        .ds-design-preset-exchange__status.is-success{color:#166534;}
        .ds-design-preset-exchange__status.is-error{color:#b91c1c;}
        </style>';

        echo '<div class="ds-design-preset-manager" data-schema-version="' . esc_attr( $schema_version ) . '" data-current-preset="' . esc_attr( $current_preset ) . '" data-current-preset-label="' . esc_attr( $current_preset_label ) . '">';
        echo '<p class="ds-design-preset-manager__tip">' . esc_html__( '这里可以把一整套站点风格收起来复用。除了颜色，也能顺手带上排版、布局和组件样式，适合做行业风格、品牌风格和快速换肤。', 'developer-starter' ) . '</p>';
        echo '<input type="hidden" name="' . esc_attr( $option_name ) . '[design_custom_presets_present]" value="1" />';
        echo '<div class="ds-design-preset-manager__actions">';
        echo '<button type="button" class="button button-primary ds-capture-design-preset">' . esc_html__( '保存当前站点为新预设', 'developer-starter' ) . '</button>';
        echo '<button type="button" class="button button-secondary ds-add-design-preset">' . esc_html__( '新增自定义预设', 'developer-starter' ) . '</button>';
        echo '<button type="button" class="button ds-clone-design-preset">' . esc_html__( '复制当前预设', 'developer-starter' ) . '</button>';
        if ( ! empty( $custom_presets ) && isset( $payload['presetSource'] ) && 'custom' === (string) $payload['presetSource'] ) {
            echo '<span class="ds-design-preset-manager__badge">' . esc_html( sprintf( __( '当前使用：%s', 'developer-starter' ), $current_preset ) ) . '</span>';
        }
        echo '</div>';
        echo '<div class="ds-design-preset-manager__list" data-design-preset-list="1" data-next-index="' . esc_attr( count( $custom_presets ) ) . '">';

        $render_card = function( $preset, $index ) use ( $field_groups, $option_name ) {
            $preset = is_array( $preset ) ? $preset : array();
            $preset_id = isset( $preset['id'] ) ? (string) $preset['id'] : '';
            $preset_label = isset( $preset['label'] ) ? (string) $preset['label'] : '';
            $preset_tokens = isset( $preset['tokens'] ) && is_array( $preset['tokens'] ) ? $preset['tokens'] : array();

            echo '<div class="ds-design-preset-card">';
            echo '<div class="ds-design-preset-card__head">';
            $preset_typography = isset( $preset['typography_system'] ) && is_array( $preset['typography_system'] ) ? $preset['typography_system'] : array();
            $preset_layout = isset( $preset['layout_system'] ) && is_array( $preset['layout_system'] ) ? $preset['layout_system'] : array();
            $preset_components = isset( $preset['component_styles'] ) && is_array( $preset['component_styles'] ) ? $preset['component_styles'] : array();
            $typography_json = wp_json_encode( $preset_typography );
            $layout_json = wp_json_encode( $preset_layout );
            $components_json = wp_json_encode( $preset_components );

            echo '<div><strong>' . esc_html__( '一套站点风格', 'developer-starter' ) . '</strong><p>' . esc_html__( '可以同时收下颜色、排版、布局和常用组件效果，后面直接复用。', 'developer-starter' ) . '</p></div>';
            echo '<button type="button" class="button-link-delete ds-remove-design-preset">' . esc_html__( '删除', 'developer-starter' ) . '</button>';
            echo '</div>';

            echo '<div class="ds-design-preset-card__meta">';
            echo '<input type="hidden" data-design-preset-field="id" name="' . esc_attr( $option_name ) . '[design_custom_presets][' . esc_attr( $index ) . '][id]" value="' . esc_attr( $preset_id ) . '" />';
            echo '<label><span>' . esc_html__( '预设名称', 'developer-starter' ) . '</span><input type="text" class="regular-text" data-design-preset-field="label" name="' . esc_attr( $option_name ) . '[design_custom_presets][' . esc_attr( $index ) . '][label]" value="' . esc_attr( $preset_label ) . '" placeholder="' . esc_attr__( '我的品牌预设', 'developer-starter' ) . '" /></label>';
            echo '</div>';

            echo '<div class="ds-design-preset-card__groups">';
            echo '<section class="ds-design-preset-card__snapshot">';
            echo '<div class="ds-design-preset-card__snapshot-head">';
            echo '<div class="ds-design-preset-card__snapshot-title">' . esc_html__( '把当前站点样式收进来', 'developer-starter' ) . '</div>';
            echo '<button type="button" class="button button-secondary ds-sync-design-preset-snapshot">' . esc_html__( '收下当前站点效果', 'developer-starter' ) . '</button>';
            echo '</div>';
            echo '<div class="ds-design-preset-card__snapshot-badges">';
            echo '<span class="ds-design-preset-card__snapshot-badge" data-design-preset-snapshot-badge="typography">' . esc_html__( '排版 0', 'developer-starter' ) . '</span>';
            echo '<span class="ds-design-preset-card__snapshot-badge" data-design-preset-snapshot-badge="layout">' . esc_html__( '布局 0', 'developer-starter' ) . '</span>';
            echo '<span class="ds-design-preset-card__snapshot-badge" data-design-preset-snapshot-badge="components">' . esc_html__( '组件 0', 'developer-starter' ) . '</span>';
            echo '</div>';
            echo '<p class="ds-design-preset-card__snapshot-summary" data-design-preset-snapshot-summary="1">' . esc_html__( '当前仅保存了颜色；可使用上方按钮保存完整效果。', 'developer-starter' ) . '</p>';
            echo '<textarea data-design-preset-json="typography" name="' . esc_attr( $option_name ) . '[design_custom_presets][' . esc_attr( $index ) . '][typography_json]">' . esc_textarea( is_string( $typography_json ) ? $typography_json : '{}' ) . '</textarea>';
            echo '<textarea data-design-preset-json="layout" name="' . esc_attr( $option_name ) . '[design_custom_presets][' . esc_attr( $index ) . '][layout_json]">' . esc_textarea( is_string( $layout_json ) ? $layout_json : '{}' ) . '</textarea>';
            echo '<textarea data-design-preset-json="components" name="' . esc_attr( $option_name ) . '[design_custom_presets][' . esc_attr( $index ) . '][components_json]">' . esc_textarea( is_string( $components_json ) ? $components_json : '{}' ) . '</textarea>';
            echo '</section>';
            foreach ( $field_groups as $group ) {
                echo '<section class="ds-design-preset-card__group">';
                echo '<h5>' . esc_html( (string) $group['title'] ) . '</h5>';
                echo '<div class="ds-design-preset-card__grid">';
                foreach ( $group['fields'] as $token_key => $placeholder ) {
                    $value = isset( $preset_tokens[ $token_key ] ) ? (string) $preset_tokens[ $token_key ] : '';
                    echo '<label>';
                    echo '<span>' . esc_html( str_replace( '_', ' ', $token_key ) ) . '</span>';
                    echo '<input type="text" class="regular-text" data-token-key="' . esc_attr( $token_key ) . '" name="' . esc_attr( $option_name ) . '[design_custom_presets][' . esc_attr( $index ) . '][tokens][' . esc_attr( $token_key ) . ']" value="' . esc_attr( $value ) . '" placeholder="' . esc_attr( (string) $placeholder ) . '" />';
                    echo '</label>';
                }
                echo '</div>';
                echo '</section>';
            }
            echo '</div>';
            echo '</div>';
        };

        foreach ( $custom_presets as $index => $preset ) {
            $render_card( $preset, (int) $index );
        }

        echo '</div>';
        echo '<section class="ds-design-preset-exchange">';
        echo '<h4>' . esc_html__( '分享 / 导入预设', 'developer-starter' ) . '</h4>';
        echo '<p>' . esc_html__( '可生成可分享内容，也可粘贴外部预设内容。系统会一起带上颜色、排版、布局和组件快照。', 'developer-starter' ) . '</p>';
        echo '<textarea class="large-text code" data-design-preset-exchange rows="10" spellcheck="false" placeholder="' . esc_attr__( '生成后可复制；也可在此粘贴预设内容。', 'developer-starter' ) . '"></textarea>';
        echo '<div class="ds-design-preset-exchange__actions">';
        echo '<button type="button" class="button ds-export-design-presets">' . esc_html__( '生成可分享内容', 'developer-starter' ) . '</button>';
        echo '<button type="button" class="button ds-copy-design-presets">' . esc_html__( '复制内容', 'developer-starter' ) . '</button>';
        echo '<button type="button" class="button ds-import-design-presets">' . esc_html__( '导入到当前列表', 'developer-starter' ) . '</button>';
        echo '<button type="button" class="button ds-replace-design-presets">' . esc_html__( '用导入内容替换当前列表', 'developer-starter' ) . '</button>';
        echo '</div>';
        echo '<p class="ds-design-preset-exchange__status" data-design-preset-status="1">' . esc_html__( '保存主题设置后，这里的导入或新增内容才会真正生效。', 'developer-starter' ) . '</p>';
        echo '</section>';
        echo '</div>';

        ob_start();
        $render_card( array(), '__INDEX__' );
        $template = (string) ob_get_clean();
        echo '<script type="text/template" id="ds-design-preset-template">' . $template . '</script>';
        echo '<script type="application/json" id="ds-design-preset-current-seed">' . wp_json_encode( $current_preset_seed ) . '</script>';
        echo '<script type="application/json" id="ds-design-preset-token-option-map">' . wp_json_encode( $current_token_option_map ) . '</script>';
        echo '<script type="application/json" id="ds-design-preset-manager-messages">' . wp_json_encode( $manager_messages ) . '</script>';
        echo '<script>
        document.addEventListener("DOMContentLoaded", function(){
            var root = document.querySelector(".ds-design-preset-manager");
            if (!root) { return; }
            var list = root.querySelector("[data-design-preset-list]");
            var templateNode = document.getElementById("ds-design-preset-template");
            var currentSeedNode = document.getElementById("ds-design-preset-current-seed");
            var tokenOptionMapNode = document.getElementById("ds-design-preset-token-option-map");
            var messagesNode = document.getElementById("ds-design-preset-manager-messages");
            var exchangeTextarea = root.querySelector("[data-design-preset-exchange]");
            var statusNode = root.querySelector("[data-design-preset-status]");
            var presetSelect = document.querySelector(' . $preset_select_selector . ');
            if (!list || !templateNode) { return; }
            function parseJsonNode(node, fallback) {
                if (!node) {
                    return fallback;
                }
                try {
                    return JSON.parse(node.textContent || "");
                } catch (error) {
                    return fallback;
                }
            }
            var messages = parseJsonNode(messagesNode, {});
            var tokenOptionMap = parseJsonNode(tokenOptionMapNode, {});
            function slugify(value) {
                return String(value || "")
                    .toLowerCase()
                    .replace(/[^a-z0-9]+/g, "-")
                    .replace(/^-+|-+$/g, "")
                    .replace(/-{2,}/g, "-");
            }
            function buildUniquePresetId(baseId, currentCard) {
                var normalized = slugify(baseId || "");
                if (!normalized) {
                    normalized = "custom-preset-" + Date.now();
                }
                var usedIds = {};
                Array.prototype.slice.call(list.querySelectorAll(".ds-design-preset-card")).forEach(function(card){
                    if (!card || card === currentCard) {
                        return;
                    }
                    var idInput = card.querySelector("[data-design-preset-field=\'id\']");
                    var value = idInput ? slugify(String(idInput.value || "").trim()) : "";
                    if (value) {
                        usedIds[value] = true;
                    }
                });
                if (!usedIds[normalized]) {
                    return normalized;
                }
                var index = 2;
                while (usedIds[normalized + "-" + index]) {
                    index += 1;
                }
                return normalized + "-" + index;
            }
            function ensurePresetIdentity(card, options) {
                if (!card) {
                    return null;
                }
                options = options && typeof options === "object" ? options : {};
                var idInput = card.querySelector("[data-design-preset-field=\'id\']");
                var labelInput = card.querySelector("[data-design-preset-field=\'label\']");
                var label = labelInput ? String(labelInput.value || "").trim() : "";
                var currentId = idInput ? String(idInput.value || "").trim() : "";
                if (!label && options.forceLabel) {
                    label = String(options.forceLabel || "").trim();
                    if (labelInput) {
                        labelInput.value = label;
                    }
                }
                if (!label && !currentId && !options.preferredId) {
                    return null;
                }
                var baseId = options.preferredId
                    ? String(options.preferredId)
                    : (currentId || label || ("custom-preset-" + Date.now()));
                var uniqueId = buildUniquePresetId(baseId, card);
                if (idInput) {
                    idInput.value = uniqueId;
                }
                return {
                    id: uniqueId,
                    label: label
                };
            }
            function setStatus(message, tone) {
                if (!statusNode) {
                    return;
                }
                statusNode.textContent = message || "";
                statusNode.className = "ds-design-preset-exchange__status" + (tone ? " is-" + tone : "");
            }
            var presetSnapshotHelper = window.DSDesignPresetSnapshot || {};
            function readPresetSnapshot(card) {
                if (typeof presetSnapshotHelper.readSnapshot === "function") {
                    return presetSnapshotHelper.readSnapshot(card);
                }
                return {
                    typographySystem: {},
                    layoutSystem: {},
                    componentStyles: {}
                };
            }
            function applySnapshotToCard(card, snapshot) {
                if (typeof presetSnapshotHelper.applySnapshotToCard === "function") {
                    presetSnapshotHelper.applySnapshotToCard(card, snapshot, messages);
                }
            }
            function optionExists(value) {
                if (!presetSelect) {
                    return false;
                }
                return Array.prototype.slice.call(presetSelect.options).some(function(option){
                    return option.value === value;
                });
            }
            function getNextIndex() {
                var index = parseInt(list.getAttribute("data-next-index") || "0", 10);
                if (isNaN(index) || index < 0) {
                    index = list.children.length;
                }
                list.setAttribute("data-next-index", String(index + 1));
                return index;
            }
            function applyPresetSeed(card, seed) {
                if (!card || !seed || typeof seed !== "object") {
                    return card;
                }
                var idInput = card.querySelector("[data-design-preset-field=\'id\']");
                var labelInput = card.querySelector("[data-design-preset-field=\'label\']");
                var rawLabel = seed.label || seed.name || seed.title || "";
                var label = rawLabel ? String(rawLabel) : (messages.defaultPresetLabel || "我的站点风格");
                var rawId = seed.id || seed.key || seed.slug || "";
                if (labelInput) {
                    labelInput.value = label;
                }
                ensurePresetIdentity(card, { preferredId: rawId });
                var tokens = seed.tokens && typeof seed.tokens === "object" ? seed.tokens : {};
                Object.keys(tokens).forEach(function(key){
                    var input = card.querySelector("[data-token-key=\'" + key + "\']");
                    if (input) {
                        input.value = tokens[key];
                    }
                });
                applySnapshotToCard(card, {
                    typographySystem: seed.typographySystem || seed.typography_system || {},
                    layoutSystem: seed.layoutSystem || seed.layout_system || {},
                    componentStyles: seed.componentStyles || seed.component_styles || {}
                });
                return card;
            }
            function appendPresetCard(seed) {
                var html = templateNode.innerHTML.replace(/__INDEX__/g, String(getNextIndex()));
                list.insertAdjacentHTML("beforeend", html);
                var card = list.lastElementChild;
                if (!card) {
                    return card;
                }
                if (!seed || typeof seed !== "object") {
                    applySnapshotToCard(card, readPresetSnapshot(card));
                    return card;
                }
                if (!seed._keepLabel) {
                    seed = Object.assign({}, seed, {
                        label: seed.label ? String(seed.label) + " 副本" : (messages.defaultPresetLabel || "我的站点风格")
                    });
                }
                return applyPresetSeed(card, seed);
            }
            function normalizePresetCollection(collection) {
                if (Array.isArray(collection)) {
                    return collection;
                }
                if (!collection || typeof collection !== "object") {
                    return [];
                }
                return Object.keys(collection).map(function(key){
                    var item = collection[key];
                    if (!item || typeof item !== "object" || Array.isArray(item)) {
                        return null;
                    }
                    if (!item.id && !item.key) {
                        item = Object.assign({ id: key }, item);
                    }
                    return item;
                }).filter(Boolean);
            }
            function normalizeImportedPresets(raw) {
                if (Array.isArray(raw)) {
                    return raw;
                }
                if (!raw || typeof raw !== "object") {
                    return [];
                }
                if (raw.customPresets || raw.custom_presets) {
                    return normalizePresetCollection(raw.customPresets || raw.custom_presets);
                }
                if (raw.presets) {
                    return normalizePresetCollection(raw.presets);
                }
                if (raw.id || raw.key || raw.label || raw.name || raw.title) {
                    return [raw];
                }
                return [];
            }
            function readPresetCard(card) {
                if (!card) {
                    return null;
                }
                var snapshot = readPresetSnapshot(card);
                var preset = {
                    id: "",
                    label: "",
                    tokens: {},
                    typographySystem: snapshot.typographySystem,
                    layoutSystem: snapshot.layoutSystem,
                    componentStyles: snapshot.componentStyles
                };
                var idInput = card.querySelector("[data-design-preset-field=\'id\']");
                var labelInput = card.querySelector("[data-design-preset-field=\'label\']");
                if (idInput) {
                    preset.id = String(idInput.value || "").trim();
                }
                if (labelInput) {
                    preset.label = String(labelInput.value || "").trim();
                }
                card.querySelectorAll("[data-token-key]").forEach(function(input){
                    var key = input.getAttribute("data-token-key") || "";
                    var value = String(input.value || "").trim();
                    if (key && value) {
                        preset.tokens[key] = value;
                    }
                });
                if (!preset.id && !preset.label && Object.keys(preset.tokens).length === 0) {
                    return null;
                }
                return preset;
            }
            function collectPresetCards() {
                return Array.prototype.slice.call(list.querySelectorAll(".ds-design-preset-card"))
                    .map(readPresetCard)
                    .filter(Boolean);
            }
            function syncPresetSelectOptions() {
                if (!presetSelect) {
                    return;
                }
                var currentValue = presetSelect.value;
                Array.prototype.slice.call(presetSelect.querySelectorAll("[data-design-custom-preset-option=\"1\"]")).forEach(function(option){
                    option.remove();
                });
                var seen = {};
                collectPresetCards().forEach(function(preset, index){
                    var value = slugify(preset.id || preset.label || ("custom-preset-" + (index + 1)));
                    if (!value || seen[value]) {
                        return;
                    }
                    seen[value] = true;
                    var label = preset.label || preset.id || ((messages.customUntitled || "未命名自定义预设") + " " + (index + 1));
                    var option = document.createElement("option");
                    option.value = value;
                    option.textContent = label + (messages.customSuffix || "（自定义）");
                    option.setAttribute("data-design-custom-preset-option", "1");
                    presetSelect.appendChild(option);
                });
                if (currentValue && optionExists(currentValue)) {
                    presetSelect.value = currentValue;
                    return;
                }
                var fallbackValue = root.getAttribute("data-current-preset") || "default";
                if (fallbackValue && optionExists(fallbackValue)) {
                    presetSelect.value = fallbackValue;
                } else if (presetSelect.options.length) {
                    presetSelect.value = presetSelect.options[0].value;
                }
            }
            function buildExportPayload() {
                return {
                    schemaVersion: root.getAttribute("data-schema-version") || "",
                    exportedAt: new Date().toISOString(),
                    currentPreset: root.getAttribute("data-current-preset") || "",
                    currentPresetLabel: root.getAttribute("data-current-preset-label") || "",
                    customPresets: collectPresetCards()
                };
            }
            function copyText(text) {
                if (!exchangeTextarea) {
                    return Promise.reject(new Error("missing textarea"));
                }
                exchangeTextarea.value = text;
                exchangeTextarea.focus();
                exchangeTextarea.select();
                if (navigator.clipboard && window.isSecureContext) {
                    return navigator.clipboard.writeText(text);
                }
                try {
                    if (document.execCommand("copy")) {
                        return Promise.resolve();
                    }
                } catch (error) {
                    return Promise.reject(error);
                }
                return Promise.reject(new Error("copy unsupported"));
            }
            function replacePresetCards(presets) {
                list.innerHTML = "";
                list.setAttribute("data-next-index", "0");
                presets.forEach(function(preset){
                    appendPresetCard(Object.assign({ _keepLabel: true }, preset));
                });
            }
            function appendImportedPresets(presets) {
                presets.forEach(function(preset){
                    appendPresetCard(Object.assign({ _keepLabel: true }, preset));
                });
            }
            var currentSeed = parseJsonNode(currentSeedNode, null);
            function buildCurrentTokenSnapshot() {
                var tokens = currentSeed && currentSeed.tokens && typeof currentSeed.tokens === "object"
                    ? JSON.parse(JSON.stringify(currentSeed.tokens))
                    : {};
                Object.keys(tokenOptionMap || {}).forEach(function(tokenKey){
                    var optionKey = String(tokenOptionMap[tokenKey] || "").trim();
                    if (!optionKey) {
                        return;
                    }
                    var input = document.getElementById(optionKey) || document.querySelector("[name$=\'[" + optionKey + "]\']");
                    if (!input) {
                        return;
                    }
                    var value = String(input.value || "").trim();
                    if (value) {
                        tokens[tokenKey] = value;
                    } else {
                        delete tokens[tokenKey];
                    }
                });
                return tokens;
            }
            function buildCurrentDesignSnapshot() {
                var snapshot = {
                    typographySystem: currentSeed && currentSeed.typographySystem && typeof currentSeed.typographySystem === "object"
                        ? JSON.parse(JSON.stringify(currentSeed.typographySystem))
                        : {},
                    layoutSystem: currentSeed && currentSeed.layoutSystem && typeof currentSeed.layoutSystem === "object"
                        ? JSON.parse(JSON.stringify(currentSeed.layoutSystem))
                        : {},
                    componentStyles: currentSeed && currentSeed.componentStyles && typeof currentSeed.componentStyles === "object"
                        ? JSON.parse(JSON.stringify(currentSeed.componentStyles))
                        : {}
                };
                document.querySelectorAll("[data-ds-typography-input=\'1\']").forEach(function(input){
                    var styleKey = input.getAttribute("data-style-key");
                    var deviceKey = input.getAttribute("data-device-key");
                    var propertyKey = input.getAttribute("data-property-key");
                    if (!styleKey || !deviceKey || !propertyKey) {
                        return;
                    }
                    snapshot.typographySystem[styleKey] = snapshot.typographySystem[styleKey] && typeof snapshot.typographySystem[styleKey] === "object" ? snapshot.typographySystem[styleKey] : {};
                    snapshot.typographySystem[styleKey][deviceKey] = snapshot.typographySystem[styleKey][deviceKey] && typeof snapshot.typographySystem[styleKey][deviceKey] === "object" ? snapshot.typographySystem[styleKey][deviceKey] : {};
                    snapshot.typographySystem[styleKey][deviceKey][propertyKey] = String(input.value || "").trim();
                });
                document.querySelectorAll("[data-ds-layout-input=\'1\']").forEach(function(input){
                    var layoutGroup = input.getAttribute("data-layout-group");
                    var layoutDevice = input.getAttribute("data-layout-device");
                    if (!layoutGroup || !layoutDevice) {
                        return;
                    }
                    snapshot.layoutSystem[layoutGroup] = snapshot.layoutSystem[layoutGroup] && typeof snapshot.layoutSystem[layoutGroup] === "object" ? snapshot.layoutSystem[layoutGroup] : {};
                    snapshot.layoutSystem[layoutGroup][layoutDevice] = String(input.value || "").trim();
                });
                var layoutModeField = document.querySelector("[data-ds-layout-mode=\'1\']");
                if (layoutModeField) {
                    snapshot.layoutSystem.layout_mode = String(layoutModeField.value || "").trim();
                }
                Array.prototype.slice.call(document.querySelectorAll("[id^=\'design_component_\']")).forEach(function(input){
                    if (!input || !input.id) {
                        return;
                    }
                    var componentKey = input.id.replace(/^design_component_/, "");
                    var value = String(input.value || "").trim();
                    if (!componentKey) {
                        return;
                    }
                    if (value) {
                        snapshot.componentStyles[componentKey] = value;
                    } else {
                        delete snapshot.componentStyles[componentKey];
                    }
                });
                return snapshot;
            }
            Array.prototype.slice.call(list.querySelectorAll(".ds-design-preset-card")).forEach(function(card){
                applySnapshotToCard(card, readPresetSnapshot(card));
            });
            syncPresetSelectOptions();
            root.addEventListener("input", function(event){
                if (event.target.closest(".ds-design-preset-card")) {
                    if (event.target.matches("[data-design-preset-field=\'label\']")) {
                        ensurePresetIdentity(event.target.closest(".ds-design-preset-card"));
                    }
                    syncPresetSelectOptions();
                }
            });
            root.addEventListener("click", function(event){
                if (event.target.closest(".ds-capture-design-preset")) {
                    event.preventDefault();
                    var card = appendPresetCard({
                        _keepLabel: true,
                        label: messages.defaultPresetLabel || "我的站点风格"
                    });
                    if (card) {
                        applyTokensToCard(card, buildCurrentTokenSnapshot());
                        applySnapshotToCard(card, buildCurrentDesignSnapshot());
                        ensurePresetIdentity(card, {
                            forceLabel: root.getAttribute("data-current-preset-label") || messages.defaultPresetLabel || "我的站点风格"
                        });
                        syncPresetSelectOptions();
                        setStatus(messages.captureSaved || "", "success");
                    }
                }
                if (event.target.closest(".ds-add-design-preset")) {
                    event.preventDefault();
                    appendPresetCard(null);
                    syncPresetSelectOptions();
                    setStatus("", "");
                }
                if (event.target.closest(".ds-clone-design-preset")) {
                    event.preventDefault();
                    appendPresetCard(currentSeed);
                    syncPresetSelectOptions();
                    setStatus("", "");
                }
                if (event.target.closest(".ds-sync-design-preset-snapshot")) {
                    event.preventDefault();
                    var snapshotCard = event.target.closest(".ds-design-preset-card");
                    if (snapshotCard) {
                        applyTokensToCard(snapshotCard, buildCurrentTokenSnapshot());
                        applySnapshotToCard(snapshotCard, buildCurrentDesignSnapshot());
                        ensurePresetIdentity(snapshotCard, {
                            forceLabel: messages.defaultPresetLabel || "我的站点风格"
                        });
                        setStatus(messages.snapshotSynced || "", "success");
                    }
                }
                if (event.target.closest(".ds-export-design-presets")) {
                    event.preventDefault();
                    if (!exchangeTextarea) { return; }
                    exchangeTextarea.value = JSON.stringify(buildExportPayload(), null, 2);
                    setStatus(messages.exportReady || "", "success");
                }
                if (event.target.closest(".ds-copy-design-presets")) {
                    event.preventDefault();
                    var exportText = exchangeTextarea && exchangeTextarea.value
                        ? exchangeTextarea.value
                        : JSON.stringify(buildExportPayload(), null, 2);
                    copyText(exportText).then(function(){
                        setStatus(messages.copySuccess || "", "success");
                    }).catch(function(){
                        if (exchangeTextarea) {
                            exchangeTextarea.focus();
                            exchangeTextarea.select();
                            setStatus(messages.copyFallback || messages.copyFailed || "", "error");
                        }
                    });
                }
                if (event.target.closest(".ds-import-design-presets") || event.target.closest(".ds-replace-design-presets")) {
                    event.preventDefault();
                    if (!exchangeTextarea || !String(exchangeTextarea.value || "").trim()) {
                        setStatus(messages.importEmpty || "", "error");
                        return;
                    }
                    var parsed;
                    try {
                        parsed = JSON.parse(exchangeTextarea.value);
                    } catch (error) {
                        setStatus(messages.importInvalid || "", "error");
                        return;
                    }
                    var presets = normalizeImportedPresets(parsed).filter(function(item){
                        return item && typeof item === "object";
                    });
                    if (!presets.length) {
                        setStatus(messages.importInvalid || "", "error");
                        return;
                    }
                    if (event.target.closest(".ds-replace-design-presets")) {
                        replacePresetCards(presets);
                        syncPresetSelectOptions();
                        setStatus(messages.importReplace || "", "success");
                    } else {
                        appendImportedPresets(presets);
                        syncPresetSelectOptions();
                        setStatus(messages.importAppend || "", "success");
                    }
                }
                if (event.target.closest(".ds-remove-design-preset")) {
                    event.preventDefault();
                    var card = event.target.closest(".ds-design-preset-card");
                    if (card) {
                        card.remove();
                        syncPresetSelectOptions();
                        setStatus("", "");
                    }
                }
            });
        });
        </script>';

        echo '</td></tr>';
    }

    private function render_design_preset_scope_manager_field( $options ) {
        echo '<tr id="setting-row-design_preset_context_rules" data-setting-id="design_preset_context_rules"><th scope="row">' . esc_html__( '多品牌应用范围', 'developer-starter' ) . '</th><td>';

        if ( ! class_exists( '\Developer_Starter\Core\Design_Tokens' ) ) {
            echo '<p class="description">' . esc_html__( '设计预设服务未加载。', 'developer-starter' ) . '</p>';
            echo '</td></tr>';
            return;
        }

        $option_name    = $this->option_name;
        $rules          = \Developer_Starter\Core\Design_Tokens::get_context_preset_rules( is_array( $options ) ? $options : array() );
        $preset_choices = \Developer_Starter\Core\Design_Tokens::get_context_preset_choices( is_array( $options ) ? $options : array() );
        $page_choices   = array();
        $category_choices = array();

        if ( function_exists( 'get_pages' ) ) {
            $pages = get_pages(
                array(
                    'sort_column' => 'post_title',
                    'sort_order'  => 'ASC',
                    'number'      => 500,
                )
            );
            foreach ( $pages as $page ) {
                if ( ! $page instanceof \WP_Post ) {
                    continue;
                }
                $page_choices[ (int) $page->ID ] = get_the_title( $page );
            }
        }

        if ( function_exists( 'get_categories' ) ) {
            $categories = get_categories(
                array(
                    'hide_empty' => false,
                    'orderby'    => 'name',
                    'order'      => 'ASC',
                    'number'     => 500,
                )
            );
            foreach ( $categories as $category ) {
                if ( ! $category instanceof \WP_Term ) {
                    continue;
                }
                $category_choices[ (int) $category->term_id ] = $category->name;
            }
        }

        $render_select_options = static function( $choices, $selected, $placeholder ) {
            echo '<option value="">' . esc_html( $placeholder ) . '</option>';
            foreach ( $choices as $value => $label ) {
                echo '<option value="' . esc_attr( (string) $value ) . '" ' . selected( (string) $selected, (string) $value, false ) . '>' . esc_html( (string) $label ) . '</option>';
            }
        };

        $render_preset_options = static function( $choices, $selected ) {
            foreach ( $choices as $value => $label ) {
                echo '<option value="' . esc_attr( (string) $value ) . '" ' . selected( (string) $selected, (string) $value, false ) . '>' . esc_html( (string) $label ) . '</option>';
            }
        };

        $render_rows = function( $group_key, $items, $object_choices, $object_label, $empty_label, $template = false ) use ( $option_name, $preset_choices, $render_select_options, $render_preset_options ) {
            $items = is_array( $items ) ? $items : array();
            if ( empty( $items ) && ! $template ) {
                $items = array( '__EMPTY__' => '' );
            }

            foreach ( $items as $object_id => $preset ) {
                $index = $template ? '__INDEX__' : (string) $object_id;
                $object_id = '__EMPTY__' === $object_id ? '' : $object_id;
                echo '<tr class="ds-design-scope-row">';
                echo '<td>';
                echo '<select name="' . esc_attr( $option_name ) . '[design_preset_context_rules][' . esc_attr( $group_key ) . '][' . esc_attr( $index ) . '][id]" aria-label="' . esc_attr( $object_label ) . '">';
                $render_select_options( $object_choices, $object_id, $empty_label );
                echo '</select>';
                echo '</td>';
                echo '<td>';
                echo '<select name="' . esc_attr( $option_name ) . '[design_preset_context_rules][' . esc_attr( $group_key ) . '][' . esc_attr( $index ) . '][preset]" aria-label="' . esc_attr__( '品牌配色预设', 'developer-starter' ) . '">';
                $render_preset_options( $preset_choices, $preset ?: 'inherit' );
                echo '</select>';
                echo '</td>';
                echo '<td><button type="button" class="button-link-delete ds-remove-design-scope-row">' . esc_html__( '删除', 'developer-starter' ) . '</button></td>';
                echo '</tr>';
            }
        };

        echo '<div class="ds-design-scope-manager" data-design-scope-manager="1">';
        echo '<p class="ds-design-scope-manager__tip">' . esc_html__( '这里集中管理多站点/多品牌的应用范围。上方负责创建和保存预设，这里决定哪些页面或分类使用哪套品牌配色。', 'developer-starter' ) . '</p>';
        echo '<input type="hidden" name="' . esc_attr( $option_name ) . '[design_preset_context_rules_present]" value="1" />';
        echo '<div class="ds-design-scope-manager__grid">';

        echo '<section class="ds-design-scope-card" data-design-scope-card="pages">';
        echo '<div class="ds-design-scope-card__head"><h4>' . esc_html__( '页面配色规则', 'developer-starter' ) . '</h4><p>' . esc_html__( '适合不同品牌落地页、活动页或多站点共用主题时快速换肤。', 'developer-starter' ) . '</p></div>';
        echo '<table class="widefat striped"><thead><tr><th>' . esc_html__( '页面', 'developer-starter' ) . '</th><th>' . esc_html__( '品牌配色预设', 'developer-starter' ) . '</th><th></th></tr></thead><tbody data-design-scope-list="pages">';
        $render_rows( 'pages', isset( $rules['pages'] ) ? $rules['pages'] : array(), $page_choices, __( '页面', 'developer-starter' ), __( '选择页面', 'developer-starter' ) );
        echo '</tbody></table>';
        echo '<div class="ds-design-scope-card__actions"><button type="button" class="button button-secondary ds-add-design-scope-row" data-design-scope-add="pages">' . esc_html__( '添加页面规则', 'developer-starter' ) . '</button></div>';
        echo '</section>';

        echo '<section class="ds-design-scope-card" data-design-scope-card="categories">';
        echo '<div class="ds-design-scope-card__head"><h4>' . esc_html__( '分类配色规则', 'developer-starter' ) . '</h4><p>' . esc_html__( '分类页和该分类下文章会继承这里指定的品牌配色。', 'developer-starter' ) . '</p></div>';
        echo '<table class="widefat striped"><thead><tr><th>' . esc_html__( '分类', 'developer-starter' ) . '</th><th>' . esc_html__( '品牌配色预设', 'developer-starter' ) . '</th><th></th></tr></thead><tbody data-design-scope-list="categories">';
        $render_rows( 'categories', isset( $rules['categories'] ) ? $rules['categories'] : array(), $category_choices, __( '分类', 'developer-starter' ), __( '选择分类', 'developer-starter' ) );
        echo '</tbody></table>';
        echo '<div class="ds-design-scope-card__actions"><button type="button" class="button button-secondary ds-add-design-scope-row" data-design-scope-add="categories">' . esc_html__( '添加分类规则', 'developer-starter' ) . '</button></div>';
        echo '</section>';

        echo '</div>';

        echo '<script type="text/template" data-design-scope-template="pages">';
        $render_rows( 'pages', array( '__EMPTY__' => '' ), $page_choices, __( '页面', 'developer-starter' ), __( '选择页面', 'developer-starter' ), true );
        echo '</script>';
        echo '<script type="text/template" data-design-scope-template="categories">';
        $render_rows( 'categories', array( '__EMPTY__' => '' ), $category_choices, __( '分类', 'developer-starter' ), __( '选择分类', 'developer-starter' ), true );
        echo '</script>';
        echo '<script>
        document.addEventListener("DOMContentLoaded", function(){
            var root = document.querySelector("[data-design-scope-manager=\"1\"]");
            if (!root) { return; }
            root.addEventListener("click", function(event){
                var addButton = event.target.closest("[data-design-scope-add]");
                if (addButton) {
                    event.preventDefault();
                    var group = addButton.getAttribute("data-design-scope-add");
                    var list = root.querySelector("[data-design-scope-list=\"" + group + "\"]");
                    var template = root.querySelector("[data-design-scope-template=\"" + group + "\"]");
                    if (!list || !template) { return; }
                    var index = String(Date.now()) + String(Math.floor(Math.random() * 1000));
                    list.insertAdjacentHTML("beforeend", template.innerHTML.replace(/__INDEX__/g, index));
                    return;
                }
                var removeButton = event.target.closest(".ds-remove-design-scope-row");
                if (removeButton) {
                    event.preventDefault();
                    var row = removeButton.closest(".ds-design-scope-row");
                    if (row && row.parentNode) {
                        row.parentNode.removeChild(row);
                    }
                }
            });
        });
        </script>';
        echo '</div>';
        echo '</td></tr>';
    }

    private function render_design_typography_system_field( $options ) {
        echo '<tr id="setting-row-design_typography_system" data-setting-id="design_typography_system"><th scope="row">' . esc_html__( '响应式排版体系', 'developer-starter' ) . '</th><td>';

        if ( ! class_exists( '\Developer_Starter\Core\Design_Tokens' ) ) {
            echo '<p class="description">' . esc_html__( '全局排版服务未加载。', 'developer-starter' ) . '</p>';
            echo '</td></tr>';
            return;
        }

        $payload = \Developer_Starter\Core\Design_Tokens::get_client_payload( is_array( $options ) ? $options : array() );
        $typography_system = isset( $payload['typographySystem'] ) && is_array( $payload['typographySystem'] ) ? $payload['typographySystem'] : array();
        $style_definitions = \Developer_Starter\Core\Design_Tokens::get_typography_style_definitions();
        $property_definitions = \Developer_Starter\Core\Design_Tokens::get_typography_property_definitions();
        $device_definitions = \Developer_Starter\Core\Design_Tokens::get_responsive_device_definitions();
        $option_name = $this->option_name;

        $build_style_attr = static function ( $properties ) {
            $properties = is_array( $properties ) ? $properties : array();
            $style_map = array(
                'font-size'      => isset( $properties['font_size'] ) ? (string) $properties['font_size'] : '',
                'line-height'    => isset( $properties['line_height'] ) ? (string) $properties['line_height'] : '',
                'font-weight'    => isset( $properties['font_weight'] ) ? (string) $properties['font_weight'] : '',
                'letter-spacing' => isset( $properties['letter_spacing'] ) ? (string) $properties['letter_spacing'] : '',
            );
            $fragments = array();
            foreach ( $style_map as $css_property => $css_value ) {
                if ( '' === trim( $css_value ) ) {
                    continue;
                }
                $fragments[] = $css_property . ':' . $css_value;
            }
            return implode( ';', $fragments );
        };

        ob_start();
        ?>
        <style>
        .ds-typography-system{display:grid;gap:18px;max-width:1220px;}
        .ds-typography-system__tip{margin:0;color:#475569;line-height:1.75;}
        .ds-typography-system__preview{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px;}
        .ds-typography-system__preview-panel{border:1px solid #dbe3f0;border-radius:18px;background:#ffffff;box-shadow:0 16px 38px rgba(15,23,42,.05);overflow:hidden;}
        .ds-typography-system__preview-head{display:flex;justify-content:space-between;align-items:center;padding:14px 16px;border-bottom:1px solid #eef2f7;background:linear-gradient(135deg,#f8fafc 0%,#f1f5f9 100%);}
        .ds-typography-system__preview-head strong{color:#0f172a;font-size:14px;}
        .ds-typography-system__preview-body{display:grid;gap:12px;padding:16px;}
        .ds-typography-system__preview-item{display:grid;gap:6px;padding:12px 14px;border:1px solid #edf2f7;border-radius:14px;background:#fcfdff;color:#0f172a;}
        .ds-typography-system__preview-item code{font-size:11px;color:#64748b;background:transparent;padding:0;}
        .ds-typography-system__preview-item.is-button{display:inline-flex;align-items:center;justify-content:center;min-height:48px;background:linear-gradient(135deg,#e2e8f0 0%,#f8fafc 100%);}
        .ds-typography-system__preview-item.is-input{background:#ffffff;border:1px solid #cbd5e1;color:#475569;}
        .ds-typography-system__cards{display:grid;gap:16px;}
        .ds-typography-system__card{border:1px solid #dbe3f0;border-radius:18px;background:#ffffff;box-shadow:0 12px 34px rgba(15,23,42,.04);overflow:hidden;}
        .ds-typography-system__card-head{display:flex;justify-content:space-between;gap:12px;align-items:flex-start;padding:16px 18px;border-bottom:1px solid #eef2f7;background:linear-gradient(135deg,#f8fafc 0%,#f1f5f9 100%);}
        .ds-typography-system__card-head strong{display:block;font-size:15px;color:#0f172a;}
        .ds-typography-system__card-head p{margin:4px 0 0;color:#64748b;}
        .ds-typography-system__matrix{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px;padding:16px 18px 18px;}
        .ds-typography-system__device{border:1px solid #edf2f7;border-radius:14px;background:#fcfdff;padding:14px;}
        .ds-typography-system__device h5{margin:0 0 12px;font-size:13px;color:#0f172a;}
        .ds-typography-system__field-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px;}
        .ds-typography-system__field{display:flex;flex-direction:column;gap:6px;}
        .ds-typography-system__field span{font-size:12px;font-weight:600;color:#334155;}
        .ds-typography-system__field input{width:100%;}
        @media (max-width: 1280px){
            .ds-typography-system__preview,
            .ds-typography-system__matrix{grid-template-columns:1fr;}
        }
        </style>
        <div class="ds-typography-system" data-ds-typography-system="1">
            <p class="ds-typography-system__tip"><?php echo esc_html__( '统一设置正文、导语、导航、按钮、表单和标题在桌面、平板、手机端的排版。', 'developer-starter' ); ?></p>
            <input type="hidden" name="<?php echo esc_attr( $option_name ); ?>[design_typography_system_present]" value="1" />

            <div class="ds-typography-system__preview">
                <?php foreach ( $device_definitions as $device_key => $device_definition ) : ?>
                    <section class="ds-typography-system__preview-panel">
                        <div class="ds-typography-system__preview-head">
                            <strong><?php echo esc_html( $device_definition['label'] ); ?></strong>
                            <code><?php echo esc_html( strtoupper( (string) $device_key ) ); ?></code>
                        </div>
                        <div class="ds-typography-system__preview-body">
                            <?php foreach ( $style_definitions as $style_key => $style_definition ) : ?>
                                <?php
                                $properties = isset( $typography_system[ $style_key ][ $device_key ] ) && is_array( $typography_system[ $style_key ][ $device_key ] )
                                    ? $typography_system[ $style_key ][ $device_key ]
                                    : array();
                                $preview_class = 'ds-typography-system__preview-item';
                                if ( 'button' === $style_key ) {
                                    $preview_class .= ' is-button';
                                } elseif ( 'input' === $style_key ) {
                                    $preview_class .= ' is-input';
                                }
                                ?>
                                <div class="<?php echo esc_attr( $preview_class ); ?>" data-ds-typography-preview-item="1" data-style-key="<?php echo esc_attr( $style_key ); ?>" data-device-key="<?php echo esc_attr( $device_key ); ?>" style="<?php echo esc_attr( $build_style_attr( $properties ) ); ?>">
                                    <strong><?php echo esc_html( $style_definition['label'] ); ?></strong>
                                    <span><?php echo esc_html( $style_definition['sample'] ); ?></span>
                                    <code data-ds-typography-preview-meta="1"><?php echo esc_html( implode( ' / ', array_filter( array(
                                        isset( $properties['font_size'] ) ? (string) $properties['font_size'] : '',
                                        isset( $properties['line_height'] ) ? 'LH ' . (string) $properties['line_height'] : '',
                                        isset( $properties['font_weight'] ) ? 'W ' . (string) $properties['font_weight'] : '',
                                        isset( $properties['letter_spacing'] ) ? 'LS ' . (string) $properties['letter_spacing'] : '',
                                    ) ) ) ); ?></code>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endforeach; ?>
            </div>

            <div class="ds-typography-system__cards">
                <?php foreach ( $style_definitions as $style_key => $style_definition ) : ?>
                    <section class="ds-typography-system__card">
                        <div class="ds-typography-system__card-head">
                            <div>
                                <strong><?php echo esc_html( $style_definition['label'] ); ?></strong>
                                <p><?php echo esc_html( $style_definition['sample'] ); ?></p>
                            </div>
                            <code><?php echo esc_html( strtoupper( (string) $style_key ) ); ?></code>
                        </div>
                        <div class="ds-typography-system__matrix">
                            <?php foreach ( $device_definitions as $device_key => $device_definition ) : ?>
                                <div class="ds-typography-system__device">
                                    <h5><?php echo esc_html( $device_definition['label'] ); ?></h5>
                                    <div class="ds-typography-system__field-grid">
                                        <?php foreach ( $property_definitions as $property_key => $property_definition ) : ?>
                                            <?php
                                            $value = isset( $typography_system[ $style_key ][ $device_key ][ $property_key ] )
                                                ? (string) $typography_system[ $style_key ][ $device_key ][ $property_key ]
                                                : '';
                                            ?>
                                            <label class="ds-typography-system__field">
                                                <span><?php echo esc_html( $property_definition['label'] ); ?></span>
                                                <input
                                                    type="text"
                                                    class="regular-text"
                                                    data-ds-typography-input="1"
                                                    data-style-key="<?php echo esc_attr( $style_key ); ?>"
                                                    data-device-key="<?php echo esc_attr( $device_key ); ?>"
                                                    data-property-key="<?php echo esc_attr( $property_key ); ?>"
                                                    name="<?php echo esc_attr( $option_name ); ?>[design_typography_system][<?php echo esc_attr( $style_key ); ?>][<?php echo esc_attr( $device_key ); ?>][<?php echo esc_attr( $property_key ); ?>]"
                                                    value="<?php echo esc_attr( $value ); ?>"
                                                    placeholder="<?php echo esc_attr( $property_definition['placeholder'] ); ?>"
                                                />
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endforeach; ?>
            </div>
        </div>
        <script>
        document.addEventListener("DOMContentLoaded", function(){
            var root = document.querySelector("[data-ds-typography-system='1']");
            if (!root) {
                return;
            }
            function collectProps(styleKey, deviceKey) {
                var result = {
                    font_size: "",
                    line_height: "",
                    font_weight: "",
                    letter_spacing: ""
                };
                root.querySelectorAll("[data-ds-typography-input='1']").forEach(function(input){
                    if (input.getAttribute("data-style-key") !== styleKey || input.getAttribute("data-device-key") !== deviceKey) {
                        return;
                    }
                    result[input.getAttribute("data-property-key")] = String(input.value || "").trim();
                });
                return result;
            }
            function applyPreview() {
                root.querySelectorAll("[data-ds-typography-preview-item='1']").forEach(function(node){
                    var styleKey = node.getAttribute("data-style-key");
                    var deviceKey = node.getAttribute("data-device-key");
                    var props = collectProps(styleKey, deviceKey);
                    node.style.fontSize = props.font_size || "";
                    node.style.lineHeight = props.line_height || "";
                    node.style.fontWeight = props.font_weight || "";
                    node.style.letterSpacing = props.letter_spacing || "";
                    var meta = node.querySelector("[data-ds-typography-preview-meta='1']");
                    if (meta) {
                        meta.textContent = [props.font_size, props.line_height ? "LH " + props.line_height : "", props.font_weight ? "W " + props.font_weight : "", props.letter_spacing ? "LS " + props.letter_spacing : ""].filter(Boolean).join(" / ");
                    }
                });
            }
            root.addEventListener("input", function(event){
                if (event.target && event.target.matches("[data-ds-typography-input='1']")) {
                    applyPreview();
                }
            });
            applyPreview();
        });
        </script>
        <?php
        echo (string) ob_get_clean();
        echo '</td></tr>';
    }

    private function render_design_layout_system_field( $options ) {
        echo '<tr id="setting-row-design_layout_system" data-setting-id="design_layout_system"><th scope="row">' . esc_html__( '响应式布局尺度', 'developer-starter' ) . '</th><td>';

        if ( ! class_exists( '\Developer_Starter\Core\Design_Tokens' ) ) {
            echo '<p class="description">' . esc_html__( '全局布局服务未加载。', 'developer-starter' ) . '</p>';
            echo '</td></tr>';
            return;
        }

        $payload = \Developer_Starter\Core\Design_Tokens::get_client_payload( is_array( $options ) ? $options : array() );
        $layout_system = isset( $payload['layoutSystem'] ) && is_array( $payload['layoutSystem'] ) ? $payload['layoutSystem'] : array();
        $layout_definitions = \Developer_Starter\Core\Design_Tokens::get_layout_field_definitions();
        $device_definitions = \Developer_Starter\Core\Design_Tokens::get_responsive_device_definitions();
        $option_name = $this->option_name;
        $to_preview_spacing = static function( $value ) {
            $raw = trim( (string) $value );
            if ( '' === $raw ) {
                return '';
            }
            if ( preg_match( '/^([0-9]+(?:\.[0-9]+)?)px$/i', $raw, $matches ) ) {
                $scaled = (float) $matches[1] * 0.55;
                $scaled = max( 18, min( 32, $scaled ) );
                return (string) round( $scaled ) . 'px';
            }
            return '26px';
        };

        ob_start();
        ?>
        <style>
        .ds-layout-system{display:grid;gap:18px;max-width:1180px;}
        .ds-layout-system__tip{margin:0;color:#475569;line-height:1.75;}
        .ds-layout-system__preview{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px;}
        .ds-layout-system__preview-panel{min-width:0;border:1px solid #dbe3f0;border-radius:18px;background:#ffffff;box-shadow:0 16px 38px rgba(15,23,42,.05);overflow:hidden;}
        .ds-layout-system__preview-head{display:flex;justify-content:space-between;align-items:center;padding:14px 16px;border-bottom:1px solid #eef2f7;background:linear-gradient(135deg,#f8fafc 0%,#f1f5f9 100%);}
        .ds-layout-system__preview-head strong{color:#0f172a;font-size:14px;}
        .ds-layout-system__canvas{padding:18px;background:linear-gradient(180deg,#f8fafc 0%,#ffffff 100%);overflow:hidden;box-sizing:border-box;}
        .ds-layout-system__shell{min-width:0;border-radius:18px;border:1px solid #e2e8f0;background:#ffffff;padding:14px;transition:all .2s ease;box-sizing:border-box;}
        .ds-layout-system__shell.is-boxed{box-shadow:0 18px 34px rgba(15,23,42,.08);}
        .ds-layout-system__container{width:100%;max-width:100%;min-width:0;margin:0 auto;border:1px dashed #94a3b8;border-radius:14px;padding:12px;background:rgba(255,255,255,.88);box-sizing:border-box;}
        .ds-layout-system__grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));width:100%;min-width:0;box-sizing:border-box;}
        .ds-layout-system__grid span{display:block;height:52px;border-radius:10px;background:linear-gradient(135deg,#dbeafe 0%,#eff6ff 100%);}
        .ds-layout-system__metrics{display:grid;gap:8px;padding:0 18px 18px;}
        .ds-layout-system__metrics code{font-size:12px;color:#475569;background:#f8fafc;padding:4px 8px;border-radius:999px;width:max-content;}
        .ds-layout-system__cards{display:grid;gap:16px;}
        .ds-layout-system__card{border:1px solid #dbe3f0;border-radius:18px;background:#ffffff;box-shadow:0 12px 34px rgba(15,23,42,.04);overflow:hidden;}
        .ds-layout-system__card-head{padding:16px 18px;border-bottom:1px solid #eef2f7;background:linear-gradient(135deg,#f8fafc 0%,#f1f5f9 100%);}
        .ds-layout-system__card-head strong{display:block;font-size:15px;color:#0f172a;}
        .ds-layout-system__card-head p{margin:4px 0 0;color:#64748b;}
        .ds-layout-system__matrix{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px;padding:16px 18px 18px;}
        .ds-layout-system__device{border:1px solid #edf2f7;border-radius:14px;background:#fcfdff;padding:14px;}
        .ds-layout-system__device h5{margin:0 0 12px;font-size:13px;color:#0f172a;}
        .ds-layout-system__field{display:flex;flex-direction:column;gap:6px;}
        .ds-layout-system__field span{font-size:12px;font-weight:600;color:#334155;}
        .ds-layout-system__field input,
        .ds-layout-system__field select{width:100%;height:36px;min-height:36px;box-sizing:border-box;}
        .ds-layout-system__meta-card{border:1px solid #dbe3f0;border-radius:18px;background:#ffffff;box-shadow:0 12px 34px rgba(15,23,42,.04);overflow:hidden;}
        .ds-layout-system__meta-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px;padding:16px 18px 18px;}
        @media (max-width: 1280px){
            .ds-layout-system__preview,
            .ds-layout-system__matrix,
            .ds-layout-system__meta-grid{grid-template-columns:1fr;}
        }
        </style>
        <div class="ds-layout-system" data-ds-layout-system="1">
            <p class="ds-layout-system__tip"><?php echo esc_html__( '统一设置桌面、平板、手机端的容器宽度、区块间距、栅格间距和布局模式。', 'developer-starter' ); ?></p>
            <input type="hidden" name="<?php echo esc_attr( $option_name ); ?>[design_layout_system_present]" value="1" />

            <div class="ds-layout-system__preview">
                <?php foreach ( $device_definitions as $device_key => $device_definition ) : ?>
                    <?php
                    $container_width = isset( $layout_system['container_width'][ $device_key ] ) ? (string) $layout_system['container_width'][ $device_key ] : '';
                    $section_spacing = isset( $layout_system['section_spacing'][ $device_key ] ) ? (string) $layout_system['section_spacing'][ $device_key ] : '';
                    $grid_gap = isset( $layout_system['grid_gap'][ $device_key ] ) ? (string) $layout_system['grid_gap'][ $device_key ] : '';
                    $layout_mode = isset( $layout_system['layout_mode'] ) ? (string) $layout_system['layout_mode'] : 'wide';
                    $preview_spacing = $to_preview_spacing( $section_spacing );
                    ?>
                    <section class="ds-layout-system__preview-panel" data-layout-device="<?php echo esc_attr( $device_key ); ?>">
                        <div class="ds-layout-system__preview-head">
                            <strong><?php echo esc_html( $device_definition['label'] ); ?></strong>
                            <code data-ds-layout-meta="label"><?php echo esc_html( strtoupper( (string) $device_key ) ); ?></code>
                        </div>
                        <div class="ds-layout-system__canvas" style="padding-top:<?php echo esc_attr( $preview_spacing ); ?>;padding-bottom:<?php echo esc_attr( $preview_spacing ); ?>;">
                            <div class="ds-layout-system__shell <?php echo 'boxed' === $layout_mode ? 'is-boxed' : ''; ?>" data-ds-layout-shell="1">
                                <div class="ds-layout-system__container" data-ds-layout-container="1" style="max-width:<?php echo esc_attr( $container_width ); ?>;">
                                    <div class="ds-layout-system__grid" data-ds-layout-grid="1" style="gap:<?php echo esc_attr( $grid_gap ); ?>;">
                                        <span></span><span></span><span></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="ds-layout-system__metrics">
                            <code data-ds-layout-meta="container"><?php echo esc_html( sprintf( __( '容器 %s', 'developer-starter' ), $container_width ) ); ?></code>
                            <code data-ds-layout-meta="spacing"><?php echo esc_html( sprintf( __( '区块 %s', 'developer-starter' ), $section_spacing ) ); ?></code>
                            <code data-ds-layout-meta="gap"><?php echo esc_html( sprintf( __( '栅格 %s', 'developer-starter' ), $grid_gap ) ); ?></code>
                        </div>
                    </section>
                <?php endforeach; ?>
            </div>

            <div class="ds-layout-system__cards">
                <?php foreach ( array( 'container_width', 'section_spacing', 'grid_gap' ) as $layout_key ) : ?>
                    <section class="ds-layout-system__card">
                        <div class="ds-layout-system__card-head">
                            <strong><?php echo esc_html( $layout_definitions[ $layout_key ]['label'] ); ?></strong>
                            <p><?php echo esc_html__( '支持桌面 / 平板 / 手机三端独立设置。', 'developer-starter' ); ?></p>
                        </div>
                        <div class="ds-layout-system__matrix">
                            <?php foreach ( $device_definitions as $device_key => $device_definition ) : ?>
                                <?php $value = isset( $layout_system[ $layout_key ][ $device_key ] ) ? (string) $layout_system[ $layout_key ][ $device_key ] : ''; ?>
                                <div class="ds-layout-system__device">
                                    <h5><?php echo esc_html( $device_definition['label'] ); ?></h5>
                                    <label class="ds-layout-system__field">
                                        <span><?php echo esc_html( $layout_definitions[ $layout_key ]['label'] ); ?></span>
                                        <input
                                            type="text"
                                            class="regular-text"
                                            data-ds-layout-input="1"
                                            data-layout-group="<?php echo esc_attr( $layout_key ); ?>"
                                            data-layout-device="<?php echo esc_attr( $device_key ); ?>"
                                            name="<?php echo esc_attr( $option_name ); ?>[design_layout_system][<?php echo esc_attr( $layout_key ); ?>][<?php echo esc_attr( $device_key ); ?>]"
                                            value="<?php echo esc_attr( $value ); ?>"
                                            placeholder="<?php echo esc_attr( $layout_definitions[ $layout_key ]['placeholder'][ $device_key ] ); ?>"
                                        />
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endforeach; ?>

                <section class="ds-layout-system__meta-card">
                    <div class="ds-layout-system__card-head">
                        <strong><?php echo esc_html__( '断点与布局模式', 'developer-starter' ); ?></strong>
                        <p><?php echo esc_html__( '断点会决定排版与容器何时切换到平板 / 手机值；布局模式用于切换 wide 与 boxed 的站点框架。', 'developer-starter' ); ?></p>
                    </div>
                    <div class="ds-layout-system__meta-grid">
                        <div class="ds-layout-system__device">
                            <h5><?php echo esc_html__( '平板断点', 'developer-starter' ); ?></h5>
                            <label class="ds-layout-system__field">
                                <span><?php echo esc_html__( '平板最大宽度', 'developer-starter' ); ?></span>
                                <input
                                    type="text"
                                    class="regular-text"
                                    data-ds-layout-input="1"
                                    data-layout-group="breakpoints"
                                    data-layout-device="tablet"
                                    name="<?php echo esc_attr( $option_name ); ?>[design_layout_system][breakpoints][tablet]"
                                    value="<?php echo esc_attr( isset( $layout_system['breakpoints']['tablet'] ) ? (string) $layout_system['breakpoints']['tablet'] : '' ); ?>"
                                    placeholder="<?php echo esc_attr( $layout_definitions['breakpoints']['placeholder']['tablet'] ); ?>"
                                />
                            </label>
                        </div>
                        <div class="ds-layout-system__device">
                            <h5><?php echo esc_html__( '手机断点', 'developer-starter' ); ?></h5>
                            <label class="ds-layout-system__field">
                                <span><?php echo esc_html__( '手机最大宽度', 'developer-starter' ); ?></span>
                                <input
                                    type="text"
                                    class="regular-text"
                                    data-ds-layout-input="1"
                                    data-layout-group="breakpoints"
                                    data-layout-device="mobile"
                                    name="<?php echo esc_attr( $option_name ); ?>[design_layout_system][breakpoints][mobile]"
                                    value="<?php echo esc_attr( isset( $layout_system['breakpoints']['mobile'] ) ? (string) $layout_system['breakpoints']['mobile'] : '' ); ?>"
                                    placeholder="<?php echo esc_attr( $layout_definitions['breakpoints']['placeholder']['mobile'] ); ?>"
                                />
                            </label>
                        </div>
                        <div class="ds-layout-system__device">
                            <h5><?php echo esc_html__( '站点布局模式', 'developer-starter' ); ?></h5>
                            <label class="ds-layout-system__field">
                                <span><?php echo esc_html__( '布局模式', 'developer-starter' ); ?></span>
                                <select data-ds-layout-mode="1" name="<?php echo esc_attr( $option_name ); ?>[design_layout_system][layout_mode]">
                                    <?php foreach ( $layout_definitions['layout_mode']['choices'] as $choice_key => $choice_label ) : ?>
                                        <option value="<?php echo esc_attr( $choice_key ); ?>" <?php selected( isset( $layout_system['layout_mode'] ) ? (string) $layout_system['layout_mode'] : 'wide', (string) $choice_key ); ?>><?php echo esc_html( $choice_label ); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </label>
                        </div>
                    </div>
                </section>
            </div>
        </div>
        <script>
        document.addEventListener("DOMContentLoaded", function(){
            var root = document.querySelector("[data-ds-layout-system='1']");
            if (!root) {
                return;
            }
            function readValue(group, device) {
                if (group === "layout_mode") {
                    var select = root.querySelector("[data-ds-layout-mode='1']");
                    return select ? String(select.value || "").trim() : "";
                }
                var input = root.querySelector("[data-ds-layout-input='1'][data-layout-group='" + group + "'][data-layout-device='" + device + "']");
                return input ? String(input.value || "").trim() : "";
            }
            function toPreviewSpacing(value) {
                var raw = String(value || "").trim();
                var match = raw.match(/^([0-9]+(?:\.[0-9]+)?)px$/i);
                if (!raw) {
                    return "";
                }
                if (match) {
                    var scaled = parseFloat(match[1]) * 0.55;
                    scaled = Math.max(18, Math.min(32, scaled));
                    return Math.round(scaled) + "px";
                }
                return "26px";
            }
            function applyPreview() {
                var layoutMode = readValue("layout_mode", "");
                root.querySelectorAll(".ds-layout-system__preview-panel").forEach(function(panel){
                    var device = panel.getAttribute("data-layout-device");
                    var container = readValue("container_width", device);
                    var spacing = readValue("section_spacing", device);
                    var gap = readValue("grid_gap", device);
                    var shell = panel.querySelector("[data-ds-layout-shell='1']");
                    var containerNode = panel.querySelector("[data-ds-layout-container='1']");
                    var grid = panel.querySelector("[data-ds-layout-grid='1']");
                    var canvas = panel.querySelector(".ds-layout-system__canvas");
                    if (canvas) {
                        var previewSpacing = toPreviewSpacing(spacing);
                        canvas.style.paddingTop = previewSpacing;
                        canvas.style.paddingBottom = previewSpacing;
                    }
                    if (containerNode) {
                        containerNode.style.maxWidth = container || "";
                    }
                    if (grid) {
                        grid.style.gap = gap || "";
                    }
                    if (shell) {
                        shell.classList.toggle("is-boxed", layoutMode === "boxed");
                    }
                    var containerMeta = panel.querySelector("[data-ds-layout-meta='container']");
                    var spacingMeta = panel.querySelector("[data-ds-layout-meta='spacing']");
                    var gapMeta = panel.querySelector("[data-ds-layout-meta='gap']");
                    if (containerMeta) {
                        containerMeta.textContent = "容器 " + (container || "-");
                    }
                    if (spacingMeta) {
                        spacingMeta.textContent = "区块 " + (spacing || "-");
                    }
                    if (gapMeta) {
                        gapMeta.textContent = "栅格 " + (gap || "-");
                    }
                });
            }
            root.addEventListener("input", function(event){
                if (event.target && event.target.matches("[data-ds-layout-input='1']")) {
                    applyPreview();
                }
            });
            root.addEventListener("change", function(event){
                if (event.target && event.target.matches("[data-ds-layout-mode='1']")) {
                    applyPreview();
                }
            });
            applyPreview();
        });
        </script>
        <?php
        echo (string) ob_get_clean();
        echo '</td></tr>';
    }
}
