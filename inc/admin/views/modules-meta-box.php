<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
        <div style="padding: 10px 16px; background: #fff; border-bottom: 1px solid #eee; display: flex; align-items: center; gap: 10px;">
            <label style="font-weight: 600; color: #2271b1; display: flex; align-items: center; gap: 5px; cursor: pointer;">
                <input type="checkbox" name="developer_starter_enable_scroll_reveal" value="1" <?php checked( $enable_scroll_reveal, '1' ); ?> />
                <span class="dashicons dashicons-performance" style="font-size: 18px; width: 18px; height: 18px;"></span>
                <?php esc_html_e( '开启全页模块滚动视差效果', 'developer-starter' ); ?>
            </label>
            <span style="margin-left: auto; padding: 4px 10px; border-radius: 999px; background: #e7f3ff; color: #135e96; font-size: 12px; font-weight: 600;">
                <?php echo esc_html( sprintf( __( '功能模块：%d 个', 'developer-starter' ), (int) $available_module_count ) ); ?>
            </span>
        </div>
        <style>
            #developer_starter_modules .inside { padding: 0; margin: 0; }
            .dsm-wrap { background: #f0f0f1; }
            .dsm-toolbar { 
                display: flex; 
                flex-wrap: wrap; 
                gap: 8px; 
                padding: 16px; 
                background: #2271b1; 
                align-items: center;
            }
            .dsm-add-btn { 
                padding: 8px 12px; 
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
            .dsm-btn-templates {
                margin-left: auto;
                background: #d63638; /* Red to stand out or maybe Green? */
                background: #00a32a;
                border-color: rgba(255,255,255,0.3);
                font-weight: 600;
            }
            .dsm-btn-templates:hover { background: #008a20; }
            .dsm-btn-page-json-import,
            .dsm-btn-page-json-export {
                font-weight: 600;
            }
            .dsm-btn-page-json-import {
                background: #8c4bff;
                border-color: rgba(255,255,255,0.3);
            }
            .dsm-btn-page-json-import:hover {
                background: #7935ec;
            }
            .dsm-btn-page-json-export {
                background: #1f6feb;
                border-color: rgba(255,255,255,0.3);
            }
            .dsm-btn-page-json-export:hover {
                background: #1859bd;
            }
            .dsm-btn-ai-decorate {
                background: #c2410c;
                border-color: rgba(255,255,255,0.3);
                font-weight: 600;
            }
            .dsm-btn-ai-decorate:hover {
                background: #9a3412;
            }
            .dsm-page-package-hint {
                display: none;
                width: 100%;
                margin-top: 10px;
                padding: 10px 12px;
                border-radius: 6px;
                background: rgba(255,255,255,0.16);
                color: #fff;
                font-size: 12px;
                line-height: 1.6;
            }
            .dsm-page-package-hint.is-visible {
                display: block;
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
            .dsm-save-template { margin-right: 12px; color: #2271b1; text-decoration: none; font-size: 16px; padding: 4px 8px; display: none; }
            .dsm-item:hover .dsm-save-template { display: inline-block; }
            .dsm-save-template:hover { background: #eef; border-radius: 3px; }
            
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
            .dsm-dynamic-binding {
                display: grid;
                grid-template-columns: 82px minmax(0, 1fr);
                align-items: center;
                gap: 8px;
                max-width: 500px;
                margin-top: 8px;
            }
            .dsm-field .dsm-dynamic-label {
                margin: 0;
                color: #646970;
                font-size: 12px;
            }
            .dsm-field .dsm-dynamic-binding select {
                max-width: none;
                min-height: 30px;
                padding: 4px 8px;
                border-color: #c3c4c7;
                background: #f6f7f7;
                font-size: 12px;
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
            .dsm-img-preview { margin-top: 0; }
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
                .dsm-toolbar { flex-direction: column; align-items: stretch; }
                .dsm-btn-templates { margin-left: 0; margin-top: 8px; }
            }

            /* Modal Styles */
            .dsm-modal-overlay {
                position: fixed; top: 0; left: 0; right: 0; bottom: 0;
                background: rgba(0,0,0,0.5); z-index: 1000000; display: none;
                align-items: center; justify-content: center;
            }
            .dsm-modal {
                background: #fff; width: 90%; max-width: 600px; max-height: 80vh;
                border-radius: 6px; box-shadow: 0 5px 15px rgba(0,0,0,0.3);
                display: flex; flex-direction: column;
            }
            .dsm-modal-header {
                padding: 15px 20px; border-bottom: 1px solid #eee;
                display: flex; justify-content: space-between; align-items: center;
            }
            .dsm-modal-title { font-size: 16px; font-weight: 600; margin: 0; }
            .dsm-modal-close { background: none; border: none; font-size: 20px; cursor: pointer; color: #666; }
            .dsm-modal-body { padding: 20px; overflow-y: auto; flex: 1; }
            .dsm-template-list { list-style: none; margin: 0; padding: 0; }
            .dsm-template-item {
                display: flex; justify-content: space-between; align-items: center;
                padding: 12px; border: 1px solid #eee; margin-bottom: 8px; border-radius: 4px;
            }
            .dsm-template-item:hover { background: #f9f9f9; }
            .dsm-tpl-info { flex: 1; }
            .dsm-tpl-name { font-weight: 600; display: block; color: #2271b1; }
            .dsm-tpl-meta { font-size: 12px; color: #888; }
            .dsm-tpl-actions { display: flex; gap: 8px; }
            .dsm-btn-small { padding: 4px 10px; font-size: 12px; border-radius: 3px; cursor: pointer; border: 1px solid transparent; }
            .dsm-use-template { background: #2271b1; color: #fff; }
            .dsm-delete-template { background: #fff; color: #b32d2e; border-color: #b32d2e; }
            .dsm-ai-modal .dsm-modal {
                max-width: 920px;
            }
            .dsm-ai-grid {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 12px;
                margin-bottom: 16px;
            }
            .dsm-ai-field {
                display: flex;
                flex-direction: column;
                gap: 6px;
            }
            .dsm-ai-field label {
                font-weight: 600;
            }
            .dsm-ai-field-wide {
                grid-column: 1 / -1;
            }
            .dsm-ai-scope-notice {
                grid-column: 1 / -1;
                padding: 10px 12px;
                border: 1px solid #bae6fd;
                border-radius: 8px;
                background: #f0f9ff;
                color: #075985;
                line-height: 1.6;
            }
            .dsm-ai-module-tools {
                display: flex;
                justify-content: space-between;
                align-items: center;
                gap: 12px;
                margin-bottom: 10px;
            }
            .dsm-ai-module-list {
                max-height: 320px;
                overflow-y: auto;
                border: 1px solid #dcdcde;
                border-radius: 6px;
                padding: 10px;
                background: #f8fafc;
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 8px;
            }
            .dsm-ai-module-item {
                display: flex;
                align-items: center;
                gap: 8px;
                padding: 8px 10px;
                border: 1px solid #d0d7de;
                border-radius: 6px;
                background: #fff;
            }
            .dsm-ai-group-title {
                grid-column: 1 / -1;
                margin-top: 4px;
                padding: 4px 2px 0;
                font-size: 12px;
                font-weight: 700;
                color: #475569;
            }
            .dsm-ai-group-title.is-hidden {
                display: none;
            }
            .dsm-ai-module-item.is-hidden {
                display: none;
            }
            .dsm-ai-status {
                margin-top: 12px;
                font-weight: 600;
            }
            .dsm-ai-status.is-error {
                color: #b32d2e;
            }
            .dsm-ai-status.is-success {
                color: #15803d;
            }
            .dsm-ai-status.is-warning {
                color: #9a6700;
            }
            .dsm-ai-warning-list {
                margin: 12px 0 0;
                padding-left: 18px;
                color: #7c2d12;
            }
            .dsm-ai-pending-preview {
                margin-top: 14px;
                padding: 14px;
                border: 1px solid #bfdbfe;
                border-radius: 8px;
                background: #eff6ff;
            }
            .dsm-ai-pending-head {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 12px;
                margin-bottom: 8px;
                color: #1d4ed8;
            }
            .dsm-ai-pending-preview p {
                margin: 0 0 10px;
                color: #334155;
                line-height: 1.6;
            }
            .dsm-ai-pending-summary {
                margin: 0 0 12px;
                padding: 10px;
                max-height: 180px;
                overflow: auto;
                border: 1px solid #dbeafe;
                border-radius: 6px;
                background: #fff;
                color: #1e293b;
                white-space: pre-wrap;
            }
            .dsm-ai-pending-actions {
                display: flex;
                align-items: center;
                flex-wrap: wrap;
                gap: 8px;
            }
            .dsm-ai-disabled {
                padding: 18px;
                border: 1px dashed #cbd5e1;
                border-radius: 8px;
                background: #f8fafc;
                color: #475569;
            }
            @media (max-width: 960px) {
                .dsm-ai-grid,
                .dsm-ai-module-list {
                    grid-template-columns: 1fr;
                }
            }
        </style>

        <div class="dsm-wrap">
            <div class="dsm-toolbar" id="dsm-toolbar">
                <span style="color:#fff; opacity:0.9;"><?php esc_html_e( 'WordPress 编辑器加载完成后，将自动加载主题装修模块…', 'developer-starter' ); ?></span>
            </div>

            <div class="dsm-list" id="dsm-list">
                <div style="padding: 20px; color: #666; text-align: center;"><?php esc_html_e( '正在等待编辑器与页面核心资源完成…', 'developer-starter' ); ?></div>
            </div>
        </div>

        <!-- Template Modal -->
        <div class="dsm-modal-overlay" id="dsm-template-modal">
            <div class="dsm-modal">
                <div class="dsm-modal-header">
                    <h3 class="dsm-modal-title"><?php esc_html_e( '我的模版库', 'developer-starter' ); ?></h3>
                    <button type="button" class="dsm-modal-close">&times;</button>
                </div>
                <div class="dsm-modal-body">
                    <ul class="dsm-template-list" id="dsm-template-list">
                        <!-- Ajax Loaded -->
                        <li style="text-align:center; padding: 20px; color:#666;"><?php esc_html_e( '加载中...', 'developer-starter' ); ?></li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="dsm-modal-overlay dsm-ai-modal" id="dsm-ai-modal">
            <div class="dsm-modal">
                <div class="dsm-modal-header">
                    <h3 class="dsm-modal-title"><?php esc_html_e( 'AI 局部辅助', 'developer-starter' ); ?></h3>
                    <button type="button" class="dsm-modal-close">&times;</button>
                </div>
                <div class="dsm-modal-body">
                    <?php if ( ! empty( $ai_builder_config['enabled'] ) ) : ?>
                        <div class="dsm-ai-grid">
                            <div class="dsm-ai-scope-notice">
                                <?php
                                $ai_scope_policy = isset( $ai_builder_config['scopePolicy'] ) && is_array( $ai_builder_config['scopePolicy'] ) ? $ai_builder_config['scopePolicy'] : array();
                                echo esc_html(
                                    ! empty( $ai_scope_policy['notice'] )
                                        ? (string) $ai_scope_policy['notice']
                                        : __( '当前 AI 只辅助当前页面；单页草稿属于高级入口，保存页面前不会正式生效。', 'developer-starter' )
                                );
                                ?>
                            </div>
                            <div class="dsm-ai-field">
                                <label for="dsm-ai-connection"><?php esc_html_e( 'AI 连接', 'developer-starter' ); ?></label>
                                <select id="dsm-ai-connection">
                                    <?php foreach ( $ai_builder_config['connections'] as $connection ) : ?>
                                        <?php if ( ! is_array( $connection ) || empty( $connection['id'] ) ) : ?>
                                            <?php continue; ?>
                                        <?php endif; ?>
                                        <option value="<?php echo esc_attr( (string) $connection['id'] ); ?>" <?php selected( $ai_builder_config['defaultConnectionId'], (string) $connection['id'] ); ?>>
                                            <?php echo esc_html( isset( $connection['name'] ) ? (string) $connection['name'] : (string) $connection['id'] ); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="dsm-ai-field">
                                <label for="dsm-ai-model"><?php esc_html_e( '模型', 'developer-starter' ); ?></label>
                                <input type="text" id="dsm-ai-model" list="dsm-ai-model-list" value="<?php echo esc_attr( isset( $ai_builder_config['defaultModel'] ) ? (string) $ai_builder_config['defaultModel'] : '' ); ?>" placeholder="qwen-plus" />
                                <datalist id="dsm-ai-model-list"></datalist>
                            </div>
                            <div class="dsm-ai-field dsm-ai-field-wide">
                                <label for="dsm-ai-prompt"><?php esc_html_e( '装修需求', 'developer-starter' ); ?></label>
                                <textarea id="dsm-ai-prompt" rows="7" placeholder="<?php esc_attr_e( '例如：优化当前首页，面向软件服务公司，风格现代可信。保留可用内容，强化首屏卖点、服务能力、客户案例、CTA 和 SEO 标题描述。', 'developer-starter' ); ?>"></textarea>
                            </div>
                            <div class="dsm-ai-field dsm-ai-field-wide">
                                <div class="dsm-ai-module-tools">
                                    <label for="dsm-ai-module-search"><?php esc_html_e( '候选模块', 'developer-starter' ); ?></label>
                                    <span><strong id="dsm-ai-selected-count">0</strong>/<?php echo esc_html( (string) $ai_max_modules ); ?></span>
                                </div>
                                <input type="search" id="dsm-ai-module-search" placeholder="<?php esc_attr_e( '搜索模块名称...', 'developer-starter' ); ?>" />
                                <div class="dsm-ai-module-list" id="dsm-ai-module-list">
                                    <?php foreach ( $ai_module_groups as $module_group ) : ?>
                                        <?php
                                        $group_key = isset( $module_group['key'] ) ? (string) $module_group['key'] : 'general';
                                        $group_label = isset( $module_group['label'] ) ? (string) $module_group['label'] : $group_key;
                                        $group_items = isset( $module_group['items'] ) && is_array( $module_group['items'] ) ? $module_group['items'] : array();
                                        if ( empty( $group_items ) ) {
                                            continue;
                                        }
                                        ?>
                                        <div class="dsm-ai-group-title" data-group-key="<?php echo esc_attr( $group_key ); ?>">
                                            <?php echo esc_html( $group_label ); ?>
                                        </div>
                                        <?php foreach ( $group_items as $module_choice ) : ?>
                                            <?php
                                            $keywords = isset( $module_choice['keywords'] ) && is_array( $module_choice['keywords'] ) ? $module_choice['keywords'] : array();
                                            $search_text = strtolower(
                                                trim(
                                                    implode(
                                                        ' ',
                                                        array_filter(
                                                            array_merge(
                                                                array(
                                                                    isset( $module_choice['title'] ) ? (string) $module_choice['title'] : '',
                                                                    isset( $module_choice['id'] ) ? (string) $module_choice['id'] : '',
                                                                    $group_label,
                                                                ),
                                                                array_map( 'strval', $keywords )
                                                            )
                                                        )
                                                    )
                                                )
                                            );
                                            ?>
                                            <label class="dsm-ai-module-item" data-group-key="<?php echo esc_attr( $group_key ); ?>" data-module-name="<?php echo esc_attr( $search_text ); ?>">
                                                <input type="checkbox" value="<?php echo esc_attr( $module_choice['id'] ); ?>" />
                                                <span><?php echo esc_html( $module_choice['title'] ); ?></span>
                                            </label>
                                        <?php endforeach; ?>
                                    <?php endforeach; ?>
                                </div>
                                <p class="description">
                                    <?php
                                    printf(
                                        /* translators: %d: max module count */
                                        esc_html__( '高级单页草稿：建议勾选 1-%d 个候选模块。生成后会先预览摘要，确认后才导入当前模块列表。', 'developer-starter' ),
                                        (int) $ai_max_modules
                                    );
                                    ?>
                                </p>
                            </div>
                        </div>
                        <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
                            <button type="button" class="button button-primary" id="dsm-ai-generate"><?php esc_html_e( '生成/优化当前单页', 'developer-starter' ); ?></button>
                            <button type="button" class="button" id="dsm-ai-undo-apply" style="display:none;"><?php esc_html_e( '撤回本次导入', 'developer-starter' ); ?></button>
                            <span class="dsm-ai-status" id="dsm-ai-status"></span>
                        </div>
                        <ul class="dsm-ai-warning-list" id="dsm-ai-warnings" style="display:none;"></ul>
                        <div class="dsm-ai-pending-preview" id="dsm-ai-pending-preview" style="display:none;">
                            <div class="dsm-ai-pending-head">
                                <strong><?php esc_html_e( 'AI 草稿待确认', 'developer-starter' ); ?></strong>
                                <span><?php esc_html_e( '当前页面', 'developer-starter' ); ?></span>
                            </div>
                            <p><?php esc_html_e( '请先查看摘要；确认后才会导入当前模块列表。保存页面前不会正式生效。', 'developer-starter' ); ?></p>
                            <pre class="dsm-ai-pending-summary" id="dsm-ai-pending-summary"></pre>
                            <div class="dsm-ai-pending-actions">
                                <button type="button" class="button button-primary" id="dsm-ai-apply-pending"><?php esc_html_e( '确认应用草稿', 'developer-starter' ); ?></button>
                                <button type="button" class="button" id="dsm-ai-discard-pending"><?php esc_html_e( '放弃草稿', 'developer-starter' ); ?></button>
                            </div>
                        </div>
                    <?php elseif ( $ai_builder_available && ! $ai_builder_supported ) : ?>
                        <div class="dsm-ai-disabled">
                            <p style="margin-top:0;"><?php esc_html_e( '当前积分商城页面暂不支持 AI 装修。', 'developer-starter' ); ?></p>
                            <p style="margin-bottom:0;"><?php esc_html_e( '请使用积分商城模块手动搭建页面；普通主题页面仍可使用 AI 装修。', 'developer-starter' ); ?></p>
                        </div>
                    <?php else : ?>
                        <div class="dsm-ai-disabled">
                            <p style="margin-top:0;"><?php esc_html_e( '当前还没有可用的 AI 连接。', 'developer-starter' ); ?></p>
                            <p style="margin-bottom:0;"><?php esc_html_e( '请先到「启灵主题设置 -> AI装修」里启用 AI 装修，并至少配置一个启用中的 AI 连接。', 'developer-starter' ); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <script>
        jQuery(document).ready(function($){
            var idx = 0;
            var modulesUiLoaded = false;
            var modulesUiRequested = false;
            var modulesUiRetryCount = 0;
            var initialPostId = <?php echo (int) $post->ID; ?>;
            var aiBuilderConfig = <?php echo wp_json_encode( $ai_builder_config, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ); ?> || { enabled: false, connections: [] };
            var aiBuilderService = window.QilingAiBuilderService || null;
            var pendingAiPackageJson = '';
            var pendingAiPackageSuccessMessage = '';
            var lastAiApplySnapshot = null;

            function isBlockEditorPage() {
                return !!(document.body && document.body.classList.contains('block-editor-page'));
            }

            function resolveCurrentPostId() {
                var postId = parseInt(initialPostId, 10) || 0;

                if (window.wp && wp.data && wp.data.select) {
                    var editorStore = wp.data.select('core/editor');
                    if (editorStore && typeof editorStore.getCurrentPostId === 'function') {
                        var runtimePostId = parseInt(editorStore.getCurrentPostId(), 10) || 0;
                        if (runtimePostId > 0) {
                            postId = runtimePostId;
                        }
                    }
                }

                return postId;
            }

            function getPagePackageHint() {
                return $('#dsm-page-package-hint');
            }

            function setPagePackageHint(message) {
                var $hint = getPagePackageHint();
                if (!$hint.length) return;
                if (!message) {
                    $hint.removeClass('is-visible').text('');
                    return;
                }
                $hint.addClass('is-visible').text(message);
            }

            function clearImportedPagePackageMeta() {
                $('#developer-starter-page-package-imported').val('0');
                $('#developer-starter-page-package-template').val('');
                $('#developer-starter-page-package-hide-header').val('');
                $('#developer-starter-page-package-hide-header-defined').val('0');
                $('#developer-starter-page-package-transparent-header').val('');
                $('#developer-starter-page-package-design').val('');
                $('#developer-starter-page-package-footer').val('');
                $('#developer-starter-page-package-region-decoration').val('');
                $('#developer-starter-page-package-visual-style').val('');
            }

            function getEditedPostTitleValue() {
                if (window.wp && wp.data && wp.data.select) {
                    var editorStore = wp.data.select('core/editor');
                    if (editorStore && typeof editorStore.getEditedPostAttribute === 'function') {
                        return String(editorStore.getEditedPostAttribute('title') || '');
                    }
                }
                return String($('#title').val() || '');
            }

            function setEditedPostTitleValue(title) {
                title = String(title || '');
                if (window.wp && wp.data && wp.data.dispatch) {
                    var editorDispatcher = wp.data.dispatch('core/editor');
                    if (editorDispatcher && typeof editorDispatcher.editPost === 'function') {
                        editorDispatcher.editPost({ title: title });
                    }
                }
                $('#title').val(title).attr('value', title).trigger('input').trigger('change').trigger('keyup').trigger('blur');
                $('.editor-post-title__input, textarea.editor-post-title__input').first().val(title).trigger('input').trigger('change');
                syncClassicTitlePromptState();
            }

            function captureAiApplySnapshot() {
                var fields = [
                    '#developer-starter-page-package-imported',
                    '#developer-starter-page-package-template',
                    '#developer-starter-page-package-hide-header',
                    '#developer-starter-page-package-hide-header-defined',
                    '#developer-starter-page-package-transparent-header',
                    '#developer-starter-page-package-design',
                    '#developer-starter-page-package-footer',
                    '#developer-starter-page-package-region-decoration',
                    '#developer-starter-page-package-visual-style',
                    '[name="seo_title"]',
                    '[name="seo_description"]',
                    '[name="seo_keywords"]',
                    '[name="og_title"]',
                    '[name="og_description"]'
                ];
                var checkboxes = [
                    'input[name="qiling_hide_page_header"]',
                    'input[name="qiling_transparent_header"]',
                    'input[name="developer_starter_enable_scroll_reveal"]'
                ];
                var snapshot = {
                    listHtml: $('#dsm-list').html(),
                    idx: idx,
                    title: getEditedPostTitleValue(),
                    hint: getPagePackageHint().text(),
                    hintVisible: getPagePackageHint().hasClass('is-visible'),
                    fields: {},
                    checkboxes: {}
                };

                $.each(fields, function(_, selector) {
                    var $field = $(selector).first();
                    if ($field.length) {
                        snapshot.fields[selector] = $field.val();
                    }
                });
                $.each(checkboxes, function(_, selector) {
                    var $field = $(selector).first();
                    if ($field.length) {
                        snapshot.checkboxes[selector] = $field.prop('checked');
                    }
                });

                return snapshot;
            }

            function restoreAiApplySnapshot(snapshot) {
                if (!snapshot) {
                    return;
                }

                $('#dsm-list').html(snapshot.listHtml || '');
                idx = parseInt(snapshot.idx, 10) || 0;
                renumberModuleInputs();
                setEditedPostTitleValue(snapshot.title || '');

                $.each(snapshot.fields || {}, function(selector, value) {
                    $(selector).first().val(value).trigger('input').trigger('change');
                });
                $.each(snapshot.checkboxes || {}, function(selector, checked) {
                    $(selector).first().prop('checked', !!checked).trigger('change');
                });

                if (snapshot.hintVisible) {
                    setPagePackageHint(snapshot.hint || '');
                } else {
                    setPagePackageHint('');
                }
                setTimeout(checkDependencies, 80);
            }

            function setAiPendingButtonsDisabled(disabled) {
                $('#dsm-ai-apply-pending, #dsm-ai-discard-pending').prop('disabled', !!disabled);
            }

            function setAiApplyUndoVisible(visible) {
                $('#dsm-ai-undo-apply').toggle(!!visible).prop('disabled', !visible);
            }

            function clearAiPendingPackage() {
                pendingAiPackageJson = '';
                pendingAiPackageSuccessMessage = '';
                $('#dsm-ai-pending-preview').hide();
                $('#dsm-ai-pending-summary').text('');
                setAiPendingButtonsDisabled(false);
            }

            function syncClassicTitlePromptState() {
                var $classicTitle = $('#title');
                var $titlePrompt = $('#title-prompt-text');

                if (!$classicTitle.length || !$titlePrompt.length) {
                    return;
                }

                var hasTitle = $.trim($classicTitle.val() || '') !== '';
                $titlePrompt.toggleClass('screen-reader-text', hasTitle);
                $titlePrompt.attr('aria-hidden', hasTitle ? 'true' : 'false');
                $titlePrompt.css('display', hasTitle ? 'none' : '');
            }

            function applyImportedPagePackageTitle(packageData) {
                packageData = packageData || {};
                var importedTitle = $.trim(packageData.title || '');
                if (!importedTitle) return;

                var currentTitle = '';

                if (window.wp && wp.data && wp.data.select) {
                    var editorStore = wp.data.select('core/editor');
                    if (editorStore && typeof editorStore.getEditedPostAttribute === 'function') {
                        currentTitle = $.trim(editorStore.getEditedPostAttribute('title') || '');
                    }
                }

                var $classicTitle = $('#title');
                if (!currentTitle && $classicTitle.length) {
                    currentTitle = $.trim($classicTitle.val() || '');
                }

                var $blockTitle = $('.editor-post-title__input, textarea.editor-post-title__input').first();
                if (!currentTitle && $blockTitle.length) {
                    currentTitle = $.trim($blockTitle.val() || '');
                }

                if (currentTitle) {
                    return;
                }

                if (window.wp && wp.data && wp.data.dispatch) {
                    var editorDispatcher = wp.data.dispatch('core/editor');
                    if (editorDispatcher && typeof editorDispatcher.editPost === 'function') {
                        editorDispatcher.editPost({ title: importedTitle });
                    }
                }

                if ($classicTitle.length) {
                    $classicTitle.val(importedTitle);
                    $classicTitle.attr('value', importedTitle);
                    $classicTitle.trigger('input').trigger('change').trigger('keyup').trigger('blur');
                    syncClassicTitlePromptState();
                }

                if ($blockTitle.length && !$.trim($blockTitle.val() || '')) {
                    $blockTitle.val(importedTitle).trigger('input').trigger('change');
                }
            }

            function applyImportedPagePackageMeta(packageData) {
                packageData = packageData || {};
                var fullscreenTemplate = '<?php echo esc_js( $default_page_package_template ); ?>';
                var pageTemplate = String(packageData.pageTemplate || '');
                var usesFullscreenTemplate = pageTemplate === fullscreenTemplate;
                var hasHidePageHeader = !!packageData.hidePageHeaderDefined;

                $('#developer-starter-page-package-imported').val('1');
                $('#developer-starter-page-package-template').val(pageTemplate);
                $('#developer-starter-page-package-hide-header').val(hasHidePageHeader ? (packageData.hidePageHeader ? '1' : '0') : '');
                $('#developer-starter-page-package-hide-header-defined').val(hasHidePageHeader ? '1' : '0');
                $('#developer-starter-page-package-transparent-header').val(packageData.transparentHeader ? '1' : '0');
                $('#developer-starter-page-package-design').val(JSON.stringify(packageData.pageDesign || {}));
                $('#developer-starter-page-package-footer').val(JSON.stringify(packageData.footer || packageData.footerSettings || {}));
                $('#developer-starter-page-package-region-decoration').val(JSON.stringify(packageData.regionDecoration || packageData.region_decoration || {}));
                $('#developer-starter-page-package-visual-style').val(JSON.stringify(packageData.visualStyle || packageData.visual_style || {}));

                var $hideHeader = $('input[name="qiling_hide_page_header"]');
                if ($hideHeader.length && hasHidePageHeader) {
                    $hideHeader.prop('checked', !!packageData.hidePageHeader);
                }

                var $transparentHeader = $('input[name="qiling_transparent_header"]');
                if ($transparentHeader.length) {
                    $transparentHeader.prop('checked', !!packageData.transparentHeader);
                }

                var $scrollReveal = $('input[name="developer_starter_enable_scroll_reveal"]');
                if ($scrollReveal.length) {
                    $scrollReveal.prop('checked', !!packageData.enableScrollReveal);
                }

                applyImportedPagePackageSeo(packageData.seo || {});

                var hint = usesFullscreenTemplate
                    ? '<?php echo esc_js( __( '已导入页面 JSON。保存页面后将自动切换为独立全屏模板，不显示页面头部面包屑或侧边栏。', 'developer-starter' ) ); ?>'
                    : '<?php echo esc_js( __( '已导入页面 JSON。保存页面后会按包内模板与模块数据应用到当前页面。', 'developer-starter' ) ); ?>';
                if (packageData.templateLabel) {
                    hint += ' <?php echo esc_js( __( '目标模板：', 'developer-starter' ) ); ?>' + packageData.templateLabel;
                }
                setPagePackageHint(hint);
            }

            function applyImportedPagePackageSeo(seoData) {
                seoData = seoData && typeof seoData === 'object' ? seoData : {};
                var mapping = {
                    seo_title: seoData.title || seoData.seo_title || '',
                    seo_description: seoData.description || seoData.seo_description || '',
                    seo_keywords: seoData.keywords || seoData.focus_keywords || '',
                    og_title: seoData.og_title || '',
                    og_description: seoData.og_description || ''
                };

                $.each(mapping, function(fieldName, fieldValue) {
                    if (Array.isArray(fieldValue)) {
                        fieldValue = fieldValue.join(',');
                    }
                    fieldValue = $.trim(String(fieldValue || ''));
                    if (!fieldValue) {
                        return;
                    }
                    var $field = $('[name="' + fieldName + '"]');
                    if ($field.length) {
                        $field.val(fieldValue).trigger('input').trigger('change');
                    }
                });
            }

            function downloadTextFile(filename, content) {
                var blob = new Blob([content], { type: 'application/json;charset=utf-8' });
                var url = window.URL.createObjectURL(blob);
                var link = document.createElement('a');
                link.href = url;
                link.download = filename;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                window.URL.revokeObjectURL(url);
            }

            function applyImportedPagePackageResponse(res, fallbackError) {
                if (!(res && res.success && res.data)) {
                    clearImportedPagePackageMeta();
                    setPagePackageHint('');
                    alert((fallbackError || '<?php echo esc_js( __( '导入失败：', 'developer-starter' ) ); ?>') + ((res && res.data) ? res.data : '<?php echo esc_js( __( '未知错误', 'developer-starter' ) ); ?>'));
                    return false;
                }

                $('#dsm-list').html(res.data.list || '');
                idx = parseInt(res.data.moduleCount, 10) || 0;
                renumberModuleInputs();
                applyImportedPagePackageTitle(res.data.package || {});
                applyImportedPagePackageMeta(res.data.package || {});
                setTimeout(checkDependencies, 80);
                return true;
            }

            function updateAiModelSuggestions() {
                if (!aiBuilderService) {
                    return;
                }

                aiBuilderService.updateModelSuggestions({
                    config: aiBuilderConfig,
                    connectionId: String($('#dsm-ai-connection').val() || ''),
                    datalist: $('#dsm-ai-model-list'),
                    modelInput: $('#dsm-ai-model')
                });
            }

            function getAiSelectedModules() {
                if (!aiBuilderService) {
                    return [];
                }

                return aiBuilderService.getSelectedValues('#dsm-ai-module-list input[type="checkbox"]:checked');
            }

            function getAiMaxModules() {
                if (!aiBuilderService) {
                    return 10;
                }

                return aiBuilderService.getMaxModules(aiBuilderConfig);
            }

            function updateAiSelectedCount() {
                $('#dsm-ai-selected-count').text(getAiSelectedModules().length);
            }

            function setAiStatus(message, type) {
                var $status = $('#dsm-ai-status');
                $status.removeClass('is-error is-success is-warning');
                if (!message) {
                    $status.text('');
                    return;
                }
                $status.text(message);
                if (type) {
                    $status.addClass('is-' + type);
                }
            }

            function renderAiWarnings(warnings) {
                var $list = $('#dsm-ai-warnings');
                warnings = Array.isArray(warnings) ? warnings : [];
                if (!warnings.length) {
                    $list.hide().empty();
                    return;
                }

                var html = '';
                $.each(warnings, function(_, warning){
                    if (!warning) {
                        return;
                    }
                    html += '<li>' + $('<div>').text(String(warning)).html() + '</li>';
                });
                $list.html(html).show();
            }

            function buildAiGeneratedPackageSummary(packageData) {
                packageData = packageData && typeof packageData === 'object' ? packageData : {};
                var modules = Array.isArray(packageData.modules) ? packageData.modules : [];
                var visualStyle = packageData.visualStyle || packageData.visual_style || {};
                var lines = [];
                var title = $.trim(String(packageData.title || ''));
                var templateLabel = $.trim(String(packageData.templateLabel || packageData.template_label || ''));
                var moduleNames = modules.slice(0, 8).map(function(moduleItem) {
                    moduleItem = moduleItem && typeof moduleItem === 'object' ? moduleItem : {};
                    return moduleItem.title || moduleItem.name || moduleItem.label || moduleItem.type || moduleItem.id || '';
                }).filter(Boolean);

                if (title) {
                    lines.push('<?php echo esc_js( __( '页面标题：', 'developer-starter' ) ); ?>' + title);
                }
                if (templateLabel) {
                    lines.push('<?php echo esc_js( __( '目标模板：', 'developer-starter' ) ); ?>' + templateLabel);
                }
                lines.push('<?php echo esc_js( __( '模块数量：', 'developer-starter' ) ); ?>' + modules.length);
                if (moduleNames.length) {
                    lines.push('<?php echo esc_js( __( '模块预览：', 'developer-starter' ) ); ?>' + moduleNames.join('、') + (modules.length > moduleNames.length ? '…' : ''));
                }
                if (visualStyle && typeof visualStyle === 'object' && Object.keys(visualStyle).length) {
                    lines.push('<?php echo esc_js( __( '包含页面风格：是', 'developer-starter' ) ); ?>');
                }
                if (packageData.seo && typeof packageData.seo === 'object' && Object.keys(packageData.seo).length) {
                    lines.push('<?php echo esc_js( __( '包含 SEO 建议：是', 'developer-starter' ) ); ?>');
                }
                if (packageData.footer || packageData.footerSettings) {
                    lines.push('<?php echo esc_js( __( '包含页脚设置：是', 'developer-starter' ) ); ?>');
                }

                return lines.length ? lines.join("\n") : '<?php echo esc_js( __( '草稿已生成，请确认后应用到当前页面模块列表。', 'developer-starter' ) ); ?>';
            }

            function renderAiPendingPackage(packageData, jsonPayload, successMessage) {
                pendingAiPackageJson = String(jsonPayload || '');
                pendingAiPackageSuccessMessage = String(successMessage || '');
                lastAiApplySnapshot = null;
                setAiApplyUndoVisible(false);
                $('#dsm-ai-pending-summary').text(buildAiGeneratedPackageSummary(packageData));
                $('#dsm-ai-pending-preview').show();
                setAiPendingButtonsDisabled(false);
                setAiStatus('<?php echo esc_js( __( 'AI 草稿已生成，请查看摘要后确认应用。', 'developer-starter' ) ); ?>', 'success');
            }

            function applyAiGeneratedPackage(jsonPayload, doneCallback, successMessage) {
                setPagePackageHint('<?php echo esc_js( __( '正在把 AI 草稿应用到当前页面模块…', 'developer-starter' ) ); ?>');
                lastAiApplySnapshot = captureAiApplySnapshot();
                setAiPendingButtonsDisabled(true);

                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'qiling_import_page_json_preview',
                        nonce: $('#modules_nonce').val(),
                        json: jsonPayload
                    },
                    success: function(res) {
                        if (applyImportedPagePackageResponse(res, '<?php echo esc_js( __( '草稿应用失败：', 'developer-starter' ) ); ?>')) {
                            setAiStatus(successMessage || '<?php echo esc_js( __( '草稿已应用到页面模块列表，请保存页面。', 'developer-starter' ) ); ?>', 'success');
                            clearAiPendingPackage();
                            setAiApplyUndoVisible(true);
                        } else {
                            restoreAiApplySnapshot(lastAiApplySnapshot);
                            lastAiApplySnapshot = null;
                            setAiPendingButtonsDisabled(false);
                            setAiStatus('<?php echo esc_js( __( '草稿应用失败，请重试。', 'developer-starter' ) ); ?>', 'error');
                        }
                    },
                    error: function() {
                        restoreAiApplySnapshot(lastAiApplySnapshot);
                        lastAiApplySnapshot = null;
                        setAiPendingButtonsDisabled(false);
                        setAiStatus('<?php echo esc_js( __( '网络错误，草稿未能应用到当前页面。', 'developer-starter' ) ); ?>', 'error');
                    },
                    complete: function() {
                        if (typeof doneCallback === 'function') {
                            doneCallback();
                        }
                    }
                });
            }

            function runAiGenerationFlow(args) {
                if (!aiBuilderService) {
                    setAiStatus('<?php echo esc_js( __( 'AI 服务未加载，请刷新后重试。', 'developer-starter' ) ); ?>', 'error');
                    return;
                }

                var $button = args.button && args.button.jquery ? args.button : $('#dsm-ai-generate');

                renderAiWarnings([]);
                clearAiPendingPackage();
                lastAiApplySnapshot = null;
                setAiApplyUndoVisible(false);
                setAiStatus('<?php echo esc_js( __( '正在生成页面草稿，请稍候…', 'developer-starter' ) ); ?>', 'warning');
                $button.prop('disabled', true);

                aiBuilderService.generatePagePackage({
                    ajaxUrl: ajaxurl,
                    config: aiBuilderConfig,
                    postId: resolveCurrentPostId(),
                    prompt: String(args.prompt || ''),
                    connectionId: String(args.connectionId || ''),
                    model: String(args.model || ''),
                    moduleIds: Array.isArray(args.moduleIds) ? args.moduleIds.slice() : [],
                    successMessage: '<?php echo esc_js( __( '草稿已应用到页面模块列表，请保存页面。', 'developer-starter' ) ); ?>',
                    generateFailedMessage: '<?php echo esc_js( __( '生成失败，请重试。', 'developer-starter' ) ); ?>',
                    timeoutMessage: '<?php echo esc_js( __( 'AI 生成超时，请稍后重试，或减少候选模块数量。', 'developer-starter' ) ); ?>',
                    networkErrorMessage: '<?php echo esc_js( __( '网络错误，生成失败。', 'developer-starter' ) ); ?>'
                }).done(function(result) {
                    var successMessage = result && result.successMessage
                        ? result.successMessage
                        : '<?php echo esc_js( __( '草稿已应用到页面模块列表，请保存页面。', 'developer-starter' ) ); ?>';
                    var finalJson = '';

                    try {
                        finalJson = JSON.stringify(result.package);
                    } catch (err) {
                        setAiStatus('<?php echo esc_js( __( '草稿组装失败，请重试。', 'developer-starter' ) ); ?>', 'error');
                        $button.prop('disabled', false);
                        return;
                    }

                    renderAiWarnings(result.warnings);
                    renderAiPendingPackage(result.package, finalJson, successMessage);
                    $button.prop('disabled', false);
                }).fail(function(result) {
                    setAiStatus(result && result.message ? result.message : '<?php echo esc_js( __( '网络错误，生成失败。', 'developer-starter' ) ); ?>', 'error');
                    $button.prop('disabled', false);
                });
            }

            function showModulesUiLoadError() {
                modulesUiLoaded = false;
                modulesUiRequested = false;
                $('#dsm-toolbar').html('<span style="color:#fff;"><?php echo esc_js( __( '主题模块加载失败，请点击重试。', 'developer-starter' ) ); ?></span> <button type="button" class="dsm-add-btn dsm-retry-load"><?php echo esc_js( __( '重试加载', 'developer-starter' ) ); ?></button>');
                $('#dsm-list').html('<div style="padding:20px;color:#b32d2e;text-align:center;"><?php echo esc_js( __( '模块未加载，本次保存不会覆盖已存在模块。', 'developer-starter' ) ); ?></div>');
            }

            function loadModulesEditorUI() {
                if (modulesUiLoaded) return;
                modulesUiLoaded = true;
                var postId = resolveCurrentPostId();

                if (postId <= 0) {
                    modulesUiLoaded = false;
                    modulesUiRequested = false;
                    if (modulesUiRetryCount < 3) {
                        modulesUiRetryCount++;
                        setTimeout(requestModulesEditorUILoad, 700);
                        return;
                    }
                    showModulesUiLoadError();
                    return;
                }

                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'qiling_load_modules_editor_ui',
                        nonce: $('#modules_nonce').val(),
                        post_id: postId
                    },
                    success: function(res) {
                        if (!(res && res.success && res.data)) {
                            if (modulesUiRetryCount < 2) {
                                modulesUiLoaded = false;
                                modulesUiRequested = false;
                                modulesUiRetryCount++;
                                setTimeout(requestModulesEditorUILoad, 900);
                                return;
                            }
                            showModulesUiLoadError();
                            return;
                        }

                        $('#dsm-toolbar').html(res.data.toolbar || '');
                        $('#dsm-list').html(res.data.list || '');
                        idx = parseInt(res.data.moduleCount, 10) || 0;
                        renumberModuleInputs();
                        modulesUiRetryCount = 0;
                        $('#developer-starter-modules-ui-loaded').val('1');
                        setTimeout(checkDependencies, 80);
                    },
                    error: function() {
                        if (modulesUiRetryCount < 2) {
                            modulesUiLoaded = false;
                            modulesUiRequested = false;
                            modulesUiRetryCount++;
                            setTimeout(requestModulesEditorUILoad, 900);
                            return;
                        }
                        showModulesUiLoadError();
                    }
                });
            }

            function requestModulesEditorUILoad() {
                if (modulesUiRequested || modulesUiLoaded) return;
                modulesUiRequested = true;

                var runLoad = function() {
                    loadModulesEditorUI();
                };

                if (isBlockEditorPage()) {
                    setTimeout(runLoad, 80);
                } else if (typeof window.requestIdleCallback === 'function') {
                    window.requestIdleCallback(function() {
                        runLoad();
                    }, { timeout: 5000 });
                } else {
                    setTimeout(runLoad, 1200);
                }
            }

            function isModulesBoxNearViewport() {
                var box = document.getElementById('developer_starter_modules');
                if (!box || !box.getBoundingClientRect) return true;

                var rect = box.getBoundingClientRect();
                var viewportHeight = window.innerHeight || document.documentElement.clientHeight || 0;
                return rect.top <= (viewportHeight + 500);
            }

            function setupDeferredModulesUiLoad() {
                var startDeferredLoad = function() {
                    if (isBlockEditorPage()) {
                        requestModulesEditorUILoad();
                        setTimeout(function() {
                            if (!modulesUiLoaded) {
                                modulesUiRequested = false;
                                requestModulesEditorUILoad();
                            }
                        }, 1800);
                        setTimeout(function() {
                            if (!modulesUiLoaded) {
                                modulesUiRequested = false;
                                requestModulesEditorUILoad();
                            }
                        }, 3600);
                        return;
                    }

                    if (isModulesBoxNearViewport()) {
                        requestModulesEditorUILoad();
                        return;
                    }

                    if ('IntersectionObserver' in window) {
                        var target = document.getElementById('developer_starter_modules');
                        if (target) {
                            var observer = new IntersectionObserver(function(entries) {
                                entries.forEach(function(entry) {
                                    if (entry.isIntersecting) {
                                        observer.disconnect();
                                        requestModulesEditorUILoad();
                                    }
                                });
                            }, {
                                rootMargin: '500px 0px 500px 0px',
                                threshold: 0
                            });
                            observer.observe(target);
                        }
                    }

                    $(window).one('scroll.dsm touchstart.dsm keydown.dsm', requestModulesEditorUILoad);

                    // 最终兜底：确保核心加载后在空闲期自动拉起模块 UI。
                    setTimeout(requestModulesEditorUILoad, 3500);
                };

                if (document.readyState === 'complete') {
                    setTimeout(startDeferredLoad, 300);
                } else {
                    $(window).on('load', function() {
                        setTimeout(startDeferredLoad, 300);
                    });
                }
            }

            setupDeferredModulesUiLoad();
            syncClassicTitlePromptState();

            function showDsmModal(selector) {
                var $modal = $(selector);
                if (!$modal.length) return;

                if (!$modal.parent().is('body')) {
                    $modal.appendTo(document.body);
                }

                $modal.stop(true, true).fadeIn(200).css('display', 'flex');
            }

            function renumberModuleInputs() {
                var $items = $('#dsm-list .dsm-item');
                $items.each(function(moduleIndex) {
                    $(this).find(':input[name^="modules["]').each(function() {
                        var name = $(this).attr('name');
                        if (!name) {
                            return;
                        }
                        $(this).attr('name', name.replace(/^modules\[[^\]]+\]/, 'modules[' + moduleIndex + ']'));
                    });
                });
                idx = $items.length;
                syncModulesPayload();
            }

            function setNestedModuleValue(target, path, value) {
                var cursor = target;
                for (var i = 0; i < path.length; i++) {
                    var key = path[i];
                    if (!key) {
                        continue;
                    }

                    if (i === path.length - 1) {
                        cursor[key] = value;
                        return;
                    }

                    if (!Object.prototype.hasOwnProperty.call(cursor, key) || !cursor[key] || typeof cursor[key] !== 'object') {
                        cursor[key] = {};
                    }
                    cursor = cursor[key];
                }
            }

            function collectModulesFromEditor() {
                var modules = [];

                $('#dsm-list .dsm-item').each(function() {
                    var $item = $(this);
                    var type = String($item.data('type') || $item.find('input[name$="[type]"]').first().val() || '');
                    var data = {};

                    if (!type) {
                        return;
                    }

                    $item.find('.dsm-content :input[name^="modules["]').each(function() {
                        var $input = $(this);
                        var name = $input.attr('name') || '';
                        var inputType = String(($input.attr('type') || '')).toLowerCase();
                        var match = name.match(/^modules\[[^\]]+\]\[data\](.*)$/);
                        var path = [];
                        var value;

                        if (!match || !match[1]) {
                            return;
                        }

                        if ((inputType === 'checkbox' || inputType === 'radio') && !$input.prop('checked')) {
                            return;
                        }

                        String(match[1]).replace(/\[([^\]]*)\]/g, function(_, key) {
                            path.push(key);
                            return '';
                        });

                        if (!path.length) {
                            return;
                        }

                        value = $input.val();
                        if (Array.isArray(value)) {
                            value = value.join(',');
                        }

                        setNestedModuleValue(data, path, value == null ? '' : String(value));
                    });

                    modules.push({
                        type: type,
                        data: data
                    });
                });

                return modules;
            }

            function syncModulesPayload() {
                var $payload = $('#developer-starter-modules-payload');
                if (!$payload.length) {
                    return;
                }

                try {
                    $payload.val(JSON.stringify(collectModulesFromEditor()));
                } catch (err) {
                    $payload.val('');
                }
            }

            function saveModulesEditorState() {
                var postId = resolveCurrentPostId();
                var payload = $('#developer-starter-modules-payload').val() || '';

                if (!modulesUiLoaded || postId <= 0 || !payload) {
                    return;
                }

                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'qiling_save_modules_editor_state',
                        nonce: $('#modules_nonce').val(),
                        post_id: postId,
                        modules: payload,
                        enable_scroll_reveal: $('input[name="developer_starter_enable_scroll_reveal"]').prop('checked') ? '1' : '0'
                    }
                });
            }

            $(document).on('input change keyup blur', '#title', function() {
                syncClassicTitlePromptState();
            });

            $(document).on('submit', 'form#post, form[name="post"]', function() {
                if (modulesUiLoaded) {
                    renumberModuleInputs();
                }
            });

            if (window.wp && wp.data && wp.data.select && wp.data.subscribe) {
                var wasSavingPost = false;
                wp.data.subscribe(function() {
                    var editorStore = wp.data.select('core/editor');
                    if (!editorStore || typeof editorStore.isSavingPost !== 'function') {
                        return;
                    }

                    var isSavingPost = !!editorStore.isSavingPost();
                    var isAutosaving = typeof editorStore.isAutosavingPost === 'function' && !!editorStore.isAutosavingPost();
                    var saveSucceeded = typeof editorStore.didPostSaveRequestSucceed === 'function' && !!editorStore.didPostSaveRequestSucceed();

                    if (wasSavingPost && !isSavingPost && !isAutosaving && saveSucceeded) {
                        renumberModuleInputs();
                        saveModulesEditorState();
                    }

                    wasSavingPost = isSavingPost;
                });
            }

            $(document).on('click', '.dsm-retry-load', function(e){
                e.preventDefault();
                modulesUiRequested = false;
                modulesUiRetryCount = 0;
                loadModulesEditorUI();
            });

            // Add module
            $(document).on('click', '.dsm-add-btn:not(.dsm-btn-templates):not(.dsm-btn-page-json-import):not(.dsm-btn-page-json-export):not(.dsm-btn-ai-decorate)', function(e){
                e.preventDefault();
                var type = $(this).data('type');
                var $btn = $(this);
                if (!type || $btn.data('loading')) return;

                $btn.data('loading', 1).prop('disabled', true).css('opacity', 0.7);

                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'qiling_render_module_item',
                        nonce: $('#modules_nonce').val(),
                        type: type,
                        idx: idx
                    },
                    success: function(res) {
                        if (res && res.success && res.data) {
                            var $item = $(res.data);
                            $item.addClass('open');
                            $('#dsm-list').append($item);
                            idx++;
                            renumberModuleInputs();
                            $('html, body').animate({ scrollTop: $item.offset().top - 100 }, 300);
                            setTimeout(checkDependencies, 100);
                        } else {
                            alert('<?php echo esc_js( __( '模块加载失败，请稍后重试。', 'developer-starter' ) ); ?>');
                        }
                    },
                    error: function() {
                        alert('<?php echo esc_js( __( '网络错误，请稍后重试。', 'developer-starter' ) ); ?>');
                    },
                    complete: function() {
                        $btn.removeData('loading').prop('disabled', false).css('opacity', '');
                    }
                });
            });

            // Toggle module
            $(document).on('click', '.dsm-item-header', function(e){
                if($(e.target).closest('.dsm-remove, .dsm-save-template').length) return;
                $(this).closest('.dsm-item').toggleClass('open');
            });

            // Remove module
            $(document).on('click', '.dsm-remove', function(e){
                e.preventDefault();
                e.stopPropagation();
                if(confirm('<?php echo esc_js( __( '确定删除此模块吗？', 'developer-starter' ) ); ?>')){
                    $(this).closest('.dsm-item').remove();
                    renumberModuleInputs();
                }
            });

            // Sortable
            if($.fn.sortable) {
                $('#dsm-list').sortable({
                    handle: '.dsm-handle',
                    placeholder: 'dsm-placeholder',
                    tolerance: 'pointer',
                    update: function() {
                        renumberModuleInputs();
                    }
                });
            }

            // Image/File upload logic (Standard WP Media)
            $(document).on('click', '.dsm-upload', function(e){
                e.preventDefault();
                var $btn = $(this);
                var $field = $btn.closest('.dsm-field');
                var $inp = $field.find('.dsm-img-input');
                var $wrap = $field.find('.dsm-img-wrap');
                var isGallery = $btn.hasClass('dsm-gallery-upload');
                
                if(typeof wp === 'undefined' || typeof wp.media === 'undefined') {
                    alert('<?php echo esc_js( __( '媒体库加载失败', 'developer-starter' ) ); ?>'); return;
                }
                
                // Create frame if not exists (simplified for inline)
                var frame = wp.media({ 
                    title: isGallery ? '<?php echo esc_js( __( '选择多张图片 (按住Ctrl/Cmd多选)', 'developer-starter' ) ); ?>' : '<?php echo esc_js( __( '选择文件', 'developer-starter' ) ); ?>', 
                    multiple: isGallery ? 'add' : false, 
                    library: {type: 'image'} 
                });
                
                frame.on('select', function(){
                    var selection = frame.state().get('selection');
                    
                    if (isGallery) {
                        var urls = [];
                        var html = '';
                        selection.map(function(attachment){
                            attachment = attachment.toJSON();
                            urls.push(attachment.url);
                            html += '<span class="dsm-img-wrap gallery-item"><img src="'+ attachment.url +'" class="dsm-img-preview"/><button type="button" class="dsm-img-remove">×</button></span> ';
                        });
                        
                        // Append newly selected images to the existing gallery value.
                        var current = $inp.val();
                        if(current) {
                            var newUrls = urls.join(',');
                            $inp.val(current + ',' + newUrls); // Append
                            $btn.siblings('.dsm-gallery-preview').append(html);
                        } else {
                            $inp.val(urls.join(','));
                            $btn.siblings('.dsm-gallery-preview').html(html);
                        }
                    } else {
                        var att = selection.first().toJSON();
                        $inp.val(att.url);
                        if($wrap.length){ $wrap.find('.dsm-img-preview').attr('src', att.url); $wrap.show(); } 
                        else { $btn.after('<span class="dsm-img-wrap"><img src="'+ att.url +'" class="dsm-img-preview"/><button type="button" class="dsm-img-remove">×</button></span>'); }
                    }
                });
                frame.open();
            });
            $(document).on('click', '.dsm-img-remove', function(e){
                e.preventDefault();
                var $wrap = $(this).closest('.dsm-img-wrap');
                var $field = $wrap.closest('.dsm-field');
                var $inp = $field.find('.dsm-img-input');
                
                if ($wrap.hasClass('gallery-item')) {
                     // Gallery removal logic
                     var urlToRemove = $wrap.find('img').attr('src');
                     var currentUrls = $inp.val().split(',');
                     var newUrls = currentUrls.filter(function(url) { return url.trim() !== urlToRemove; });
                     $inp.val(newUrls.join(','));
                     $wrap.remove();
                } else {
                    // Single image removal
                    $inp.val('');
                    $wrap.remove();
                }
            });

            // Repeater Add
            $(document).on('click', '.dsm-rep-add', function(){
                var $wrap = $(this).parent();
                var $list = $wrap.find('.dsm-repeater-list');
                var $tpl = $wrap.find('.dsm-rep-tpl');
                var tpl = $tpl.data('template');
                var ridx = $list.children().length;
                tpl = tpl.replace(/__RIDX__/g, ridx);
                $list.append(tpl);
                setTimeout(checkDependencies, 50);
            });
            $(document).on('click', '.dsm-repeater-remove', function(e){ e.preventDefault(); $(this).closest('.dsm-repeater-item').remove(); });

            // Dependency Logic
            function checkDependencies() {
                $('.dsm-field[data-dependency]').each(function(){
                    var $field = $(this);
                    var dep = $field.data('dependency');
                    if(!dep || !dep.id) return;
                    var $scope = $field.closest('.dsm-repeater-item');
                    if(!$scope.length) $scope = $field.closest('.dsm-content');
                    var $controller = $scope.find('[name*="[' + dep.id + ']"]');
                    if(!$controller.length) return;
                    var val = $controller.val();
                    var show = Array.isArray(dep.value) ? dep.value.includes(val) : (val == dep.value);
                    show ? $field.slideDown(200) : $field.slideUp(200);
                });
            }
            checkDependencies();
            $(document).on('change input', '.dsm-field input, .dsm-field select', function(){ checkDependencies(); });
            $(document).on('click', '.dsm-rep-add, .dsm-add-btn', function(){ setTimeout(checkDependencies, 100); });

            $(document).on('click', '.dsm-btn-page-json-import', function(e){
                e.preventDefault();
                $('#developer-starter-page-json-file').trigger('click');
            });

            $(document).on('change', '#developer-starter-page-json-file', function(){
                var file = this.files && this.files[0] ? this.files[0] : null;
                var input = this;

                if (!file) {
                    return;
                }

                if ($('#dsm-list .dsm-item').length && !window.confirm('<?php echo esc_js( __( '导入页面 JSON 会替换当前模块列表，是否继续？', 'developer-starter' ) ); ?>')) {
                    input.value = '';
                    return;
                }

                var reader = new FileReader();
                reader.onload = function(event) {
                    var rawJson = event && event.target ? event.target.result : '';

                    if (!rawJson || typeof rawJson !== 'string') {
                        alert('<?php echo esc_js( __( 'JSON 文件读取失败，请重试。', 'developer-starter' ) ); ?>');
                        input.value = '';
                        return;
                    }

                    setPagePackageHint('<?php echo esc_js( __( '正在导入页面 JSON…', 'developer-starter' ) ); ?>');

                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'qiling_import_page_json_preview',
                            nonce: $('#modules_nonce').val(),
                            json: rawJson
                        },
                        success: function(res) {
                            applyImportedPagePackageResponse(res, '<?php echo esc_js( __( '导入失败：', 'developer-starter' ) ); ?>');
                        },
                        error: function() {
                            clearImportedPagePackageMeta();
                            setPagePackageHint('');
                            alert('<?php echo esc_js( __( '网络错误，请稍后重试。', 'developer-starter' ) ); ?>');
                        },
                        complete: function() {
                            input.value = '';
                        }
                    });
                };
                reader.onerror = function() {
                    alert('<?php echo esc_js( __( 'JSON 文件读取失败，请重试。', 'developer-starter' ) ); ?>');
                    input.value = '';
                };
                reader.readAsText(file, 'utf-8');
            });

            $(document).on('click', '.dsm-btn-page-json-export', function(e){
                e.preventDefault();

                var postId = resolveCurrentPostId();
                if (postId <= 0) {
                    alert('<?php echo esc_js( __( '请先保存页面，再导出 JSON。', 'developer-starter' ) ); ?>');
                    return;
                }

                setPagePackageHint('<?php echo esc_js( __( '正在导出页面 JSON…', 'developer-starter' ) ); ?>');

                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'qiling_export_page_json',
                        nonce: $('#modules_nonce').val(),
                        post_id: postId
                    },
                    success: function(res) {
                        if (!(res && res.success && res.data && res.data.json)) {
                            setPagePackageHint('');
                            alert('<?php echo esc_js( __( '导出失败：', 'developer-starter' ) ); ?>' + ((res && res.data) ? res.data : '<?php echo esc_js( __( '未知错误', 'developer-starter' ) ); ?>'));
                            return;
                        }

                        downloadTextFile(res.data.filename || 'page-package.json', res.data.json);
                        setPagePackageHint('<?php echo esc_js( __( '页面 JSON 已导出。该文件默认使用独立全屏模板，可直接用于新页面导入。', 'developer-starter' ) ); ?>');
                    },
                    error: function() {
                        setPagePackageHint('');
                        alert('<?php echo esc_js( __( '网络错误，请稍后重试。', 'developer-starter' ) ); ?>');
                    }
                });
            });

            $(document).on('click', '.dsm-btn-ai-decorate', function(e){
                e.preventDefault();
                showDsmModal('#dsm-ai-modal');
                if (pendingAiPackageJson) {
                    setAiStatus('<?php echo esc_js( __( 'AI 草稿待确认，请查看摘要后应用或放弃。', 'developer-starter' ) ); ?>', 'success');
                } else {
                    setAiStatus('', '');
                }
                renderAiWarnings([]);
                updateAiModelSuggestions();
                updateAiSelectedCount();
            });

            $(document).on('change', '#dsm-ai-connection', function(){
                $('#dsm-ai-model').val('');
                updateAiModelSuggestions();
            });

            $(document).on('input change', '#dsm-ai-module-list input[type="checkbox"]', function(){
                updateAiSelectedCount();
            });

            $(document).on('input', '#dsm-ai-module-search', function(){
                var keyword = String($(this).val() || '').toLowerCase();
                $('#dsm-ai-module-list .dsm-ai-module-item').each(function(){
                    var haystack = String($(this).data('module-name') || '');
                    var hit = !keyword || haystack.indexOf(keyword) !== -1;
                    $(this).toggleClass('is-hidden', !hit);
                });
                $('#dsm-ai-module-list .dsm-ai-group-title').each(function(){
                    var groupKey = String($(this).data('groupKey') || '');
                    var visibleCount = $('#dsm-ai-module-list .dsm-ai-module-item').filter(function(){
                        return String($(this).data('groupKey') || '') === groupKey && !$(this).hasClass('is-hidden');
                    }).length;
                    $(this).toggleClass('is-hidden', visibleCount === 0);
                });
            });

            $(document).on('click', '#dsm-ai-generate', function(e){
                e.preventDefault();

                if (!aiBuilderService) {
                    setAiStatus('<?php echo esc_js( __( 'AI 服务未加载，请刷新后重试。', 'developer-starter' ) ); ?>', 'error');
                    return;
                }

                var prompt = $.trim($('#dsm-ai-prompt').val() || '');
                var connectionId = String($('#dsm-ai-connection').val() || '');
                var model = $.trim($('#dsm-ai-model').val() || '');
                var moduleIds = getAiSelectedModules();
                var $button = $(this);
                var requestArgs = aiBuilderService.validateGenerationRequest({
                    config: aiBuilderConfig,
                    prompt: prompt,
                    connectionId: connectionId,
                    model: model,
                    moduleIds: moduleIds,
                    hasExistingModules: $('#dsm-list .dsm-item').length > 0,
                    confirmReplace: function(message) {
                        return window.confirm(message);
                    },
                    messages: {
                        unavailable: '<?php echo esc_js( __( 'AI 装修尚未配置完成，请先到主题设置中启用。', 'developer-starter' ) ); ?>',
                        missingPrompt: '<?php echo esc_js( __( '请先输入装修需求。', 'developer-starter' ) ); ?>',
                        missingModules: '<?php echo esc_js( __( '请先选择候选模块。', 'developer-starter' ) ); ?>',
                        tooManyModules: '<?php echo esc_js( sprintf( __( '候选模块最多选择 %d 个。', 'developer-starter' ), (int) $ai_max_modules ) ); ?>',
                        disallowedSitePrompt: '<?php echo esc_js( __( '在线 AI 整站生成已关闭。请改为生成当前单页，或使用前台装修器选中单个模块后做模块优化。', 'developer-starter' ) ); ?>',
                        replaceConfirm: '<?php echo esc_js( __( '这是高级单页草稿入口。生成后会先预览摘要；确认应用时才替换当前模块列表，保存页面前不会正式生效。是否继续？', 'developer-starter' ) ); ?>'
                    }
                });

                if (!requestArgs.ok) {
                    if (requestArgs.message) {
                        setAiStatus(requestArgs.message, 'error');
                    }
                    return;
                }

                renderAiWarnings([]);
                runAiGenerationFlow({
                    prompt: requestArgs.prompt,
                    connectionId: requestArgs.connectionId,
                    model: requestArgs.model,
                    moduleIds: requestArgs.moduleIds,
                    button: $button
                });
            });

            $(document).on('click', '#dsm-ai-apply-pending', function(e){
                e.preventDefault();
                if (!pendingAiPackageJson) {
                    setAiStatus('<?php echo esc_js( __( '没有可应用的 AI 草稿，请重新生成。', 'developer-starter' ) ); ?>', 'error');
                    return;
                }
                applyAiGeneratedPackage(pendingAiPackageJson, null, pendingAiPackageSuccessMessage);
            });

            $(document).on('click', '#dsm-ai-discard-pending', function(e){
                e.preventDefault();
                clearAiPendingPackage();
                setAiStatus('<?php echo esc_js( __( '已放弃本次 AI 草稿，页面未被改动。', 'developer-starter' ) ); ?>', 'warning');
            });

            $(document).on('click', '#dsm-ai-undo-apply', function(e){
                e.preventDefault();
                if (!lastAiApplySnapshot) {
                    setAiStatus('<?php echo esc_js( __( '没有可撤回的 AI 导入。', 'developer-starter' ) ); ?>', 'error');
                    return;
                }
                restoreAiApplySnapshot(lastAiApplySnapshot);
                lastAiApplySnapshot = null;
                setAiApplyUndoVisible(false);
                setAiStatus('<?php echo esc_js( __( '已撤回本次 AI 导入，页面恢复到导入前状态。', 'developer-starter' ) ); ?>', 'success');
            });


            /* ================= Template System ================= */
            
            // Helper: Serialize Module Data from DOM
            function serializeModule($item) {
                var data = {};
                $item.find('.dsm-content :input').each(function(){
                    var name = $(this).attr('name');
                    if(!name) return;
                    // Field names follow modules[0][data][key]...
                    // Extract the module data path.
                    // Strip modules[xxx][data].
                    var match = name.match(/modules\[\d+\]\[data\](.*)/);
                    if(!match || !match[1]) return;
                    
                    var path = match[1]; // [key] or [key][0][subkey]
                    var val = $(this).val();
                    
                    // Keep relative paths available for future structured serialization.
                });
                // Server-side template saving expects serialized module data.
                
                var raw = $item.find(':input').serialize(); 
                // Keep the raw serialized payload for server-side parsing.
                return raw;
            }

            // Save Template
            $(document).on('click', '.dsm-save-template', function(e){
                e.preventDefault();
                e.stopPropagation();
                var $item = $(this).closest('.dsm-item');
                var type = $item.data('type');
                var name = prompt("<?php echo esc_js( __( '请输入模版名称 (例如：首页联系我们):', 'developer-starter' ) ); ?>");
                if(!name) return;

                var rawData = $item.find(':input').serialize(); // Serialize all inputs in this module
                
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'qiling_save_template',
                        nonce: $('#modules_nonce').val(),
                        title: name,
                        type: type,
                        raw_data: rawData // Send raw form string
                    },
                    success: function(res){
                        if(res.success){
                            alert('<?php echo esc_js( __( '模版保存成功！', 'developer-starter' ) ); ?>');
                        } else {
                            alert('<?php echo esc_js( __( '保存失败: ', 'developer-starter' ) ); ?>' + res.data);
                        }
                    },
                    error: function(){ alert('<?php echo esc_js( __( '网络错误', 'developer-starter' ) ); ?>'); }
                });
            });

            // View Templates
            $(document).on('click', '.dsm-btn-templates', function(){
                showDsmModal('#dsm-template-modal');
                loadTemplates();
            });

            $('.dsm-modal-close, .dsm-modal-overlay').click(function(e){
                if (e.target !== this) {
                    return;
                }

                $(this).closest('.dsm-modal-overlay').fadeOut(200);
            });

            function loadTemplates() {
                var $list = $('#dsm-template-list');
                $list.html('<li style="padding:20px;text-align:center;"><?php echo esc_js( __( '加载中...', 'developer-starter' ) ); ?></li>');
                
                $.ajax({
                    url: ajaxurl,
                    data: { action: 'qiling_get_templates', nonce: $('#modules_nonce').val() },
                    success: function(res) {
                        if(!res.success) { $list.html('<li><?php echo esc_js( __( '加载失败', 'developer-starter' ) ); ?></li>'); return; }
                        var html = '';
                        if(res.data.length === 0) {
                            html = '<li style="padding:20px;text-align:center;color:#888;"><?php echo esc_js( __( '暂无保存的模版', 'developer-starter' ) ); ?></li>';
                        } else {
                            $.each(res.data, function(i, tpl){
                                html += '<li class="dsm-template-item">';
                                html += '<div class="dsm-tpl-info">';
                                html += '<span class="dsm-tpl-name">' + tpl.title + '</span>';
                                html += '<span class="dsm-tpl-meta"><?php echo esc_js( __( '类型: ', 'developer-starter' ) ); ?>' + tpl.type_name + ' | <?php echo esc_js( __( '时间: ', 'developer-starter' ) ); ?>' + tpl.date + '</span>';
                                html += '</div>';
                                html += '<div class="dsm-tpl-actions">';
                                html += '<button type="button" class="dsm-btn-small dsm-use-template" data-id="'+tpl.id+'"><?php echo esc_js( __( '使用', 'developer-starter' ) ); ?></button>';
                                html += '<button type="button" class="dsm-btn-small dsm-delete-template" data-id="'+tpl.id+'"><?php echo esc_js( __( '删除', 'developer-starter' ) ); ?></button>';
                                html += '</div>';
                                html += '</li>';
                            });
                        }
                        $list.html(html);
                    }
                });
            }

            // Delete Template
            $(document).on('click', '.dsm-delete-template', function(){
                if(!confirm('<?php echo esc_js( __( '确定删除此模版吗？', 'developer-starter' ) ); ?>')) return;
                var id = $(this).data('id');
                var $btn = $(this);
                $.post(ajaxurl, { action: 'delete_template', id: id, nonce: $('#modules_nonce').val() }, function(res){
                    if(res.success) $btn.closest('li').remove();
                    else alert(res.data);
                });
            });

            // Use Template (Load)
            $(document).on('click', '.dsm-use-template', function(){
                var id = $(this).data('id');
                var $btn = $(this);
                $btn.text('<?php echo esc_js( __( '加载中...', 'developer-starter' ) ); ?>');
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: { 
                        action: 'qiling_load_template_html', 
                        id: id, 
                        idx: idx,
                        nonce: $('#modules_nonce').val() 
                    },
                    success: function(res){
                        if(res.success) {
                            // Append the rendered HTML
                            var $item = $(res.data);
                            $item.addClass('open');
                            $('#dsm-list').append($item);
                            idx++;
                            renumberModuleInputs();
                            $('#dsm-template-modal').fadeOut(200);
                            $('html, body').animate({ scrollTop: $item.offset().top - 100 }, 300);
                            
                            // Ensure dependencies are checked for new item
                            setTimeout(checkDependencies, 100);
                        } else {
                            alert('<?php echo esc_js( __( '加载失败: ', 'developer-starter' ) ); ?>' + res.data);
                        }
                        $btn.text('<?php echo esc_js( __( '使用', 'developer-starter' ) ); ?>');
                    },
                    error: function(){ alert('<?php echo esc_js( __( '网络错误', 'developer-starter' ) ); ?>'); $btn.text('<?php echo esc_js( __( '使用', 'developer-starter' ) ); ?>'); }
                });
            });

        });
        </script>
