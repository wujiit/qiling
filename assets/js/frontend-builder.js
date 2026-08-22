/**
 * 启灵主题前端模块构建器脚本
 *
 * 负责前端可视化模块的状态管理、配置面板与保存交互。
 */
(function($) {
    'use strict';

    var builderData = window.qilingFrontendBuilderData || null;
    var aiBuilderService = window.QilingAiBuilderService || null;
    if (!builderData || !builderData.builderMode) {
        return;
    }

    document.documentElement.classList.add('qfb-builder-active');

    var state = {
        modules: [],
        pageSettings: {},
        designSystem: {},
        contentModels: {},
        dynamicData: {},
        availableModules: [],
        myLibraryTemplates: [],
        aiConfig: { enabled: false, connections: [] },
        panelMode: 'settings',
        previewMode: 'desktop',
        selectedScope: 'module',
        libraryGroupFilter: 'all',
        schemaCache: {},
        settingsRequestSeq: 0,
        selectedIndex: -1,
        dirty: false,
        saving: false,
        previewTimer: null,
        previewRequestSeq: 0,
        previewXhr: null,
        templateRequestCache: {},
        pageVisualPreviewStyleEl: null,
        loadedExternalScripts: {},
        loadedExternalStyles: {},
        externalAssetPromises: {},
        domWrappers: [],
        wrapperParent: null,
        startMarker: null,
        endMarker: null,
        snapshots: [],
        snapshotsLoaded: false,
        snapshotsLoading: false,
        aiSnapshots: [],
        aiPromptHistory: [],
        aiStyleRecommendations: [],
        aiBusy: false,
        aiPendingResult: null
    };

    var els = {};

    function deepClone(value) {
        try {
            return JSON.parse(JSON.stringify(value));
        } catch (e) {
            return value;
        }
    }

    function escapeHtml(str) {
        return String(str || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function normalizeFieldValue(value) {
        if (value === null || typeof value === 'undefined') {
            return '';
        }
        return String(value);
    }

    function getText(key, fallback) {
        if (builderData.texts && builderData.texts[key]) {
            return builderData.texts[key];
        }
        return fallback;
    }

    function getBuilderLimits() {
        return builderData.limits && typeof builderData.limits === 'object' ? builderData.limits : {};
    }

    function jsonLength(value) {
        try {
            return JSON.stringify(value).length;
        } catch (error) {
            return 0;
        }
    }

    function validateModulesForTransport(modules) {
        var limits = getBuilderLimits();
        var maxPayloadBytes = parseInt(limits.maxPayloadBytes, 10) || 0;
        var maxModules = parseInt(limits.maxModules, 10) || 0;
        var maxModuleDataBytes = parseInt(limits.maxModuleDataBytes, 10) || 0;

        if (maxModules > 0 && modules.length > maxModules) {
            return {
                valid: false,
                message: getText('builderTooManyModules', '当前页面模块超过上限，请减少模块后再保存或预览。')
            };
        }

        if (maxPayloadBytes > 0 && jsonLength(modules) > maxPayloadBytes) {
            return {
                valid: false,
                message: getText('builderPayloadTooLarge', '装修数据过大，请减少模块或复杂内容后再试。')
            };
        }

        if (maxModuleDataBytes > 0) {
            for (var i = 0; i < modules.length; i++) {
                if (jsonLength((modules[i] && modules[i].data) || {}) > maxModuleDataBytes) {
                    return {
                        valid: false,
                        message: getText('builderModuleDataTooLarge', '某个模块的数据过大，请减少该模块中的列表项、图片或长文本后再试。')
                    };
                }
            }
        }

        return { valid: true, message: '' };
    }

    function normalizeBannerFlag(value) {
        if (value === true || value === 1) {
            return '1';
        }
        var normalized = normalizeFieldValue(value).toLowerCase().trim();
        return normalized === '1' || normalized === 'yes' || normalized === 'true' || normalized === 'on' ? '1' : '0';
    }

    function getFirstNonEmptyValue(source, keys) {
        if (!source || typeof source !== 'object') {
            return '';
        }
        for (var i = 0; i < keys.length; i++) {
            var key = keys[i];
            if (Object.prototype.hasOwnProperty.call(source, key) && normalizeFieldValue(source[key]) !== '') {
                return normalizeFieldValue(source[key]);
            }
        }
        return '';
    }

    function normalizeBannerStatsItems(items) {
        if (!Array.isArray(items)) {
            return [];
        }

        var normalized = [];
        items.forEach(function(item) {
            if (!item || typeof item !== 'object') {
                return;
            }

            var stat = {
                icon: getFirstNonEmptyValue(item, ['icon', 'stat_icon']),
                number: getFirstNonEmptyValue(item, ['number', 'stat_number', 'stat_value', 'value']),
                label: getFirstNonEmptyValue(item, ['label', 'stat_label', 'text']),
                color: getFirstNonEmptyValue(item, ['color', 'stat_color']),
                label_color: getFirstNonEmptyValue(item, ['label_color', 'description_color', 'desc_color', 'text_color'])
            };

            if (!stat.icon.trim() && !stat.number.trim() && !stat.label.trim()) {
                return;
            }

            normalized.push(stat);
        });

        return normalized;
    }

    function normalizeBannerModuleData(data) {
        var normalized = data && typeof data === 'object' && !Array.isArray(data) ? data : {};

        if (Object.prototype.hasOwnProperty.call(normalized, 'show_stats_bar')) {
            normalized.show_stats_bar = normalizeBannerFlag(normalized.show_stats_bar);
        }

        var statsSources = ['stats_data', 'stats_items', 'items'];
        for (var i = 0; i < statsSources.length; i++) {
            var key = statsSources[i];
            if (!Array.isArray(normalized[key]) || !normalized[key].length) {
                continue;
            }

            var stats = normalizeBannerStatsItems(normalized[key]);
            if (stats.length) {
                normalized.stats_data = stats;
                break;
            }
        }

        return normalized;
    }

    function normalizeModuleForTransport(module) {
        var normalized = {
            type: module && module.type ? String(module.type) : '',
            data: module && module.data && typeof module.data === 'object' && !Array.isArray(module.data)
                ? deepClone(module.data)
                : {}
        };

        if (normalized.type === 'banner') {
            normalized.data = normalizeBannerModuleData(normalized.data);
        }

        return normalized;
    }

    function prepareModulesForTransport(modules) {
        if (!Array.isArray(modules)) {
            return [];
        }

        return modules.filter(function(module) {
            return module && typeof module === 'object' && module.type;
        }).map(normalizeModuleForTransport);
    }

    function getAiSnapshotStorageKey() {
        return [
            'qiling_frontend_builder_ai_snapshots',
            String(builderData.postId || 0),
            String(builderData.dataSource || 'theme')
        ].join(':');
    }

    function getAiPromptHistoryStorageKey() {
        return [
            'qiling_frontend_builder_ai_prompt_history',
            String(builderData.dataSource || 'theme')
        ].join(':');
    }

    function normalizeAiSnapshot(snapshot) {
        if (!snapshot || typeof snapshot !== 'object') {
            return null;
        }

        return {
            id: String(snapshot.id || ('snapshot-' + Date.now())),
            reason: String(snapshot.reason || ''),
            createdAt: parseInt(snapshot.createdAt, 10) || Date.now(),
            modules: Array.isArray(snapshot.modules) ? deepClone(snapshot.modules) : [],
            pageSettings: snapshot.pageSettings && typeof snapshot.pageSettings === 'object'
                ? deepClone(snapshot.pageSettings)
                : {},
            selectedScope: snapshot.selectedScope === 'page' ? 'page' : 'module',
            selectedIndex: parseInt(snapshot.selectedIndex, 10) || -1,
            previewMode: ['desktop', 'tablet', 'mobile'].indexOf(String(snapshot.previewMode || '')) !== -1
                ? String(snapshot.previewMode)
                : 'desktop'
        };
    }

    function persistAiSnapshots() {
        try {
            if (!window.sessionStorage) {
                return;
            }
            window.sessionStorage.setItem(getAiSnapshotStorageKey(), JSON.stringify(state.aiSnapshots || []));
        } catch (e) {}
    }

    function loadAiSnapshots() {
        try {
            if (!window.sessionStorage) {
                return;
            }
            var raw = window.sessionStorage.getItem(getAiSnapshotStorageKey());
            if (!raw) {
                return;
            }
            var parsed = JSON.parse(raw);
            if (!Array.isArray(parsed)) {
                return;
            }
            state.aiSnapshots = parsed.map(normalizeAiSnapshot).filter(Boolean).slice(-10);
        } catch (e) {
            state.aiSnapshots = [];
        }
    }

    function normalizeAiPromptHistoryItem(item) {
        if (!item || typeof item !== 'object') {
            return null;
        }

        var prompt = String(item.prompt || '').trim();
        if (!prompt) {
            return null;
        }

        return {
            id: String(item.id || ('prompt-' + Date.now())),
            prompt: prompt.slice(0, 4000),
            moduleIds: Array.isArray(item.moduleIds) ? item.moduleIds.map(function(moduleId) {
                return String(moduleId || '');
            }).filter(Boolean).slice(0, getAiMaxModules()) : [],
            connectionId: String(item.connectionId || ''),
            model: String(item.model || ''),
            createdAt: parseInt(item.createdAt, 10) || Date.now()
        };
    }

    function persistAiPromptHistory() {
        try {
            if (!window.localStorage) {
                return;
            }
            window.localStorage.setItem(getAiPromptHistoryStorageKey(), JSON.stringify(state.aiPromptHistory || []));
        } catch (e) {}
    }

    function loadAiPromptHistory() {
        try {
            if (!window.localStorage) {
                return;
            }
            var raw = window.localStorage.getItem(getAiPromptHistoryStorageKey());
            if (!raw) {
                return;
            }
            var parsed = JSON.parse(raw);
            if (!Array.isArray(parsed)) {
                return;
            }
            state.aiPromptHistory = parsed.map(normalizeAiPromptHistoryItem).filter(Boolean).slice(0, 8);
        } catch (e) {
            state.aiPromptHistory = [];
        }
    }

    function updateAiUndoButton() {
        if (!els.aiPane) {
            return;
        }

        var undoButton = els.aiPane.querySelector('#qfb-ai-undo');
        if (!undoButton) {
            return;
        }

        var hasSnapshot = Array.isArray(state.aiSnapshots) && state.aiSnapshots.length > 0;
        undoButton.disabled = state.aiBusy || !hasSnapshot;
        undoButton.style.display = hasSnapshot ? '' : 'none';
    }

    function updateAiBusyControls() {
        if (!els.aiPane) {
            return;
        }

        [
            '#qfb-ai-generate',
            '#qfb-ai-optimize-module',
            '#qfb-ai-localize-module',
            '#qfb-ai-localize-page',
            '.qfb-ai-local-action',
            '[data-ai-style-recommendation]'
        ].forEach(function(selector) {
            Array.prototype.forEach.call(els.aiPane.querySelectorAll(selector), function(button) {
                button.disabled = !!state.aiBusy;
            });
        });
        updateAiUndoButton();
    }

    function setAiBusy(isBusy) {
        state.aiBusy = !!isBusy;
        updateAiBusyControls();
    }

    function isAiBusy() {
        return !!state.aiBusy;
    }

    function pushAiSnapshot(reason) {
        var snapshot = normalizeAiSnapshot({
            id: 'ai-' + Date.now() + '-' + Math.floor(Math.random() * 1000),
            reason: reason || 'ai_change',
            createdAt: Date.now(),
            modules: state.modules,
            pageSettings: state.pageSettings,
            selectedScope: state.selectedScope,
            selectedIndex: state.selectedIndex,
            previewMode: state.previewMode
        });

        if (!snapshot) {
            return;
        }

        state.aiSnapshots.push(snapshot);
        if (state.aiSnapshots.length > 10) {
            state.aiSnapshots = state.aiSnapshots.slice(-10);
        }
        persistAiSnapshots();
        updateAiUndoButton();
    }

    function restoreLatestAiSnapshot(options) {
        if (!Array.isArray(state.aiSnapshots) || !state.aiSnapshots.length) {
            updateAiUndoButton();
            return false;
        }

        var restoreOptions = options && typeof options === 'object' ? options : {};
        var shouldClearPending = restoreOptions.clearPending !== false;
        var snapshot = state.aiSnapshots.pop();
        persistAiSnapshots();

        state.modules = Array.isArray(snapshot.modules) ? deepClone(snapshot.modules) : [];
        state.pageSettings = snapshot.pageSettings && typeof snapshot.pageSettings === 'object'
            ? deepClone(snapshot.pageSettings)
            : {};
        ensurePageSettingsState();
        state.selectedScope = snapshot.selectedScope === 'page' ? 'page' : 'module';
        state.selectedIndex = parseInt(snapshot.selectedIndex, 10);
        if (Number.isNaN(state.selectedIndex)) {
            state.selectedIndex = state.modules.length ? 0 : -1;
        }
        if (state.selectedIndex >= state.modules.length) {
            state.selectedIndex = state.modules.length ? state.modules.length - 1 : -1;
        }

        applyPageDesignPreview();
        applyPageVisualStylePreview();
        renderPageList();
        if (state.panelMode === 'settings') {
            renderSettings();
        } else {
            highlightSelectedWrapper();
        }
        setPreviewMode(snapshot.previewMode || state.previewMode || 'desktop');
        queuePreviewRender(true);
        markDirty();
        updateAiUndoButton();
        if (shouldClearPending) {
            clearAiPendingResult();
            renderAiWarnings([]);
        }
        setAiStatus(getText('aiUndoSuccess', '已撤回到 AI 修改前，请确认预览后保存。'), 'success');
        setStatus(getText('aiUndoSuccess', '已撤回到 AI 修改前，请确认预览后保存。'), 'warning');
        return true;
    }

    function getRepeaterItemTitle(index) {
        return getText('repeaterItemPrefix', '项目 #') + (index + 1);
    }

    function getDesignSystemPayload() {
        return state.designSystem && typeof state.designSystem === 'object' ? state.designSystem : {};
    }

    function getDesignTokens() {
        var payload = getDesignSystemPayload();
        return payload.tokens && typeof payload.tokens === 'object' ? payload.tokens : {};
    }

    function getDesignCssVariables() {
        var payload = getDesignSystemPayload();
        return payload.cssVariables && typeof payload.cssVariables === 'object' ? payload.cssVariables : {};
    }

    function getComponentStyles() {
        var payload = getDesignSystemPayload();
        return payload.componentStyles && typeof payload.componentStyles === 'object' ? payload.componentStyles : {};
    }

    function getContentModelPayload() {
        if (state.contentModels && typeof state.contentModels === 'object') {
            return state.contentModels;
        }
        if (state.aiConfig && state.aiConfig.contentModels && typeof state.aiConfig.contentModels === 'object') {
            return state.aiConfig.contentModels;
        }
        return {};
    }

    function getDynamicDataPayload() {
        return state.dynamicData && typeof state.dynamicData === 'object' ? state.dynamicData : {};
    }

    function getDynamicDataKey() {
        var payload = getDynamicDataPayload();
        return payload.dynamicKey ? String(payload.dynamicKey) : '_ds_dynamic';
    }

    function getDynamicDataSources() {
        var payload = getDynamicDataPayload();
        return Array.isArray(payload.sources) ? payload.sources : [];
    }

    function isDynamicFieldSupported(fieldType) {
        var type = String(fieldType || 'text');
        return ['text', 'textarea', 'editor', 'image', 'upload', 'file', 'gallery', 'url', 'link'].indexOf(type) !== -1;
    }

    function getSelectedModule() {
        if (state.selectedIndex < 0 || state.selectedIndex >= state.modules.length) {
            return null;
        }
        return state.modules[state.selectedIndex] || null;
    }

    function getDynamicBindingSource(fieldId) {
        var selectedModule = getSelectedModule();
        if (!selectedModule || !selectedModule.data || typeof selectedModule.data !== 'object') {
            return '';
        }

        var bindings = selectedModule.data[getDynamicDataKey()];
        if (!bindings || typeof bindings !== 'object') {
            return '';
        }

        var binding = bindings[fieldId];
        if (typeof binding === 'string') {
            return binding;
        }
        if (binding && typeof binding === 'object' && binding.source) {
            return String(binding.source);
        }
        return '';
    }

    function setDynamicBindingForField(fieldId, source) {
        var selectedModule = getSelectedModule();
        if (!selectedModule || !fieldId) {
            return;
        }
        if (!selectedModule.data || typeof selectedModule.data !== 'object' || Array.isArray(selectedModule.data)) {
            selectedModule.data = {};
        }

        var dynamicKey = getDynamicDataKey();
        if (!selectedModule.data[dynamicKey] || typeof selectedModule.data[dynamicKey] !== 'object' || Array.isArray(selectedModule.data[dynamicKey])) {
            selectedModule.data[dynamicKey] = {};
        }

        var normalizedSource = normalizeFieldValue(source).trim();
        if (normalizedSource) {
            selectedModule.data[dynamicKey][fieldId] = { source: normalizedSource };
        } else {
            delete selectedModule.data[dynamicKey][fieldId];
            if (!Object.keys(selectedModule.data[dynamicKey]).length) {
                delete selectedModule.data[dynamicKey];
            }
        }

        markDirty();
        queueModulePreviewRender(state.selectedIndex, false);
        setStatus(
            normalizedSource ? getText('dynamicDataApplied', '已绑定动态数据，预览已更新。') : getText('dynamicDataCleared', '已改回静态内容。'),
            'warning'
        );
    }

    function renderDynamicDataPicker(fieldId, fieldType) {
        var sources = getDynamicDataSources();
        if (!fieldId || !isDynamicFieldSupported(fieldType) || !sources.length) {
            return '';
        }

        var currentSource = getDynamicBindingSource(fieldId);
        var seenSources = {};
        var currentGroup = '';
        var html = '<div class="qfb-dynamic-picker' + (currentSource ? ' is-bound' : '') + '" data-dynamic-field-id="' + escapeHtml(fieldId) + '">';
        html += '<span class="qfb-dynamic-picker-title">' + escapeHtml(getText('dynamicDataLabel', '动态数据')) + '</span>';
        html += '<select class="qfb-dynamic-select" data-dynamic-field-id="' + escapeHtml(fieldId) + '">';
        html += '<option value="">' + escapeHtml(getText('dynamicDataStatic', '静态内容')) + '</option>';

        sources.forEach(function(source) {
            if (!source || !source.id) {
                return;
            }

            var groupLabel = source.groupLabel || source.group || '';
            if (groupLabel !== currentGroup) {
                if (currentGroup) {
                    html += '</optgroup>';
                }
                currentGroup = groupLabel;
                html += '<optgroup label="' + escapeHtml(groupLabel || getText('dynamicDataLabel', '动态数据')) + '">';
            }

            seenSources[String(source.id)] = true;
            html += '<option value="' + escapeHtml(source.id) + '"' + (currentSource === String(source.id) ? ' selected' : '') + '>' + escapeHtml(source.label || source.id) + '</option>';
        });

        if (currentGroup) {
            html += '</optgroup>';
        }
        if (currentSource && !seenSources[currentSource]) {
            html += '<option value="' + escapeHtml(currentSource) + '" selected>' + escapeHtml(currentSource) + '</option>';
        }

        html += '</select>';
        html += '</div>';
        return html;
    }

    function getPageTemplateChoices() {
        return builderData.pageTemplates && typeof builderData.pageTemplates === 'object'
            ? builderData.pageTemplates
            : { default: getText('pageTemplateLabel', '默认模板') };
    }

    function getPageFooterPresetChoices(currentPreset) {
        var choices = builderData.footerPresets && typeof builderData.footerPresets === 'object'
            ? deepClone(builderData.footerPresets)
            : {};

        if (!Object.prototype.hasOwnProperty.call(choices, '')) {
            choices[''] = getText('pageFooterPresetNone', '不指定预设');
        }
        currentPreset = String(currentPreset || '');
        if (currentPreset && !Object.prototype.hasOwnProperty.call(choices, currentPreset)) {
            choices[currentPreset] = currentPreset;
        }

        return choices;
    }

    function getPageVisualPresetChoices(currentPreset) {
        var choices = builderData.pageVisualPresets && typeof builderData.pageVisualPresets === 'object'
            ? deepClone(builderData.pageVisualPresets)
            : {};

        if (!Object.prototype.hasOwnProperty.call(choices, '')) {
            choices[''] = getText('pageVisualPresetAuto', '按当前页面模板自动匹配');
        }
        currentPreset = String(currentPreset || '');
        if (currentPreset && !Object.prototype.hasOwnProperty.call(choices, currentPreset)) {
            choices[currentPreset] = currentPreset;
        }

        return choices;
    }

    function getPageVisualFieldGroups() {
        return builderData.pageVisualFields && typeof builderData.pageVisualFields === 'object'
            ? builderData.pageVisualFields
            : {};
    }

    function getPageVisualPresetVars(presetKey) {
        var key = String(presetKey || '');
        var presets = builderData.pageVisualPresetVars && typeof builderData.pageVisualPresetVars === 'object'
            ? builderData.pageVisualPresetVars
            : {};

        return key && presets[key] && typeof presets[key] === 'object' ? presets[key] : {};
    }

    function getPageVisualResolvedVars() {
        var resolved = builderData.pageVisualResolved && typeof builderData.pageVisualResolved === 'object'
            ? builderData.pageVisualResolved
            : {};
        return resolved.vars && typeof resolved.vars === 'object' ? resolved.vars : {};
    }

    function getPageVisualFieldValueFromVars(field, vars) {
        vars = vars && typeof vars === 'object' ? vars : {};
        var fieldVars = field && Array.isArray(field.vars) ? field.vars : [];
        for (var i = 0; i < fieldVars.length; i++) {
            var varName = String(fieldVars[i] || '');
            if (varName && Object.prototype.hasOwnProperty.call(vars, varName)) {
                return normalizeFieldValue(vars[varName]);
            }
        }
        return '';
    }

    function getPageVisualFieldPresetValue(field, presetKey) {
        return getPageVisualFieldValueFromVars(field, getPageVisualPresetVars(presetKey));
    }

    function getPageVisualFieldEffectivePresetValue(field, settings) {
        settings = settings && typeof settings === 'object' ? settings : {};
        if (settings.mode === 'global') {
            return '';
        }
        if (settings.mode === 'custom' && settings.preset) {
            return getPageVisualFieldPresetValue(field, settings.preset || '');
        }
        return getPageVisualFieldValueFromVars(field, getPageVisualResolvedVars());
    }

    function renderPageVisualPresetValue(value, fieldType) {
        value = normalizeFieldValue(value);
        if (!value) {
            return '';
        }

        var html = '<div class="qfb-page-visual-preset-value">';
        if (fieldType !== 'opacity') {
            html += '<span class="qfb-page-visual-preset-swatch" style="background:' + escapeHtml(value) + ';"></span>';
        }
        html += '<span>' + escapeHtml(getText('pageVisualPresetValueLabel', '当前预设值')) + '</span>';
        html += '<code>' + escapeHtml(value) + '</code>';
        html += '</div>';
        return html;
    }

    function getDesignPresetPayload(presetKey) {
        var payload = getDesignSystemPayload();
        var key = normalizeFieldValue(presetKey || '');
        var collections = [
            payload.systemPresets,
            payload.customPresets
        ];

        for (var i = 0; i < collections.length; i++) {
            var collection = collections[i];
            if (!collection || typeof collection !== 'object') {
                continue;
            }
            if (!Array.isArray(collection) && collection[key] && typeof collection[key] === 'object') {
                return collection[key];
            }
            if (Array.isArray(collection)) {
                for (var j = 0; j < collection.length; j++) {
                    var item = collection[j];
                    if (!item || typeof item !== 'object') {
                        continue;
                    }
                    if (normalizeFieldValue(item.id || item.key || '') === key) {
                        return item;
                    }
                }
            }
        }

        return null;
    }

    function getPageDesignPresetChoices() {
        var payload = getDesignSystemPayload();
        var choices = {
            '': getText('pageDesignPresetInherited', '跟随全局预设')
        };
        var presets = payload.presets && typeof payload.presets === 'object' ? payload.presets : {};

        Object.keys(presets).forEach(function(key) {
            choices[key] = presets[key];
        });

        return choices;
    }

    function normalizeDesignPresetKey(presetKey) {
        var key = normalizeFieldValue(presetKey || '');
        if (!key || key === 'inherit') {
            return '';
        }

        return getDesignPresetPayload(key) ? key : '';
    }

    function buildPageDesignStateFromPreset(presetKey) {
        var preset = getDesignPresetPayload(presetKey);
        var result = getDefaultPageDesignState();
        if (!preset || typeof preset !== 'object') {
            return result;
        }

        var tokens = preset.tokens && typeof preset.tokens === 'object' ? preset.tokens : {};
        [
            ['primary', 'primary'],
            ['secondary', 'secondary'],
            ['accent', 'accent'],
            ['success', 'success'],
            ['info', 'info'],
            ['warning', 'warning'],
            ['error', 'error'],
            ['overlay', 'overlay'],
            ['background', 'background'],
            ['surface', 'surface'],
            ['surface_alt', 'surfaceAlt'],
            ['text', 'text'],
            ['text_muted', 'textMuted'],
            ['heading', 'heading'],
            ['border', 'border'],
            ['dark_bg', 'darkBg'],
            ['dark_surface', 'darkSurface'],
            ['dark_text', 'darkText'],
            ['dark_text_muted', 'darkTextMuted'],
            ['dark_border', 'darkBorder']
        ].forEach(function(pair) {
            if (Object.prototype.hasOwnProperty.call(tokens, pair[0])) {
                result.palette[pair[1]] = normalizeFieldValue(tokens[pair[0]] || '');
            }
        });

        var typography = preset.typographySystem && typeof preset.typographySystem === 'object'
            ? preset.typographySystem
            : (preset.typography_system && typeof preset.typography_system === 'object' ? preset.typography_system : {});
        if (Object.keys(typography).length) {
            result.typography = normalizePageTypographyState(typography, result.typography);
        }

        var layout = preset.layoutSystem && typeof preset.layoutSystem === 'object'
            ? preset.layoutSystem
            : (preset.layout_system && typeof preset.layout_system === 'object' ? preset.layout_system : {});
        if (Object.keys(layout).length) {
            result.layout.containerWidth = normalizeResponsivePageValues(layout.container_width || layout.containerWidth);
            result.layout.sectionSpacing = normalizeResponsivePageValues(layout.section_spacing || layout.sectionSpacing);
            result.layout.gridGap = normalizeResponsivePageValues(layout.grid_gap || layout.gridGap);
            result.layout.layoutMode = normalizeFieldValue(layout.layout_mode || layout.layoutMode || '');
        }

        var components = preset.componentStyles && typeof preset.componentStyles === 'object'
            ? preset.componentStyles
            : (preset.component_styles && typeof preset.component_styles === 'object' ? preset.component_styles : {});
        Object.keys(components).forEach(function(styleKey) {
            if (Object.prototype.hasOwnProperty.call(components, styleKey)) {
                result.componentStyles[styleKey] = normalizeFieldValue(components[styleKey] || '');
            }
        });

        return normalizePageDesignState(result);
    }

    function getDefaultPageDesignState() {
        var payload = getDesignSystemPayload();
        if (payload.pageDesignDefaults && typeof payload.pageDesignDefaults === 'object') {
            return deepClone(payload.pageDesignDefaults);
        }
        return {
            palette: {
                primary: '',
                secondary: '',
                accent: '',
                success: '',
                info: '',
                warning: '',
                error: '',
                overlay: '',
                background: '',
                surface: '',
                surfaceAlt: '',
                text: '',
                textMuted: '',
                heading: '',
                border: '',
                darkBg: '',
                darkSurface: '',
                darkText: '',
                darkTextMuted: '',
                darkBorder: ''
            },
            layout: {
                containerWidth: { desktop: '', tablet: '', mobile: '' },
                sectionSpacing: { desktop: '', tablet: '', mobile: '' },
                gridGap: { desktop: '', tablet: '', mobile: '' },
                layoutMode: ''
            },
            structure: {
                cardRadius: '',
                buttonRadius: '',
                inputRadius: '',
                animationSpeed: ''
            },
            typography: createEmptyPageTypographyState(getPageTypographyStyles(), getPageTypographyProperties()),
            componentStyles: {}
        };
    }

    function normalizeResponsivePageValues(source) {
        source = source && typeof source === 'object' ? source : {};
        return {
            desktop: normalizeFieldValue(source.desktop || ''),
            tablet: normalizeFieldValue(source.tablet || ''),
            mobile: normalizeFieldValue(source.mobile || '')
        };
    }

    function getPageStructureStorageKey(builderKey) {
        switch (String(builderKey || '')) {
            case 'cardRadius':
                return 'card_radius';
            case 'buttonRadius':
                return 'button_radius';
            case 'inputRadius':
                return 'input_radius';
            case 'animationSpeed':
                return 'animation_speed';
            default:
                return String(builderKey || '');
        }
    }

    function normalizePageStructureState(structure, defaults) {
        var result = defaults && typeof defaults === 'object'
            ? deepClone(defaults)
            : {
                cardRadius: '',
                buttonRadius: '',
                inputRadius: '',
                animationSpeed: ''
            };

        structure = structure && typeof structure === 'object' ? structure : {};

        Object.keys(result).forEach(function(builderKey) {
            var storageKey = getPageStructureStorageKey(builderKey);
            var value = '';

            if (Object.prototype.hasOwnProperty.call(structure, builderKey)) {
                value = structure[builderKey];
            } else if (storageKey !== builderKey && Object.prototype.hasOwnProperty.call(structure, storageKey)) {
                value = structure[storageKey];
            }

            result[builderKey] = normalizeFieldValue(value || '');
        });

        return result;
    }

    function normalizePageTypographyState(typography, defaults) {
        var result = defaults && typeof defaults === 'object'
            ? deepClone(defaults)
            : createEmptyPageTypographyState(getPageTypographyStyles(), getPageTypographyProperties());
        typography = typography && typeof typography === 'object' ? typography : {};

        Object.keys(result).forEach(function(styleKey) {
            var styleValues = typography[styleKey] && typeof typography[styleKey] === 'object'
                ? typography[styleKey]
                : {};

            ['desktop', 'tablet', 'mobile'].forEach(function(deviceKey) {
                var deviceValues = styleValues[deviceKey] && typeof styleValues[deviceKey] === 'object'
                    ? styleValues[deviceKey]
                    : {};

                Object.keys(result[styleKey][deviceKey] || {}).forEach(function(builderKey) {
                    var storageKey = getPageTypographyStorageKey(builderKey);
                    var value = '';

                    if (Object.prototype.hasOwnProperty.call(deviceValues, builderKey)) {
                        value = deviceValues[builderKey];
                    } else if (storageKey !== builderKey && Object.prototype.hasOwnProperty.call(deviceValues, storageKey)) {
                        value = deviceValues[storageKey];
                    }

                    result[styleKey][deviceKey][builderKey] = normalizeFieldValue(value || '');
                });
            });
        });

        return result;
    }

    function normalizePageDesignState(design) {
        var defaults = getDefaultPageDesignState();
        var result = deepClone(defaults);
        design = design && typeof design === 'object' ? design : {};
        var palette = design.palette && typeof design.palette === 'object' ? design.palette : {};
        var layout = design.layout && typeof design.layout === 'object' ? design.layout : {};
        var structure = design.structure && typeof design.structure === 'object' ? design.structure : {};
        var typography = design.typography && typeof design.typography === 'object'
            ? design.typography
            : (design.typography_system && typeof design.typography_system === 'object'
                ? design.typography_system
                : (design.typographySystem && typeof design.typographySystem === 'object' ? design.typographySystem : {}));
        var componentStyles = design.componentStyles && typeof design.componentStyles === 'object'
            ? design.componentStyles
            : (design.component_styles && typeof design.component_styles === 'object' ? design.component_styles : {});

        result.palette.primary = normalizeFieldValue(palette.primary || '');
        result.palette.secondary = normalizeFieldValue(palette.secondary || '');
        result.palette.accent = normalizeFieldValue(palette.accent || '');
        result.palette.success = normalizeFieldValue(palette.success || '');
        result.palette.info = normalizeFieldValue(palette.info || '');
        result.palette.warning = normalizeFieldValue(palette.warning || '');
        result.palette.error = normalizeFieldValue(palette.error || '');
        result.palette.overlay = normalizeFieldValue(palette.overlay || '');
        result.palette.background = normalizeFieldValue(palette.background || '');
        result.palette.surface = normalizeFieldValue(palette.surface || '');
        result.palette.surfaceAlt = normalizeFieldValue(palette.surfaceAlt || palette.surface_alt || '');
        result.palette.text = normalizeFieldValue(palette.text || '');
        result.palette.textMuted = normalizeFieldValue(palette.textMuted || palette.text_muted || '');
        result.palette.heading = normalizeFieldValue(palette.heading || '');
        result.palette.border = normalizeFieldValue(palette.border || '');
        result.palette.darkBg = normalizeFieldValue(palette.darkBg || palette.dark_bg || '');
        result.palette.darkSurface = normalizeFieldValue(palette.darkSurface || palette.dark_surface || '');
        result.palette.darkText = normalizeFieldValue(palette.darkText || palette.dark_text || '');
        result.palette.darkTextMuted = normalizeFieldValue(palette.darkTextMuted || palette.dark_text_muted || '');
        result.palette.darkBorder = normalizeFieldValue(palette.darkBorder || palette.dark_border || '');

        result.layout.containerWidth = normalizeResponsivePageValues(layout.containerWidth || layout.container_width);
        result.layout.sectionSpacing = normalizeResponsivePageValues(layout.sectionSpacing || layout.section_spacing);
        result.layout.gridGap = normalizeResponsivePageValues(layout.gridGap || layout.grid_gap);
        result.layout.layoutMode = normalizeFieldValue(layout.layoutMode || layout.layout_mode || '');
        result.structure = normalizePageStructureState(structure, result.structure);
        result.typography = normalizePageTypographyState(typography, result.typography);
        result.componentStyles = result.componentStyles && typeof result.componentStyles === 'object' ? result.componentStyles : {};
        Object.keys(result.componentStyles).forEach(function(styleKey) {
            result.componentStyles[styleKey] = normalizeFieldValue(componentStyles[styleKey] || '');
        });

        return result;
    }

    function serializePageDesignForPackage(design) {
        var normalized = normalizePageDesignState(design);
        var palette = {};
        var layout = {};
        var structure = {};
        var typography = {};
        var componentStyles = {};

        [
            ['primary', 'primary'],
            ['secondary', 'secondary'],
            ['accent', 'accent'],
            ['success', 'success'],
            ['info', 'info'],
            ['warning', 'warning'],
            ['error', 'error'],
            ['overlay', 'overlay'],
            ['background', 'background'],
            ['surface', 'surface'],
            ['surfaceAlt', 'surface_alt'],
            ['text', 'text'],
            ['textMuted', 'text_muted'],
            ['heading', 'heading'],
            ['border', 'border'],
            ['darkBg', 'dark_bg'],
            ['darkSurface', 'dark_surface'],
            ['darkText', 'dark_text'],
            ['darkTextMuted', 'dark_text_muted'],
            ['darkBorder', 'dark_border']
        ].forEach(function(pair) {
            var value = normalizeFieldValue(normalized.palette[pair[0]] || '');
            if (value) {
                palette[pair[1]] = value;
            }
        });

        [
            ['containerWidth', 'container_width'],
            ['sectionSpacing', 'section_spacing'],
            ['gridGap', 'grid_gap']
        ].forEach(function(pair) {
            var responsiveValues = normalizeResponsivePageValues(normalized.layout[pair[0]]);
            var compactValues = {};
            Object.keys(responsiveValues).forEach(function(deviceKey) {
                if (responsiveValues[deviceKey]) {
                    compactValues[deviceKey] = responsiveValues[deviceKey];
                }
            });
            if (Object.keys(compactValues).length) {
                layout[pair[1]] = compactValues;
            }
        });

        if (normalizeFieldValue(normalized.layout.layoutMode || '')) {
            layout.layout_mode = normalizeFieldValue(normalized.layout.layoutMode || '');
        }

        [
            ['cardRadius', 'card_radius'],
            ['buttonRadius', 'button_radius'],
            ['inputRadius', 'input_radius'],
            ['animationSpeed', 'animation_speed']
        ].forEach(function(pair) {
            var value = normalizeFieldValue(normalized.structure[pair[0]] || '');
            if (value) {
                structure[pair[1]] = value;
            }
        });

        Object.keys(normalized.typography || {}).forEach(function(styleKey) {
            var styleValues = normalized.typography[styleKey] && typeof normalized.typography[styleKey] === 'object'
                ? normalized.typography[styleKey]
                : {};

            ['desktop', 'tablet', 'mobile'].forEach(function(deviceKey) {
                var deviceValues = styleValues[deviceKey] && typeof styleValues[deviceKey] === 'object'
                    ? styleValues[deviceKey]
                    : {};
                var compactDeviceValues = {};

                Object.keys(deviceValues).forEach(function(builderKey) {
                    var value = normalizeFieldValue(deviceValues[builderKey] || '');
                    if (!value) {
                        return;
                    }
                    compactDeviceValues[getPageTypographyStorageKey(builderKey)] = value;
                });

                if (!Object.keys(compactDeviceValues).length) {
                    return;
                }

                if (!typography[styleKey]) {
                    typography[styleKey] = {};
                }
                typography[styleKey][deviceKey] = compactDeviceValues;
            });
        });

        Object.keys(normalized.componentStyles || {}).forEach(function(styleKey) {
            var value = normalizeFieldValue(normalized.componentStyles[styleKey] || '');
            if (value) {
                componentStyles[styleKey] = value;
            }
        });

        var serialized = {};
        if (Object.keys(palette).length) {
            serialized.palette = palette;
        }
        if (Object.keys(layout).length) {
            serialized.layout = layout;
        }
        if (Object.keys(structure).length) {
            serialized.structure = structure;
        }
        if (Object.keys(typography).length) {
            serialized.typography = typography;
        }
        if (Object.keys(componentStyles).length) {
            serialized.component_styles = componentStyles;
        }

        return serialized;
    }

    function ensurePageSettingsState() {
        if (!state.pageSettings || typeof state.pageSettings !== 'object') {
            state.pageSettings = {};
        }
        if (!state.pageSettings.seo || typeof state.pageSettings.seo !== 'object') {
            state.pageSettings.seo = {};
        }
        state.pageSettings.footer = normalizePageFooterSettings(state.pageSettings.footer || state.pageSettings.footer_settings || {});
        state.pageSettings.visualStyle = normalizePageVisualStyleSettings(state.pageSettings.visualStyle || state.pageSettings.visual_style || {});
        state.pageSettings.designPreset = normalizeDesignPresetKey(
            state.pageSettings.designPreset || state.pageSettings.design_preset || state.pageSettings.pageDesignPreset || ''
        );
        state.pageSettings.design = normalizePageDesignState(state.pageSettings.design);
        return state.pageSettings;
    }

    function normalizePageFooterSettings(settings) {
        settings = settings && typeof settings === 'object' ? settings : {};

        var mode = String(settings.mode || settings.footer_mode || settings.visual_mode || 'inherit');
        if (mode === 'hide') {
            mode = 'hidden';
        } else if (mode === 'skin') {
            mode = 'page_skin';
        }
        if (['inherit', 'page_skin', 'preset', 'hidden'].indexOf(mode) === -1) {
            mode = 'inherit';
        }

        var wave = String(settings.wave || settings.wave_mode || settings.footer_wave || 'inherit');
        if (wave === 'enabled' || wave === 'enable' || wave === 'true') {
            wave = 'on';
        } else if (wave === 'disabled' || wave === 'disable' || wave === 'false') {
            wave = 'off';
        }
        if (['inherit', 'on', 'off'].indexOf(wave) === -1) {
            wave = 'inherit';
        }

        var inheritSkinColors = false;
        ['inheritSkinColors', 'inherit_skin_colors', 'inherit_skin', 'skin_colors'].some(function(key) {
            if (!Object.prototype.hasOwnProperty.call(settings, key)) {
                return false;
            }
            inheritSkinColors = normalizePageFooterBool(settings[key]);
            return true;
        });

        return {
            mode: mode,
            wave: wave,
            preset: String(settings.preset || settings.footer_preset || settings.footerPreset || settings.visual_skin || settings.visualSkin || ''),
            inheritSkinColors: inheritSkinColors
        };
    }

    function normalizePageVisualStyleSettings(settings) {
        settings = settings && typeof settings === 'object' ? settings : {};

        var mode = String(settings.mode || settings.visual_mode || 'inherit');
        if (['inherit', 'global', 'custom'].indexOf(mode) === -1) {
            mode = 'inherit';
        }

        var normalized = {
            mode: mode,
            preset: mode === 'custom' ? String(settings.preset || settings.visual_skin || settings.visualSkin || '') : '',
            colors: {},
            header: {},
            footer: {},
            buttons: {}
        };

        ['colors', 'header', 'footer', 'buttons'].forEach(function(groupKey) {
            var group = settings[groupKey] && typeof settings[groupKey] === 'object' ? settings[groupKey] : {};
            Object.keys(group).forEach(function(fieldKey) {
                if (group[fieldKey] === null || typeof group[fieldKey] === 'undefined') {
                    return;
                }
                var value = String(group[fieldKey]).trim();
                if (value !== '') {
                    normalized[groupKey][fieldKey] = value;
                }
            });
        });

        return normalized;
    }

    function normalizePageFooterBool(value) {
        if (typeof value === 'boolean') {
            return value;
        }
        if (typeof value === 'number') {
            return value > 0;
        }

        return ['1', 'true', 'yes', 'on'].indexOf(String(value || '').trim().toLowerCase()) !== -1;
    }

    function getPageDesignState() {
        return ensurePageSettingsState().design;
    }

    function getPageDesignDefinitions() {
        var payload = getDesignSystemPayload();
        return payload.pageDesignDefinitions && typeof payload.pageDesignDefinitions === 'object'
            ? payload.pageDesignDefinitions
            : {};
    }

    function getPageComponentStyleGroups() {
        var definitions = getPageDesignDefinitions();
        return definitions.component_styles && definitions.component_styles.groups && typeof definitions.component_styles.groups === 'object'
            ? definitions.component_styles.groups
            : {};
    }

    function getPageTypographyDefinitions() {
        var definitions = getPageDesignDefinitions();
        return definitions.typography && typeof definitions.typography === 'object'
            ? definitions.typography
            : {};
    }

    function getPageTypographyStyles() {
        var definitions = getPageTypographyDefinitions();
        if (definitions.styles && typeof definitions.styles === 'object') {
            return definitions.styles;
        }

        var payload = getDesignSystemPayload();
        return payload.typographyDefinitions && typeof payload.typographyDefinitions === 'object'
            ? payload.typographyDefinitions
            : {};
    }

    function getPageTypographyProperties() {
        var definitions = getPageTypographyDefinitions();
        if (definitions.properties && typeof definitions.properties === 'object') {
            return definitions.properties;
        }

        var payload = getDesignSystemPayload();
        return payload.typographyPropertyDefinitions && typeof payload.typographyPropertyDefinitions === 'object'
            ? payload.typographyPropertyDefinitions
            : {};
    }

    function getPageTypographyPropertyAliases() {
        return {
            font_size: 'fontSize',
            line_height: 'lineHeight',
            font_weight: 'fontWeight',
            letter_spacing: 'letterSpacing'
        };
    }

    function formatCountText(template, count) {
        return String(template || '').replace('%d', String(parseInt(count, 10) || 0));
    }

    function countPageDesignLeafValues(value) {
        if (Array.isArray(value)) {
            return value.reduce(function(total, item) {
                return total + countPageDesignLeafValues(item);
            }, 0);
        }

        if (value && typeof value === 'object') {
            return Object.keys(value).reduce(function(total, key) {
                return total + countPageDesignLeafValues(value[key]);
            }, 0);
        }

        return normalizeFieldValue(value || '') ? 1 : 0;
    }

    function getPageDesignGroupLabels() {
        return {
            palette: getText('pagePaletteSectionTitle', '页面配色'),
            structure: getText('pageStructureSectionTitle', '页面圆角与动效'),
            typography: getText('pageTypographySectionTitle', '页面排版'),
            layout: getText('pageLayoutSectionTitle', '页面布局'),
            componentStyles: getText('pageComponentSectionTitle', '页面组件样式')
        };
    }

    function getPageDesignOverrideSummary(design) {
        design = normalizePageDesignState(design);
        var componentStyleGroups = getPageComponentStyleGroups();
        var componentGroupCounts = {};
        var groups = {
            palette: countPageDesignLeafValues(design.palette),
            structure: countPageDesignLeafValues(design.structure),
            typography: countPageDesignLeafValues(design.typography),
            layout: countPageDesignLeafValues(design.layout),
            componentStyles: countPageDesignLeafValues(design.componentStyles)
        };

        Object.keys(componentStyleGroups).forEach(function(groupKey) {
            var group = componentStyleGroups[groupKey] && typeof componentStyleGroups[groupKey] === 'object'
                ? componentStyleGroups[groupKey]
                : {};
            var fields = group.fields && typeof group.fields === 'object' ? group.fields : {};
            componentGroupCounts[groupKey] = Object.keys(fields).reduce(function(total, styleKey) {
                return total + (normalizeFieldValue(design.componentStyles && design.componentStyles[styleKey] || '') ? 1 : 0);
            }, 0);
        });

        return {
            total: Object.keys(groups).reduce(function(total, key) {
                return total + groups[key];
            }, 0),
            groups: groups,
            componentGroups: componentGroupCounts
        };
    }

    function getPageDesignSummaryStatusText(total) {
        total = parseInt(total, 10) || 0;
        if (total > 0) {
            return formatCountText(getText('pageDesignSummaryActive', '当前页已单独调整 %d 项'), total);
        }

        return getText('pageDesignSummaryEmpty', '当前页还没有单独设置，正在跟随全站。');
    }

    function getPageDesignSectionCountText(count) {
        count = parseInt(count, 10) || 0;
        if (count > 0) {
            return formatCountText(getText('pageDesignSectionCount', '已改 %d 项'), count);
        }

        return getText('pageDesignSectionClean', '当前跟随全站');
    }

    function renderPageDesignSummary(design) {
        var labels = getPageDesignGroupLabels();
        var summary = getPageDesignOverrideSummary(design);
        var html = '<div class="qfb-page-design-summary-card" data-qfb-page-design-summary>';
        html += '<div class="qfb-page-design-summary-head">';
        html += '<div class="qfb-page-design-summary-copy">';
        html += '<div class="qfb-page-design-summary-title-row">';
        html += '<strong>' + escapeHtml(getText('pageDesignSummaryTitle', '当前页覆盖概览')) + '</strong>';
        html += '<span class="qfb-page-design-total" data-qfb-page-design-total>' + escapeHtml(String(summary.total)) + '</span>';
        html += '</div>';
        html += '<p data-qfb-page-design-status>' + escapeHtml(getPageDesignSummaryStatusText(summary.total)) + '</p>';
        html += '</div>';
        html += '<button type="button" class="qfb-page-reset-btn" data-page-design-reset="all"' + (summary.total > 0 ? '' : ' hidden') + '>' + escapeHtml(getText('pageDesignResetAll', '恢复本页跟随全站')) + '</button>';
        html += '</div>';
        html += '<div class="qfb-page-design-pill-list">';
        Object.keys(labels).forEach(function(groupKey) {
            var count = summary.groups[groupKey] || 0;
            html += '<span class="qfb-page-design-pill' + (count > 0 ? ' is-active' : '') + '" data-qfb-page-design-pill="' + escapeHtml(groupKey) + '">';
            html += '<span>' + escapeHtml(labels[groupKey]) + '</span>';
            html += '<b data-qfb-page-design-count="' + escapeHtml(groupKey) + '">' + escapeHtml(String(count)) + '</b>';
            html += '</span>';
        });
        html += '</div>';
        html += '</div>';
        return html;
    }

    function renderPageDesignSectionToolbar(sectionKey, count, resetAttrs) {
        var html = '<div class="qfb-page-section-toolbar">';
        html += '<span class="qfb-page-section-count' + (count > 0 ? ' is-active' : '') + '" data-qfb-page-design-section-count="' + escapeHtml(sectionKey) + '">';
        html += escapeHtml(getPageDesignSectionCountText(count));
        html += '</span>';
        if (resetAttrs) {
            html += '<button type="button" class="qfb-page-reset-link" ' + resetAttrs + (count > 0 ? '' : ' hidden') + '>';
            html += escapeHtml(getText('pageDesignResetGroup', '清空本组'));
            html += '</button>';
        }
        html += '</div>';
        return html;
    }

    function refreshPageDesignSummaryUI() {
        if (!els.settings) {
            return;
        }

        var design = getPageDesignState();
        var summary = getPageDesignOverrideSummary(design);

        Array.prototype.forEach.call(els.settings.querySelectorAll('[data-qfb-page-design-total]'), function(node) {
            node.textContent = String(summary.total);
        });

        Array.prototype.forEach.call(els.settings.querySelectorAll('[data-qfb-page-design-status]'), function(node) {
            node.textContent = getPageDesignSummaryStatusText(summary.total);
        });

        Array.prototype.forEach.call(els.settings.querySelectorAll('[data-page-design-reset="all"]'), function(button) {
            button.hidden = summary.total <= 0;
        });

        Object.keys(summary.groups).forEach(function(groupKey) {
            var count = summary.groups[groupKey] || 0;
            Array.prototype.forEach.call(els.settings.querySelectorAll('[data-qfb-page-design-count="' + groupKey + '"]'), function(node) {
                node.textContent = String(count);
            });
            Array.prototype.forEach.call(els.settings.querySelectorAll('[data-qfb-page-design-pill="' + groupKey + '"]'), function(node) {
                node.classList.toggle('is-active', count > 0);
            });
            Array.prototype.forEach.call(els.settings.querySelectorAll('[data-qfb-page-design-section-count="' + groupKey + '"]'), function(node) {
                node.textContent = getPageDesignSectionCountText(count);
                node.classList.toggle('is-active', count > 0);
            });
            Array.prototype.forEach.call(els.settings.querySelectorAll('[data-page-design-reset-group="' + groupKey + '"]'), function(button) {
                button.hidden = count <= 0;
            });
        });

        Object.keys(summary.componentGroups).forEach(function(groupKey) {
            var count = summary.componentGroups[groupKey] || 0;
            Array.prototype.forEach.call(els.settings.querySelectorAll('[data-qfb-page-design-section-count="component:' + groupKey + '"]'), function(node) {
                node.textContent = getPageDesignSectionCountText(count);
                node.classList.toggle('is-active', count > 0);
            });
            Array.prototype.forEach.call(els.settings.querySelectorAll('[data-page-design-reset-component-group="' + groupKey + '"]'), function(button) {
                button.hidden = count <= 0;
            });
        });
    }

    function resetPageDesignGroup(groupKey, componentGroupKey) {
        ensurePageSettingsState();

        var defaults = getDefaultPageDesignState();
        if (groupKey === 'all') {
            state.pageSettings.design = defaults;
        } else if (groupKey === 'componentStyles' && componentGroupKey) {
            var groups = getPageComponentStyleGroups();
            var group = groups[componentGroupKey] && typeof groups[componentGroupKey] === 'object'
                ? groups[componentGroupKey]
                : {};
            var fields = group.fields && typeof group.fields === 'object' ? group.fields : {};

            if (!state.pageSettings.design.componentStyles || typeof state.pageSettings.design.componentStyles !== 'object') {
                state.pageSettings.design.componentStyles = {};
            }

            Object.keys(fields).forEach(function(styleKey) {
                state.pageSettings.design.componentStyles[styleKey] = defaults.componentStyles && Object.prototype.hasOwnProperty.call(defaults.componentStyles, styleKey)
                    ? defaults.componentStyles[styleKey]
                    : '';
            });
        } else if (Object.prototype.hasOwnProperty.call(defaults, groupKey)) {
            state.pageSettings.design[groupKey] = deepClone(defaults[groupKey]);
        }

        state.pageSettings.design = normalizePageDesignState(state.pageSettings.design);
        applyPageDesignPreview();
        markDirty();
        renderPageSettings();
        setStatus(
            groupKey === 'all'
                ? getText('pageDesignResetAllDone', '当前页已恢复跟随全站，保存后正式生效。')
                : getText('pageDesignResetGroupDone', '已清空当前分组，保存后继续跟随全站。'),
            'warning'
        );
    }

    function hydratePageVisualFieldsFromPreset() {
        ensurePageSettingsState();
        var visualStyle = normalizePageVisualStyleSettings(state.pageSettings.visualStyle || {});
        var presetKey = visualStyle.preset || '';
        var groups = getPageVisualFieldGroups();

        if (!presetKey) {
            setStatus(getText('pageVisualPresetRequired', '请先选择一个基础预设，再填充预设值。'), 'warning');
            return;
        }

        visualStyle.mode = 'custom';
        Object.keys(groups || {}).forEach(function(groupKey) {
            var group = groups[groupKey];
            var fields = group && group.fields && typeof group.fields === 'object' ? group.fields : {};
            if (!visualStyle[groupKey] || typeof visualStyle[groupKey] !== 'object') {
                visualStyle[groupKey] = {};
            }

            Object.keys(fields).forEach(function(fieldKey) {
                var value = getPageVisualFieldPresetValue(fields[fieldKey], presetKey);
                if (value) {
                    visualStyle[groupKey][fieldKey] = value;
                }
            });
        });

        state.pageSettings.visualStyle = normalizePageVisualStyleSettings(visualStyle);
        applyPageVisualStylePreview();
        renderPageSettings();
        markDirty();
        setStatus(getText('pageVisualPreviewApplied', '页面视觉风格已同步到当前预览，保存后会正式生效。'), 'warning');
    }

    function clearPageVisualCustomValues() {
        ensurePageSettingsState();
        var visualStyle = normalizePageVisualStyleSettings(state.pageSettings.visualStyle || {});
        visualStyle.colors = {};
        visualStyle.header = {};
        visualStyle.footer = {};
        visualStyle.buttons = {};
        state.pageSettings.visualStyle = normalizePageVisualStyleSettings(visualStyle);

        applyPageVisualStylePreview();
        renderPageSettings();
        markDirty();
        setStatus(getText('pageVisualPreviewApplied', '页面视觉风格已同步到当前预览，保存后会正式生效。'), 'warning');
    }

    function getPageTypographyBuilderKey(propertyKey) {
        var aliases = getPageTypographyPropertyAliases();
        return aliases[propertyKey] || propertyKey;
    }

    function getPageTypographyStorageKey(propertyKey) {
        var aliases = getPageTypographyPropertyAliases();
        var storageKey = propertyKey;
        Object.keys(aliases).forEach(function(candidateKey) {
            if (aliases[candidateKey] === propertyKey) {
                storageKey = candidateKey;
            }
        });
        return storageKey;
    }

    function getPagePaletteDefinitionKey(propertyKey) {
        var aliases = {
            surfaceAlt: 'surface_alt',
            textMuted: 'text_muted',
            darkBg: 'dark_bg',
            darkSurface: 'dark_surface',
            darkText: 'dark_text',
            darkTextMuted: 'dark_text_muted',
            darkBorder: 'dark_border'
        };
        return aliases[propertyKey] || propertyKey;
    }

    function createEmptyPageTypographyState(styleDefinitions, propertyDefinitions) {
        var styles = styleDefinitions && typeof styleDefinitions === 'object' ? styleDefinitions : {};
        var properties = propertyDefinitions && typeof propertyDefinitions === 'object' ? propertyDefinitions : {};
        var typography = {};

        Object.keys(styles).forEach(function(styleKey) {
            typography[styleKey] = {
                desktop: {},
                tablet: {},
                mobile: {}
            };

            Object.keys(properties).forEach(function(propertyKey) {
                var builderKey = getPageTypographyBuilderKey(propertyKey);
                typography[styleKey].desktop[builderKey] = '';
                typography[styleKey].tablet[builderKey] = '';
                typography[styleKey].mobile[builderKey] = '';
            });
        });

        return typography;
    }

    function getGlobalLayoutMode() {
        var payload = getDesignSystemPayload();
        var layoutSystem = payload.layoutSystem && typeof payload.layoutSystem === 'object' ? payload.layoutSystem : {};
        return normalizeFieldValue(layoutSystem.layout_mode || 'wide') || 'wide';
    }

    function getGlobalBreakpoints() {
        var payload = getDesignSystemPayload();
        var layoutSystem = payload.layoutSystem && typeof payload.layoutSystem === 'object' ? payload.layoutSystem : {};
        var breakpoints = layoutSystem.breakpoints && typeof layoutSystem.breakpoints === 'object' ? layoutSystem.breakpoints : {};
        return {
            tablet: normalizeFieldValue(breakpoints.tablet || '992px') || '992px',
            mobile: normalizeFieldValue(breakpoints.mobile || '768px') || '768px'
        };
    }

    function isHexColor(value) {
        return /^#(?:[0-9a-f]{3}|[0-9a-f]{6})$/i.test(String(value || '').trim());
    }

    function normalizeHex(value) {
        var hex = String(value || '').trim().replace('#', '');
        if (hex.length === 3) {
            return '#' + hex.charAt(0) + hex.charAt(0) + hex.charAt(1) + hex.charAt(1) + hex.charAt(2) + hex.charAt(2);
        }
        if (hex.length === 6) {
            return '#' + hex;
        }
        return '';
    }

    function shiftHexColor(value, amount) {
        var hex = normalizeHex(value);
        if (!hex) {
            return '';
        }
        var channels = [1, 3, 5].map(function(start) {
            var channel = parseInt(hex.substr(start, 2), 16);
            if (Number.isNaN(channel)) {
                channel = 0;
            }
            channel = Math.max(0, Math.min(255, channel + amount));
            return ('0' + channel.toString(16)).slice(-2);
        });
        return '#' + channels.join('');
    }

    function hexToRgbString(value) {
        var hex = normalizeHex(value);
        if (!hex) {
            return '';
        }
        return [1, 3, 5].map(function(start) {
            var channel = parseInt(hex.substr(start, 2), 16);
            return Number.isNaN(channel) ? '0' : String(channel);
        }).join(', ');
    }

    function hexToRgb(value) {
        var hex = normalizeHex(value);
        if (!hex) {
            return null;
        }

        return {
            r: parseInt(hex.substr(1, 2), 16),
            g: parseInt(hex.substr(3, 2), 16),
            b: parseInt(hex.substr(5, 2), 16)
        };
    }

    function rgbToHex(color) {
        if (!color) {
            return '';
        }

        function channelToHex(value) {
            var channel = Math.max(0, Math.min(255, Math.round(value)));
            var hex = channel.toString(16);
            return hex.length === 1 ? '0' + hex : hex;
        }

        return '#' + channelToHex(color.r) + channelToHex(color.g) + channelToHex(color.b);
    }

    function mixRgb(color, target, amount) {
        amount = Math.max(0, Math.min(1, typeof amount === 'number' ? amount : 0));
        color = color || { r: 79, g: 125, b: 255 };
        target = target || { r: 255, g: 255, b: 255 };

        return {
            r: color.r + (target.r - color.r) * amount,
            g: color.g + (target.g - color.g) * amount,
            b: color.b + (target.b - color.b) * amount
        };
    }

    function rgbToHsl(color) {
        color = color || { r: 79, g: 125, b: 255 };
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
        var h = (((hsl && hsl.h) || 0) % 1 + 1) % 1;
        var s = Math.max(0, Math.min(1, hsl && typeof hsl.s === 'number' ? hsl.s : 0));
        var l = Math.max(0, Math.min(1, hsl && typeof hsl.l === 'number' ? hsl.l : 0));
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
            r: Math.max(0, Math.min(255, Math.round(r * 255))),
            g: Math.max(0, Math.min(255, Math.round(g * 255))),
            b: Math.max(0, Math.min(255, Math.round(b * 255)))
        };
    }

    function getReadableTextColorForHex(backgroundHex) {
        var color = hexToRgb(backgroundHex);
        if (!color) {
            return '#111827';
        }

        function linear(channel) {
            channel = channel / 255;
            return channel <= 0.03928 ? channel / 12.92 : Math.pow((channel + 0.055) / 1.055, 2.4);
        }

        var luminance = 0.2126 * linear(color.r) + 0.7152 * linear(color.g) + 0.0722 * linear(color.b);
        var whiteRatio = (1.05) / (luminance + 0.05);
        var darkRatio = (Math.max(luminance, 0.006) + 0.05) / (Math.min(luminance, 0.006) + 0.05);

        return whiteRatio >= darkRatio ? '#ffffff' : '#111827';
    }

    function buildAiAccentHex(primaryHex, offset) {
        var primary = hexToRgb(primaryHex) || { r: 79, g: 125, b: 255 };
        var hsl = rgbToHsl(primary);
        var accent = hslToRgb({
            h: hsl.h + (typeof offset === 'number' ? offset : 0.42),
            s: Math.max(0.52, Math.min(0.84, hsl.s + 0.06)),
            l: Math.max(0.42, Math.min(0.58, hsl.l + 0.03))
        });
        return rgbToHex(accent);
    }

    function applyDerivedHexColorPreviewVars(vars, value, config) {
        var normalized = normalizeFieldValue(value || '');
        if (!isHexColor(normalized)) {
            if (config.hoverVar) {
                vars[config.hoverVar] = 'inherit';
            }
            if (config.darkVar) {
                vars[config.darkVar] = 'inherit';
            }
            if (config.lightVar) {
                vars[config.lightVar] = 'inherit';
            }
            if (config.rgbVar) {
                vars[config.rgbVar] = 'inherit';
            }
            return;
        }

        if (config.hoverVar) {
            vars[config.hoverVar] = shiftHexColor(normalized, typeof config.hoverShift === 'number' ? config.hoverShift : -16) || 'inherit';
        }
        if (config.darkVar) {
            vars[config.darkVar] = shiftHexColor(normalized, typeof config.darkShift === 'number' ? config.darkShift : -14) || 'inherit';
        }
        if (config.lightVar) {
            vars[config.lightVar] = shiftHexColor(normalized, typeof config.lightShift === 'number' ? config.lightShift : 12) || 'inherit';
        }
        if (config.rgbVar) {
            vars[config.rgbVar] = hexToRgbString(normalized) || 'inherit';
        }
    }

    function getPageDesignPreviewStyleEl() {
        if (state.pageDesignPreviewStyleEl && state.pageDesignPreviewStyleEl.parentNode) {
            return state.pageDesignPreviewStyleEl;
        }
        var styleEl = document.getElementById('qfb-page-design-preview');
        if (!styleEl) {
            styleEl = document.createElement('style');
            styleEl.id = 'qfb-page-design-preview';
            document.head.appendChild(styleEl);
        }
        state.pageDesignPreviewStyleEl = styleEl;
        return styleEl;
    }

    function getPageVisualPreviewStyleEl() {
        if (state.pageVisualPreviewStyleEl && state.pageVisualPreviewStyleEl.parentNode) {
            return state.pageVisualPreviewStyleEl;
        }
        var styleEl = document.getElementById('qfb-page-visual-preview');
        if (!styleEl) {
            styleEl = document.createElement('style');
            styleEl.id = 'qfb-page-visual-preview';
            document.head.appendChild(styleEl);
        }
        state.pageVisualPreviewStyleEl = styleEl;
        return styleEl;
    }

    function sanitizePageVisualPreviewValue(value) {
        var normalized = normalizeFieldValue(value || '').trim();
        if (!normalized) {
            return '';
        }
        if (/[;{}<>]/.test(normalized) || /(?:expression|javascript\s*:|url\s*\()/i.test(normalized)) {
            return '';
        }
        return normalized;
    }

    function normalizePageVisualVarName(varName) {
        var name = String(varName || '').trim();
        return /^--[a-z0-9_-]+$/i.test(name) ? name : '';
    }

    function buildPageVisualCustomPreviewVars(settings) {
        var vars = {};
        var groups = getPageVisualFieldGroups();
        settings = normalizePageVisualStyleSettings(settings || {});

        Object.keys(groups || {}).forEach(function(groupKey) {
            var group = groups[groupKey];
            var fields = group && group.fields && typeof group.fields === 'object' ? group.fields : {};
            var values = settings[groupKey] && typeof settings[groupKey] === 'object' ? settings[groupKey] : {};
            Object.keys(fields).forEach(function(fieldKey) {
                if (!Object.prototype.hasOwnProperty.call(values, fieldKey)) {
                    return;
                }
                var value = sanitizePageVisualPreviewValue(values[fieldKey]);
                if (!value) {
                    return;
                }

                var fieldVars = Array.isArray(fields[fieldKey].vars) ? fields[fieldKey].vars : [];
                fieldVars.forEach(function(varName) {
                    var normalizedVar = normalizePageVisualVarName(varName);
                    if (normalizedVar) {
                        vars[normalizedVar] = value;
                    }
                });
            });
        });

        return vars;
    }

    function buildPageVisualPreviewVars() {
        var settings = ensurePageSettingsState().visualStyle || {};
        if (settings.mode !== 'custom') {
            return {};
        }

        var vars = {};
        var presetVars = getPageVisualPresetVars(settings.preset || '');
        Object.keys(presetVars || {}).forEach(function(varName) {
            var normalizedVar = normalizePageVisualVarName(varName);
            var value = sanitizePageVisualPreviewValue(presetVars[varName]);
            if (normalizedVar && value) {
                vars[normalizedVar] = value;
            }
        });

        var customVars = buildPageVisualCustomPreviewVars(settings);
        Object.keys(customVars).forEach(function(varName) {
            vars[varName] = customVars[varName];
        });

        return vars;
    }

    function buildPageVisualPreviewCss() {
        var postId = parseInt(builderData.postId, 10);
        if (Number.isNaN(postId) || postId <= 0) {
            return '';
        }

        var vars = buildPageVisualPreviewVars();
        var varNames = Object.keys(vars);
        if (!varNames.length) {
            return '';
        }

        var selector = 'body.qfb-builder-mode';
        var targets = [
            selector,
            selector + ' #page',
            selector + ' #masthead.site-header',
            selector + ' .site-header',
            selector + ' #colophon.site-footer',
            selector + ' .site-footer'
        ];
        var css = targets.join(',\n') + '{\n';
        varNames.forEach(function(name) {
            css += '    ' + name + ': ' + vars[name] + ' !important;\n';
        });
        css += '}\n';

        return css;
    }

    function applyPageVisualStylePreview() {
        var styleEl = getPageVisualPreviewStyleEl();
        if (!styleEl) {
            return;
        }
        styleEl.textContent = buildPageVisualPreviewCss();
    }

    function buildPageDesignPreviewCss() {
        var postId = parseInt(builderData.postId, 10);
        if (Number.isNaN(postId) || postId <= 0) {
            return '';
        }

        var selector = 'body.page-id-' + postId;
        var design = getPageDesignState();
        var vars = {};
        var paletteVarMap = {
            primary: ['--color-primary'],
            secondary: ['--color-secondary'],
            accent: ['--color-accent'],
            success: ['--color-success'],
            info: ['--color-info'],
            warning: ['--color-warning'],
            error: ['--color-error'],
            overlay: ['--color-overlay'],
            background: ['--color-background'],
            surface: ['--color-surface'],
            surfaceAlt: ['--color-surface-alt'],
            text: ['--color-dark', '--color-text'],
            textMuted: ['--color-text-muted'],
            heading: ['--color-heading'],
            border: ['--color-border'],
            darkBg: ['--color-bg-dark', '--qiling-dark-bg', '--dm-bg'],
            darkSurface: ['--color-card-dark', '--qiling-dark-surface', '--dm-bg-secondary', '--dm-bg-card'],
            darkText: ['--color-text-light', '--qiling-dark-text', '--dm-text'],
            darkTextMuted: ['--qiling-dark-text-muted', '--dm-text-muted'],
            darkBorder: ['--qiling-dark-border', '--dm-border']
        };

        Object.keys(paletteVarMap).forEach(function(key) {
            var value = normalizeFieldValue(design.palette[key] || '') || 'inherit';
            paletteVarMap[key].forEach(function(varName) {
                vars[varName] = value;
            });
        });

        vars['--qiling-container-width-desktop'] = normalizeFieldValue(design.layout.containerWidth.desktop || '') || 'inherit';
        vars['--qiling-container-width-tablet'] = normalizeFieldValue(design.layout.containerWidth.tablet || '') || 'inherit';
        vars['--qiling-container-width-mobile'] = normalizeFieldValue(design.layout.containerWidth.mobile || '') || 'inherit';
        vars['--qiling-section-spacing-desktop'] = normalizeFieldValue(design.layout.sectionSpacing.desktop || '') || 'inherit';
        vars['--qiling-section-spacing-tablet'] = normalizeFieldValue(design.layout.sectionSpacing.tablet || '') || 'inherit';
        vars['--qiling-section-spacing-mobile'] = normalizeFieldValue(design.layout.sectionSpacing.mobile || '') || 'inherit';
        vars['--qiling-grid-gap-desktop'] = normalizeFieldValue(design.layout.gridGap.desktop || '') || 'inherit';
        vars['--qiling-grid-gap-tablet'] = normalizeFieldValue(design.layout.gridGap.tablet || '') || 'inherit';
        vars['--qiling-grid-gap-mobile'] = normalizeFieldValue(design.layout.gridGap.mobile || '') || 'inherit';
        vars['--qiling-layout-mode'] = normalizeFieldValue(design.layout.layoutMode || '') || 'inherit';
        vars['--qiling-card-radius'] = normalizeFieldValue(design.structure.cardRadius || '') || 'inherit';
        vars['--qiling-button-radius'] = normalizeFieldValue(design.structure.buttonRadius || '') || 'inherit';
        vars['--qiling-input-radius'] = normalizeFieldValue(design.structure.inputRadius || '') || 'inherit';
        vars['--qiling-animation-speed'] = normalizeFieldValue(design.structure.animationSpeed || '') || 'inherit';

        var typographyStyles = getPageTypographyStyles();
        var typographyProperties = getPageTypographyProperties();
        Object.keys(typographyStyles).forEach(function(styleKey) {
            var styleValues = design.typography && design.typography[styleKey] && typeof design.typography[styleKey] === 'object'
                ? design.typography[styleKey]
                : {};

            ['desktop', 'tablet', 'mobile'].forEach(function(deviceKey) {
                var deviceValues = styleValues[deviceKey] && typeof styleValues[deviceKey] === 'object'
                    ? styleValues[deviceKey]
                    : {};

                Object.keys(typographyProperties).forEach(function(propertyKey) {
                    var builderKey = getPageTypographyBuilderKey(propertyKey);
                    vars['--qiling-' + styleKey + '-' + propertyKey.replace(/_/g, '-') + '-' + deviceKey] =
                        normalizeFieldValue(deviceValues[builderKey] || deviceValues[propertyKey] || '') || 'inherit';
                });
            });
        });

        var componentStyleGroups = getPageComponentStyleGroups();
        Object.keys(componentStyleGroups).forEach(function(groupKey) {
            var group = componentStyleGroups[groupKey];
            var fields = group && group.fields && typeof group.fields === 'object' ? group.fields : {};
            Object.keys(fields).forEach(function(styleKey) {
                var definition = fields[styleKey] && typeof fields[styleKey] === 'object' ? fields[styleKey] : {};
                if (!definition.cssVar) {
                    return;
                }
                vars[definition.cssVar] = normalizeFieldValue(design.componentStyles && design.componentStyles[styleKey] || '') || 'inherit';
            });
        });

        applyDerivedHexColorPreviewVars(vars, design.palette.primary, {
            hoverVar: '--color-primary-hover',
            darkVar: '--color-primary-dark',
            lightVar: '--color-primary-light',
            rgbVar: '--color-primary-rgb',
            hoverShift: -16,
            darkShift: -16,
            lightShift: 12
        });
        applyDerivedHexColorPreviewVars(vars, design.palette.accent, {
            darkVar: '--color-accent-dark',
            rgbVar: '--color-accent-rgb',
            darkShift: -12
        });
        [
            { key: 'success', darkVar: '--color-success-dark', rgbVar: '--color-success-rgb' },
            { key: 'info', darkVar: '--color-info-dark', rgbVar: '--color-info-rgb' },
            { key: 'warning', darkVar: '--color-warning-dark', rgbVar: '--color-warning-rgb' },
            { key: 'error', darkVar: '--color-error-dark', rgbVar: '--color-error-rgb' }
        ].forEach(function(item) {
            applyDerivedHexColorPreviewVars(vars, design.palette[item.key], {
                darkVar: item.darkVar,
                rgbVar: item.rgbVar,
                darkShift: -14
            });
        });

        var css = selector + '{\n';
        Object.keys(vars).forEach(function(name) {
            css += '    ' + name + ': ' + vars[name] + ';\n';
        });
        css += '}\n';

        var effectiveLayoutMode = normalizeFieldValue(design.layout.layoutMode || '') || getGlobalLayoutMode();
        var breakpoints = getGlobalBreakpoints();
        if (effectiveLayoutMode === 'boxed') {
            css += selector + '{background:var(--color-surface-alt);}\n';
            css += selector + ' #page.site{max-width:calc(var(--qiling-container-width) + 48px);margin:0 auto;background:var(--color-background);box-shadow:var(--shadow-lg);}\n';
            css += '@media (max-width:' + breakpoints.tablet + '){' + selector + ' #page.site{max-width:none;box-shadow:none;}}\n';
        } else {
            css += selector + '{background:var(--color-background);}\n';
            css += selector + ' #page.site{max-width:none;margin:0;background:transparent;box-shadow:none;}\n';
        }

        return css;
    }

    function applyPageDesignPreview() {
        var styleEl = getPageDesignPreviewStyleEl();
        if (!styleEl) {
            return;
        }
        styleEl.textContent = buildPageDesignPreviewCss();
    }

    function buildQuickColorSuggestion(label, value) {
        return {
            label: label,
            value: value,
            color: value
        };
    }

    function fieldLooksLikeQuickColorTarget(fieldId, fieldType, scope) {
        var id = String(fieldId || '').toLowerCase();
        if (fieldType === 'image' || fieldType === 'upload' || fieldType === 'gallery' || fieldType === 'date' || fieldType === 'number' || fieldType === 'range') {
            return false;
        }
        if (fieldType === 'color') {
            return true;
        }
        if (scope === 'page' && /(^|[_.-])(text|transparent_text)(_|[.-]|$)/.test(id)) {
            return true;
        }
        return /(^|[_.-])(color|bg|background|border|accent|primary|secondary)(_|[.-]|$)/.test(id);
    }

    function getQuickColorSuggestions(fieldId, fieldType, scope) {
        if (!fieldLooksLikeQuickColorTarget(fieldId, fieldType, scope)) {
            return [];
        }

        var id = String(fieldId || '').toLowerCase();
        var textFirst = /(text|title|heading|label|desc|content|font)/.test(id);
        var neutralColors = [
            buildQuickColorSuggestion(getText('quickColorBlack', '黑色'), '#111827'),
            buildQuickColorSuggestion(getText('quickColorWhite', '白色'), '#ffffff'),
            buildQuickColorSuggestion(getText('quickColorGray', '灰色'), '#64748b')
        ];
        var brightColors = [
            buildQuickColorSuggestion(getText('quickColorRed', '红色'), '#ef4444'),
            buildQuickColorSuggestion(getText('quickColorOrange', '橙色'), '#f97316'),
            buildQuickColorSuggestion(getText('quickColorYellow', '黄色'), '#f59e0b'),
            buildQuickColorSuggestion(getText('quickColorGreen', '绿色'), '#22c55e'),
            buildQuickColorSuggestion(getText('quickColorCyan', '青色'), '#06b6d4'),
            buildQuickColorSuggestion(getText('quickColorBlue', '蓝色'), '#2563eb'),
            buildQuickColorSuggestion(getText('quickColorPurple', '紫色'), '#7c3aed'),
            buildQuickColorSuggestion(getText('quickColorPink', '粉色'), '#ec4899')
        ];
        var suggestions = textFirst ? neutralColors.concat(brightColors) : brightColors.concat(neutralColors);

        if (/border/.test(id)) {
            suggestions = [
                buildQuickColorSuggestion(getText('quickColorBorderLight', '浅边框'), '#e5e7eb')
            ].concat(suggestions);
        }

        return suggestions.filter(function(item) {
            return item && item.value;
        }).slice(0, 10);
    }

    function renderDesignTokenPicker(fieldId, fieldType, scope) {
        var suggestions = getQuickColorSuggestions(fieldId, fieldType, scope || 'field');
        if (!suggestions.length) {
            return '';
        }

        var html = '<div class="qfb-token-picker" data-token-scope="' + escapeHtml(scope || 'field') + '" data-token-field-id="' + escapeHtml(fieldId || '') + '">';
        html += '<span class="qfb-token-picker-title">' + escapeHtml(getText('tokenApply', '快速颜色选择')) + '</span>';
        suggestions.forEach(function(item) {
            html += '<button type="button" class="qfb-token-chip" data-token-value="' + escapeHtml(item.value) + '" title="' + escapeHtml(item.value) + '">';
            if (item.color) {
                html += '<span class="qfb-token-dot" style="background:' + escapeHtml(item.color) + ';"></span>';
            }
            html += '<span>' + escapeHtml(item.label) + '</span>';
            html += '</button>';
        });
        html += '</div>';
        return html;
    }

    function setStatus(message, type) {
        if (!els.status) {
            return;
        }
        els.status.textContent = message;
        els.status.classList.remove('is-error', 'is-success', 'is-warning');
        if (type === 'error') {
            els.status.classList.add('is-error');
        } else if (type === 'success') {
            els.status.classList.add('is-success');
        } else if (type === 'warning') {
            els.status.classList.add('is-warning');
        }
    }

    function setPreviewMode(mode) {
        var normalized = mode === 'tablet' || mode === 'mobile' ? mode : 'desktop';
        state.previewMode = normalized;
        document.body.classList.remove('qfb-preview-desktop', 'qfb-preview-tablet', 'qfb-preview-mobile');
        document.body.classList.add('qfb-preview-' + normalized);

        if (!els.previewTools) {
            return;
        }
        var buttons = els.previewTools.querySelectorAll('[data-preview-mode]');
        Array.prototype.forEach.call(buttons, function(button) {
            button.classList.toggle('is-active', button.getAttribute('data-preview-mode') === normalized);
        });
    }

    function markDirty() {
        state.dirty = true;
        setStatus(getText('unsaved', '有未保存修改'), 'warning');
    }

    function markSaved() {
        state.dirty = false;
        setStatus(getText('saved', '当前内容已保存'), 'success');
    }

    function renderSnapshotsPanel() {
        if (!els.snapshotsPanel) {
            return;
        }

        if (state.snapshotsLoading) {
            els.snapshotsPanel.innerHTML = '<div class="qfb-snapshots-card">' + escapeHtml(getText('loading', '加载中...')) + '</div>';
            return;
        }

        var snapshots = Array.isArray(state.snapshots) ? state.snapshots : [];
        var html = '<div class="qfb-snapshots-card">';
        html += '<div class="qfb-snapshots-head"><strong>' + escapeHtml(getText('snapshotHistory', '保存历史')) + '</strong></div>';
        if (!snapshots.length) {
            html += '<p class="qfb-snapshots-empty">' + escapeHtml(getText('snapshotEmpty', '暂无保存历史。每次保存前都会自动生成一份快照。')) + '</p>';
        } else {
            html += '<ul class="qfb-snapshots-list">';
            snapshots.forEach(function(snapshot) {
                var id = String(snapshot.id || '');
                var createdAt = String(snapshot.createdAt || '');
                var moduleCount = parseInt(snapshot.moduleCount, 10) || 0;
                html += '<li class="qfb-snapshot-item">';
                html += '<span class="qfb-snapshot-meta">' + escapeHtml(createdAt || id) + ' | ' + moduleCount + '</span>';
                html += '<button type="button" class="button button-small" data-qfb-restore-snapshot="' + escapeHtml(id) + '">' + escapeHtml(getText('snapshotRestore', '恢复')) + '</button>';
                html += '</li>';
            });
            html += '</ul>';
        }
        html += '</div>';
        els.snapshotsPanel.innerHTML = html;
    }

    function loadSnapshots(force) {
        if (!els.snapshotsPanel || state.snapshotsLoading || (state.snapshotsLoaded && !force)) {
            renderSnapshotsPanel();
            return;
        }

        state.snapshotsLoading = true;
        renderSnapshotsPanel();
        $.ajax({
            url: builderData.ajaxUrl,
            method: 'POST',
            dataType: 'json',
            cache: false,
            data: {
                action: 'qiling_frontend_builder_get_snapshots',
                nonce: builderData.nonce,
                post_id: builderData.postId,
                _t: Date.now()
            }
        }).done(function(response) {
            if (!response || !response.success) {
                state.snapshots = [];
                setStatus(getText('snapshotLoadFailed', '保存历史加载失败'), 'warning');
                return;
            }
            state.snapshots = response.data && Array.isArray(response.data.snapshots) ? response.data.snapshots : [];
            state.snapshotsLoaded = true;
        }).fail(function() {
            state.snapshots = [];
            setStatus(getText('snapshotLoadFailed', '保存历史加载失败'), 'warning');
        }).always(function() {
            state.snapshotsLoading = false;
            renderSnapshotsPanel();
        });
    }

    function restoreSnapshot(snapshotId) {
        snapshotId = String(snapshotId || '');
        if (!snapshotId || state.saving) {
            return;
        }
        if (!window.confirm(getText('snapshotRestoreConfirm', '确定恢复到这次保存前的状态吗？当前状态会先生成一份新快照。'))) {
            return;
        }

        state.saving = true;
        setStatus(getText('snapshotRestoring', '正在恢复保存历史...'), 'warning');
        $.ajax({
            url: builderData.ajaxUrl,
            method: 'POST',
            dataType: 'json',
            cache: false,
            data: {
                action: 'qiling_frontend_builder_restore_snapshot',
                nonce: builderData.nonce,
                post_id: builderData.postId,
                snapshot_id: snapshotId,
                _t: Date.now()
            }
        }).done(function(response) {
            if (!response || !response.success) {
                var msg = response && response.data && response.data.message ? response.data.message : getText('snapshotRestoreFailed', '恢复保存历史失败，请重试');
                setStatus(msg, 'error');
                return;
            }
            state.dirty = false;
            setStatus(getText('snapshotRestoreSuccess', '已恢复保存历史，正在刷新预览...'), 'success');
            window.setTimeout(function() {
                window.location.reload();
            }, 350);
        }).fail(function() {
            setStatus(getText('snapshotRestoreFailed', '恢复保存历史失败，请重试'), 'error');
        }).always(function() {
            state.saving = false;
        });
    }

    function getModuleName(moduleId) {
        for (var i = 0; i < state.availableModules.length; i++) {
            if (state.availableModules[i].id === moduleId) {
                return state.availableModules[i].name || moduleId;
            }
        }
        if (state.schemaCache[moduleId] && state.schemaCache[moduleId].name) {
            return state.schemaCache[moduleId].name;
        }
        return moduleId;
    }

    function normalizeInitialData() {
        state.modules = Array.isArray(builderData.modules) ? deepClone(builderData.modules) : [];
        state.modules = state.modules.filter(function(item) {
            return item && typeof item === 'object' && item.type;
        }).map(function(item) {
            return {
                type: String(item.type),
                data: item.data && typeof item.data === 'object' ? item.data : {}
            };
        });
        state.availableModules = Array.isArray(builderData.availableModules) ? builderData.availableModules : [];
        state.pageSettings = builderData.pageSettings && typeof builderData.pageSettings === 'object'
            ? deepClone(builderData.pageSettings)
            : {};
        ensurePageSettingsState();
        state.designSystem = builderData.designSystem && typeof builderData.designSystem === 'object'
            ? deepClone(builderData.designSystem)
            : {};
        state.contentModels = builderData.contentModels && typeof builderData.contentModels === 'object'
            ? deepClone(builderData.contentModels)
            : {};
        state.dynamicData = builderData.dynamicData && typeof builderData.dynamicData === 'object'
            ? deepClone(builderData.dynamicData)
            : {};
        state.aiConfig = builderData.aiBuilder && typeof builderData.aiBuilder === 'object'
            ? deepClone(builderData.aiBuilder)
            : { enabled: false, connections: [] };
        state.myLibraryTemplates = Array.isArray(builderData.myLibraryTemplates) ? deepClone(builderData.myLibraryTemplates) : [];
        state.myLibraryTemplates = state.myLibraryTemplates.filter(function(item) {
            return item && typeof item === 'object' && item.type;
        }).map(function(item) {
            return {
                id: item.id,
                title: String(item.title || ''),
                type: String(item.type || ''),
                typeName: String(item.typeName || item.type || ''),
                data: item.data && typeof item.data === 'object' ? item.data : null,
                date: String(item.date || '')
            };
        });
        state.selectedScope = state.modules.length ? 'module' : 'page';
    }

    function initDomRefs() {
        els.root = document.getElementById('qiling-frontend-builder');
        els.panel = document.getElementById('qfb-panel');
        els.toggle = document.getElementById('qfb-toggle');
        els.save = document.getElementById('qfb-save');
        els.snapshotsToggle = document.getElementById('qfb-snapshots-toggle');
        els.snapshotsPanel = document.getElementById('qfb-snapshots-panel');
        els.status = document.getElementById('qfb-status');
        els.librarySearch = document.getElementById('qfb-library-search');
        els.libraryFilters = document.getElementById('qfb-library-filters');
        els.designSummary = document.getElementById('qfb-design-summary');
        els.previewTools = document.getElementById('qfb-preview-tools');
        els.myLibraryList = document.getElementById('qfb-my-library-list');
        els.libraryList = document.getElementById('qfb-library-list');
        els.pageList = document.getElementById('qfb-page-list');
        els.settings = document.getElementById('qfb-settings');
        els.aiPane = document.getElementById('qfb-ai-pane');
        els.aiToggle = document.getElementById('qfb-ai-toggle');
        els.rightTitle = document.getElementById('qfb-right-title');
        els.rightDesc = document.getElementById('qfb-right-desc');
    }

    function isQilingShopSource() {
        return String(builderData.dataSource || '') === 'qilingshop';
    }

    function canUseLivePreview() {
        if (!state.wrapperParent || !state.endMarker) {
            return false;
        }
        if (!isQilingShopSource()) {
            return true;
        }
        if (!state.modules.length) {
            return true;
        }
        return state.domWrappers.length === state.modules.length;
    }

    function initWrappers() {
        var wrappers = [];

        if (isQilingShopSource()) {
            var shopContainer = document.querySelector('.qls-shop-wrapper .qls-container');
            if (shopContainer) {
                wrappers = Array.prototype.filter.call(shopContainer.children, function(node) {
                    if (!node || node.nodeType !== 1 || !node.classList) {
                        return false;
                    }
                    return node.classList.contains('qls-module') || node.classList.contains('qls-module-hero-carousel');
                });

                if (state.modules.length && wrappers.length > state.modules.length) {
                    wrappers = wrappers.slice(0, state.modules.length);
                }
                wrappers.forEach(function(node) {
                    node.classList.add('qiling-builder-module');
                });
            }
        }

        if (!wrappers.length) {
            wrappers = Array.prototype.slice.call(document.querySelectorAll('.qiling-builder-module'));
        }

        state.domWrappers = wrappers;
        var startAnchor = document.getElementById('qiling-builder-start');
        var endAnchor = document.getElementById('qiling-builder-end');

        if (startAnchor && endAnchor && startAnchor.parentNode && startAnchor.parentNode === endAnchor.parentNode) {
            state.wrapperParent = startAnchor.parentNode;
            state.startMarker = startAnchor;
            state.endMarker = endAnchor;
            refreshWrapperMeta();
            return;
        }

        var parent = wrappers.length ? wrappers[0].parentNode : null;
        if (wrappers.length && parent) {
            var sameParent = wrappers.every(function(node) {
                return node && node.parentNode === parent;
            });
            if (!sameParent) {
                parent = null;
            }
        }

        if (parent) {
            state.wrapperParent = parent;
            state.startMarker = document.createComment('qfb-start');
            state.endMarker = document.createComment('qfb-end');
            parent.insertBefore(state.startMarker, wrappers[0]);
            parent.insertBefore(state.endMarker, wrappers[wrappers.length - 1].nextSibling);
            refreshWrapperMeta();
            return;
        }

        var fallbackParent =
            document.querySelector('article.page-content > .container') ||
            document.querySelector('.page-content .container') ||
            document.querySelector('main#primary') ||
            document.querySelector('#primary') ||
            document.querySelector('.site-main') ||
            document.getElementById('page');

        if (!fallbackParent) {
            return;
        }

        state.wrapperParent = fallbackParent;
        state.startMarker = document.createComment('qfb-start');
        state.endMarker = document.createComment('qfb-end');
        fallbackParent.appendChild(state.startMarker);
        fallbackParent.appendChild(state.endMarker);

        refreshWrapperMeta();
    }

    function refreshWrapperMeta() {
        for (var i = 0; i < state.domWrappers.length; i++) {
            var wrapper = state.domWrappers[i];
            if (!wrapper || !wrapper.setAttribute) {
                continue;
            }
            wrapper.setAttribute('data-builder-index', String(i));
            var module = state.modules[i];
            if (module && module.type) {
                wrapper.setAttribute('data-module-id', module.type);
            }
        }
    }

    function highlightSelectedWrapper() {
        var all = document.querySelectorAll('.qiling-builder-module.qfb-selected');
        Array.prototype.forEach.call(all, function(node) {
            node.classList.remove('qfb-selected');
        });

        if (state.selectedScope !== 'module') {
            return;
        }
        if (state.selectedIndex < 0 || state.selectedIndex >= state.domWrappers.length) {
            return;
        }
        var wrapper = state.domWrappers[state.selectedIndex];
        if (wrapper && wrapper.classList) {
            wrapper.classList.add('qfb-selected');
        }
    }

    function createPlaceholderWrapper(moduleId, moduleName) {
        if (!state.wrapperParent || !state.endMarker) {
            return null;
        }

        var placeholder = document.createElement('div');
        placeholder.className = 'module-wrapper qiling-builder-module qfb-module-placeholder';
        placeholder.setAttribute('data-module-id', moduleId);
        placeholder.innerHTML = '<div class="qfb-module-placeholder-inner"><strong>' +
            escapeHtml(moduleName) +
            '</strong> ' +
            escapeHtml(getText('placeholderSuffix', '（正在生成实时预览）')) +
            '</div>';

        state.wrapperParent.insertBefore(placeholder, state.endMarker);
        return placeholder;
    }

    function reflowDomWrappers() {
        if (!state.wrapperParent || !state.endMarker) {
            refreshWrapperMeta();
            return;
        }

        for (var i = 0; i < state.domWrappers.length; i++) {
            var wrapper = state.domWrappers[i];
            if (!wrapper || wrapper.parentNode !== state.wrapperParent) {
                continue;
            }
            state.wrapperParent.insertBefore(wrapper, state.endMarker);
        }
        refreshWrapperMeta();
        highlightSelectedWrapper();
    }

    function reorderArray(arr, order) {
        var result = [];
        for (var i = 0; i < order.length; i++) {
            result.push(arr[order[i]]);
        }
        return result;
    }

    function getAvailableModuleById(moduleId) {
        for (var i = 0; i < state.availableModules.length; i++) {
            if (state.availableModules[i].id === moduleId) {
                return state.availableModules[i];
            }
        }
        return null;
    }

    function getModuleGroupKey(item) {
        if (!item || typeof item !== 'object') {
            return 'general';
        }
        return String(item.group || item.category || 'general');
    }

    function getModuleGroupLabel(item) {
        if (!item || typeof item !== 'object') {
            return '';
        }
        return String(item.groupLabel || item.group || item.category || '');
    }

    function getModuleSearchText(item) {
        if (!item || typeof item !== 'object') {
            return '';
        }

        var parts = [
            String(item.name || item.id || ''),
            String(item.id || ''),
            getModuleGroupLabel(item)
        ];

        if (Array.isArray(item.keywords)) {
            item.keywords.forEach(function(keyword) {
                parts.push(String(keyword || ''));
            });
        }

        return parts.join(' ').toLowerCase();
    }

    function buildGroupHeaderHtml(groupKey, groupLabel, className) {
        return '<li class="' + escapeHtml(className || 'qfb-group-title') + '" data-group-key="' + escapeHtml(groupKey || 'general') + '">' +
            escapeHtml(groupLabel || groupKey || '') +
            '</li>';
    }

    function getLibraryGroups() {
        var groups = [];
        var seen = {};
        state.availableModules.forEach(function(item) {
            var key = getModuleGroupKey(item);
            if (seen[key]) {
                return;
            }
            seen[key] = true;
            groups.push({
                key: key,
                label: getModuleGroupLabel(item) || key
            });
        });
        return groups;
    }

    function renderLibraryFilters() {
        if (!els.libraryFilters) {
            return;
        }

        var groups = getLibraryGroups();
        if (!groups.length) {
            els.libraryFilters.innerHTML = '';
            return;
        }

        var html = '';
        html += '<button type="button" class="qfb-filter-chip' + (state.libraryGroupFilter === 'all' ? ' is-active' : '') + '" data-group-filter="all">' + escapeHtml(getText('libraryFilterAll', '全部')) + '</button>';
        groups.forEach(function(group) {
            html += '<button type="button" class="qfb-filter-chip' + (state.libraryGroupFilter === group.key ? ' is-active' : '') + '" data-group-filter="' + escapeHtml(group.key) + '">' + escapeHtml(group.label) + '</button>';
        });
        els.libraryFilters.innerHTML = html;
    }

    function renderDesignSystemSummary() {
        if (!els.designSummary) {
            return;
        }

        var payload = getDesignSystemPayload();
        var tokens = getDesignTokens();
        var componentStyles = getComponentStyles();
        if (!tokens || !Object.keys(tokens).length) {
            els.designSummary.innerHTML = '';
            return;
        }

        var presetLabel = payload.presetLabel || payload.preset || '';
        var swatches = [
            { label: getText('tokenPrimaryColor', '主色'), value: tokens.primary || getTokenValue('--color-primary') },
            { label: getText('tokenSecondaryColor', '辅助'), value: tokens.secondary || getTokenValue('--color-secondary') },
            { label: getText('tokenAccentColor', '点缀'), value: tokens.accent || getTokenValue('--color-accent') },
            { label: getText('tokenBackgroundColor', '背景'), value: tokens.surface_alt || getTokenValue('--color-surface-alt') }
        ];
        var html = '';
        html += '<div class="qfb-design-summary-head">';
        html += '<strong>' + escapeHtml(getText('designSummaryTitle', '全局设计')) + '</strong>';
        html += '<span>' + escapeHtml(presetLabel) + '</span>';
        html += '</div>';
        html += '<div class="qfb-design-swatches">';
        swatches.forEach(function(item) {
            if (!item.value) {
                return;
            }
            html += '<span class="qfb-design-swatch" title="' + escapeHtml(item.label + ' ' + item.value) + '">';
            html += '<i style="background:' + escapeHtml(item.value) + ';"></i>';
            html += '<b>' + escapeHtml(item.label) + '</b>';
            html += '</span>';
        });
        html += '</div>';
        if (componentStyles && Object.keys(componentStyles).length) {
            html += '<div class="qfb-design-swatches qfb-design-swatches-components">';
            [
                { label: getText('componentButtonLabel', '按钮'), value: componentStyles.button_bg || '' },
                { label: getText('componentCardLabel', '卡片'), value: componentStyles.card_bg || '' },
                { label: getText('componentFormLabel', '表单'), value: componentStyles.form_input_bg || '' },
                { label: getText('componentPostCardLabel', '文章卡片'), value: componentStyles.post_card_bg || '' }
            ].forEach(function(item) {
                if (!item.value) {
                    return;
                }
                html += '<span class="qfb-design-swatch" title="' + escapeHtml(item.label + ' ' + item.value) + '">';
                html += '<i style="background:' + escapeHtml(item.value) + ';"></i>';
                html += '<b>' + escapeHtml(item.label) + '</b>';
                html += '</span>';
            });
            html += '</div>';
        }
        els.designSummary.innerHTML = html;
    }

    function syncGroupedListHeaders(container, itemSelector, headerSelector) {
        if (!container) {
            return;
        }

        var visibleCountByGroup = {};
        var items = container.querySelectorAll(itemSelector);
        Array.prototype.forEach.call(items, function(item) {
            if (!item || item.style.display === 'none' || (item.classList && item.classList.contains('is-hidden'))) {
                return;
            }
            var groupKey = String(item.getAttribute('data-group-key') || 'general');
            visibleCountByGroup[groupKey] = (visibleCountByGroup[groupKey] || 0) + 1;
        });

        var headers = container.querySelectorAll(headerSelector);
        Array.prototype.forEach.call(headers, function(header) {
            var groupKey = String(header.getAttribute('data-group-key') || 'general');
            header.style.display = visibleCountByGroup[groupKey] ? '' : 'none';
        });
    }

    function getMyLibraryTemplateById(templateId) {
        for (var i = 0; i < state.myLibraryTemplates.length; i++) {
            var item = state.myLibraryTemplates[i];
            if (String(item.id) === String(templateId)) {
                return item;
            }
        }
        return null;
    }

    function buildLibraryCardHtml(item) {
        var moduleId = item.id || '';
        var groupKey = getModuleGroupKey(item);
        var searchText = getModuleSearchText(item);
        var html = '';
        html += '<li class="qfb-lib-item" data-group-key="' + escapeHtml(groupKey) + '" data-module-id="' + escapeHtml(moduleId) + '" data-module-name="' + escapeHtml(searchText) + '">';
        html += '<div class="qfb-lib-main">';
        html += '<span class="qfb-lib-name">' + escapeHtml(item.name || moduleId) + '</span>';
        html += '</div>';
        html += '<button type="button" class="button button-small qfb-lib-add">' + escapeHtml(getText('addAction', '添加')) + '</button>';
        html += '</li>';
        return html;
    }

    function buildMyLibraryCardHtml(item) {
        var moduleType = item.type || '';
        var html = '';
        html += '<li class="qfb-lib-item qfb-lib-template" data-template-id="' + escapeHtml(item.id) + '" data-module-id="' + escapeHtml(moduleType) + '">';
        html += '<div class="qfb-lib-main">';
        html += '<span class="qfb-lib-name">' + escapeHtml(item.title || moduleType) + '</span>';
        html += '</div>';
        html += '<button type="button" class="button button-small qfb-template-add">' + escapeHtml(getText('addAction', '添加')) + '</button>';
        html += '</li>';
        return html;
    }

    function renderMyLibrary() {
        if (!els.myLibraryList) {
            return;
        }
        if (!state.myLibraryTemplates.length) {
            els.myLibraryList.innerHTML = '<li class="qfb-empty qfb-my-library-empty">' + escapeHtml(getText('myLibraryEmpty', '后台“我的模版库”暂无数据，请先在后台页面装修中保存模版。')) + '</li>';
            return;
        }

        var html = '';
        state.myLibraryTemplates.forEach(function(item) {
            html += buildMyLibraryCardHtml(item);
        });
        els.myLibraryList.innerHTML = html;
    }

    function renderLibrary() {
        if (!els.libraryList) {
            return;
        }

        if (!state.availableModules.length) {
            els.libraryList.innerHTML = '<li class="qfb-empty qfb-lib-search-empty">' + escapeHtml(getText('libraryEmpty', '模块库为空，请检查模块是否正常加载。')) + '</li>';
            renderLibraryFilters();
            return;
        }

        var html = '';
        var currentGroupKey = '';
        for (var i = 0; i < state.availableModules.length; i++) {
            var item = state.availableModules[i];
            var groupKey = getModuleGroupKey(item);
            var groupLabel = getModuleGroupLabel(item);
            if (groupKey !== currentGroupKey) {
                html += buildGroupHeaderHtml(groupKey, groupLabel || groupKey, 'qfb-lib-group-title');
                currentGroupKey = groupKey;
            }
            html += buildLibraryCardHtml(item);
        }
        els.libraryList.innerHTML = html;
        renderLibraryFilters();
        applyLibrarySearch(els.librarySearch ? els.librarySearch.value : '');
    }

    function applyLibrarySearch(keyword) {
        if (!els.libraryList) {
            return;
        }
        var search = String(keyword || '').toLowerCase();
        var emptyTip = els.libraryList.querySelector('.qfb-lib-search-empty');
        if (emptyTip) {
            emptyTip.parentNode.removeChild(emptyTip);
        }
        var items = els.libraryList.querySelectorAll('.qfb-lib-item');
        var hitCount = 0;
        Array.prototype.forEach.call(items, function(item) {
            var text = item.getAttribute('data-module-name') || '';
            var groupKey = item.getAttribute('data-group-key') || 'general';
            var groupHit = state.libraryGroupFilter === 'all' || state.libraryGroupFilter === groupKey;
            var searchHit = search === '' || text.toLowerCase().indexOf(search) !== -1;
            var hit = groupHit && searchHit;
            item.classList.toggle('is-hidden', !hit);
            item.style.display = '';
            if (hit) {
                hitCount++;
            }
        });
        syncGroupedListHeaders(els.libraryList, '.qfb-lib-item', '.qfb-lib-group-title');

        if (search && hitCount === 0) {
            els.libraryList.insertAdjacentHTML('beforeend', '<li class="qfb-empty qfb-lib-search-empty">' + escapeHtml(getText('noLibraryMatch', '没有匹配的模块，请换个关键词。')) + '</li>');
        }
    }

    function renderPageList() {
        if (!els.pageList) {
            return;
        }

        var html = '<li class="qfb-page-item qfb-page-item-page' + (state.selectedScope === 'page' ? ' is-active' : '') + '" data-scope="page" data-sortable="0">';
        html += '<span class="qfb-drag qfb-drag-static">⚙</span>';
        html += '<span class="qfb-page-title">' + escapeHtml(getText('pageSettingsEntry', '页面设置')) + '</span>';
        html += '<div class="qfb-page-actions">';
        html += '<button type="button" class="button-link qfb-page-select">' + escapeHtml(getText('settingsAction', '设置')) + '</button>';
        html += '</div>';
        html += '</li>';

        if (!state.modules.length) {
            html += '<li class="qfb-empty">' + escapeHtml(getText('pageEmpty', '暂无模块，请先从左侧模块库添加。')) + '</li>';
            els.pageList.innerHTML = html;
            return;
        }

        for (var i = 0; i < state.modules.length; i++) {
            var item = state.modules[i];
            var activeClass = state.selectedScope === 'module' && i === state.selectedIndex ? ' is-active' : '';
            html += '<li class="qfb-page-item' + activeClass + '" data-index="' + i + '" data-scope="module" data-sortable="1">';
            html += '<span class="qfb-drag" title="' + escapeHtml(getText('dragSort', '拖拽排序')) + '">⋮⋮</span>';
            html += '<span class="qfb-page-title">' + escapeHtml(getModuleName(item.type)) + '</span>';
            html += '<div class="qfb-page-actions">';
            html += '<button type="button" class="button-link qfb-page-select">' + escapeHtml(getText('settingsAction', '设置')) + '</button>';
            html += '<button type="button" class="button-link qfb-page-duplicate">' + escapeHtml(getText('duplicateAction', '复制')) + '</button>';
            html += '<button type="button" class="button-link-delete qfb-page-delete">' + escapeHtml(getText('deleteAction', '删除')) + '</button>';
            html += '</div>';
            html += '</li>';
        }
        els.pageList.innerHTML = html;
        initSortable();
    }

    function initSortable() {
        if (!els.pageList || !$.fn.sortable || !state.modules.length) {
            return;
        }

        var $list = $(els.pageList);
        if ($list.data('ui-sortable')) {
            $list.sortable('destroy');
        }

        $list.sortable({
            items: '.qfb-page-item[data-sortable="1"]',
            handle: '.qfb-drag',
            axis: 'y',
            update: function() {
                var order = [];
                $list.children('.qfb-page-item').each(function() {
                    if (this.getAttribute('data-sortable') !== '1') {
                        return;
                    }
                    order.push(parseInt(this.getAttribute('data-index'), 10));
                });

                if (order.length !== state.modules.length) {
                    return;
                }

                var selectedOld = state.selectedIndex;
                state.modules = reorderArray(state.modules, order);
                state.domWrappers = reorderArray(state.domWrappers, order);
                state.selectedIndex = selectedOld >= 0 ? order.indexOf(selectedOld) : -1;

                reflowDomWrappers();
                markDirty();
                renderPageList();
                renderSettings();
                queuePreviewRender(true);
            }
        });
    }

    function fetchModuleSchema(moduleId) {
        if (state.schemaCache[moduleId]) {
            return $.Deferred().resolve(state.schemaCache[moduleId]).promise();
        }

        return $.ajax({
            url: builderData.ajaxUrl,
            method: 'POST',
            dataType: 'json',
            cache: false,
            headers: {
                'Cache-Control': 'no-cache, no-store, must-revalidate',
                'Pragma': 'no-cache'
            },
            data: {
                action: 'qiling_frontend_builder_get_schema',
                nonce: builderData.nonce,
                post_id: builderData.postId,
                module_id: moduleId,
                _t: Date.now()
            }
        }).then(function(response) {
            if (!response || !response.success || !response.data) {
                return $.Deferred().reject(response).promise();
            }
            state.schemaCache[moduleId] = response.data;
            return response.data;
        });
    }

    function extractPreviewHtml(payload) {
        var text = String(payload || '');
        var trimmed = text.trim();
        if (trimmed.charAt(0) === '{') {
            try {
                var parsed = JSON.parse(trimmed);
                if (parsed && parsed.success && parsed.data && parsed.data.html) {
                    return String(parsed.data.html);
                }
            } catch (e) {
                return text;
            }
        }
        return text;
    }

    function extractPreviewWrappers(html) {
        var holder = document.createElement('div');
        holder.innerHTML = String(html || '');
        var wrappers = Array.prototype.slice.call(holder.querySelectorAll('.qiling-builder-module'));
        if (!wrappers.length && holder.firstElementChild) {
            var first = holder.firstElementChild;
            if (first.classList) {
                first.classList.add('qiling-builder-module');
                wrappers = [first];
            }
        }
        return wrappers;
    }

    function replaceAllWrappers(newWrappers) {
        if (!state.wrapperParent || !state.endMarker) {
            return;
        }

        for (var i = 0; i < state.domWrappers.length; i++) {
            var old = state.domWrappers[i];
            if (old && old.parentNode === state.wrapperParent) {
                state.wrapperParent.removeChild(old);
            }
        }

        state.domWrappers = [];
        for (var j = 0; j < newWrappers.length; j++) {
            var wrapper = newWrappers[j];
            if (!wrapper || !wrapper.nodeType) {
                continue;
            }
            state.wrapperParent.insertBefore(wrapper, state.endMarker);
            state.domWrappers.push(wrapper);
        }

        executeScriptsInWrappers(state.domWrappers);
        reflowDomWrappers();
        initDynamicAosInWrappers(state.domWrappers);
        initDynamicStatCountersInWrappers(state.domWrappers);
        initDynamicVideoCoverHoverInWrappers(state.domWrappers);
    }

    function replaceWrapperAtIndex(index, newWrapper) {
        if (!state.wrapperParent || !state.endMarker || !newWrapper || !newWrapper.nodeType) {
            return;
        }

        index = parseInt(index, 10);
        if (Number.isNaN(index) || index < 0) {
            return;
        }

        var oldWrapper = state.domWrappers[index] || null;
        var insertBeforeNode = oldWrapper && oldWrapper.parentNode === state.wrapperParent
            ? oldWrapper
            : (state.domWrappers[index + 1] || state.endMarker);

        state.wrapperParent.insertBefore(newWrapper, insertBeforeNode || state.endMarker);

        if (oldWrapper && oldWrapper.parentNode === state.wrapperParent) {
            oldWrapper.parentNode.removeChild(oldWrapper);
        }

        state.domWrappers[index] = newWrapper;
        executeScriptsInWrappers([newWrapper]);
        reflowDomWrappers();
        initDynamicAosInWrappers([newWrapper]);
        initDynamicStatCountersInWrappers([newWrapper]);
        initDynamicVideoCoverHoverInWrappers([newWrapper]);
    }

    function initLoadedScriptsCache() {
        var existing = document.querySelectorAll('script[src]');
        Array.prototype.forEach.call(existing, function(script) {
            var src = script.getAttribute('src');
            if (src) {
                state.loadedExternalScripts[src] = true;
            }
        });

        var styles = document.querySelectorAll('link[rel="stylesheet"][href]');
        Array.prototype.forEach.call(styles, function(link) {
            var href = link.getAttribute('href');
            if (href) {
                state.loadedExternalStyles[href] = true;
            }
        });
    }

    function getExternalAssetsConfig() {
        if (!builderData || !builderData.externalAssets || typeof builderData.externalAssets !== 'object') {
            return {};
        }
        return builderData.externalAssets;
    }

    function getExternalAssetUrls() {
        var config = getExternalAssetsConfig();
        if (!config.urls || typeof config.urls !== 'object') {
            return {};
        }
        return config.urls;
    }

    function getExternalModuleDependencies() {
        var config = getExternalAssetsConfig();
        if (!config.moduleDependencies || typeof config.moduleDependencies !== 'object') {
            return {};
        }
        return config.moduleDependencies;
    }

    function normalizeModuleType(moduleType) {
        return String(moduleType || '').trim().toLowerCase();
    }

    function isModuleTypeInDependencyList(moduleType, dependencyList) {
        if (!Array.isArray(dependencyList) || !dependencyList.length) {
            return false;
        }

        var normalized = normalizeModuleType(moduleType);
        if (!normalized) {
            return false;
        }

        for (var i = 0; i < dependencyList.length; i++) {
            if (normalizeModuleType(dependencyList[i]) === normalized) {
                return true;
            }
        }
        return false;
    }

    function moduleTypeNeedsAsset(moduleType, assetType) {
        var normalizedType = normalizeModuleType(moduleType);
        if (!normalizedType) {
            return false;
        }

        var dependencies = getExternalModuleDependencies();
        var dependencyList = dependencies && dependencies[assetType] ? dependencies[assetType] : [];
        if (isModuleTypeInDependencyList(normalizedType, dependencyList)) {
            return true;
        }
        return false;
    }

    function moduleNeedsSwiperByData(module) {
        if (!module || typeof module !== 'object') {
            return false;
        }

        var type = normalizeModuleType(module.type);
        if (!type) {
            return false;
        }

        var data = (module.data && typeof module.data === 'object') ? module.data : {};
        var items;
        var slides;
        var columns;

        switch (type) {
            case 'banner':
                var layout = String(data.banner_layout || 'slider');
                slides = Array.isArray(data.banner_slides) ? data.banner_slides : [];
                return layout !== 'image_text' && slides.length > 1;
            case 'products':
                items = Array.isArray(data.items) ? data.items : [];
                columns = parseInt(data.columns, 10);
                if (!Number.isFinite(columns) || columns < 1) {
                    columns = 4;
                }
                return items.length > columns;
            case 'hero_search':
                items = Array.isArray(data.hs_bg_items) ? data.hs_bg_items : [];
                return items.length > 1;
            case 'double_column_carousel':
                slides = Array.isArray(data.dcc_slides) ? data.dcc_slides : [];
                return slides.length > 1;
            case 'product_showcase':
                items = Array.isArray(data.ps_media_items) ? data.ps_media_items : [];
                return items.length > 1;
            case 'qiling_shop_showcase':
            case 'tabbed_carousel':
                return true;
            default:
                return true;
        }
    }

    function collectRequiredExternalAssets(modules) {
        var required = {
            swiper: false,
            chart: false
        };

        if (!Array.isArray(modules) || !modules.length) {
            return required;
        }

        for (var i = 0; i < modules.length; i++) {
            var module = modules[i];
            if (!module || typeof module !== 'object') {
                continue;
            }
            var moduleType = module.type;
            if (!required.swiper && moduleTypeNeedsAsset(moduleType, 'swiper')) {
                required.swiper = moduleNeedsSwiperByData(module);
            }
            if (!required.chart && moduleTypeNeedsAsset(moduleType, 'chart')) {
                required.chart = true;
            }
            if (required.swiper && required.chart) {
                break;
            }
        }

        return required;
    }

    function loadExternalStyle(url, marker) {
        var href = String(url || '').trim();
        if (!href) {
            return Promise.resolve(false);
        }
        if (state.loadedExternalStyles[href]) {
            return Promise.resolve(true);
        }

        var existing = document.querySelector('link[rel="stylesheet"][href="' + href.replace(/"/g, '\\"') + '"]');
        if (existing) {
            state.loadedExternalStyles[href] = true;
            return Promise.resolve(true);
        }

        if (state.externalAssetPromises[href]) {
            return state.externalAssetPromises[href];
        }

        state.externalAssetPromises[href] = new Promise(function(resolve, reject) {
            var link = document.createElement('link');
            link.rel = 'stylesheet';
            link.href = href;
            if (marker) {
                link.setAttribute(marker, '1');
            }
            link.onload = function() {
                state.loadedExternalStyles[href] = true;
                resolve(true);
            };
            link.onerror = function() {
                delete state.externalAssetPromises[href];
                reject(new Error('style load failed'));
            };
            document.head.appendChild(link);
        });

        return state.externalAssetPromises[href];
    }

    function loadExternalScript(url, marker, globalName) {
        var src = String(url || '').trim();
        if (!src) {
            return Promise.resolve(false);
        }

        if (globalName && typeof window[globalName] !== 'undefined') {
            state.loadedExternalScripts[src] = true;
            return Promise.resolve(true);
        }

        if (state.loadedExternalScripts[src]) {
            if (!globalName || typeof window[globalName] !== 'undefined') {
                return Promise.resolve(true);
            }
            delete state.loadedExternalScripts[src];
        }

        if (state.externalAssetPromises[src]) {
            return state.externalAssetPromises[src];
        }

        state.externalAssetPromises[src] = new Promise(function(resolve, reject) {
            var script = document.createElement('script');
            script.src = src;
            script.defer = true;
            if (marker) {
                script.setAttribute(marker, '1');
            }
            script.onload = function() {
                state.loadedExternalScripts[src] = true;
                resolve(true);
            };
            script.onerror = function() {
                delete state.externalAssetPromises[src];
                reject(new Error('script load failed'));
            };
            document.head.appendChild(script);
        });

        return state.externalAssetPromises[src];
    }

    function ensureExternalAssetsForModules(modules) {
        var required = collectRequiredExternalAssets(modules);
        if (!required.swiper && !required.chart) {
            return Promise.resolve(required);
        }

        var urls = getExternalAssetUrls();
        var tasks = [];

        if (required.swiper) {
            tasks.push(loadExternalStyle(urls.swiperCss || '', 'data-qfb-swiper-style'));
            tasks.push(loadExternalScript(urls.swiperJs || '', 'data-qfb-swiper-script', 'Swiper'));
        }
        if (required.chart) {
            tasks.push(loadExternalScript(urls.chartJs || '', 'data-qfb-chart-script', 'Chart'));
        }

        if (!tasks.length) {
            return Promise.resolve(required);
        }

        return Promise.all(tasks).then(function() {
            return required;
        });
    }

    function copyScriptAttributes(from, to) {
        if (!from || !to || !from.attributes) {
            return;
        }
        for (var i = 0; i < from.attributes.length; i++) {
            var attr = from.attributes[i];
            if (!attr || !attr.name) {
                continue;
            }
            to.setAttribute(attr.name, attr.value);
        }
    }

    function shouldExecuteInlineScript(script) {
        if (!script) {
            return false;
        }
        var type = (script.getAttribute('type') || '').trim().toLowerCase();
        if (!type) {
            return true;
        }
        return type === 'text/javascript' || type === 'application/javascript' || type === 'module';
    }

    function isModuleInlineScript(script) {
        if (!script) {
            return false;
        }
        return (script.getAttribute('type') || '').trim().toLowerCase() === 'module';
    }

    function invokeListenerNow(listener, context, eventObj) {
        if (!listener) {
            return;
        }
        try {
            if (typeof listener === 'function') {
                listener.call(context, eventObj);
            } else if (listener && typeof listener.handleEvent === 'function') {
                listener.handleEvent.call(listener, eventObj);
            }
        } catch (e) {
            if (window.console && console.warn) {
                console.warn('qiling builder immediate listener error:', e);
            }
        }
    }

    function executeInlineScript(code) {
        if (!code || !String(code).trim()) {
            return;
        }

        var originalDocumentAdd = document.addEventListener;
        var originalWindowAdd = window.addEventListener;

        document.addEventListener = function(type, listener, options) {
            if (type === 'DOMContentLoaded') {
                invokeListenerNow(listener, document, new Event('DOMContentLoaded'));
                return;
            }
            return originalDocumentAdd.call(document, type, listener, options);
        };

        window.addEventListener = function(type, listener, options) {
            if (type === 'load') {
                invokeListenerNow(listener, window, new Event('load'));
                return;
            }
            return originalWindowAdd.call(window, type, listener, options);
        };

        try {
            // eslint-disable-next-line no-eval
            (0, eval)(String(code));
        } catch (e) {
            if (window.console && console.warn) {
                console.warn('qiling builder inline script execute failed:', e);
            }
        } finally {
            document.addEventListener = originalDocumentAdd;
            window.addEventListener = originalWindowAdd;
        }
    }

    function executeScriptElement(script) {
        if (!script || !script.parentNode) {
            return;
        }

        var src = script.getAttribute('src');
        if (src) {
            if (state.loadedExternalScripts[src]) {
                script.parentNode.removeChild(script);
                return;
            }

            var newScript = document.createElement('script');
            copyScriptAttributes(script, newScript);
            newScript.async = false;
            script.parentNode.replaceChild(newScript, script);
            state.loadedExternalScripts[src] = true;
            return;
        }

        if (!shouldExecuteInlineScript(script)) {
            return;
        }

        var code = script.textContent || script.text || '';
        if (isModuleInlineScript(script)) {
            var moduleScript = document.createElement('script');
            copyScriptAttributes(script, moduleScript);
            moduleScript.text = code;
            script.parentNode.replaceChild(moduleScript, script);
            return;
        }

        var marker = document.createComment('qfb-script-executed');
        script.parentNode.replaceChild(marker, script);
        executeInlineScript(code);
    }

    function executeScriptsInWrappers(wrappers) {
        if (!Array.isArray(wrappers) || !wrappers.length) {
            return;
        }
        wrappers.forEach(function(wrapper) {
            if (!wrapper || !wrapper.querySelectorAll) {
                return;
            }
            var scripts = wrapper.querySelectorAll('script');
            Array.prototype.forEach.call(scripts, function(script) {
                executeScriptElement(script);
            });
        });
    }

    function initDynamicAosInWrappers(wrappers) {
        if (!Array.isArray(wrappers) || !wrappers.length) {
            return;
        }

        var aosElements = [];
        wrappers.forEach(function(wrapper) {
            if (!wrapper || !wrapper.querySelectorAll) {
                return;
            }

            if (wrapper.hasAttribute && wrapper.hasAttribute('data-aos')) {
                aosElements.push(wrapper);
            }

            var children = wrapper.querySelectorAll('[data-aos]');
            Array.prototype.forEach.call(children, function(node) {
                aosElements.push(node);
            });
        });

        if (!aosElements.length) {
            return;
        }

        aosElements.forEach(function(el) {
            if (!el || !el.classList) {
                return;
            }
            el.classList.add('aos-init');
            el.classList.remove('aos-animate');
        });

        window.requestAnimationFrame(function() {
            aosElements.forEach(function(el) {
                if (!el || !el.classList) {
                    return;
                }
                el.classList.add('aos-animate');
            });
        });
    }

    function initDynamicStatCountersInWrappers(wrappers) {
        if (!Array.isArray(wrappers) || !wrappers.length) {
            return;
        }

        var counters = [];
        wrappers.forEach(function(wrapper) {
            if (!wrapper || !wrapper.querySelectorAll) {
                return;
            }
            var nodes = wrapper.querySelectorAll('.stat-number');
            Array.prototype.forEach.call(nodes, function(node) {
                counters.push(node);
            });
        });

        counters.forEach(function(el) {
            if (!el || el.getAttribute('data-qfb-counter-init') === '1') {
                return;
            }
            el.setAttribute('data-qfb-counter-init', '1');

            var raw = (el.textContent || '').replace(/[^0-9]/g, '');
            var target = parseInt(raw, 10);
            if (!target || Number.isNaN(target) || target <= 0) {
                return;
            }

            var duration = 1200;
            var startedAt = null;
            var suffix = '+';

            function tick(ts) {
                if (!startedAt) {
                    startedAt = ts;
                }
                var p = Math.min((ts - startedAt) / duration, 1);
                var current = Math.floor(target * p);
                el.innerHTML = String(current) + '<span class="stat-plus">' + suffix + '</span>';
                if (p < 1) {
                    window.requestAnimationFrame(tick);
                } else {
                    el.innerHTML = String(target) + '<span class="stat-plus">' + suffix + '</span>';
                }
            }

            window.requestAnimationFrame(tick);
        });
    }

    function initDynamicVideoCoverHoverInWrappers(wrappers) {
        if (!Array.isArray(wrappers) || !wrappers.length) {
            return;
        }

        var isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
        wrappers.forEach(function(wrapper) {
            if (!wrapper || !wrapper.querySelectorAll) {
                return;
            }

            var covers = wrapper.querySelectorAll('.post-video-cover');
            Array.prototype.forEach.call(covers, function(cover) {
                if (!cover || cover.getAttribute('data-qfb-video-init') === '1') {
                    return;
                }

                var video = cover.querySelector('.video-cover-player');
                if (!video) {
                    return;
                }
                var hasPosterImage = !!(video.getAttribute('poster') || cover.querySelector('.video-poster'));
                var previewTime = hasPosterImage ? 0 : 0.001;

                cover.setAttribute('data-qfb-video-init', '1');
                video.preload = hasPosterImage ? 'metadata' : 'auto';
                video.load();

                var restorePreviewFrame = function() {
                    if (previewTime <= 0) {
                        try {
                            video.currentTime = 0;
                        } catch (err) {}
                        return;
                    }

                    try {
                        if (video.currentTime !== previewTime) {
                            video.currentTime = previewTime;
                        }
                    } catch (err) {}
                };

                if (previewTime > 0) {
                    video.addEventListener('loadedmetadata', function() {
                        if (!cover.classList.contains('is-playing')) {
                            restorePreviewFrame();
                        }
                    });
                }

                if (isMobile) {
                    var isPlaying = false;
                    cover.addEventListener('click', function(e) {
                        if (!(e.target.closest('.video-play-overlay') || e.target.closest('.video-cover-overlay-link'))) {
                            return;
                        }
                        if (isPlaying) {
                            return;
                        }
                        e.preventDefault();
                        e.stopPropagation();
                        video.play().then(function() {
                            isPlaying = true;
                            cover.classList.add('is-playing');
                        }).catch(function() {});
                    });

                    video.addEventListener('ended', function() {
                        isPlaying = false;
                        cover.classList.remove('is-playing');
                        restorePreviewFrame();
                    });
                } else {
                    cover.addEventListener('mouseenter', function() {
                        video.play().catch(function() {});
                    });
                    cover.addEventListener('mouseleave', function() {
                        video.pause();
                        restorePreviewFrame();
                    });
                }

                if (previewTime > 0) {
                    restorePreviewFrame();
                }

                video.addEventListener('error', function() {
                    video.style.display = 'none';
                    cover.classList.remove('post-video-cover');
                });
            });
        });
    }

    function renderModulesPreview() {
        if (!canUseLivePreview()) {
            return;
        }

        syncVisibleRepeatersToSelectedModuleData();

        if (state.previewXhr && state.previewXhr.readyState !== 4) {
            state.previewXhr.abort();
        }

        var requestId = ++state.previewRequestSeq;
        var modulesForTransport = prepareModulesForTransport(state.modules);
        var validation = validateModulesForTransport(modulesForTransport);
        if (!validation.valid) {
            setStatus(validation.message, 'warning');
            return;
        }

        if (!modulesForTransport.length) {
            replaceAllWrappers([]);
            return;
        }

        ensureExternalAssetsForModules(modulesForTransport)
            .catch(function() {
                setStatus(getText('externalAssetLoadFailed', '模块依赖资源加载失败，部分预览效果可能不可用。'), 'warning');
                return false;
            })
            .then(function() {
                if (requestId !== state.previewRequestSeq) {
                    return;
                }

                state.previewXhr = $.ajax({
                    url: builderData.ajaxUrl,
                    method: 'POST',
                    dataType: 'text',
                    cache: false,
                    headers: {
                        'Cache-Control': 'no-cache, no-store, must-revalidate',
                        'Pragma': 'no-cache'
                    },
                    data: {
                        action: 'qiling_frontend_builder_render_preview',
                        nonce: builderData.nonce,
                        post_id: builderData.postId,
                        modules: JSON.stringify(modulesForTransport),
                        _t: Date.now()
                    }
                }).done(function(responseText) {
                    if (requestId !== state.previewRequestSeq) {
                        return;
                    }
                    var html = extractPreviewHtml(responseText);
                    var wrappers = extractPreviewWrappers(html);
                    if (modulesForTransport.length && !wrappers.length) {
                        setStatus(getText('previewFailed', '实时预览失败，请稍后重试'), 'warning');
                        return;
                    }
                    replaceAllWrappers(wrappers);
                }).fail(function(xhr, textStatus) {
                    if (textStatus === 'abort' || requestId !== state.previewRequestSeq) {
                        return;
                    }
                    setStatus(getText('previewFailed', '实时预览失败，请稍后重试'), 'warning');
                }).always(function() {
                    if (requestId === state.previewRequestSeq) {
                        state.previewXhr = null;
                    }
                });
            });
    }

    function renderModulePreview(index) {
        if (!canUseLivePreview()) {
            return;
        }

        index = parseInt(index, 10);
        if (Number.isNaN(index) || index < 0 || index >= state.modules.length) {
            return;
        }

        if (index === state.selectedIndex) {
            syncVisibleRepeatersToSelectedModuleData();
        }

        var currentModule = normalizeModuleForTransport(state.modules[index]);
        if (!currentModule || !currentModule.type) {
            return;
        }
        var validation = validateModulesForTransport([currentModule]);
        if (!validation.valid) {
            setStatus(validation.message, 'warning');
            return;
        }

        if (state.previewXhr && state.previewXhr.readyState !== 4) {
            state.previewXhr.abort();
        }

        var requestId = ++state.previewRequestSeq;

        ensureExternalAssetsForModules([currentModule])
            .catch(function() {
                setStatus(getText('externalAssetLoadFailed', '模块依赖资源加载失败，部分预览效果可能不可用。'), 'warning');
                return false;
            })
            .then(function() {
                if (requestId !== state.previewRequestSeq) {
                    return;
                }

                state.previewXhr = $.ajax({
                    url: builderData.ajaxUrl,
                    method: 'POST',
                    dataType: 'text',
                    cache: false,
                    headers: {
                        'Cache-Control': 'no-cache, no-store, must-revalidate',
                        'Pragma': 'no-cache'
                    },
                    data: {
                        action: 'qiling_frontend_builder_render_module_preview',
                        nonce: builderData.nonce,
                        post_id: builderData.postId,
                        module_id: currentModule.type,
                        module_data: JSON.stringify(currentModule.data || {}),
                        index: index,
                        _t: Date.now()
                    }
                }).done(function(responseText) {
                    if (requestId !== state.previewRequestSeq) {
                        return;
                    }
                    var html = extractPreviewHtml(responseText);
                    var wrappers = extractPreviewWrappers(html);
                    if (!wrappers.length) {
                        setStatus(getText('previewFailed', '实时预览失败，请稍后重试'), 'warning');
                        return;
                    }
                    replaceWrapperAtIndex(index, wrappers[0]);
                }).fail(function(xhr, textStatus) {
                    if (textStatus === 'abort' || requestId !== state.previewRequestSeq) {
                        return;
                    }
                    setStatus(getText('previewFailed', '实时预览失败，请稍后重试'), 'warning');
                }).always(function() {
                    if (requestId === state.previewRequestSeq) {
                        state.previewXhr = null;
                    }
                });
            });
    }

    function schedulePreview(callback, immediate) {
        if (!canUseLivePreview()) {
            return;
        }

        if (state.previewTimer) {
            window.clearTimeout(state.previewTimer);
            state.previewTimer = null;
        }

        if (immediate) {
            callback();
            return;
        }

        state.previewTimer = window.setTimeout(function() {
            state.previewTimer = null;
            callback();
        }, 280);
    }

    function queuePreviewRender(immediate) {
        schedulePreview(renderModulesPreview, immediate);
    }

    function queueModulePreviewRender(index, immediate) {
        schedulePreview(function() {
            renderModulePreview(index);
        }, immediate);
    }

    function addModule(moduleId) {
        fetchModuleSchema(moduleId).done(function(schema) {
            var defaultData = schema && schema.defaultData ? deepClone(schema.defaultData) : {};
            state.modules.push({
                type: moduleId,
                data: defaultData
            });

            var wrapper = createPlaceholderWrapper(moduleId, schema.name || moduleId);
            state.domWrappers.push(wrapper);
            reflowDomWrappers();

            state.selectedScope = 'module';
            state.selectedIndex = state.modules.length - 1;
            markDirty();
            renderPageList();
            renderSettings();
            queuePreviewRender(true);
        }).fail(function() {
            setStatus(getText('schemaLoadFailed', '模块配置加载失败'), 'error');
        });
    }

    function addTemplateFromLibrary(templateId) {
        return fetchLibraryTemplate(templateId).done(function(template) {
            if (!template || !template.type) {
                setStatus(getText('templateLoadFailed', '模板加载失败，请稍后重试'), 'error');
                return;
            }
            if (!getAvailableModuleById(template.type)) {
                setStatus(getText('templateTypeMissing', '该模版对应的模块已不存在，无法添加。'), 'error');
                return;
            }

            state.modules.push({
                type: template.type,
                data: template.data && typeof template.data === 'object' ? deepClone(template.data) : {}
            });

            var displayName = template.title || template.typeName || template.type;
            var wrapper = createPlaceholderWrapper(template.type, displayName);
            state.domWrappers.push(wrapper);
            reflowDomWrappers();

            state.selectedScope = 'module';
            state.selectedIndex = state.modules.length - 1;
            markDirty();
            renderPageList();
            renderSettings();
            queuePreviewRender(true);
        }).fail(function(xhr) {
            var message = xhr && xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message
                ? xhr.responseJSON.data.message
                : getText('templateLoadFailed', '模板加载失败，请稍后重试');
            setStatus(message, 'error');
        });
    }

    function fetchLibraryTemplate(templateId) {
        var cachedTemplate = getMyLibraryTemplateById(templateId);
        if (cachedTemplate && cachedTemplate.type && cachedTemplate.data && typeof cachedTemplate.data === 'object') {
            return $.Deferred().resolve(cachedTemplate).promise();
        }

        var cacheKey = String(templateId || '');
        if (cacheKey && state.templateRequestCache[cacheKey]) {
            return state.templateRequestCache[cacheKey];
        }

        var deferred = $.Deferred();
        var request = $.ajax({
            url: builderData.ajaxUrl,
            method: 'POST',
            dataType: 'json',
            cache: false,
            data: {
                action: builderData.templateAction || 'qiling_frontend_builder_get_library_template',
                nonce: builderData.nonce,
                post_id: builderData.postId,
                template_id: templateId,
                _t: Date.now()
            }
        });

        request.done(function(response) {
            if (!response || !response.success || !response.data || !response.data.template) {
                deferred.reject(request);
                return;
            }

            var template = response.data.template;
            var existing = getMyLibraryTemplateById(templateId);
            if (existing) {
                existing.title = String(template.title || existing.title || '');
                existing.type = String(template.type || existing.type || '');
                existing.typeName = String(template.typeName || existing.typeName || existing.type || '');
                existing.data = template.data && typeof template.data === 'object' ? template.data : {};
                existing.date = String(template.date || existing.date || '');
                deferred.resolve(existing);
                return;
            }

            deferred.resolve({
                id: template.id,
                title: String(template.title || ''),
                type: String(template.type || ''),
                typeName: String(template.typeName || template.type || ''),
                data: template.data && typeof template.data === 'object' ? template.data : {},
                date: String(template.date || '')
            });
        }).fail(function(xhr) {
            deferred.reject(xhr);
        }).always(function() {
            if (cacheKey) {
                delete state.templateRequestCache[cacheKey];
            }
        });

        if (cacheKey) {
            state.templateRequestCache[cacheKey] = deferred.promise();
        }

        return deferred.promise();
    }

    function duplicateModule(index) {
        if (index < 0 || index >= state.modules.length) {
            return;
        }
        var copy = deepClone(state.modules[index]);
        state.modules.splice(index + 1, 0, copy);

        var originalWrapper = state.domWrappers[index] || null;
        var newWrapper = null;
        if (originalWrapper && originalWrapper.cloneNode) {
            newWrapper = originalWrapper.cloneNode(true);
            newWrapper.classList.add('qfb-module-placeholder');
            if (state.wrapperParent && state.endMarker) {
                state.wrapperParent.insertBefore(newWrapper, state.endMarker);
            }
        }
        state.domWrappers.splice(index + 1, 0, newWrapper);

        state.selectedScope = 'module';
        state.selectedIndex = index + 1;
        reflowDomWrappers();
        markDirty();
        renderPageList();
        renderSettings();
        queuePreviewRender(true);
    }

    function deleteModule(index) {
        if (index < 0 || index >= state.modules.length) {
            return;
        }

        state.modules.splice(index, 1);
        var wrapper = state.domWrappers.splice(index, 1)[0];
        if (wrapper && wrapper.parentNode) {
            wrapper.parentNode.removeChild(wrapper);
        }

        if (!state.modules.length) {
            state.selectedScope = 'page';
            state.selectedIndex = -1;
        } else if (state.selectedIndex >= state.modules.length) {
            state.selectedScope = 'module';
            state.selectedIndex = state.modules.length - 1;
        } else if (state.selectedIndex > index) {
            state.selectedIndex--;
        }

        reflowDomWrappers();
        markDirty();
        renderPageList();
        renderSettings();
        queuePreviewRender(true);
    }

    function getRepeaterSubFields(field) {
        if (!field || !Array.isArray(field.fields)) {
            return [];
        }
        return field.fields.filter(function(subField) {
            return subField && typeof subField === 'object' && (subField.id || ['header', 'heading', 'separator', 'info'].indexOf(subField.type || '') !== -1);
        });
    }

    function encodeBuilderData(value) {
        try {
            return encodeURIComponent(JSON.stringify(value || {}));
        } catch (e) {
            return encodeURIComponent('{}');
        }
    }

    function decodeBuilderData(value, fallback) {
        try {
            return JSON.parse(decodeURIComponent(value || ''));
        } catch (e) {
            return typeof fallback === 'undefined' ? {} : fallback;
        }
    }

    function fieldDependencyMatches(dependency, data) {
        if (!dependency || typeof dependency !== 'object') {
            return true;
        }
        var source = data && typeof data === 'object' ? data : {};
        var fieldId = '';
        var operator = '==';
        var expected = '';
        if (Array.isArray(dependency)) {
            fieldId = dependency[0] || '';
            operator = dependency.length > 2 ? dependency[1] : '==';
            expected = dependency.length > 2 ? dependency[2] : dependency[1];
        } else {
            fieldId = dependency.id || dependency.field || '';
            operator = dependency.operator || dependency.compare || '==';
            expected = Object.prototype.hasOwnProperty.call(dependency, 'value') ? dependency.value : dependency.values;
        }
        if (!fieldId) {
            return true;
        }
        var actual = normalizeFieldValue(source[fieldId]);
        var expectedValues = Array.isArray(expected) ? expected.map(normalizeFieldValue) : [normalizeFieldValue(expected)];
        var contains = expectedValues.indexOf(actual) !== -1;
        return operator === '!=' || operator === '!==' || operator === 'not_in' ? !contains : contains;
    }

    function refreshFieldDependencies(root, data) {
        if (!root) {
            return;
        }
        Array.prototype.forEach.call(root.querySelectorAll('[data-field-dependency]'), function(fieldEl) {
            var dependency = decodeBuilderData(fieldEl.getAttribute('data-field-dependency'), null);
            fieldEl.hidden = !fieldDependencyMatches(dependency, data);
        });
        var repeaterItems = Array.prototype.slice.call(root.querySelectorAll('.qfb-repeater-item'));
        if (root.matches && root.matches('.qfb-repeater-item')) {
            repeaterItems.unshift(root);
        }
        repeaterItems.forEach(function(itemEl) {
            var itemData = decodeBuilderData(itemEl.getAttribute('data-item-data'), {});
            Array.prototype.forEach.call(itemEl.querySelectorAll('[data-sub-field-dependency]'), function(fieldEl) {
                var dependency = decodeBuilderData(fieldEl.getAttribute('data-sub-field-dependency'), null);
                fieldEl.hidden = !fieldDependencyMatches(dependency, itemData);
            });
        });
    }

    function buildFieldDependencyData(fields, data) {
        var effective = data && typeof data === 'object' ? deepClone(data) : {};
        (Array.isArray(fields) ? fields : []).forEach(function(field) {
            if (!field || !field.id || Object.prototype.hasOwnProperty.call(effective, field.id)) {
                return;
            }
            if (Object.prototype.hasOwnProperty.call(field, 'default')) {
                effective[field.id] = field.default;
            } else if (field.type === 'repeater') {
                effective[field.id] = Array.isArray(field.default_items) ? field.default_items : [];
            }
        });
        return effective;
    }

    function getRepeaterDefaultItems(field, value) {
        if (Array.isArray(value)) {
            return value;
        }
        if (typeof value === 'string' && value.trim()) {
            try {
                var parsed = JSON.parse(value);
                if (Array.isArray(parsed)) {
                    return parsed;
                }
            } catch (e) {
                // ignore invalid legacy JSON
            }
        }
        if (field && Array.isArray(field.default_items)) {
            return field.default_items;
        }
        return [];
    }

    function renderRepeaterSubFieldControl(subField, subValue) {
        var subType = subField.type || 'text';
        var subId = subField.id || '';
        var subLabel = subField.label || subId;
        var subDescription = subField.description ? '<p class="qfb-field-desc">' + subField.description + '</p>' : '';
        var html = '';

        if (subType === 'header' || subType === 'heading' || subType === 'separator') {
            return '<div class="qfb-field-header qfb-field-header-sub">' + escapeHtml(subLabel) + '</div>';
        }
        if (subType === 'info') {
            return '<div class="qfb-field-info">' + (subField.description || '') + '</div>';
        }
        var dependencyAttr = subField.dependency ? ' data-sub-field-dependency="' + escapeHtml(encodeBuilderData(subField.dependency)) + '"' : '';
        html += '<div class="qfb-repeater-sub-field"' + dependencyAttr + '>';
        html += '<label class="qfb-label">' + escapeHtml(subLabel) + '</label>';

        if (subType === 'textarea' || subType === 'editor') {
            html += '<textarea class="qfb-repeater-input" data-sub-field-id="' + escapeHtml(subId) + '" data-sub-field-type="' + escapeHtml(subType) + '" rows="' + escapeHtml(subField.rows || '3') + '" placeholder="' + escapeHtml(subField.placeholder || '') + '">' + escapeHtml(normalizeFieldValue(subValue)) + '</textarea>';
        } else if (subType === 'number') {
            html += '<input class="qfb-repeater-input" type="number" data-sub-field-id="' + escapeHtml(subId) + '" data-sub-field-type="number" value="' + escapeHtml(normalizeFieldValue(subValue)) + '" min="' + escapeHtml(subField.min || '') + '" max="' + escapeHtml(subField.max || '') + '" step="' + escapeHtml(subField.step || '') + '" />';
        } else if (subType === 'date') {
            html += '<input class="qfb-repeater-input" type="date" data-sub-field-id="' + escapeHtml(subId) + '" data-sub-field-type="date" value="' + escapeHtml(normalizeFieldValue(subValue)) + '" />';
        } else if (subType === 'select') {
            var subOptions = subField.options || {};
            html += '<select class="qfb-repeater-input" data-sub-field-id="' + escapeHtml(subId) + '" data-sub-field-type="select">';
            Object.keys(subOptions).forEach(function(optKey) {
                var selected = normalizeFieldValue(subValue) === String(optKey) ? ' selected' : '';
                html += '<option value="' + escapeHtml(optKey) + '"' + selected + '>' + escapeHtml(subOptions[optKey]) + '</option>';
            });
            html += '</select>';
        } else if (subType === 'switcher') {
            var switcherValue = normalizeFieldValue(subValue);
            var switcherUsesNumeric = switcherValue === '1' || switcherValue === '0' || normalizeFieldValue(subField.default) === '1' || normalizeFieldValue(subField.default) === '0';
            var switcherOn = switcherUsesNumeric ? '1' : 'yes';
            var switcherOff = switcherUsesNumeric ? '0' : 'no';
            html += '<select class="qfb-repeater-input" data-sub-field-id="' + escapeHtml(subId) + '" data-sub-field-type="switcher">';
            html += '<option value="' + switcherOn + '"' + (switcherValue === switcherOn ? ' selected' : '') + '>' + escapeHtml(getText('switchYes', '是')) + '</option>';
            html += '<option value="' + switcherOff + '"' + (switcherValue === switcherOff ? ' selected' : '') + '>' + escapeHtml(getText('switchNo', '否')) + '</option>';
            html += '</select>';
        } else if (subType === 'range') {
            var rangeValue = normalizeFieldValue(subValue);
            var rangeMin = Object.prototype.hasOwnProperty.call(subField, 'min') ? normalizeFieldValue(subField.min) : '0';
            var rangeMax = Object.prototype.hasOwnProperty.call(subField, 'max') ? normalizeFieldValue(subField.max) : '100';
            var rangeStep = Object.prototype.hasOwnProperty.call(subField, 'step') ? normalizeFieldValue(subField.step) : '1';
            if (rangeValue === '') {
                rangeValue = rangeMin;
            }
            html += '<input class="qfb-repeater-input qfb-range-input" type="range" data-sub-field-id="' + escapeHtml(subId) + '" data-sub-field-type="range" min="' + escapeHtml(rangeMin) + '" max="' + escapeHtml(rangeMax) + '" step="' + escapeHtml(rangeStep) + '" value="' + escapeHtml(rangeValue) + '" />';
            html += '<div class="qfb-field-desc">' + escapeHtml(getText('currentValue', '当前值：')) + '<span class="qfb-range-value">' + escapeHtml(rangeValue) + '</span></div>';
        } else if (subType === 'checkbox') {
            var checked = subValue === '1' || subValue === 1 || subValue === true ? ' checked' : '';
            html += '<label class="qfb-checkbox"><input class="qfb-repeater-input" type="checkbox" data-sub-field-id="' + escapeHtml(subId) + '" data-sub-field-type="checkbox"' + checked + ' /> ' + escapeHtml(getText('enabledLabel', '启用')) + '</label>';
        } else if (subType === 'gallery') {
            html += '<textarea class="qfb-repeater-input" data-sub-field-id="' + escapeHtml(subId) + '" data-sub-field-type="gallery" rows="2" placeholder="' + escapeHtml(getText('galleryPlaceholder', '多个URL用英文逗号分隔')) + '">' + escapeHtml(normalizeFieldValue(subValue)) + '</textarea>';
        } else {
            html += '<input class="qfb-repeater-input" type="text" data-sub-field-id="' + escapeHtml(subId) + '" data-sub-field-type="' + escapeHtml(subType) + '" value="' + escapeHtml(normalizeFieldValue(subValue)) + '" placeholder="' + escapeHtml(subField.placeholder || '') + '" />';
        }

        html += renderDesignTokenPicker(subId, subType, 'repeater');
        html += subDescription;
        html += '</div>';

        return html;
    }

    function createRepeaterItemData(subFields) {
        var itemData = {};
        subFields.forEach(function(subField) {
            var subId = subField.id || '';
            if (!subId) {
                return;
            }
            if (Object.prototype.hasOwnProperty.call(subField, 'default')) {
                itemData[subId] = subField.default;
                return;
            }
            if (subField.type === 'checkbox') {
                itemData[subId] = '0';
                return;
            }
            itemData[subId] = '';
        });
        return itemData;
    }

    function renderRepeaterItem(fieldId, subFields, itemData, itemIndex) {
        var item = itemData && typeof itemData === 'object' ? itemData : {};
        subFields.forEach(function(subField) {
            if (subField && subField.id && !Object.prototype.hasOwnProperty.call(item, subField.id) && Object.prototype.hasOwnProperty.call(subField, 'default')) {
                item[subField.id] = subField.default;
            }
        });
        var html = '';
        html += '<div class="qfb-repeater-item" data-item-index="' + itemIndex + '" data-item-data="' + escapeHtml(encodeBuilderData(item)) + '">';
        html += '<div class="qfb-repeater-item-head">';
        html += '<span class="qfb-repeater-item-title">' + escapeHtml(getRepeaterItemTitle(itemIndex)) + '</span>';
        html += '<button type="button" class="button-link-delete qfb-repeater-remove" data-field-id="' + escapeHtml(fieldId) + '">' + escapeHtml(getText('deleteAction', '删除')) + '</button>';
        html += '</div>';
        html += '<div class="qfb-repeater-item-grid">';
        subFields.forEach(function(subField) {
            var subId = subField.id || '';
            var subValue = Object.prototype.hasOwnProperty.call(item, subId) ? item[subId] : (Object.prototype.hasOwnProperty.call(subField, 'default') ? subField.default : '');
            html += renderRepeaterSubFieldControl(subField, subValue);
        });
        html += '</div>';
        html += '</div>';
        return html;
    }

    function renderRepeaterField(field, value) {
        var fieldId = field.id || '';
        var subFields = getRepeaterSubFields(field);
        var items = getRepeaterDefaultItems(field, value);
        var encodedSubFields = '';
        try {
            encodedSubFields = encodeURIComponent(JSON.stringify(subFields));
        } catch (e) {
            encodedSubFields = encodeURIComponent('[]');
        }

        var html = '';
        html += '<div class="qfb-repeater" data-field-id="' + escapeHtml(fieldId) + '" data-sub-fields="' + escapeHtml(encodedSubFields) + '" data-max-items="' + escapeHtml(field.maxItems || field.max_items || '') + '">';
        html += '<div class="qfb-repeater-items">';

        if (!items.length) {
            html += '<div class="qfb-repeater-empty">' + escapeHtml(getText('repeaterEmpty', '暂无项目，请点击“添加项目”。')) + '</div>';
        } else {
            for (var i = 0; i < items.length; i++) {
                html += renderRepeaterItem(fieldId, subFields, items[i], i);
            }
        }

        html += '</div>';
        html += '<div class="qfb-repeater-actions">';
        html += '<button type="button" class="button button-small qfb-repeater-add" data-field-id="' + escapeHtml(fieldId) + '">' + escapeHtml(field.add_button || getText('repeaterAdd', '添加项目')) + '</button>';
        html += '</div>';
        html += '</div>';
        return html;
    }

    function parseRepeaterSubFields(repeaterEl) {
        if (!repeaterEl) {
            return [];
        }
        var encoded = repeaterEl.getAttribute('data-sub-fields') || '';
        if (!encoded) {
            return [];
        }
        try {
            var parsed = JSON.parse(decodeURIComponent(encoded));
            return Array.isArray(parsed) ? parsed : [];
        } catch (e) {
            return [];
        }
    }

    function refreshRepeaterItemIndexes(repeaterEl) {
        if (!repeaterEl) {
            return;
        }
        var items = repeaterEl.querySelectorAll('.qfb-repeater-item');
        Array.prototype.forEach.call(items, function(itemEl, index) {
            itemEl.setAttribute('data-item-index', String(index));
            var titleEl = itemEl.querySelector('.qfb-repeater-item-title');
            if (titleEl) {
                titleEl.textContent = getRepeaterItemTitle(index);
            }
        });
    }

    function collectRepeaterFieldValue(repeaterEl) {
        var items = [];
        if (!repeaterEl) {
            return items;
        }
        var itemEls = repeaterEl.querySelectorAll('.qfb-repeater-item');
        Array.prototype.forEach.call(itemEls, function(itemEl) {
            var itemData = decodeBuilderData(itemEl.getAttribute('data-item-data'), {});
            var inputs = itemEl.querySelectorAll('.qfb-repeater-input');
            Array.prototype.forEach.call(inputs, function(inputEl) {
                var subFieldId = inputEl.getAttribute('data-sub-field-id') || '';
                var subType = inputEl.getAttribute('data-sub-field-type') || 'text';
                if (!subFieldId) {
                    return;
                }
                if (subType === 'checkbox') {
                    itemData[subFieldId] = inputEl.checked ? '1' : '0';
                } else {
                    itemData[subFieldId] = inputEl.value;
                }
            });
            items.push(itemData);
        });
        return items;
    }

    function updateRepeaterFieldData(repeaterEl) {
        if (!repeaterEl || state.selectedIndex < 0 || state.selectedIndex >= state.modules.length) {
            return;
        }
        var fieldId = repeaterEl.getAttribute('data-field-id') || '';
        if (!fieldId) {
            return;
        }
        var selectedModule = state.modules[state.selectedIndex];
        if (!selectedModule.data || typeof selectedModule.data !== 'object') {
            selectedModule.data = {};
        }
        selectedModule.data[fieldId] = collectRepeaterFieldValue(repeaterEl);
        markDirty();
        queueModulePreviewRender(state.selectedIndex, false);
    }

    function syncVisibleRepeatersToSelectedModuleData() {
        if (!els.settings || state.selectedIndex < 0 || state.selectedIndex >= state.modules.length) {
            return;
        }

        var selectedModule = state.modules[state.selectedIndex];
        if (!selectedModule || !selectedModule.type) {
            return;
        }
        if (!selectedModule.data || typeof selectedModule.data !== 'object' || Array.isArray(selectedModule.data)) {
            selectedModule.data = {};
        }

        var repeaters = els.settings.querySelectorAll('.qfb-repeater');
        Array.prototype.forEach.call(repeaters, function(repeaterEl) {
            var fieldId = repeaterEl.getAttribute('data-field-id') || '';
            if (!fieldId) {
                return;
            }
            selectedModule.data[fieldId] = collectRepeaterFieldValue(repeaterEl);
        });

        if (selectedModule.type === 'banner') {
            selectedModule.data = normalizeBannerModuleData(selectedModule.data);
        }
    }

    function applyTokenChipValue(button) {
        if (!button) {
            return;
        }
        var value = button.getAttribute('data-token-value') || '';
        var picker = button.closest('.qfb-token-picker');
        if (!picker || !value) {
            return;
        }

        var fieldId = picker.getAttribute('data-token-field-id') || '';
        var scope = picker.getAttribute('data-token-scope') || 'field';
        var input = null;
        if (scope === 'repeater') {
            var subField = picker.closest('.qfb-repeater-sub-field');
            input = subField ? subField.querySelector('.qfb-repeater-input[data-sub-field-id="' + fieldId.replace(/"/g, '\\"') + '"]') : null;
        } else if (scope === 'advanced') {
            var advancedField = picker.closest('.qfb-field');
            input = advancedField ? advancedField.querySelector('.qfb-advanced-input[data-advanced-path="' + fieldId.replace(/"/g, '\\"') + '"]') : null;
        } else if (scope === 'page') {
            var pageField = picker.closest('.qfb-field');
            input = pageField ? pageField.querySelector('.qfb-page-setting-input[data-page-setting-path="' + fieldId.replace(/"/g, '\\"') + '"]') : null;
        } else {
            var field = picker.closest('.qfb-field');
            input = field ? field.querySelector('.qfb-input[data-field-id="' + fieldId.replace(/"/g, '\\"') + '"]') : null;
        }

        if (!input) {
            return;
        }

        input.value = value;
        if (scope === 'repeater') {
            var repeaterEl = input.closest('.qfb-repeater');
            if (repeaterEl) {
                updateRepeaterFieldData(repeaterEl);
            }
        } else {
            input.dispatchEvent(new Event('input', { bubbles: true }));
            input.dispatchEvent(new Event('change', { bubbles: true }));
        }
        setStatus(getText('tokenApplied', '已选择快捷颜色'), 'warning');
    }

    function getNestedValueByPath(source, path, fallback) {
        if (!source || typeof source !== 'object' || !path) {
            return typeof fallback === 'undefined' ? '' : fallback;
        }

        var current = source;
        var segments = String(path).split('.');
        for (var i = 0; i < segments.length; i++) {
            if (!current || typeof current !== 'object' || !Object.prototype.hasOwnProperty.call(current, segments[i])) {
                return typeof fallback === 'undefined' ? '' : fallback;
            }
            current = current[segments[i]];
        }

        return current;
    }

    function setNestedValueByPath(target, path, value) {
        if (!target || typeof target !== 'object' || !path) {
            return;
        }

        var segments = String(path).split('.');
        var current = target;
        for (var i = 0; i < segments.length; i++) {
            var key = segments[i];
            if (i === segments.length - 1) {
                current[key] = value;
                return;
            }

            if (!current[key] || typeof current[key] !== 'object' || Array.isArray(current[key])) {
                current[key] = {};
            }
            current = current[key];
        }
    }

    function getAdvancedInputValue(rootValue, path, fallback) {
        var value = getNestedValueByPath(rootValue, path, typeof fallback === 'undefined' ? '' : fallback);
        return normalizeFieldValue(value);
    }

    function advancedPathLooksLikeQuickColorTarget(path) {
        var normalized = String(path || '').toLowerCase();
        if (!normalized) {
            return false;
        }
        if (/(^|\.)(image|size|position|repeat|width|style|shadow|radius|line_height)(\.|$)/.test(normalized)) {
            return false;
        }
        return /(^|[_.-])(color|bg|background|border|accent|primary|secondary|title|text|muted)(_|[.-]|$)/.test(normalized);
    }

    function renderAdvancedTextControl(rootId, path, label, value, placeholder, description) {
        var html = '';
        html += '<div class="qfb-field">';
        html += '<label class="qfb-label">' + escapeHtml(label) + '</label>';
        html += '<input class="qfb-input qfb-advanced-input" type="text" data-advanced-root="' + escapeHtml(rootId) + '" data-advanced-path="' + escapeHtml(path) + '" value="' + escapeHtml(normalizeFieldValue(value)) + '" placeholder="' + escapeHtml(placeholder || '') + '" />';
        if (description) {
            html += '<p class="qfb-field-desc">' + escapeHtml(description) + '</p>';
        }
        if (advancedPathLooksLikeQuickColorTarget(path)) {
            html += renderDesignTokenPicker(path, 'color', 'advanced');
        }
        html += '</div>';
        return html;
    }

    function renderAdvancedSelectControl(rootId, path, label, value, options) {
        var html = '';
        var currentValue = normalizeFieldValue(value);
        html += '<div class="qfb-field">';
        html += '<label class="qfb-label">' + escapeHtml(label) + '</label>';
        html += '<select class="qfb-input qfb-advanced-input" data-advanced-root="' + escapeHtml(rootId) + '" data-advanced-path="' + escapeHtml(path) + '">';
        Object.keys(options || {}).forEach(function(optionValue) {
            var selected = currentValue === String(optionValue) ? ' selected' : '';
            html += '<option value="' + escapeHtml(optionValue) + '"' + selected + '>' + escapeHtml(options[optionValue]) + '</option>';
        });
        html += '</select>';
        html += '</div>';
        return html;
    }

    function renderAdvancedResponsiveTable(rootId, basePath, title, rows, rootValue, placeholder) {
        var html = '';
        var devices = [
            { key: 'desktop', label: getText('desktopLabel', '桌面') },
            { key: 'tablet', label: getText('tabletLabel', '平板') },
            { key: 'mobile', label: getText('mobileLabel', '手机') }
        ];

        html += '<div class="qfb-advanced-section">';
        html += '<div class="qfb-advanced-section-title">' + escapeHtml(title) + '</div>';
        html += '<table class="qfb-advanced-table"><thead><tr><th>' + escapeHtml(getText('directionLabel', '方向')) + '</th>';
        devices.forEach(function(device) {
            html += '<th>' + escapeHtml(device.label) + '</th>';
        });
        html += '</tr></thead><tbody>';
        Object.keys(rows || {}).forEach(function(rowKey) {
            html += '<tr><td>' + escapeHtml(rows[rowKey]) + '</td>';
            devices.forEach(function(device) {
                var path = rowKey === 'value' ? (basePath + '.' + device.key) : (basePath + '.' + rowKey + '.' + device.key);
                var inputValue = getAdvancedInputValue(rootValue, path, '');
                html += '<td><input class="qfb-input qfb-advanced-input" type="text" data-advanced-root="' + escapeHtml(rootId) + '" data-advanced-path="' + escapeHtml(path) + '" value="' + escapeHtml(inputValue) + '" placeholder="' + escapeHtml(placeholder || '') + '" /></td>';
            });
            html += '</tr>';
        });
        html += '</tbody></table></div>';
        return html;
    }

    function renderAdvancedTypographyBlock(rootId, basePath, title, rootValue) {
        var html = '';
        html += '<div class="qfb-advanced-section">';
        html += '<div class="qfb-advanced-section-title">' + escapeHtml(title) + '</div>';
        html += '<div class="qfb-advanced-grid">';
        html += renderAdvancedResponsiveTable(rootId, basePath + '.size', getText('fontSizeLabel', '字号'), {
            value: getText('fontSizeLabel', '字号')
        }, rootValue, '18px / clamp(...)');
        html += renderAdvancedTextControl(rootId, basePath + '.color', getText('colorLabel', '颜色'), getAdvancedInputValue(rootValue, basePath + '.color', ''), '#111827');
        html += renderAdvancedSelectControl(rootId, basePath + '.weight', getText('fontWeightLabel', '字重'), getAdvancedInputValue(rootValue, basePath + '.weight', ''), {
            '': getText('defaultOption', '默认'),
            '300': '300',
            '400': '400',
            '500': '500',
            '600': '600',
            '700': '700',
            '800': '800',
            '900': '900',
            'normal': 'normal',
            'bold': 'bold'
        });
        html += renderAdvancedTextControl(rootId, basePath + '.line_height', getText('lineHeightLabel', '行高'), getAdvancedInputValue(rootValue, basePath + '.line_height', ''), '1.6 / 28px');
        if (basePath === 'typography.button') {
            html += renderAdvancedTextControl(rootId, basePath + '.hover_color', getText('hoverTextColorLabel', '悬停文字颜色'), getAdvancedInputValue(rootValue, basePath + '.hover_color', ''), '#ffffff');
        }
        html += '</div>';
        html += '</div>';
        return html;
    }

    function renderAdvancedStyleField(value, capabilities) {
        var rootValue = value && typeof value === 'object' ? value : {};
        capabilities = capabilities && typeof capabilities === 'object' ? capabilities : {};
        var html = '';
        html += '<div class="qfb-advanced-inline-note">' + escapeHtml(getText('advancedStyleTip', '这是模块样式的统一入口；旧公共样式已经并到这里，后续直接在这里调整就行。')) + '</div>';
        if (capabilities.title) html += renderAdvancedTypographyBlock('_ds_style', 'typography.title', getText('titleTypographyLabel', '模块主标题排版'), rootValue);
        if (capabilities.subtitle) html += renderAdvancedTypographyBlock('_ds_style', 'typography.subtitle', getText('subtitleTypographyLabel', '模块副标题排版'), rootValue);
        if (capabilities.text) html += renderAdvancedTypographyBlock('_ds_style', 'typography.text', getText('textTypographyLabel', '模块正文排版'), rootValue);
        if (capabilities.buttons) html += renderAdvancedTypographyBlock('_ds_style', 'typography.button', getText('buttonTypographyLabel', '模块行动按钮文字排版'), rootValue);
        html += renderAdvancedResponsiveTable('_ds_style', 'spacing.margin', getText('marginLabel', '外边距'), {
            top: getText('topLabel', '上'),
            right: getText('rightLabel', '右'),
            bottom: getText('bottomLabel', '下'),
            left: getText('leftLabel', '左')
        }, rootValue, '32px / 2rem / clamp(...)');
        html += renderAdvancedResponsiveTable('_ds_style', 'spacing.padding', getText('paddingLabel', '内边距'), {
            top: getText('topLabel', '上'),
            right: getText('rightLabel', '右'),
            bottom: getText('bottomLabel', '下'),
            left: getText('leftLabel', '左')
        }, rootValue, '24px / 4vw / clamp(...)');
        html += '<div class="qfb-advanced-section"><div class="qfb-advanced-section-title">' + escapeHtml(getText('backgroundBorderLabel', '背景与边框')) + '</div><div class="qfb-advanced-grid">';
        html += renderAdvancedTextControl('_ds_style', 'background.color', getText('backgroundColorLabel', '背景颜色 / 渐变'), getAdvancedInputValue(rootValue, 'background.color', ''), '#ffffff / linear-gradient(...)');
        html += renderAdvancedTextControl('_ds_style', 'background.image', getText('backgroundImageLabel', '背景图片 URL'), getAdvancedInputValue(rootValue, 'background.image', ''), 'https://');
        html += renderAdvancedSelectControl('_ds_style', 'background.size', getText('backgroundSizeLabel', '背景尺寸'), getAdvancedInputValue(rootValue, 'background.size', ''), {
            '': getText('defaultOption', '默认'),
            'cover': 'cover',
            'contain': 'contain',
            'auto': 'auto',
            '100% 100%': '100% 100%'
        });
        html += renderAdvancedSelectControl('_ds_style', 'background.position', getText('backgroundPositionLabel', '背景位置'), getAdvancedInputValue(rootValue, 'background.position', ''), {
            '': getText('defaultOption', '默认'),
            'center center': getText('bgCenter', '居中'),
            'top center': getText('bgTopCenter', '顶部居中'),
            'bottom center': getText('bgBottomCenter', '底部居中'),
            'center left': getText('bgLeftCenter', '左侧居中'),
            'center right': getText('bgRightCenter', '右侧居中')
        });
        html += renderAdvancedSelectControl('_ds_style', 'background.repeat', getText('backgroundRepeatLabel', '背景重复'), getAdvancedInputValue(rootValue, 'background.repeat', ''), {
            '': getText('defaultOption', '默认'),
            'no-repeat': 'no-repeat',
            'repeat': 'repeat',
            'repeat-x': 'repeat-x',
            'repeat-y': 'repeat-y'
        });
        html += renderAdvancedTextControl('_ds_style', 'border.width', getText('borderWidthLabel', '边框宽度'), getAdvancedInputValue(rootValue, 'border.width', ''), '1px');
        html += renderAdvancedSelectControl('_ds_style', 'border.style', getText('borderStyleLabel', '边框样式'), getAdvancedInputValue(rootValue, 'border.style', ''), {
            '': getText('defaultOption', '默认'),
            'solid': 'solid',
            'dashed': 'dashed',
            'dotted': 'dotted',
            'double': 'double'
        });
        html += renderAdvancedTextControl('_ds_style', 'border.color', getText('borderColorLabel', '边框颜色'), getAdvancedInputValue(rootValue, 'border.color', ''), '#e5e7eb');
        html += '</div></div>';
        html += renderAdvancedResponsiveTable('_ds_style', 'radius', getText('radiusLabel', '圆角'), {
            value: getText('radiusValueLabel', '半径')
        }, rootValue, '24px / 1.5rem');
        html += '<div class="qfb-advanced-section"><div class="qfb-advanced-section-title">' + escapeHtml(getText('shadowStateLabel', '阴影与状态')) + '</div><div class="qfb-advanced-grid">';
        html += renderAdvancedTextControl('_ds_style', 'shadow.default', getText('defaultShadowLabel', '默认阴影'), getAdvancedInputValue(rootValue, 'shadow.default', ''), '0 18px 48px rgba(15,23,42,.12)');
        html += renderAdvancedTextControl('_ds_style', 'shadow.hover', getText('hoverShadowLabel', '悬停阴影'), getAdvancedInputValue(rootValue, 'shadow.hover', ''), '0 24px 60px rgba(15,23,42,.18)');
        html += renderAdvancedTextControl('_ds_style', 'state.hover.background_color', getText('hoverBackgroundLabel', '悬停背景色'), getAdvancedInputValue(rootValue, 'state.hover.background_color', ''), '#111827');
        html += renderAdvancedTextControl('_ds_style', 'state.hover.border_color', getText('hoverBorderLabel', '悬停边框色'), getAdvancedInputValue(rootValue, 'state.hover.border_color', ''), '#0f172a');
        html += renderAdvancedTextControl('_ds_style', 'state.hover.title_color', getText('hoverTitleLabel', '悬停标题色'), getAdvancedInputValue(rootValue, 'state.hover.title_color', ''), '#ffffff');
        html += '</div></div>';
        return html;
    }

    function renderAdvancedVisibilityField(value) {
        var rootValue = value && typeof value === 'object' ? value : {};
        var html = '';
        html += '<div class="qfb-advanced-inline-note">' + escapeHtml(getText('advancedVisibilityTip', '按设备控制当前模块显示状态。')) + '</div>';
        html += '<div class="qfb-advanced-section"><div class="qfb-advanced-grid">';
        html += renderAdvancedSelectControl('_ds_visibility', 'desktop', getText('desktopLabel', '桌面端'), getAdvancedInputValue(rootValue, 'desktop', ''), {
            '': getText('inheritVisibleOption', '默认显示'),
            '1': getText('showOption', '显示'),
            '0': getText('hideOption', '隐藏')
        });
        html += renderAdvancedSelectControl('_ds_visibility', 'tablet', getText('tabletLabel', '平板端'), getAdvancedInputValue(rootValue, 'tablet', ''), {
            '': getText('inheritVisibleOption', '默认显示'),
            '1': getText('showOption', '显示'),
            '0': getText('hideOption', '隐藏')
        });
        html += renderAdvancedSelectControl('_ds_visibility', 'mobile', getText('mobileLabel', '手机端'), getAdvancedInputValue(rootValue, 'mobile', ''), {
            '': getText('inheritVisibleOption', '默认显示'),
            '1': getText('showOption', '显示'),
            '0': getText('hideOption', '隐藏')
        });
        html += '</div></div>';
        return html;
    }

    function getModuleVisualFieldGroups(field) {
        return field && field.groups && typeof field.groups === 'object' ? field.groups : {};
    }

    function getModuleVisualModeOptions() {
        return {
            follow: getText('moduleVisualModeFollow', '跟随页面风格'),
            light: getText('moduleVisualModeLight', '使用浅色模块'),
            dark: getText('moduleVisualModeDark', '使用深色模块'),
            accent: getText('moduleVisualModeAccent', '使用强调色模块'),
            custom: getText('moduleVisualModeCustom', '自定义')
        };
    }

    function moduleVisualHasManualValue(value, key) {
        if (value === null || typeof value === 'undefined') {
            return false;
        }

        if (Array.isArray(value)) {
            return value.some(function(item) {
                return moduleVisualHasManualValue(item, '');
            });
        }

        if (typeof value === 'object') {
            return Object.keys(value).some(function(childKey) {
                return moduleVisualHasManualValue(value[childKey], childKey);
            });
        }

        var normalized = normalizeFieldValue(value).trim();
        if (normalized === '') {
            return false;
        }
        if (key === 'inherit_page') {
            return false;
        }
        if (key === 'mode') {
            return false;
        }

        return true;
    }

    function getModuleVisualMode(rootValue) {
        var payload = rootValue && typeof rootValue === 'object' ? rootValue : {};
        var mode = getAdvancedInputValue(payload, 'base.mode', '');
        if (['follow', 'light', 'dark', 'accent', 'custom'].indexOf(mode) !== -1) {
            return mode;
        }

        return moduleVisualHasManualValue(payload, '') ? 'custom' : 'follow';
    }

    function buildModuleVisualSimplePayload(modeKey, existingPayload) {
        var mode = ['follow', 'light', 'dark', 'accent', 'custom'].indexOf(modeKey) !== -1 ? modeKey : 'follow';
        if (mode === 'follow') {
            return null;
        }

        var payload = mode === 'custom' && existingPayload && typeof existingPayload === 'object'
            ? deepClone(existingPayload)
            : {};
        if (!payload.base || typeof payload.base !== 'object' || Array.isArray(payload.base)) {
            payload.base = {};
        }
        payload.base.mode = mode;
        payload.base.inherit_page = mode === 'custom' ? '0' : '1';

        return payload;
    }

    function getPageVisualMappedValue(path, fallback) {
        var settings = normalizePageVisualStyleSettings((ensurePageSettingsState().visualStyle || {}));
        var value = getAdvancedInputValue(settings, path, '');
        if (value) {
            return value;
        }

        var fieldMap = {
            'colors.primary': ['--qiling-page-primary', '--qiling-page-accent', '--color-primary'],
            'colors.accent': ['--qiling-page-accent-2', '--qiling-page-accent', '--color-secondary'],
            'colors.background': ['--qiling-page-bg', '--qiling-page-background', '--color-background'],
            'colors.text': ['--qiling-page-text', '--color-text'],
            'buttons.background': ['--qiling-button-bg', '--qiling-page-primary', '--color-primary'],
            'buttons.text': ['--qiling-button-text'],
            'buttons.hover_background': ['--qiling-button-hover-bg', '--qiling-page-accent-2', '--color-primary-dark'],
            'buttons.hover_text': ['--qiling-button-hover-text']
        };
        var vars = getPageVisualPresetVars(settings.preset || '');
        var candidates = fieldMap[path] || [];
        for (var i = 0; i < candidates.length; i++) {
            var cssVar = candidates[i];
            if (cssVar && Object.prototype.hasOwnProperty.call(vars, cssVar)) {
                return normalizeFieldValue(vars[cssVar]);
            }
        }

        return fallback || '';
    }

    function buildModuleVisualPayloadFromPage(existingPayload) {
        var payload = existingPayload && typeof existingPayload === 'object' ? deepClone(existingPayload) : {};
        var primary = getPageVisualMappedValue('colors.primary', 'var(--qiling-page-primary,var(--color-primary))');
        var accent = getPageVisualMappedValue('colors.accent', 'var(--qiling-page-accent-2,var(--qiling-page-accent,var(--color-secondary)))');
        var background = getPageVisualMappedValue('colors.background', 'var(--qiling-page-bg,#ffffff)');
        var buttonBg = getPageVisualMappedValue('buttons.background', primary);
        var buttonHoverBg = getPageVisualMappedValue('buttons.hover_background', accent);

        if (!payload.base || typeof payload.base !== 'object' || Array.isArray(payload.base)) {
            payload.base = {};
        }
        if (!payload.buttons || typeof payload.buttons !== 'object' || Array.isArray(payload.buttons)) {
            payload.buttons = {};
        }
        if (!payload.cards || typeof payload.cards !== 'object' || Array.isArray(payload.cards)) {
            payload.cards = {};
        }

        payload.base.mode = 'custom';
        payload.base.inherit_page = '0';
        payload.base.primary = primary;
        payload.base.accent = accent;
        payload.base.background = background;
        payload.buttons.background = buttonBg;
        payload.buttons.text = '#ffffff';
        payload.buttons.hover_background = buttonHoverBg;
        payload.buttons.hover_text = '#ffffff';
        payload.cards.background = 'var(--qiling-component-card-bg,#ffffff)';
        payload.cards.border = 'color-mix(in srgb,' + primary + ' 22%,transparent)';

        return payload;
    }

    function getSelectedModuleForModuleVisual() {
        if (state.selectedIndex < 0 || state.selectedIndex >= state.modules.length) {
            return null;
        }
        var selectedModule = state.modules[state.selectedIndex];
        if (!selectedModule) {
            return null;
        }
        if (!selectedModule.data || typeof selectedModule.data !== 'object') {
            selectedModule.data = {};
        }
        return selectedModule;
    }

    function commitModuleVisualPayload(payload, statusText, tone) {
        var selectedModule = getSelectedModuleForModuleVisual();
        if (!selectedModule) {
            return;
        }

        if (payload === null) {
            delete selectedModule.data._ds_visual;
        } else {
            selectedModule.data._ds_visual = payload;
        }

        markDirty();
        renderSettings();
        queueModulePreviewRender(state.selectedIndex, false);
        setStatus(statusText, tone || 'warning');
    }

    function applyModuleVisualMode(modeKey) {
        var selectedModule = getSelectedModuleForModuleVisual();
        if (!selectedModule) {
            return;
        }

        var payload = buildModuleVisualSimplePayload(modeKey, selectedModule.data._ds_visual);
        var options = getModuleVisualModeOptions();
        var label = options[modeKey] || options.follow;
        commitModuleVisualPayload(payload, getText('moduleVisualModeApplied', '模块视觉模式已切换为：') + label, 'warning');
    }

    function syncModuleVisualWithPagePrimary() {
        var selectedModule = getSelectedModuleForModuleVisual();
        if (!selectedModule) {
            return;
        }

        var payload = buildModuleVisualPayloadFromPage(selectedModule.data._ds_visual);
        commitModuleVisualPayload(payload, getText('moduleVisualSyncedPrimary', '已把页面主色同步到当前模块，保存后生效。'), 'warning');
    }

    function getModuleVisualAdvancedFields(groups) {
        var allowedPaths = {};
        Object.keys(groups || {}).forEach(function(groupKey) {
            var groupFields = groups[groupKey] && groups[groupKey].fields ? groups[groupKey].fields : {};
            Object.keys(groupFields).forEach(function(fieldKey) {
                allowedPaths[groupKey + '.' + fieldKey] = true;
            });
        });
        return [
            { path: 'base.primary', label: getText('moduleVisualAdvancedPrimary', '模块主色'), placeholder: 'var(--qiling-page-primary)', description: getText('moduleVisualAdvancedPrimaryDesc', '用于图标、标签、链接和局部高亮，不是标题文字颜色。') },
            { path: 'base.accent', label: getText('moduleVisualAdvancedAccent', '模块辅助色'), placeholder: 'var(--qiling-page-accent-2)', description: getText('moduleVisualAdvancedAccentDesc', '用于第二强调色和部分悬停状态。') },
            { path: 'base.background', label: getText('moduleVisualAdvancedBackground', '背景色'), placeholder: '#ffffff / linear-gradient(...)', description: getText('moduleVisualAdvancedBackgroundDesc', '控制整个当前模块区块的背景。') },
            { path: 'content.title', label: getText('moduleVisualAdvancedTitleColor', '标题颜色'), placeholder: 'var(--qiling-page-text)', description: getText('moduleVisualAdvancedTitleColorDesc', '控制当前模块主标题和兼容的标题链接。') },
            { path: 'content.subtitle', label: getText('moduleVisualAdvancedSubtitleColor', '副标题颜色'), placeholder: 'var(--color-text-muted)', description: getText('moduleVisualAdvancedSubtitleColorDesc', '控制主标题下方的副标题和说明。') },
            { path: 'content.text', label: getText('moduleVisualAdvancedTextColor', '正文颜色'), placeholder: 'var(--color-text)', description: getText('moduleVisualAdvancedTextColorDesc', '控制普通段落、列表和摘要文字。') },
            { path: 'buttons.background', label: getText('moduleVisualAdvancedButton', '按钮背景'), placeholder: 'var(--qiling-button-bg)', description: getText('moduleVisualAdvancedButtonDesc', '控制当前模块实心主按钮的背景。') },
            { path: 'buttons.text', label: getText('moduleVisualAdvancedButtonText', '按钮文字'), placeholder: '#ffffff', description: getText('moduleVisualAdvancedButtonTextDesc', '控制主按钮正常状态的文字颜色。') },
            { path: 'buttons.hover_background', label: getText('moduleVisualAdvancedButtonHover', '按钮悬停背景'), placeholder: 'var(--qiling-button-hover-bg)', description: getText('moduleVisualAdvancedButtonHoverDesc', '控制鼠标移到主按钮上时的背景。') },
            { path: 'buttons.hover_text', label: getText('moduleVisualAdvancedButtonHoverText', '按钮悬停文字'), placeholder: '#ffffff', description: getText('moduleVisualAdvancedButtonHoverTextDesc', '控制鼠标移到主按钮上时的文字颜色。') },
            { path: 'cards.background', label: getText('moduleVisualAdvancedCard', '卡片背景'), placeholder: '#ffffff', description: getText('moduleVisualAdvancedCardDesc', '控制当前模块内卡片的背景。') },
            { path: 'cards.border', label: getText('moduleVisualAdvancedCardBorder', '卡片边框'), placeholder: 'rgba(15,23,42,.1)', description: getText('moduleVisualAdvancedCardBorderDesc', '控制当前模块内卡片的边框。') }
        ].filter(function(item) {
            return allowedPaths[item.path] === true;
        });
    }

    function renderModuleVisualStyleField(value, field) {
        var rootValue = value && typeof value === 'object' ? value : {};
        var modeOptions = getModuleVisualModeOptions();
        var currentMode = getModuleVisualMode(rootValue);
        var isFollowing = currentMode === 'follow';
        var hasManualValues = moduleVisualHasManualValue(rootValue, '');
        var advancedFields = getModuleVisualAdvancedFields(field && field.groups ? field.groups : {});
        var html = '';

        html += '<div class="qfb-module-visual-panel">';
        html += '<div class="qfb-advanced-inline-note">' + escapeHtml(getText('moduleVisualTip', '只影响当前模块，优先级高于页面风格；留空字段继续继承页面或全站。')) + '</div>';
        html += '<div class="qfb-module-visual-summary">';
        var isCustomPending = currentMode === 'custom' && !hasManualValues;
        var statusClass = isFollowing ? 'is-following' : (isCustomPending ? 'is-pending' : 'is-custom');
        var statusText = isFollowing
            ? getText('moduleVisualStatusFollow', '当前模块跟随页面风格')
            : (isCustomPending
                ? getText('moduleVisualStatusPending', '已选择自定义，填写高级字段后生效')
                : getText('moduleVisualStatusOverride', '当前模块已覆盖页面风格'));
        html += '<span class="qfb-module-visual-status ' + statusClass + '">' + escapeHtml(statusText) + '</span>';
        if (hasManualValues && currentMode === 'custom') {
            html += '<span class="qfb-module-visual-hint">' + escapeHtml(getText('moduleVisualManualHint', '已使用高级设置')) + '</span>';
        }
        html += '</div>';
        html += '<div class="qfb-module-visual-modes" role="group" aria-label="' + escapeHtml(getText('moduleVisualModeGroup', '模块视觉简单模式')) + '">';
        Object.keys(modeOptions).forEach(function(modeKey) {
            html += '<button type="button" class="qfb-module-visual-mode' + (currentMode === modeKey ? ' is-active' : '') + '" data-qfb-module-visual-mode="' + escapeHtml(modeKey) + '">' + escapeHtml(modeOptions[modeKey]) + '</button>';
        });
        html += '</div>';
        html += '<div class="qfb-module-visual-actions">';
        html += '<button type="button" class="qfb-mini-btn qfb-mini-btn-secondary" data-qfb-module-visual-action="follow">' + escapeHtml(getText('moduleVisualFollow', '一键恢复跟随页面')) + '</button>';
        html += '<button type="button" class="qfb-mini-btn" data-qfb-module-visual-action="sync-primary">' + escapeHtml(getText('moduleVisualSyncPrimary', '一键同步页面主色')) + '</button>';
        html += '</div>';
        html += '<details class="qfb-module-visual-advanced" ' + (currentMode === 'custom' || hasManualValues ? 'open' : '') + '>';
        html += '<summary>' + escapeHtml(getText('moduleVisualAdvancedTitleText', '高级设置')) + '</summary>';
        html += '<div class="qfb-advanced-grid">';
        advancedFields.forEach(function(item) {
            html += renderAdvancedTextControl('_ds_visual', item.path, item.label, getAdvancedInputValue(rootValue, item.path, ''), item.placeholder, item.description);
        });
        html += '</div>';
        html += '</details>';

        html += '</div>';
        return html;
    }

    function renderFieldControl(field, value) {
        var fieldType = field.type || 'text';
        var fieldId = field.id || '';
        var description = field.description ? '<p class="qfb-field-desc">' + field.description + '</p>' : '';
        var html = '';

        if (fieldType === 'header') {
            return '<div class="qfb-field-header">' + escapeHtml(field.label || '') + '</div>';
        }

        if (fieldType === 'heading' || fieldType === 'separator') {
            return '<div class="qfb-field-header">' + escapeHtml(field.label || fieldId) + '</div>';
        }

        if (fieldType === 'info') {
            return '<div class="qfb-field-info">' + (field.description || '') + '</div>';
        }

        var dependencyAttr = field.dependency ? ' data-field-dependency="' + escapeHtml(encodeBuilderData(field.dependency)) + '"' : '';
        html += '<div class="qfb-field"' + dependencyAttr + '>';
        html += '<label class="qfb-label">' + escapeHtml(field.label || fieldId) + '</label>';

        if (fieldType === 'advanced_style') {
            html += renderAdvancedStyleField(value, field.capabilities || {});
        } else if (fieldType === 'advanced_visibility') {
            html += renderAdvancedVisibilityField(value);
        } else if (fieldType === 'module_visual_style') {
            html += renderModuleVisualStyleField(value, field);
        } else if (fieldType === 'textarea' || fieldType === 'editor') {
            html += '<textarea class="qfb-input" data-field-id="' + escapeHtml(fieldId) + '" data-field-type="' + escapeHtml(fieldType) + '" rows="' + escapeHtml(field.rows || '4') + '" placeholder="' + escapeHtml(field.placeholder || '') + '">' + escapeHtml(normalizeFieldValue(value)) + '</textarea>';
        } else if (fieldType === 'number') {
            html += '<input class="qfb-input" type="number" data-field-id="' + escapeHtml(fieldId) + '" data-field-type="number" value="' + escapeHtml(normalizeFieldValue(value)) + '" min="' + escapeHtml(field.min || '') + '" max="' + escapeHtml(field.max || '') + '" step="' + escapeHtml(field.step || '') + '" />';
        } else if (fieldType === 'date') {
            html += '<input class="qfb-input" type="date" data-field-id="' + escapeHtml(fieldId) + '" data-field-type="date" value="' + escapeHtml(normalizeFieldValue(value)) + '" />';
        } else if (fieldType === 'select') {
            var options = field.options || {};
            html += '<select class="qfb-input" data-field-id="' + escapeHtml(fieldId) + '" data-field-type="select">';
            Object.keys(options).forEach(function(optKey) {
                var selected = normalizeFieldValue(value) === String(optKey) ? ' selected' : '';
                html += '<option value="' + escapeHtml(optKey) + '"' + selected + '>' + escapeHtml(options[optKey]) + '</option>';
            });
            html += '</select>';
        } else if (fieldType === 'switcher') {
            var switchValue = normalizeFieldValue(value);
            var switchUsesNumeric = switchValue === '1' || switchValue === '0' || normalizeFieldValue(field.default) === '1' || normalizeFieldValue(field.default) === '0';
            var switchOn = switchUsesNumeric ? '1' : 'yes';
            var switchOff = switchUsesNumeric ? '0' : 'no';
            html += '<select class="qfb-input" data-field-id="' + escapeHtml(fieldId) + '" data-field-type="switcher">';
            html += '<option value="' + switchOn + '"' + (switchValue === switchOn ? ' selected' : '') + '>' + escapeHtml(getText('switchYes', '是')) + '</option>';
            html += '<option value="' + switchOff + '"' + (switchValue === switchOff ? ' selected' : '') + '>' + escapeHtml(getText('switchNo', '否')) + '</option>';
            html += '</select>';
        } else if (fieldType === 'range') {
            var min = Object.prototype.hasOwnProperty.call(field, 'min') ? normalizeFieldValue(field.min) : '0';
            var max = Object.prototype.hasOwnProperty.call(field, 'max') ? normalizeFieldValue(field.max) : '100';
            var step = Object.prototype.hasOwnProperty.call(field, 'step') ? normalizeFieldValue(field.step) : '1';
            var currentRange = normalizeFieldValue(value);
            if (currentRange === '') {
                currentRange = min;
            }
            html += '<input class="qfb-input qfb-range-input" type="range" data-field-id="' + escapeHtml(fieldId) + '" data-field-type="range" min="' + escapeHtml(min) + '" max="' + escapeHtml(max) + '" step="' + escapeHtml(step) + '" value="' + escapeHtml(currentRange) + '" />';
            html += '<div class="qfb-field-desc">' + escapeHtml(getText('currentValue', '当前值：')) + '<span class="qfb-range-value">' + escapeHtml(currentRange) + '</span></div>';
        } else if (fieldType === 'checkbox') {
            var checked = value === '1' || value === 1 || value === true ? ' checked' : '';
            html += '<label class="qfb-checkbox"><input class="qfb-input" type="checkbox" data-field-id="' + escapeHtml(fieldId) + '" data-field-type="checkbox"' + checked + ' /> ' + escapeHtml(getText('enabledLabel', '启用')) + '</label>';
        } else if (fieldType === 'repeater') {
            html += renderRepeaterField(field, value);
        } else if (fieldType === 'image' || fieldType === 'upload') {
            html += '<input class="qfb-input" type="text" data-field-id="' + escapeHtml(fieldId) + '" data-field-type="' + escapeHtml(fieldType) + '" value="' + escapeHtml(normalizeFieldValue(value)) + '" placeholder="' + escapeHtml(getText('mediaUrlPlaceholder', '请输入媒体URL')) + '" />';
        } else if (fieldType === 'color') {
            html += '<input class="qfb-input" type="text" data-field-id="' + escapeHtml(fieldId) + '" data-field-type="color" value="' + escapeHtml(normalizeFieldValue(value)) + '" placeholder="' + escapeHtml(getText('colorPlaceholder', '#ffffff 或 linear-gradient(...)')) + '" />';
        } else if (fieldType === 'gallery') {
            html += '<textarea class="qfb-input" data-field-id="' + escapeHtml(fieldId) + '" data-field-type="gallery" rows="3" placeholder="' + escapeHtml(getText('galleryPlaceholder', '多个URL用英文逗号分隔')) + '">' + escapeHtml(normalizeFieldValue(value)) + '</textarea>';
        } else {
            html += '<input class="qfb-input" type="text" data-field-id="' + escapeHtml(fieldId) + '" data-field-type="' + escapeHtml(fieldType) + '" value="' + escapeHtml(normalizeFieldValue(value)) + '" placeholder="' + escapeHtml(field.placeholder || '') + '" />';
        }

        if (fieldType !== 'advanced_style' && fieldType !== 'advanced_visibility') {
            html += renderDesignTokenPicker(fieldId, fieldType, 'field');
            html += renderDynamicDataPicker(fieldId, fieldType);
        }
        html += description;
        html += '</div>';
        return html;
    }

    function updateSettingsPanelHeader() {
        if (!els.rightTitle || !els.rightDesc || state.panelMode === 'ai') {
            return;
        }

        if (state.selectedScope === 'page' || !state.modules.length) {
            els.rightTitle.textContent = getText('pageSettingsPanelTitle', '页面设置');
            els.rightDesc.textContent = getText('pageSettingsPanelDesc', '这里管理当前页的模板、页头和当前页单独风格；模块里还能继续做局部微调。');
            return;
        }

        els.rightTitle.textContent = getText('settingsPanelTitle', '模块设置');
        els.rightDesc.textContent = getText('settingsPanelDesc', '这里只改当前模块，适合做局部微调；不改时会继续跟着当前页。');
    }

    function renderPageTextControl(label, path, value, placeholder, fieldType, withTokenPicker) {
        var html = '<div class="qfb-field">';
        html += '<label class="qfb-label">' + escapeHtml(label) + '</label>';
        html += '<input class="qfb-input qfb-page-setting-input" type="text" data-page-setting-path="' + escapeHtml(path) + '" data-page-setting-type="' + escapeHtml(fieldType || 'text') + '" value="' + escapeHtml(normalizeFieldValue(value)) + '" placeholder="' + escapeHtml(placeholder || '') + '" />';
        if (withTokenPicker) {
            html += renderDesignTokenPicker(path, fieldType || 'text', 'page');
        }
        if (String(path || '').indexOf('design.') === 0) {
            html += '<p class="qfb-field-desc">' + escapeHtml(getText('pageSettingInherited', '留空就是继续跟着全局')) + '</p>';
        }
        html += '</div>';
        return html;
    }

    function renderPageSelectControl(label, path, value, options) {
        var currentValue = normalizeFieldValue(value);
        var html = '<div class="qfb-field">';
        html += '<label class="qfb-label">' + escapeHtml(label) + '</label>';
        html += '<select class="qfb-input qfb-page-setting-input" data-page-setting-path="' + escapeHtml(path) + '" data-page-setting-type="select">';
        Object.keys(options || {}).forEach(function(optionValue) {
            var selected = currentValue === String(optionValue) ? ' selected' : '';
            html += '<option value="' + escapeHtml(optionValue) + '"' + selected + '>' + escapeHtml(options[optionValue]) + '</option>';
        });
        html += '</select>';
        html += '</div>';
        return html;
    }

    function renderPageCheckboxControl(label, path, checked, description) {
        var html = '<div class="qfb-field">';
        html += '<label class="qfb-checkbox"><input class="qfb-page-setting-input" type="checkbox" data-page-setting-path="' + escapeHtml(path) + '" data-page-setting-type="checkbox"' + (checked ? ' checked' : '') + ' /> ' + escapeHtml(label) + '</label>';
        if (description) {
            html += '<p class="qfb-field-desc">' + escapeHtml(description) + '</p>';
        }
        html += '</div>';
        return html;
    }

    function renderPageVisualStyleActions() {
        var html = '<div class="qfb-page-visual-actions">';
        html += '<button type="button" class="qfb-mini-btn" data-qfb-page-visual-action="hydrate-preset">' + escapeHtml(getText('pageVisualHydratePreset', '填充预设值')) + '</button>';
        html += '<button type="button" class="qfb-mini-btn qfb-mini-btn-secondary" data-qfb-page-visual-action="clear-custom">' + escapeHtml(getText('pageVisualClearCustom', '清空细调')) + '</button>';
        html += '</div>';
        return html;
    }

    function renderPageVisualStyleFieldGroups(settings) {
        var groups = getPageVisualFieldGroups();
        var groupKeys = Object.keys(groups || {});
        if (!groupKeys.length) {
            return '';
        }

        settings = normalizePageVisualStyleSettings(settings || {});
        var html = '<div class="qfb-page-visual-groups">';
        groupKeys.forEach(function(groupKey) {
            var group = groups[groupKey];
            var fields = group && group.fields && typeof group.fields === 'object' ? group.fields : {};
            var fieldKeys = Object.keys(fields);
            if (!fieldKeys.length) {
                return;
            }

            html += '<section class="qfb-page-visual-group">';
            html += '<div class="qfb-page-visual-group-title">' + escapeHtml(group.label || groupKey) + '</div>';
            if (group.description) {
                html += '<p class="qfb-field-desc">' + escapeHtml(group.description) + '</p>';
            }
            html += '<div class="qfb-page-visual-grid">';
            fieldKeys.forEach(function(fieldKey) {
                var field = fields[fieldKey] || {};
                var value = settings[groupKey] && typeof settings[groupKey] === 'object' && Object.prototype.hasOwnProperty.call(settings[groupKey], fieldKey)
                    ? settings[groupKey][fieldKey]
                    : '';
                var presetValue = getPageVisualFieldEffectivePresetValue(field, settings);
                var placeholder = presetValue || field.placeholder || '';
                var inputType = field.type === 'opacity' ? 'number' : 'text';
                var path = 'visualStyle.' + groupKey + '.' + fieldKey;

                html += '<div class="qfb-field qfb-page-visual-field">';
                html += '<label class="qfb-label">' + escapeHtml(field.label || fieldKey) + '</label>';
                html += '<input class="qfb-input qfb-page-setting-input" type="' + escapeHtml(inputType) + '" data-page-setting-path="' + escapeHtml(path) + '" data-page-setting-type="' + escapeHtml(inputType) + '" value="' + escapeHtml(normalizeFieldValue(value)) + '" placeholder="' + escapeHtml(placeholder) + '"';
                if (field.type === 'opacity') {
                    html += ' min="0" max="1" step="0.01"';
                }
                html += ' />';
                html += renderPageVisualPresetValue(presetValue, field.type || inputType);
                if (field.type !== 'opacity') {
                    html += renderDesignTokenPicker(path, field.type || inputType, 'page');
                }
                html += '</div>';
            });
            html += '</div>';
            html += '</section>';
        });
        html += '</div>';

        return html;
    }

    function renderGovernanceCard(title, items, note) {
        var html = '<div class="qfb-governance-card">';
        html += '<div class="qfb-governance-title">' + escapeHtml(title) + '</div>';
        html += '<div class="qfb-governance-list">';
        (items || []).forEach(function(item) {
            if (!item || !item.label || !item.desc) {
                return;
            }
            var tone = item.tone ? (' qfb-governance-item--' + item.tone) : '';
            html += '<div class="qfb-governance-item' + escapeHtml(tone) + '">';
            html += '<span class="qfb-governance-badge">' + escapeHtml(item.label) + '</span>';
            html += '<div class="qfb-governance-copy">' + escapeHtml(item.desc) + '</div>';
            html += '</div>';
        });
        html += '</div>';
        if (note) {
            html += '<div class="qfb-governance-note">' + escapeHtml(note) + '</div>';
        }
        html += '</div>';
        return html;
    }

    function renderPageGovernanceCard() {
        return renderGovernanceCard(
            getText('governanceCardTitle', '作用层级'),
            [
                {
                    label: getText('governanceGlobalLabel', '站点级'),
                    desc: getText('pageGovernanceGlobalDesc', '全局设计先决定整站默认外观，当前页默认跟它走。'),
                    tone: 'global'
                },
                {
                    label: getText('governancePageLabel', '页面级'),
                    desc: getText('pageGovernancePageDesc', '这里写的内容只影响当前页；不写就继续跟随全站。'),
                    tone: 'page'
                },
                {
                    label: getText('governanceModuleLabel', '模块级'),
                    desc: getText('pageGovernanceModuleDesc', '如果某个模块还要特殊一点，再去模块设置里单独改。'),
                    tone: 'module'
                }
            ],
            getText('pageGovernanceNote', '普通做法：先定全站，再定当前页，最后只对少数模块做局部微调。')
        );
    }

    function renderModuleGovernanceCard(schema) {
        var note = getText('moduleGovernanceNote', '不填就继续跟着当前页和全站。');
        if (schema && Array.isArray(schema.legacyCommonStyleFieldIds) && schema.legacyCommonStyleFieldIds.length) {
            note = getText('moduleLegacyBridgeNote', '该模块的旧公共样式已经并到这里，后续直接在当前面板调整就行。');
        }

        return renderGovernanceCard(
            getText('governanceCardTitle', '作用层级'),
            [
                {
                    label: getText('governanceGlobalLabel', '站点级'),
                    desc: getText('moduleGovernanceGlobalDesc', '全局设计先给这个模块一个默认基线。'),
                    tone: 'global'
                },
                {
                    label: getText('governancePageLabel', '页面级'),
                    desc: getText('moduleGovernancePageDesc', '当前页的单独风格会继续传给这个模块。'),
                    tone: 'page'
                },
                {
                    label: getText('governanceModuleLabel', '模块级'),
                    desc: getText('moduleGovernanceModuleDesc', '这里的设置只改当前模块，最适合做局部强调。'),
                    tone: 'module'
                }
            ],
            note
        );
    }

    function buildPageResponsiveInputPath(path, deviceKey, options) {
        options = options && typeof options === 'object' ? options : {};

        if (options.pathSuffix) {
            if (options.deviceFirst) {
                return path + '.' + deviceKey + '.' + options.pathSuffix;
            }
            return path + '.' + options.pathSuffix + '.' + deviceKey;
        }

        return path + '.' + deviceKey;
    }

    function renderPageResponsiveControl(label, path, values, placeholders, showInheritedHint, options) {
        options = options && typeof options === 'object' ? options : {};
        var devices = [
            { key: 'desktop', label: getText('desktopLabel', '桌面') },
            { key: 'tablet', label: getText('tabletLabel', '平板') },
            { key: 'mobile', label: getText('mobileLabel', '手机') }
        ];
        var html = '<div class="qfb-field qfb-page-responsive-field">';
        html += '<label class="qfb-label">' + escapeHtml(label) + '</label>';
        html += '<div class="qfb-page-responsive-grid">';
        devices.forEach(function(device) {
            var deviceValue = values && typeof values === 'object' ? normalizeFieldValue(values[device.key] || '') : '';
            var placeholder = placeholders && typeof placeholders === 'object' ? normalizeFieldValue(placeholders[device.key] || '') : '';
            var inputPath = buildPageResponsiveInputPath(path, device.key, options);
            html += '<label class="qfb-page-responsive-item">';
            html += '<span>' + escapeHtml(device.label) + '</span>';
            html += '<input class="qfb-input qfb-page-setting-input" type="text" data-page-setting-path="' + escapeHtml(inputPath) + '" data-page-setting-type="text" value="' + escapeHtml(deviceValue) + '" placeholder="' + escapeHtml(placeholder) + '" />';
            html += '</label>';
        });
        html += '</div>';
        if (showInheritedHint !== false) {
            html += '<p class="qfb-field-desc">' + escapeHtml(getText('pageSettingInherited', '留空就是继续跟着全局')) + '</p>';
        }
        html += '</div>';
        return html;
    }

    function renderPageTypographySection(design) {
        var styleDefinitions = getPageTypographyStyles();
        var propertyDefinitions = getPageTypographyProperties();
        var typographyCount = getPageDesignOverrideSummary(design).groups.typography;
        var html = '';

        if (!Object.keys(styleDefinitions).length || !Object.keys(propertyDefinitions).length) {
            return html;
        }

        html += '<div class="qfb-field-header qfb-field-header-sub">' + escapeHtml(getText('pageTypographySectionTitle', '页面排版')) + '</div>';
        html += renderPageDesignSectionToolbar(
            'typography',
            typographyCount,
            'data-page-design-reset-group="typography"'
        );
        html += '<div class="qfb-field-info">' + escapeHtml(getText('pageTypographySectionHelp', '当前页想单独改正文、标题、按钮、导航排版，就在这里改；不填继续跟随全站。')) + '</div>';

        Object.keys(styleDefinitions).forEach(function(styleKey) {
            var styleDefinition = styleDefinitions[styleKey] && typeof styleDefinitions[styleKey] === 'object'
                ? styleDefinitions[styleKey]
                : {};
            var styleValues = design.typography && design.typography[styleKey] && typeof design.typography[styleKey] === 'object'
                ? design.typography[styleKey]
                : {};

            html += '<details class="qfb-page-typography-group"' + ((styleKey === 'body' || styleKey === 'h1') ? ' open' : '') + '>';
            html += '<summary class="qfb-page-typography-summary">';
            html += '<span class="qfb-page-typography-title">' + escapeHtml(styleDefinition.label || styleKey) + '</span>';
            if (styleDefinition.sample) {
                html += '<span class="qfb-page-typography-sample">' + escapeHtml(styleDefinition.sample) + '</span>';
            }
            html += '</summary>';
            html += '<div class="qfb-page-typography-fields">';

            Object.keys(propertyDefinitions).forEach(function(propertyKey) {
                var propertyDefinition = propertyDefinitions[propertyKey] && typeof propertyDefinitions[propertyKey] === 'object'
                    ? propertyDefinitions[propertyKey]
                    : {};
                var builderKey = getPageTypographyBuilderKey(propertyKey);
                var placeholders = {
                    desktop: normalizeFieldValue(propertyDefinition.placeholder || ''),
                    tablet: normalizeFieldValue(propertyDefinition.placeholder || ''),
                    mobile: normalizeFieldValue(propertyDefinition.placeholder || '')
                };
                var values = {
                    desktop: normalizeFieldValue(styleValues.desktop && (styleValues.desktop[builderKey] || styleValues.desktop[propertyKey]) || ''),
                    tablet: normalizeFieldValue(styleValues.tablet && (styleValues.tablet[builderKey] || styleValues.tablet[propertyKey]) || ''),
                    mobile: normalizeFieldValue(styleValues.mobile && (styleValues.mobile[builderKey] || styleValues.mobile[propertyKey]) || '')
                };

                html += renderPageResponsiveControl(
                    propertyDefinition.label || builderKey,
                    'design.typography.' + styleKey,
                    values,
                    placeholders,
                    false,
                    {
                        pathSuffix: builderKey,
                        deviceFirst: true
                    }
                );
            });

            html += '</div>';
            html += '</details>';
        });

        return html;
    }

    function renderPageSettings() {
        ensurePageSettingsState();
        updateSettingsPanelHeader();

        var settings = state.pageSettings;
        var design = getPageDesignState();
        var designSummary = getPageDesignOverrideSummary(design);
        var pageDesignDefinitions = getPageDesignDefinitions();
        var paletteDefinitions = pageDesignDefinitions.palette && pageDesignDefinitions.palette.fields && typeof pageDesignDefinitions.palette.fields === 'object'
            ? pageDesignDefinitions.palette.fields
            : {};
        var layoutDefinitions = pageDesignDefinitions.layout && pageDesignDefinitions.layout.fields && typeof pageDesignDefinitions.layout.fields === 'object'
            ? pageDesignDefinitions.layout.fields
            : {};
        var structureDefinitions = pageDesignDefinitions.structure && pageDesignDefinitions.structure.fields && typeof pageDesignDefinitions.structure.fields === 'object'
            ? pageDesignDefinitions.structure.fields
            : {};
        var componentStyleGroups = getPageComponentStyleGroups();
        var html = '<div class="qfb-settings-title">' + escapeHtml(getText('pageSettingsPanelTitle', '页面设置')) + '</div>';
        html += renderPageGovernanceCard();
        html += '<div class="qfb-field-header">' + escapeHtml(getText('pageBasicSectionTitle', '基础设置')) + '</div>';
        html += renderPageTextControl(getText('pageTitleLabel', '页面标题'), 'title', settings.title || '', '', 'text', false);
        html += renderPageSelectControl(getText('pageTemplateLabel', '页面模板'), 'pageTemplate', settings.pageTemplate || 'default', getPageTemplateChoices());
        html += renderPageCheckboxControl(getText('pageHideHeaderLabel', '隐藏页面头部'), 'hidePageHeader', !!settings.hidePageHeader, '');
        html += renderPageCheckboxControl(getText('pageTransparentHeaderLabel', '启用透明头部'), 'transparentHeader', !!settings.transparentHeader, getText('pageTransparentHeaderDesc', '首屏覆盖在 Banner 上，系统会按首屏明暗自动选择菜单文字色；也可在页面视觉预设中手动指定。'));
        html += renderPageCheckboxControl(getText('pageScrollRevealLabel', '启用滚动动效'), 'enableScrollReveal', !!settings.enableScrollReveal, '');

        var footerSettings = normalizePageFooterSettings(settings.footer || {});
        html += '<div class="qfb-field-header">' + escapeHtml(getText('pageFooterSectionTitle', '页脚风格')) + '</div>';
        html += '<div class="qfb-field-info">' + escapeHtml(getText('pageFooterHelp', '先选择当前页页脚来源；页面视觉预设里的底部颜色只作为本页显式覆盖，不会再偷偷改变“跟随全局”的结果。')) + '</div>';
        html += renderPageSelectControl(getText('pageFooterModeLabel', '页脚策略'), 'footer.mode', footerSettings.mode, {
            inherit: getText('pageFooterModeInherit', '跟随全局页脚'),
            page_skin: getText('pageFooterModeSkin', '跟随页面皮肤'),
            preset: getText('pageFooterModePreset', '使用指定页脚预设'),
            hidden: getText('pageFooterModeHidden', '当前页隐藏页脚')
        });
        if (footerSettings.mode === 'preset') {
            html += renderPageSelectControl(getText('pageFooterPresetLabel', '页脚预设'), 'footer.preset', footerSettings.preset || '', getPageFooterPresetChoices(footerSettings.preset));
        }
        if (footerSettings.mode !== 'hidden') {
            html += renderPageSelectControl(getText('pageFooterWaveLabel', '波浪衔接'), 'footer.wave', footerSettings.wave, {
                inherit: getText('pageFooterWaveInherit', '跟随页脚设置'),
                on: getText('pageFooterWaveOn', '当前页开启'),
                off: getText('pageFooterWaveOff', '当前页关闭')
            });
        }
        if (footerSettings.mode === 'inherit') {
            html += renderPageCheckboxControl(
                getText('pageFooterSkinColorsLabel', '全局页脚结构使用当前页皮肤配色'),
                'footer.inheritSkinColors',
                !!footerSettings.inheritSkinColors,
                getText('pageFooterSkinColorsDesc', '关闭时完整跟随全局；开启后只把当前页皮肤的页脚颜色和波浪带入。')
            );
        }

        var visualStyleSettings = normalizePageVisualStyleSettings(settings.visualStyle || {});
        html += '<div class="qfb-field-header">' + escapeHtml(getText('pageVisualStyleSectionTitle', '页面视觉预设')) + '</div>';
        html += '<div class="qfb-field-info">' + escapeHtml(getText('pageVisualStyleHelp', '这里控制当前页顶部、底部、波浪和按钮的基础搭配；选择自定义后还可以在后台页面视觉风格面板继续细调。')) + '</div>';
        html += renderPageSelectControl(getText('pageVisualStyleModeLabel', '视觉模式'), 'visualStyle.mode', visualStyleSettings.mode, {
            inherit: getText('pageVisualStyleModeInherit', '跟随页面模板预设'),
            global: getText('pageVisualStyleModeGlobal', '强制跟随全站默认'),
            custom: getText('pageVisualStyleModeCustom', '启用当前页面视觉预设')
        });
        html += renderPageSelectControl(getText('pageVisualStylePresetLabel', '基础预设'), 'visualStyle.preset', visualStyleSettings.preset || '', getPageVisualPresetChoices(visualStyleSettings.preset));
        html += renderPageVisualStyleActions();
        html += renderPageVisualStyleFieldGroups(visualStyleSettings);

        html += '<div class="qfb-field-header">' + escapeHtml(getText('pageDesignSectionTitle', '当前页单独风格')) + '</div>';
        html += '<div class="qfb-field-info">' + escapeHtml(getText('pageDesignHelp', '这里写了只影响当前页；不写就继续跟着全站。')) + '</div>';
        html += renderPageDesignSummary(design);
        html += '<div class="qfb-field-header qfb-field-header-sub">' + escapeHtml(getText('pagePaletteSectionTitle', '页面配色')) + '</div>';
        html += renderPageDesignSectionToolbar(
            'palette',
            designSummary.groups.palette,
            'data-page-design-reset-group="palette"'
        );
        [
            { key: 'primary', path: 'design.palette.primary' },
            { key: 'secondary', path: 'design.palette.secondary' },
            { key: 'accent', path: 'design.palette.accent' },
            { key: 'success', path: 'design.palette.success' },
            { key: 'info', path: 'design.palette.info' },
            { key: 'warning', path: 'design.palette.warning' },
            { key: 'error', path: 'design.palette.error' },
            { key: 'overlay', path: 'design.palette.overlay' },
            { key: 'background', path: 'design.palette.background' },
            { key: 'surface', path: 'design.palette.surface' },
            { key: 'surfaceAlt', path: 'design.palette.surfaceAlt' },
            { key: 'text', path: 'design.palette.text' },
            { key: 'textMuted', path: 'design.palette.textMuted' },
            { key: 'heading', path: 'design.palette.heading' },
            { key: 'border', path: 'design.palette.border' }
        ].forEach(function(item) {
            var definitionKey = getPagePaletteDefinitionKey(item.key);
            var definition = paletteDefinitions[definitionKey] && typeof paletteDefinitions[definitionKey] === 'object'
                ? paletteDefinitions[definitionKey]
                : {};
            html += renderPageTextControl(definition.label || item.key, item.path, design.palette[item.key] || '', '#ffffff', 'color', true);
        });

        html += '<div class="qfb-field-header qfb-field-header-sub">' + escapeHtml(getText('pageDarkPaletteSectionTitle', '页面暗色模式')) + '</div>';
        html += '<div class="qfb-field-info">' + escapeHtml(getText('pageDarkPaletteSectionHelp', '这里只在当前页的暗色状态生效；不填就继续跟着全站暗色方案。')) + '</div>';
        [
            { key: 'darkBg', path: 'design.palette.darkBg' },
            { key: 'darkSurface', path: 'design.palette.darkSurface' },
            { key: 'darkText', path: 'design.palette.darkText' },
            { key: 'darkTextMuted', path: 'design.palette.darkTextMuted' },
            { key: 'darkBorder', path: 'design.palette.darkBorder' }
        ].forEach(function(item) {
            var definitionKey = getPagePaletteDefinitionKey(item.key);
            var definition = paletteDefinitions[definitionKey] && typeof paletteDefinitions[definitionKey] === 'object'
                ? paletteDefinitions[definitionKey]
                : {};
            html += renderPageTextControl(definition.label || item.key, item.path, design.palette[item.key] || '', '#111827', 'color', true);
        });

        if (Object.keys(structureDefinitions).length) {
            html += '<div class="qfb-field-header qfb-field-header-sub">' + escapeHtml(getText('pageStructureSectionTitle', '页面圆角与动效')) + '</div>';
            html += renderPageDesignSectionToolbar(
                'structure',
                designSummary.groups.structure,
                'data-page-design-reset-group="structure"'
            );
            html += '<div class="qfb-field-info">' + escapeHtml(getText('pageStructureSectionHelp', '当前页想单独改圆角和动效，就在这里改；不填继续跟随全站。')) + '</div>';
            [
                { key: 'cardRadius', definitionKey: 'card_radius' },
                { key: 'buttonRadius', definitionKey: 'button_radius' },
                { key: 'inputRadius', definitionKey: 'input_radius' },
                { key: 'animationSpeed', definitionKey: 'animation_speed' }
            ].forEach(function(item) {
                var definition = structureDefinitions[item.definitionKey] && typeof structureDefinitions[item.definitionKey] === 'object'
                    ? structureDefinitions[item.definitionKey]
                    : {};
                html += renderPageTextControl(
                    definition.label || item.key,
                    'design.structure.' + item.key,
                    design.structure[item.key] || '',
                    definition.placeholder || '',
                    definition.type || 'text',
                    false
                );
            });
        }

        html += renderPageTypographySection(design);

        html += '<div class="qfb-field-header qfb-field-header-sub">' + escapeHtml(getText('pageLayoutSectionTitle', '页面布局')) + '</div>';
        html += renderPageDesignSectionToolbar(
            'layout',
            designSummary.groups.layout,
            'data-page-design-reset-group="layout"'
        );
        html += '<div class="qfb-field-info">' + escapeHtml(getText('pageSettingLayoutHelp', '当前页想单独改容器宽度、区块间距和布局模式，就在这里改。')) + '</div>';
        html += renderPageResponsiveControl(
            layoutDefinitions.container_width && layoutDefinitions.container_width.label ? layoutDefinitions.container_width.label : getText('tokenContainerWidth', '容器宽度'),
            'design.layout.containerWidth',
            design.layout.containerWidth,
            layoutDefinitions.container_width && layoutDefinitions.container_width.placeholder ? layoutDefinitions.container_width.placeholder : {}
        );
        html += renderPageResponsiveControl(
            layoutDefinitions.section_spacing && layoutDefinitions.section_spacing.label ? layoutDefinitions.section_spacing.label : getText('tokenSectionPadding', '区块间距'),
            'design.layout.sectionSpacing',
            design.layout.sectionSpacing,
            layoutDefinitions.section_spacing && layoutDefinitions.section_spacing.placeholder ? layoutDefinitions.section_spacing.placeholder : {}
        );
        html += renderPageResponsiveControl(
            layoutDefinitions.grid_gap && layoutDefinitions.grid_gap.label ? layoutDefinitions.grid_gap.label : 'Grid Gap',
            'design.layout.gridGap',
            design.layout.gridGap,
            layoutDefinitions.grid_gap && layoutDefinitions.grid_gap.placeholder ? layoutDefinitions.grid_gap.placeholder : {}
        );
        html += renderPageSelectControl(
            layoutDefinitions.layout_mode && layoutDefinitions.layout_mode.label ? layoutDefinitions.layout_mode.label : getText('pageLayoutSectionTitle', '页面布局'),
            'design.layout.layoutMode',
            design.layout.layoutMode || '',
            (function() {
                var choices = { '': getText('pageSettingInherited', '留空就是继续跟着全局') };
                var definedChoices = layoutDefinitions.layout_mode && layoutDefinitions.layout_mode.choices && typeof layoutDefinitions.layout_mode.choices === 'object'
                    ? layoutDefinitions.layout_mode.choices
                    : {};
                Object.keys(definedChoices).forEach(function(choiceKey) {
                    choices[choiceKey] = definedChoices[choiceKey];
                });
                return choices;
            })()
        );

        if (Object.keys(componentStyleGroups).length) {
            html += '<div class="qfb-field-header qfb-field-header-sub">' + escapeHtml(getText('pageComponentSectionTitle', '页面组件样式')) + '</div>';
            html += renderPageDesignSectionToolbar(
                'componentStyles',
                designSummary.groups.componentStyles,
                'data-page-design-reset-group="componentStyles"'
            );
            html += '<div class="qfb-field-info">' + escapeHtml(getText('pageComponentSectionHelp', '这里只改当前页组件外观，不影响全站；模块里仍可继续微调。')) + '</div>';
            Object.keys(componentStyleGroups).forEach(function(groupKey) {
                var group = componentStyleGroups[groupKey] && typeof componentStyleGroups[groupKey] === 'object'
                    ? componentStyleGroups[groupKey]
                    : {};
                var fields = group.fields && typeof group.fields === 'object' ? group.fields : {};
                if (!Object.keys(fields).length) {
                    return;
                }

                html += '<div class="qfb-field-header qfb-field-header-sub">' + escapeHtml(group.label || groupKey) + '</div>';
                html += renderPageDesignSectionToolbar(
                    'component:' + groupKey,
                    designSummary.componentGroups[groupKey] || 0,
                    'data-page-design-reset-component-group="' + escapeHtml(groupKey) + '"'
                );
                Object.keys(fields).forEach(function(styleKey) {
                    var definition = fields[styleKey] && typeof fields[styleKey] === 'object' ? fields[styleKey] : {};
                    var fieldType = definition.type === 'color' ? 'color' : 'text';
                    html += renderPageTextControl(
                        definition.label || styleKey,
                        'design.componentStyles.' + styleKey,
                        design.componentStyles && design.componentStyles[styleKey] ? design.componentStyles[styleKey] : '',
                        fieldType === 'color' ? '#ffffff' : '',
                        fieldType,
                        true
                    );
                });
            });
        }

        els.settings.className = 'qfb-settings';
        els.settings.innerHTML = html;
        refreshPageDesignSummaryUI();
        highlightSelectedWrapper();
    }

    function renderSettings() {
        if (!els.settings) {
            return;
        }

        if (state.selectedScope === 'page' || !state.modules.length) {
            renderPageSettings();
            return;
        }

        if (state.selectedIndex < 0 || state.selectedIndex >= state.modules.length) {
            state.selectedScope = 'page';
            renderPageSettings();
            return;
        }

        updateSettingsPanelHeader();

        var selectedModule = state.modules[state.selectedIndex];
        var selectedModuleType = selectedModule.type;
        var requestId = ++state.settingsRequestSeq;
        els.settings.className = 'qfb-settings-loading';
        els.settings.innerHTML = escapeHtml(getText('loading', '加载中...'));

        fetchModuleSchema(selectedModuleType).done(function(schema) {
            if (requestId !== state.settingsRequestSeq) {
                return;
            }
            var current = state.modules[state.selectedIndex];
            if (!current || current.type !== selectedModuleType) {
                return;
            }
            var fields = schema.fields || [];
            var html = '<div class="qfb-module-settings-title">' + escapeHtml(schema.name || selectedModuleType) + '设置</div>';
            var currentFieldGroup = '';

            for (var i = 0; i < fields.length; i++) {
                var field = fields[i];
                var fieldGroup = field.builderGroup || selectedModuleType + ':other';
                if (fieldGroup !== currentFieldGroup) {
                    if (currentFieldGroup) html += '</div></section>';
                    currentFieldGroup = fieldGroup;
                    html += '<section class="qfb-module-settings-card" data-qfb-module-settings-card="' + escapeHtml(fieldGroup) + '"><div class="qfb-module-settings-card__header"><span class="qfb-module-settings-card__rule"></span><h3>' + escapeHtml(field.builderGroupLabel || '模块设置') + '</h3><span class="qfb-module-settings-card__rule"></span></div><div class="qfb-module-settings-card__body">';
                }
                var value = '';
                if (field.id && current.data && Object.prototype.hasOwnProperty.call(current.data, field.id)) {
                    value = current.data[field.id];
                } else if (Object.prototype.hasOwnProperty.call(field, 'default')) {
                    value = field.default;
                } else if (field.type === 'repeater') {
                    value = field.default_items || [];
                }
                html += renderFieldControl(field, value);
            }
            if (currentFieldGroup) html += '</div></section>';

            els.settings.className = 'qfb-settings';
            els.settings.innerHTML = html;
            refreshFieldDependencies(els.settings, buildFieldDependencyData(fields, current.data || {}));
            highlightSelectedWrapper();
        }).fail(function() {
            if (requestId !== state.settingsRequestSeq) {
                return;
            }
            els.settings.className = 'qfb-settings-error';
            els.settings.innerHTML = escapeHtml(getText('schemaLoadFailed', '模块配置加载失败'));
            setStatus(getText('schemaLoadFailed', '模块配置加载失败'), 'error');
        });
    }

    function getAiMaxModules() {
        if (!aiBuilderService) {
            return 10;
        }

        return aiBuilderService.getMaxModules(state.aiConfig);
    }

    function setRightPaneMode(mode) {
        var nextMode = mode === 'ai' && state.aiConfig && state.aiConfig.enabled ? 'ai' : 'settings';
        state.panelMode = nextMode;

        if (els.settings) {
            els.settings.style.display = nextMode === 'settings' ? '' : 'none';
        }
        if (els.aiPane) {
            els.aiPane.style.display = nextMode === 'ai' ? '' : 'none';
        }
        if (els.rightTitle) {
            els.rightTitle.textContent = nextMode === 'ai'
                ? getText('aiPanelTitle', 'AI装修')
                : (state.selectedScope === 'page' || !state.modules.length
                    ? getText('pageSettingsPanelTitle', '页面设置')
                    : getText('settingsPanelTitle', '模块设置'));
        }
        if (els.rightDesc) {
            els.rightDesc.textContent = nextMode === 'ai'
                ? getText('aiPanelDesc', 'AI 只做当前页和当前模块的局部辅助，结果确认后才会应用')
                : (state.selectedScope === 'page' || !state.modules.length
                    ? getText('pageSettingsPanelDesc', '这里管理当前页的模板、页头和当前页单独风格；模块里还能继续做局部微调。')
                    : getText('settingsPanelDesc', '这里只改当前模块，适合做局部微调；不改时会继续跟着当前页。'));
        }
        if (els.aiToggle) {
            els.aiToggle.textContent = nextMode === 'ai'
                ? getText('aiBackButton', '返回设置')
                : getText('aiButton', 'AI装修');
        }

        if (nextMode === 'settings') {
            renderSettings();
        } else {
            highlightSelectedWrapper();
        }
    }

    function renderAiScopeNoticeHtml() {
        var policy = state.aiConfig && state.aiConfig.scopePolicy && typeof state.aiConfig.scopePolicy === 'object'
            ? state.aiConfig.scopePolicy
            : {};
        var notice = policy.notice || getText('aiScopeNotice', '当前 AI 只辅助当前页面或当前模块：先生成预览结果，确认后应用，可撤回，不直接覆盖全站。');
        if (!notice) {
            return '';
        }

        return '<div class="qfb-ai-scope-notice">' + escapeHtml(notice) + '</div>';
    }

    function renderAiDesignContextHtml() {
        var payload = state.aiConfig && state.aiConfig.designSystem ? state.aiConfig.designSystem : getDesignSystemPayload();
        var tokens = payload && payload.tokens && typeof payload.tokens === 'object' ? payload.tokens : getDesignTokens();
        var componentStyles = payload && payload.componentStyles && typeof payload.componentStyles === 'object' ? payload.componentStyles : getComponentStyles();
        if (!tokens || !Object.keys(tokens).length) {
            return '';
        }

        var presetLabel = payload.presetLabel || payload.preset || '';
        var html = '<div class="qfb-ai-design-context">';
        html += '<div><strong>' + escapeHtml(getText('designSummaryTitle', '全局设计')) + '</strong><span>' + escapeHtml(presetLabel) + '</span></div>';
        html += '<p>' + escapeHtml(getText('aiDesignContextTip', '生成时会优先沿用当前全局样式，保持模块颜色、圆角、阴影和区块间距一致。')) + '</p>';
        html += '<div class="qfb-design-swatches">';
        [
            { label: getText('tokenPrimaryColor', '主色'), value: tokens.primary || '' },
            { label: getText('tokenSecondaryColor', '辅助'), value: tokens.secondary || '' },
            { label: getText('tokenAccentColor', '点缀'), value: tokens.accent || '' },
            { label: getText('tokenBackgroundColor', '背景'), value: tokens.surface_alt || '' }
        ].forEach(function(item) {
            if (!item.value) {
                return;
            }
            html += '<span class="qfb-design-swatch"><i style="background:' + escapeHtml(item.value) + ';"></i><b>' + escapeHtml(item.label) + '</b></span>';
        });
        html += '</div>';
        if (componentStyles && Object.keys(componentStyles).length) {
            html += '<div class="qfb-design-swatches qfb-design-swatches-components">';
            [
                { label: getText('componentButtonLabel', '按钮'), value: componentStyles.button_bg || '' },
                { label: getText('componentCardLabel', '卡片'), value: componentStyles.card_bg || '' },
                { label: getText('componentFormLabel', '表单'), value: componentStyles.form_input_bg || '' },
                { label: getText('componentPostCardLabel', '文章卡片'), value: componentStyles.post_card_bg || '' }
            ].forEach(function(item) {
                if (!item.value) {
                    return;
                }
                html += '<span class="qfb-design-swatch"><i style="background:' + escapeHtml(item.value) + ';"></i><b>' + escapeHtml(item.label) + '</b></span>';
            });
            html += '</div>';
        }
        html += '</div>';
        return html;
    }

    function renderAiContentModelContextHtml() {
        var payload = state.aiConfig && state.aiConfig.contentModels ? state.aiConfig.contentModels : getContentModelPayload();
        var models = payload && payload.models && typeof payload.models === 'object' ? payload.models : {};
        var modelList = Object.keys(models).map(function(key) {
            return models[key];
        }).filter(function(model) {
            return model && typeof model === 'object' && model.label;
        }).slice(0, 10);

        if (!payload || payload.enabled === false || !modelList.length) {
            return '';
        }

        var html = '<div class="qfb-ai-content-context">';
        html += '<div><strong>' + escapeHtml(getText('contentModelSummaryTitle', '内容模型')) + '</strong><span>' + escapeHtml(modelList.length + ' 个可用') + '</span></div>';
        html += '<p>' + escapeHtml(getText('aiContentModelContextTip', 'AI 将参考已启用的内容模型生成更贴近行业内容库的页面结构。')) + '</p>';
        html += '<div class="qfb-content-model-pills">';
        modelList.forEach(function(model) {
            var schema = Array.isArray(model.schemaTypes) && model.schemaTypes.length ? model.schemaTypes[0] : '';
            html += '<span class="qfb-content-model-pill"><b>' + escapeHtml(model.label) + '</b>';
            if (schema) {
                html += '<em>' + escapeHtml(schema) + '</em>';
            }
            html += '</span>';
        });
        html += '</div>';
        html += '</div>';
        return html;
    }

    function getAiPromptRecipes() {
        return [
            {
                id: 'hero',
                label: getText('aiPromptRecipeHero', '首屏转化'),
                prompt: getText('aiPromptRecipeHeroText', '重做当前单页的首屏与转化路径：突出核心卖点、适用人群、信任背书和明确 CTA，保留当前可用内容，不要生成整站。')
            },
            {
                id: 'conversion',
                label: getText('aiPromptRecipeConversion', '成交路径'),
                prompt: getText('aiPromptRecipeConversionText', '优化当前页面的成交路径：强化服务或产品价值、客户案例、常见问题、咨询入口和按钮文案，让模块顺序更利于转化。')
            },
            {
                id: 'international',
                label: getText('aiPromptRecipeInternational', '国际化落地'),
                prompt: getText('aiPromptRecipeInternationalText', '优化成面向海外用户的国际化落地页基础：标题短句、卖点直接、CTA 清晰，SEO 标题和描述适合英文搜索，保留中文品牌调性。')
            },
            {
                id: 'visual',
                label: getText('aiPromptRecipeVisual', '视觉统一'),
                prompt: getText('aiPromptRecipeVisualText', '统一页面视觉：沿用当前全局设计令牌，减少杂色，统一按钮、卡片、间距、圆角和阴影，让页面更专业、更利于阅读。')
            },
            {
                id: 'module',
                label: getText('aiPromptRecipeModule', '当前模块'),
                prompt: getText('aiPromptRecipeModuleText', '只优化当前选中的模块：保留模块类型和信息结构，强化标题、描述、按钮文案和视觉节奏，不新增无关字段。')
            }
        ];
    }

    function renderAiPromptRecipesHtml() {
        var html = '<div class="qfb-ai-prompt-recipes-wrap">';
        html += '<div class="qfb-ai-prompt-recipes-head">';
        html += '<span>' + escapeHtml(getText('aiPromptRecipeLabel', '快捷需求')) + '</span>';
        html += '<small>' + escapeHtml(getText('aiPromptRecipeTip', '点一下会追加到装修需求里')) + '</small>';
        html += '</div>';
        html += '<div class="qfb-ai-prompt-recipes">';
        getAiPromptRecipes().forEach(function(recipe) {
            html += '<button type="button" class="button qfb-ai-prompt-recipe" data-ai-prompt-recipe="' + escapeHtml(recipe.id) + '">' + escapeHtml(recipe.label) + '</button>';
        });
        html += '</div>';
        html += '</div>';
        return html;
    }

    function applyAiPromptRecipe(recipeId) {
        if (!els.aiPane) {
            return;
        }

        var promptEl = els.aiPane.querySelector('#qfb-ai-prompt');
        if (!promptEl) {
            return;
        }

        var recipe = getAiPromptRecipes().filter(function(item) {
            return item.id === recipeId;
        })[0];
        if (!recipe || !recipe.prompt) {
            return;
        }

        var current = String(promptEl.value || '').trim();
        promptEl.value = current ? current + "\n\n" + recipe.prompt : recipe.prompt;
        promptEl.focus();
        setAiStatus(getText('aiPromptRecipeApplied', '已追加快捷需求，可继续补充细节。'), 'success');
        renderAiReadiness();
    }

    function collectAiPlainText(value, bucket, depth) {
        bucket = Array.isArray(bucket) ? bucket : [];
        depth = parseInt(depth, 10) || 0;
        if (bucket.join(' ').length > 2400 || depth > 5 || value === null || typeof value === 'undefined') {
            return bucket;
        }

        if (typeof value === 'string' || typeof value === 'number') {
            var text = String(value || '').replace(/\s+/g, ' ').trim();
            if (text && text.length < 280 && !/^(https?:|mailto:|tel:|#|var\(|rgba?\(|linear-gradient)/i.test(text)) {
                bucket.push(text);
            }
            return bucket;
        }

        if (Array.isArray(value)) {
            value.forEach(function(item) {
                collectAiPlainText(item, bucket, depth + 1);
            });
            return bucket;
        }

        if (typeof value === 'object') {
            Object.keys(value).forEach(function(key) {
                if (/(_ds_|image|img|icon|svg|video|url|link|href|src|color|background|shadow|border)/i.test(key)) {
                    return;
                }
                collectAiPlainText(value[key], bucket, depth + 1);
            });
        }

        return bucket;
    }

    function getAiPageContentText() {
        var parts = [];
        if (state.pageSettings && state.pageSettings.title) {
            parts.push(String(state.pageSettings.title));
        }
        (state.modules || []).forEach(function(moduleItem) {
            if (!moduleItem || typeof moduleItem !== 'object') {
                return;
            }
            if (moduleItem.type) {
                parts.push(getModuleName(moduleItem.type));
            }
            collectAiPlainText(moduleItem.data || {}, parts, 0);
        });

        return parts.join(' ').toLowerCase();
    }

    function getAiDesignPrimaryHex(fallback) {
        var tokens = getDesignTokens();
        var componentStyles = getComponentStyles();
        var candidates = [
            tokens.primary,
            tokens.accent,
            componentStyles.button_bg,
            componentStyles.badge_bg,
            fallback,
            '#2563eb'
        ];

        for (var i = 0; i < candidates.length; i++) {
            var hex = normalizeHex(candidates[i]);
            if (hex) {
                return hex;
            }
        }
        return '#2563eb';
    }

    function getAiPrimaryHexFromContent() {
        var text = getAiPageContentText();
        var rules = [
            { key: 'food', color: '#16a34a', words: ['食品', '餐饮', '餐厅', '菜单', '美食', '饮品', '咖啡', '茶饮', '食材', '烘焙', 'restaurant', 'food', 'menu', 'coffee'] },
            { key: 'renovation', color: '#b45309', words: ['装修', '家装', '建材', '施工', '空间', '设计院', '软装', '别墅', 'renovation', 'interior', 'construction'] },
            { key: 'medical', color: '#0f766e', words: ['医疗', '诊所', '康复', '护理', '健康', '医生', '医院', 'medical', 'health', 'clinic'] },
            { key: 'education', color: '#0f6b8f', words: ['教育', '课程', '培训', '老师', '学习', '招生', 'education', 'course', 'school'] },
            { key: 'technology', color: '#2563eb', words: ['科技', '软件', 'saas', 'ai', '系统', '数据', '云', '开发', '平台', 'technology', 'software', 'cloud'] }
        ];

        for (var i = 0; i < rules.length; i++) {
            var matched = rules[i].words.some(function(word) {
                return text.indexOf(word) !== -1;
            });
            if (matched) {
                return rules[i].color;
            }
        }

        return getAiDesignPrimaryHex('#2563eb');
    }

    function buildAiPageVisualStyle(primaryHex, options) {
        options = options && typeof options === 'object' ? options : {};
        var primary = hexToRgb(primaryHex) || hexToRgb('#2563eb');
        var primaryValue = rgbToHex(primary);
        var accentValue = normalizeHex(options.accent) || buildAiAccentHex(primaryValue, options.accentOffset);
        var accent = hexToRgb(accentValue) || hexToRgb('#38c9a6');
        var white = { r: 255, g: 255, b: 255 };
        var dark = { r: 17, g: 24, b: 39 };
        var background = normalizeHex(options.background) || rgbToHex(mixRgb(primary, white, options.bgMix || 0.93));
        var text = normalizeHex(options.text) || rgbToHex(mixRgb(primary, dark, options.textMix || 0.78));
        var footer = normalizeHex(options.footer) || rgbToHex(mixRgb(primary, dark, options.footerMix || 0.56));
        var wave = rgbToHex(mixRgb(primary, white, 0.78));
        var waveLayer = rgbToHex(mixRgb(accent, white, 0.82));
        var headerBg = options.headerBg || 'rgba(255,255,255,0.88)';
        var searchBg = options.searchBg || 'rgba(255,255,255,0.78)';
        var textOnPrimary = getReadableTextColorForHex(primaryValue);
        var textOnAccent = getReadableTextColorForHex(accentValue);

        return normalizePageVisualStyleSettings({
            mode: 'custom',
            preset: '',
            colors: {
                primary: primaryValue,
                accent: accentValue,
                background: background,
                text: text
            },
            header: {
                background: headerBg,
                text: text,
                transparent_text: '#ffffff',
                nav_hover_bg: 'linear-gradient(135deg,' + primaryValue + ',' + accentValue + ')',
                nav_hover_text: '#ffffff',
                search_bg: searchBg,
                search_text: text,
                search_placeholder: getReadableTextColorForHex(background) === '#ffffff' ? 'rgba(255,255,255,0.64)' : 'rgba(17,24,39,0.52)',
                search_icon: primaryValue,
                phone_bg: searchBg,
                phone_text: text
            },
            footer: {
                background: primaryValue,
                text: textOnPrimary,
                bottom_background: footer,
                bottom_text: textOnPrimary === '#ffffff' ? 'rgba(255,255,255,0.82)' : 'rgba(17,24,39,0.74)',
                wave_backdrop: background,
                wave_transition_from: background,
                wave_transition_height: '32px',
                wave_color: wave,
                wave_layer_color: waveLayer,
                wave_layer_opacity: '0.5'
            },
            buttons: {
                background: primaryValue,
                text: textOnPrimary,
                hover_background: accentValue,
                hover_text: textOnAccent
            }
        });
    }

    function getAiCurrentPageStylePrimary() {
        var settings = normalizePageVisualStyleSettings(ensurePageSettingsState().visualStyle || {});
        var currentPrimary = settings.colors && settings.colors.primary ? normalizeHex(settings.colors.primary) : '';
        if (currentPrimary) {
            return currentPrimary;
        }
        return getAiDesignPrimaryHex('#2563eb');
    }

    function getAiLogoPrimaryHex() {
        var logo = document.querySelector('.custom-logo, .site-logo img, .site-branding img');
        if (logo) {
            var imageColor = getAiLogoImagePrimaryHex(logo);
            if (imageColor) {
                return imageColor;
            }
            var color = window.getComputedStyle(logo).getPropertyValue('--color-primary') || '';
            var logoColor = normalizeHex(color);
            if (logoColor) {
                return logoColor;
            }
        }

        return getAiDesignPrimaryHex(getAiCurrentPageStylePrimary());
    }

    function getAiLogoImagePrimaryHex(logo) {
        if (!logo || !logo.complete || !logo.naturalWidth || !logo.naturalHeight) {
            return '';
        }

        try {
            var canvas = document.createElement('canvas');
            var size = 48;
            var ratio = Math.min(size / logo.naturalWidth, size / logo.naturalHeight);
            var width = Math.max(1, Math.round(logo.naturalWidth * ratio));
            var height = Math.max(1, Math.round(logo.naturalHeight * ratio));
            var context = canvas.getContext('2d');
            var data;
            var total = 0;
            var red = 0;
            var green = 0;
            var blue = 0;

            canvas.width = width;
            canvas.height = height;
            if (!context) {
                return '';
            }
            context.drawImage(logo, 0, 0, width, height);
            data = context.getImageData(0, 0, width, height).data;

            for (var i = 0; i < data.length; i += 4) {
                var alpha = data[i + 3];
                var r = data[i];
                var g = data[i + 1];
                var b = data[i + 2];
                var max = Math.max(r, g, b);
                var min = Math.min(r, g, b);
                var saturation = max - min;
                var brightness = (r + g + b) / 3;

                if (alpha < 80 || brightness > 245 || brightness < 18 || saturation < 10) {
                    continue;
                }
                red += r;
                green += g;
                blue += b;
                total++;
            }

            if (!total) {
                return '';
            }

            return rgbToHex(Math.round(red / total), Math.round(green / total), Math.round(blue / total));
        } catch (e) {
            return '';
        }
    }

    function getAiStyleSwatches(visualStyle) {
        visualStyle = normalizePageVisualStyleSettings(visualStyle || {});
        return [
            visualStyle.colors.primary || '',
            visualStyle.colors.accent || '',
            visualStyle.colors.background || '',
            visualStyle.buttons.background || ''
        ].filter(Boolean);
    }

    function setAiPageStylePending(action, label, visualStyle, warnings) {
        visualStyle = normalizePageVisualStyleSettings(visualStyle || {});
        clearAiPendingResult();
        renderAiWarnings(warnings || []);
        setAiPendingResult({
            kind: 'page_style',
            mode: 'visual_style',
            action: action,
            label: label,
            visualStyle: visualStyle,
            swatches: getAiStyleSwatches(visualStyle),
            baseModules: deepClone(state.modules),
            basePageSettings: deepClone(state.pageSettings || {}),
            successMessage: getText('aiPageStyleApplySuccess', '页面风格建议已应用，请确认预览后保存。')
        });
        setAiStatus(getText('aiPageStylePendingReady', '局部风格建议已生成，请先预览差异，再确认应用。'), 'success');
    }

    function generateAiPaletteFromContent() {
        var primary = getAiPrimaryHexFromContent();
        setAiPageStylePending(
            'palette_from_content',
            getText('aiLocalPaletteFromContent', '根据当前页面内容生成配色'),
            buildAiPageVisualStyle(primary),
            [getText('aiLocalPreviewOnlyWarning', 'AI 结果只会进入待应用预览，不会直接覆盖全站。')]
        );
    }

    function generateAiStyleFromLogo() {
        var primary = getAiLogoPrimaryHex();
        setAiPageStylePending(
            'style_from_logo',
            getText('aiLocalStyleFromLogo', '根据 Logo 主色生成页面风格'),
            buildAiPageVisualStyle(primary, { accentOffset: 0.35 }),
            [getText('aiLogoFallbackWarning', '如果无法读取 Logo 像素，会使用当前全局主色作为品牌主色。')]
        );
    }

    function repairAiPageReadability() {
        var primary = getAiCurrentPageStylePrimary();
        setAiPageStylePending(
            'repair_readability',
            getText('aiLocalRepairReadability', '修复当前页面看不清的问题'),
            buildAiPageVisualStyle(primary),
            [getText('aiReadabilityRepairWarning', '已按防改乱规则统一文字、按钮、CTA 和底部波浪颜色。')]
        );
    }

    function buildAiStyleRecommendations() {
        var contentPrimary = getAiPrimaryHexFromContent();
        var logoPrimary = getAiLogoPrimaryHex();
        var currentPrimary = getAiCurrentPageStylePrimary();
        return [
            {
                id: 'content',
                label: getText('aiStyleRecContent', '内容匹配'),
                desc: getText('aiStyleRecContentDesc', '按当前页面文案和模块类型匹配行业感。'),
                visualStyle: buildAiPageVisualStyle(contentPrimary)
            },
            {
                id: 'brand',
                label: getText('aiStyleRecBrand', '品牌主色'),
                desc: getText('aiStyleRecBrandDesc', '围绕 Logo 或全局主色统一按钮、顶部和页脚。'),
                visualStyle: buildAiPageVisualStyle(logoPrimary, { accentOffset: 0.35 })
            },
            {
                id: 'contrast',
                label: getText('aiStyleRecContrast', '高对比转化'),
                desc: getText('aiStyleRecContrastDesc', '强化 CTA、页脚和波浪层次，优先保证可读性。'),
                visualStyle: buildAiPageVisualStyle(currentPrimary, { accentOffset: 0.5, bgMix: 0.9, footerMix: 0.48 })
            }
        ].map(function(item) {
            item.swatches = getAiStyleSwatches(item.visualStyle);
            return item;
        });
    }

    function renderAiStyleRecommendations() {
        if (!els.aiPane) {
            return;
        }

        var target = els.aiPane.querySelector('#qfb-ai-style-recommendations');
        if (!target) {
            return;
        }

        var recommendations = Array.isArray(state.aiStyleRecommendations) ? state.aiStyleRecommendations : [];
        if (!recommendations.length) {
            target.style.display = 'none';
            target.innerHTML = '';
            return;
        }

        var html = '<div class="qfb-ai-style-recommendations-card">';
        html += '<div class="qfb-ai-local-head"><strong>' + escapeHtml(getText('aiStyleRecommendationsTitle', '推荐风格方案')) + '</strong><span>' + escapeHtml('3') + '</span></div>';
        html += '<div class="qfb-ai-style-recommendations-list">';
        recommendations.forEach(function(item) {
            html += '<button type="button" class="qfb-ai-style-recommendation" data-ai-style-recommendation="' + escapeHtml(item.id) + '">';
            html += '<span>' + escapeHtml(item.label) + '</span>';
            html += '<em>' + escapeHtml(item.desc) + '</em>';
            html += '<b>';
            (item.swatches || []).forEach(function(color) {
                html += '<i style="background:' + escapeHtml(color) + ';"></i>';
            });
            html += '</b>';
            html += '</button>';
        });
        html += '</div>';
        html += '</div>';

        target.innerHTML = html;
        target.style.display = '';
    }

    function recommendAiPageStyles() {
        state.aiStyleRecommendations = buildAiStyleRecommendations();
        renderAiStyleRecommendations();
        setAiStatus(getText('aiStyleRecommendationsReady', '已推荐 3 套风格方案，选择一套后会先进入待应用预览。'), 'success');
    }

    function applyAiStyleRecommendation(recommendationId) {
        var recommendations = Array.isArray(state.aiStyleRecommendations) ? state.aiStyleRecommendations : [];
        var selected = recommendations.filter(function(item) {
            return item && item.id === recommendationId;
        })[0];
        if (!selected) {
            return;
        }

        setAiPageStylePending('style_recommendation_' + selected.id, selected.label, selected.visualStyle, []);
    }

    function getAiModuleTaskPrompt(taskKey) {
        var selectedModule = state.selectedIndex >= 0 && state.selectedIndex < state.modules.length ? state.modules[state.selectedIndex] : null;
        var moduleName = selectedModule && selectedModule.type ? getModuleName(selectedModule.type) : getText('aiCurrentModuleName', '当前模块');
        if (taskKey === 'module_cta') {
            return getText('aiLocalModuleCtaPrompt', '只生成当前模块的 CTA 文案：保留模块类型、布局、图片、链接和样式，只优化标题附近的行动号召、按钮文案和转化语气，给出清晰、短句、适合普通用户点击的表达。目标模块：') + moduleName;
        }
        return getText('aiLocalModuleCopyPrompt', '只优化当前模块文案：保留模块类型、布局、图片、链接和样式，不新增无关字段；优化标题、描述、卖点和说明文字，让内容更清楚、更像真人表达。目标模块：') + moduleName;
    }

    function runAiModuleLocalTask(taskKey) {
        if (!els.aiPane) {
            return;
        }
        if (isAiBusy()) {
            setAiStatus(getText('aiBusy', 'AI 请求进行中，请稍候。'), 'warning');
            return;
        }
        if (state.selectedIndex < 0 || state.selectedIndex >= state.modules.length) {
            setAiStatus(getText('aiNoSelectedModule', '请先在页面结构中选中一个模块，再使用当前模块优化。'), 'error');
            return;
        }

        var promptEl = els.aiPane.querySelector('#qfb-ai-prompt');
        if (promptEl) {
            promptEl.value = getAiModuleTaskPrompt(taskKey);
            renderAiReadiness();
        }
        optimizeCurrentModuleWithAi();
    }

    function renderAiLocalAssistHtml() {
        var html = '<div class="qfb-ai-local-card">';
        var disabledAttr = state.aiBusy ? ' disabled' : '';
        html += '<div class="qfb-ai-local-head">';
        html += '<strong>' + escapeHtml(getText('aiLocalAssistTitle', '局部 AI 辅助')) + '</strong>';
        html += '<span>' + escapeHtml(getText('aiLocalAssistBadge', '当前页 / 当前模块')) + '</span>';
        html += '</div>';
        html += '<div class="qfb-ai-local-actions">';
        [
            ['palette_content', getText('aiLocalPaletteFromContent', '根据内容生成配色')],
            ['style_logo', getText('aiLocalStyleFromLogo', '根据 Logo 主色生成风格')],
            ['repair_readability', getText('aiLocalRepairReadability', '修复看不清的问题')],
            ['recommend_styles', getText('aiLocalRecommendStyles', '推荐 3 套风格')],
            ['module_copy', getText('aiLocalOptimizeCopy', '优化当前模块文案')],
            ['module_cta', getText('aiLocalGenerateCta', '生成当前 CTA 文案')]
        ].forEach(function(item) {
            html += '<button type="button" class="button qfb-ai-local-action" data-ai-local-action="' + escapeHtml(item[0]) + '"' + disabledAttr + '>' + escapeHtml(item[1]) + '</button>';
        });
        html += '</div>';
        html += '<div id="qfb-ai-style-recommendations" class="qfb-ai-style-recommendations" style="display:none;"></div>';
        html += '</div>';
        return html;
    }

    function handleAiLocalAction(action) {
        if (isAiBusy()) {
            setAiStatus(getText('aiBusy', 'AI 请求进行中，请稍候。'), 'warning');
            return;
        }
        if (action === 'palette_content') {
            generateAiPaletteFromContent();
            return;
        }
        if (action === 'style_logo') {
            generateAiStyleFromLogo();
            return;
        }
        if (action === 'repair_readability') {
            repairAiPageReadability();
            return;
        }
        if (action === 'recommend_styles') {
            recommendAiPageStyles();
            return;
        }
        if (action === 'module_cta' || action === 'module_copy') {
            runAiModuleLocalTask(action);
        }
    }

    function getAiPromptHistoryTitle(prompt) {
        var title = String(prompt || '').replace(/\s+/g, ' ').trim();
        if (!title) {
            return getText('aiPromptHistoryUntitled', '未命名需求');
        }
        return title.length > 42 ? title.slice(0, 42) + '...' : title;
    }

    function pushAiPromptHistory(payload) {
        var item = normalizeAiPromptHistoryItem({
            id: 'prompt-' + Date.now() + '-' + Math.floor(Math.random() * 1000),
            prompt: payload && payload.prompt,
            moduleIds: payload && payload.moduleIds,
            connectionId: payload && payload.connectionId,
            model: payload && payload.model,
            createdAt: Date.now()
        });
        if (!item) {
            return;
        }

        var signature = item.prompt + '|' + item.moduleIds.join(',');
        state.aiPromptHistory = (state.aiPromptHistory || []).filter(function(historyItem) {
            return historyItem && (historyItem.prompt + '|' + (historyItem.moduleIds || []).join(',')) !== signature;
        });
        state.aiPromptHistory.unshift(item);
        state.aiPromptHistory = state.aiPromptHistory.slice(0, 8);
        persistAiPromptHistory();
        renderAiPromptHistory();
    }

    function renderAiPromptHistory() {
        if (!els.aiPane) {
            return;
        }

        var historyEl = els.aiPane.querySelector('#qfb-ai-prompt-history');
        if (!historyEl) {
            return;
        }

        var history = Array.isArray(state.aiPromptHistory) ? state.aiPromptHistory.slice(0, 5) : [];
        if (!history.length) {
            historyEl.style.display = 'none';
            historyEl.innerHTML = '';
            return;
        }

        var html = '<div class="qfb-ai-prompt-history-card">';
        html += '<div class="qfb-ai-prompt-history-head">';
        html += '<strong>' + escapeHtml(getText('aiPromptHistoryLabel', '最近需求')) + '</strong>';
        html += '<button type="button" class="button-link" data-ai-prompt-history-clear="1">' + escapeHtml(getText('aiPromptHistoryClear', '清空')) + '</button>';
        html += '</div>';
        html += '<p>' + escapeHtml(getText('aiPromptHistoryTip', '只保存在当前浏览器，方便反复微调。')) + '</p>';
        html += '<ul>';
        history.forEach(function(item) {
            var moduleCount = Array.isArray(item.moduleIds) ? item.moduleIds.length : 0;
            html += '<li>';
            html += '<button type="button" class="qfb-ai-prompt-history-use" data-ai-prompt-history="' + escapeHtml(item.id) + '">';
            html += '<span>' + escapeHtml(getAiPromptHistoryTitle(item.prompt)) + '</span>';
            html += '<em>' + escapeHtml(formatCountText(getText('aiPromptHistoryModuleCount', '%d 个候选模块'), moduleCount)) + '</em>';
            html += '</button>';
            html += '<button type="button" class="button-link qfb-ai-prompt-history-delete" data-ai-prompt-history-delete="' + escapeHtml(item.id) + '">' + escapeHtml(getText('aiPromptHistoryDelete', '删除')) + '</button>';
            html += '</li>';
        });
        html += '</ul>';
        html += '</div>';

        historyEl.innerHTML = html;
        historyEl.style.display = '';
    }

    function getAiPromptHistoryItem(historyId) {
        var history = Array.isArray(state.aiPromptHistory) ? state.aiPromptHistory : [];
        return history.filter(function(item) {
            return item && item.id === historyId;
        })[0] || null;
    }

    function applyAiPromptHistory(historyId) {
        if (!els.aiPane) {
            return;
        }

        var item = getAiPromptHistoryItem(historyId);
        var promptEl = els.aiPane.querySelector('#qfb-ai-prompt');
        if (!item || !promptEl) {
            return;
        }

        promptEl.value = item.prompt;
        var connectionEl = els.aiPane.querySelector('#qfb-ai-connection');
        if (connectionEl && item.connectionId) {
            connectionEl.value = item.connectionId;
            updateAiModelSuggestions();
        }

        var modelEl = els.aiPane.querySelector('#qfb-ai-model');
        if (modelEl) {
            modelEl.value = item.model || modelEl.value || '';
        }

        setAiModuleSelection(item.moduleIds || []);
        promptEl.focus();
        renderAiReadiness();
        setAiStatus(getText('aiPromptHistoryRestored', '已恢复最近需求。'), 'success');
    }

    function deleteAiPromptHistory(historyId) {
        state.aiPromptHistory = (state.aiPromptHistory || []).filter(function(item) {
            return item && item.id !== historyId;
        });
        persistAiPromptHistory();
        renderAiPromptHistory();
        setAiStatus(getText('aiPromptHistoryDeleted', '已删除该需求。'), 'warning');
    }

    function clearAiPromptHistory() {
        state.aiPromptHistory = [];
        persistAiPromptHistory();
        renderAiPromptHistory();
        setAiStatus(getText('aiPromptHistoryCleared', '已清空最近需求。'), 'warning');
    }

    function normalizeAiMetaValues(values) {
        if (!Array.isArray(values)) {
            return [];
        }

        return values.map(function(value) {
            return String(value || '').toLowerCase();
        }).filter(Boolean);
    }

    function countAiMetaMatches(values, targets) {
        values = normalizeAiMetaValues(values);
        targets = normalizeAiMetaValues(targets);
        if (!values.length || !targets.length) {
            return 0;
        }

        var count = 0;
        targets.forEach(function(target) {
            if (values.indexOf(target) !== -1) {
                count++;
            }
        });
        return count;
    }

    function getAiModuleBundles() {
        return [
            {
                id: 'landing',
                label: getText('aiModuleBundleLanding', '落地页'),
                pageTags: ['home', 'landing'],
                intentTags: ['hero', 'conversion', 'proof', 'trust', 'lead_capture'],
                contentModels: ['page', 'service', 'product', 'case', 'faq'],
                schemaTypes: ['Organization', 'WebSite', 'Service', 'FAQPage'],
                keywords: ['hero', 'banner', 'feature', 'service', 'case', 'testimonial', 'pricing', 'faq', 'contact', 'cta']
            },
            {
                id: 'service',
                label: getText('aiModuleBundleService', '服务页'),
                pageTags: ['services', 'landing'],
                intentTags: ['conversion', 'education', 'trust', 'support', 'lead_capture'],
                contentModels: ['service', 'case', 'testimonial', 'faq', 'branch'],
                schemaTypes: ['Service', 'FAQPage', 'LocalBusiness'],
                keywords: ['service', 'feature', 'process', 'case', 'testimonial', 'faq', 'contact']
            },
            {
                id: 'product',
                label: getText('aiModuleBundleProduct', '产品页'),
                pageTags: ['products', 'pricing', 'landing'],
                intentTags: ['commerce', 'listing', 'pricing', 'comparison', 'conversion'],
                contentModels: ['product', 'service', 'faq'],
                schemaTypes: ['Product', 'ItemList', 'FAQPage'],
                keywords: ['product', 'pricing', 'comparison', 'feature', 'download', 'faq', 'cta']
            },
            {
                id: 'content',
                label: getText('aiModuleBundleContent', '内容页'),
                pageTags: ['blog', 'news', 'resource', 'home'],
                intentTags: ['content', 'listing', 'media', 'education'],
                contentModels: ['post', 'resource', 'author', 'page'],
                schemaTypes: ['Article', 'BlogPosting', 'CollectionPage'],
                keywords: ['blog', 'news', 'resource', 'article', 'media', 'category', 'author']
            },
            {
                id: 'local',
                label: getText('aiModuleBundleLocal', '本地门店'),
                pageTags: ['contact', 'booking', 'landing'],
                intentTags: ['lead_capture', 'trust', 'conversion', 'support'],
                contentModels: ['branch', 'service', 'faq'],
                schemaTypes: ['LocalBusiness', 'Organization', 'Service', 'FAQPage'],
                keywords: ['contact', 'branch', 'booking', 'faq', 'service', 'trust', 'map']
            }
        ];
    }

    function scoreAiModuleForBundle(moduleItem, bundle) {
        if (!moduleItem || typeof moduleItem !== 'object' || moduleItem.aiEnabled === false) {
            return 0;
        }

        var score = 0;
        score += countAiMetaMatches(moduleItem.pageTags, bundle.pageTags) * 6;
        score += countAiMetaMatches(moduleItem.intentTags, bundle.intentTags) * 5;
        score += countAiMetaMatches(moduleItem.contentModels, bundle.contentModels) * 4;
        score += countAiMetaMatches(moduleItem.schemaTypes, bundle.schemaTypes) * 3;

        var haystack = getModuleSearchText(moduleItem);
        normalizeAiMetaValues(bundle.keywords).forEach(function(keyword) {
            if (haystack.indexOf(keyword) !== -1) {
                score += 2;
            }
        });

        return score;
    }

    function getAiModuleBundleSelection(bundleId) {
        var bundle = getAiModuleBundles().filter(function(item) {
            return item.id === bundleId;
        })[0];
        if (!bundle || !Array.isArray(state.availableModules)) {
            return [];
        }

        var scored = [];
        state.availableModules.forEach(function(moduleItem, index) {
            var score = scoreAiModuleForBundle(moduleItem, bundle);
            if (score <= 0 || !moduleItem || !moduleItem.id) {
                return;
            }
            scored.push({
                id: String(moduleItem.id),
                score: score,
                index: index
            });
        });

        scored.sort(function(a, b) {
            if (a.score !== b.score) {
                return b.score - a.score;
            }
            return a.index - b.index;
        });

        return scored.slice(0, getAiMaxModules()).map(function(item) {
            return item.id;
        });
    }

    function setAiModuleSelection(moduleIds) {
        if (!els.aiPane) {
            return 0;
        }

        var selected = {};
        moduleIds = Array.isArray(moduleIds) ? moduleIds.slice(0, getAiMaxModules()) : [];
        moduleIds.forEach(function(moduleId) {
            selected[String(moduleId)] = true;
        });

        var selectedCount = 0;
        var inputs = els.aiPane.querySelectorAll('#qfb-ai-module-list input[type="checkbox"]');
        Array.prototype.forEach.call(inputs, function(input) {
            var shouldSelect = !!selected[String(input.value || '')];
            input.checked = shouldSelect;
            if (shouldSelect) {
                selectedCount++;
            }
        });
        updateAiSelectedCount();
        renderAiReadiness();
        return selectedCount;
    }

    function renderAiModuleBundlesHtml() {
        var html = '<div class="qfb-ai-module-bundles-wrap">';
        html += '<div class="qfb-ai-module-bundles-head">';
        html += '<span>' + escapeHtml(getText('aiModuleBundleLabel', '模块组合')) + '</span>';
        html += '<small>' + escapeHtml(getText('aiModuleBundleTip', '按页面目标快速勾选候选模块')) + '</small>';
        html += '</div>';
        html += '<div class="qfb-ai-module-bundles">';
        getAiModuleBundles().forEach(function(bundle) {
            html += '<button type="button" class="button qfb-ai-module-bundle" data-ai-module-bundle="' + escapeHtml(bundle.id) + '">' + escapeHtml(bundle.label) + '</button>';
        });
        html += '<button type="button" class="button qfb-ai-module-bundle is-clear" data-ai-module-bundle="clear">' + escapeHtml(getText('aiModuleBundleClear', '清空')) + '</button>';
        html += '</div>';
        html += '</div>';
        return html;
    }

    function applyAiModuleBundle(bundleId) {
        if (bundleId === 'clear') {
            setAiModuleSelection([]);
            setAiStatus(getText('aiModuleBundleCleared', '已清空候选模块。'), 'warning');
            return;
        }

        var moduleIds = getAiModuleBundleSelection(bundleId);
        var selectedCount = setAiModuleSelection(moduleIds);
        if (!selectedCount) {
            setAiStatus(getText('aiModuleBundleEmpty', '没有找到适合该组合的候选模块，请手动选择。'), 'warning');
            return;
        }
        setAiStatus(formatCountText(getText('aiModuleBundleApplied', '已选择 %d 个候选模块。'), selectedCount), 'success');
    }

    function getAiReadinessItems() {
        if (!els.aiPane) {
            return [];
        }

        var promptEl = els.aiPane.querySelector('#qfb-ai-prompt');
        var connectionEl = els.aiPane.querySelector('#qfb-ai-connection');
        var modelEl = els.aiPane.querySelector('#qfb-ai-model');
        var prompt = promptEl ? String(promptEl.value || '').trim() : '';
        var selectedCount = getAiSelectedModules().length;
        var maxModules = getAiMaxModules();
        var connectionLabel = connectionEl && connectionEl.options && connectionEl.selectedIndex >= 0
            ? String(connectionEl.options[connectionEl.selectedIndex].text || '').trim()
            : '';
        var model = modelEl ? String(modelEl.value || '').trim() : '';

        return [
            {
                label: getText('aiReadinessPrompt', '需求'),
                status: prompt.length >= 20 ? 'ok' : (prompt.length ? 'warning' : 'error'),
                detail: prompt.length >= 20
                    ? formatCountText(getText('aiReadinessPromptOk', '已输入 %d 字'), prompt.length)
                    : (prompt.length
                        ? getText('aiReadinessPromptShort', '建议再补充目标、人群和风格。')
                        : getText('aiReadinessPromptMissing', '还没填写装修需求。'))
            },
            {
                label: getText('aiReadinessModules', '模块'),
                status: selectedCount > 0 && selectedCount <= maxModules ? 'ok' : 'error',
                detail: selectedCount > 0
                    ? String(selectedCount) + '/' + String(maxModules)
                    : getText('aiReadinessModulesMissing', '还没选择候选模块。')
            },
            {
                label: getText('aiReadinessConnection', '连接'),
                status: connectionLabel ? 'ok' : 'warning',
                detail: connectionLabel || getText('aiReadinessConnectionDefault', '将使用后台默认连接。')
            },
            {
                label: getText('aiReadinessModel', '模型'),
                status: model ? 'ok' : 'warning',
                detail: model || getText('aiReadinessModelDefault', '将使用连接默认模型。')
            },
            {
                label: getText('aiReadinessPending', '待应用'),
                status: state.aiPendingResult ? 'warning' : 'ok',
                detail: state.aiPendingResult
                    ? getText('aiReadinessPendingExists', '已有生成结果待应用或放弃。')
                    : getText('aiReadinessPendingEmpty', '当前没有待应用结果。')
            }
        ];
    }

    function renderAiReadiness() {
        if (!els.aiPane) {
            return;
        }

        var readinessEl = els.aiPane.querySelector('#qfb-ai-readiness');
        if (!readinessEl) {
            return;
        }

        var items = getAiReadinessItems();
        var blockingCount = items.filter(function(item) {
            return item.status === 'error';
        }).length;
        var warningCount = items.filter(function(item) {
            return item.status === 'warning';
        }).length;
        var summary = blockingCount
            ? formatCountText(getText('aiReadinessBlocking', '%d 项需要处理'), blockingCount)
            : (warningCount
                ? formatCountText(getText('aiReadinessWarning', '%d 项建议确认'), warningCount)
                : getText('aiReadinessReady', '可以生成草稿'));

        var html = '<div class="qfb-ai-readiness-card is-' + (blockingCount ? 'error' : (warningCount ? 'warning' : 'ok')) + '">';
        html += '<div class="qfb-ai-readiness-head">';
        html += '<strong>' + escapeHtml(getText('aiReadinessTitle', '生成前自检')) + '</strong>';
        html += '<span>' + escapeHtml(summary) + '</span>';
        html += '</div>';
        html += '<ul>';
        items.forEach(function(item) {
            html += '<li class="is-' + escapeHtml(item.status) + '">';
            html += '<span>' + escapeHtml(item.label) + '</span>';
            html += '<em>' + escapeHtml(item.detail) + '</em>';
            html += '</li>';
        });
        html += '</ul>';
        html += '</div>';

        readinessEl.innerHTML = html;
    }

    function getAiLocalizationConfig() {
        if (!aiBuilderService || typeof aiBuilderService.getLocalizationConfig !== 'function') {
            return {
                languages: { en: '英文', ja: '日文', ko: '韩文', fr: '法文', de: '德文', es: '西班牙文' },
                tones: { professional: '专业可信', friendly: '自然友好', concise: '简洁直接', premium: '高端克制', technical: '技术清晰' },
                defaultCurrency: 'USD',
                preserveLayout: true
            };
        }

        return aiBuilderService.getLocalizationConfig(state.aiConfig);
    }

    function renderAiLocalizationControlsHtml() {
        var config = getAiLocalizationConfig();
        var languages = config.languages && typeof config.languages === 'object' ? config.languages : {};
        var tones = config.tones && typeof config.tones === 'object' ? config.tones : {};
        var tonePacks = config.industryTonePacks && typeof config.industryTonePacks === 'object' ? config.industryTonePacks : {};
        var html = '<div class="qfb-ai-localization-card">';
        html += '<div class="qfb-ai-localization-head">';
        html += '<strong>' + escapeHtml(getText('aiLocalizationModeTitle', '本地化模式')) + '</strong>';
        html += '<span>' + escapeHtml(getText('aiLocalizationModeBadge', '模块 / 整页')) + '</span>';
        html += '</div>';
        html += '<div class="qfb-ai-localization-grid">';
        html += '<label><span>' + escapeHtml(getText('aiLocalizationLanguage', '目标语言')) + '</span><select id="qfb-ai-localization-language" class="qfb-input">';
        Object.keys(languages).forEach(function(code) {
            html += '<option value="' + escapeHtml(code) + '">' + escapeHtml(languages[code]) + '</option>';
        });
        html += '</select></label>';
        html += '<label><span>' + escapeHtml(getText('aiLocalizationMarket', '目标市场')) + '</span><input type="text" id="qfb-ai-localization-market" class="qfb-input" placeholder="' + escapeHtml(getText('aiLocalizationMarketPlaceholder', '例如：美国 / 日本 / 韩国 / 法国 / 德国 / 西班牙')) + '" /></label>';
        html += '<label><span>' + escapeHtml(getText('aiLocalizationTone', '语气')) + '</span><select id="qfb-ai-localization-tone" class="qfb-input">';
        Object.keys(tones).forEach(function(code) {
            html += '<option value="' + escapeHtml(code) + '">' + escapeHtml(tones[code]) + '</option>';
        });
        html += '</select></label>';
        html += '<label><span>' + escapeHtml(getText('aiLocalizationCurrency', '币种')) + '</span><input type="text" id="qfb-ai-localization-currency" class="qfb-input" value="' + escapeHtml(config.defaultCurrency || 'USD') + '" maxlength="3" /></label>';
        html += '<label class="is-wide"><span>' + escapeHtml(getText('aiLocalizationIndustry', '行业')) + '</span><input type="text" id="qfb-ai-localization-industry" class="qfb-input" placeholder="' + escapeHtml(getText('aiLocalizationIndustryPlaceholder', '例如：SaaS / 外贸 / 教育')) + '" /></label>';
        html += '<label class="is-wide"><span>' + escapeHtml(getText('aiLocalizationTonePack', '行业语气包')) + '</span><select id="qfb-ai-localization-tone-pack" class="qfb-input">';
        html += '<option value="">' + escapeHtml(getText('aiLocalizationTonePackAuto', '按行业自动判断')) + '</option>';
        Object.keys(tonePacks).forEach(function(code) {
            var item = tonePacks[code] || {};
            html += '<option value="' + escapeHtml(code) + '">' + escapeHtml(item.label || code) + '</option>';
        });
        html += '</select></label>';
        html += '<label class="is-wide"><span>' + escapeHtml(getText('aiLocalizationFixedTranslations', '固定翻译')) + '</span><textarea id="qfb-ai-localization-fixed" class="qfb-input qfb-ai-localization-mini" rows="2" placeholder="' + escapeHtml(getText('aiLocalizationFixedPlaceholder', '每行一个：源词=译文')) + '"></textarea></label>';
        html += '<label><span>' + escapeHtml(getText('aiLocalizationForbiddenWords', '禁用词')) + '</span><textarea id="qfb-ai-localization-forbidden" class="qfb-input qfb-ai-localization-mini" rows="2" placeholder="' + escapeHtml(getText('aiLocalizationTermsPlaceholder', '逗号或换行分隔')) + '"></textarea></label>';
        html += '<label><span>' + escapeHtml(getText('aiLocalizationProductTerms', '产品名词库')) + '</span><textarea id="qfb-ai-localization-products" class="qfb-input qfb-ai-localization-mini" rows="2" placeholder="' + escapeHtml(getText('aiLocalizationTermsPlaceholder', '逗号或换行分隔')) + '"></textarea></label>';
        html += '<label class="qfb-ai-localization-preserve"><input type="checkbox" checked disabled /> <span>' + escapeHtml(getText('aiLocalizationPreserveLayout', '强制保留原布局')) + '</span></label>';
        html += '<label class="qfb-ai-localization-preserve"><input type="checkbox" id="qfb-ai-localization-sync-provider" checked /> <span>' + escapeHtml(getText('aiLocalizationSyncProvider', '同步启灵AI多语言')) + '</span></label>';
        html += '<label class="qfb-ai-localization-preserve"><input type="checkbox" id="qfb-ai-localization-create-page" /> <span>' + escapeHtml(getText('aiLocalizationCreatePage', '生成对应语言页面')) + '</span></label>';
        html += '</div>';
        html += '<p>' + escapeHtml(getText('aiLocalizationHelp', '本地化只会改文案字段；布局、样式、图片、图标、链接和数据源字段会被服务端白名单拦截。')) + '</p>';
        html += '</div>';

        return html;
    }

    function getAiLocalizationOptions() {
        if (!els.aiPane || !aiBuilderService || typeof aiBuilderService.normalizeLocalizationOptions !== 'function') {
            return {};
        }

        function read(selector) {
            var input = els.aiPane.querySelector(selector);
            return input ? String(input.value || '').trim() : '';
        }

        return aiBuilderService.normalizeLocalizationOptions({
            target_language: read('#qfb-ai-localization-language'),
            target_market: read('#qfb-ai-localization-market'),
            tone: read('#qfb-ai-localization-tone'),
            currency: read('#qfb-ai-localization-currency'),
            industry: read('#qfb-ai-localization-industry'),
            industry_tone_pack: read('#qfb-ai-localization-tone-pack'),
            fixed_translations: read('#qfb-ai-localization-fixed'),
            forbidden_words: read('#qfb-ai-localization-forbidden'),
            product_terms: read('#qfb-ai-localization-products'),
            sync_provider: !!els.aiPane.querySelector('#qfb-ai-localization-sync-provider') && els.aiPane.querySelector('#qfb-ai-localization-sync-provider').checked,
            create_language_page: !!els.aiPane.querySelector('#qfb-ai-localization-create-page') && els.aiPane.querySelector('#qfb-ai-localization-create-page').checked,
            preserve_layout: true
        }, state.aiConfig);
    }

    function renderAiPane() {
        if (!els.aiPane) {
            return;
        }

        if (!state.aiConfig || !state.aiConfig.enabled) {
            els.aiPane.className = 'qfb-settings-empty';
            els.aiPane.innerHTML = escapeHtml(getText('aiUnavailable', 'AI 装修尚未配置完成，请先到主题设置中启用并配置连接。'));
            return;
        }

        var connections = Array.isArray(state.aiConfig.connections) ? state.aiConfig.connections : [];
        var defaultConnectionId = String(state.aiConfig.defaultConnectionId || '');
        var defaultModel = String(state.aiConfig.defaultModel || '');
        var disabledAttr = state.aiBusy ? ' disabled' : '';
        var html = '';

        html += '<div class="qfb-ai-pane-inner">';
        html += renderAiScopeNoticeHtml();
        html += renderAiDesignContextHtml();
        html += renderAiContentModelContextHtml();
        html += renderAiLocalAssistHtml();
        html += '<details class="qfb-ai-advanced-panel">';
        html += '<summary>' + escapeHtml(getText('aiAdvancedSummary', '高级：单页草稿和本地化')) + '</summary>';
        html += '<div class="qfb-ai-field">';
        html += '<label class="qfb-label" for="qfb-ai-connection">' + escapeHtml(getText('aiConnectionLabel', 'AI 连接')) + '</label>';
        html += '<select id="qfb-ai-connection" class="qfb-input">';
        connections.forEach(function(connection) {
            if (!connection || !connection.id) {
                return;
            }
            var selected = String(connection.id) === defaultConnectionId ? ' selected' : '';
            html += '<option value="' + escapeHtml(connection.id) + '"' + selected + '>' + escapeHtml(connection.name || connection.id) + '</option>';
        });
        html += '</select>';
        html += '</div>';

        html += '<div class="qfb-ai-field">';
        html += '<label class="qfb-label" for="qfb-ai-model">' + escapeHtml(getText('aiModelLabel', '模型')) + '</label>';
        html += '<input type="text" id="qfb-ai-model" class="qfb-input" value="' + escapeHtml(defaultModel) + '" list="qfb-ai-model-list" />';
        html += '<datalist id="qfb-ai-model-list"></datalist>';
        html += '</div>';

        html += '<div class="qfb-ai-field">';
        html += '<label class="qfb-label" for="qfb-ai-prompt">' + escapeHtml(getText('aiPromptLabel', '装修需求')) + '</label>';
        html += '<textarea id="qfb-ai-prompt" class="qfb-input qfb-ai-textarea" rows="7" placeholder="' + escapeHtml(getText('aiPromptPlaceholder', '例如：优化当前首页，面向软件服务公司，风格现代可信。保留可用内容，强化首屏卖点、服务能力、客户案例、CTA 和 SEO 标题描述。')) + '"></textarea>';
        html += renderAiPromptRecipesHtml();
        html += '<div id="qfb-ai-prompt-history" class="qfb-ai-prompt-history" style="display:none;"></div>';
        html += '</div>';

        html += renderAiLocalizationControlsHtml();

        html += '<div class="qfb-ai-field">';
        html += '<div class="qfb-ai-module-tools">';
        html += '<label class="qfb-label" for="qfb-ai-module-search">' + escapeHtml(getText('aiModuleLabel', '候选模块')) + '</label>';
        html += '<span class="qfb-ai-selected-count"><strong id="qfb-ai-selected-count">0</strong>' + escapeHtml(getText('aiSelectedSuffix', '/10')) + '</span>';
        html += '</div>';
        html += renderAiModuleBundlesHtml();
        html += '<input type="search" id="qfb-ai-module-search" class="qfb-input" placeholder="' + escapeHtml(getText('aiModuleSearch', '搜索模块名称...')) + '" />';
        html += '<div id="qfb-ai-module-list" class="qfb-ai-module-list">';
        var currentAiGroupKey = '';
        state.availableModules.forEach(function(moduleItem) {
            if (!moduleItem || !moduleItem.id) {
                return;
            }
            if (moduleItem.aiEnabled === false) {
                return;
            }
            var groupKey = getModuleGroupKey(moduleItem);
            var groupLabel = getModuleGroupLabel(moduleItem);
            if (groupKey !== currentAiGroupKey) {
                html += buildGroupHeaderHtml(groupKey, groupLabel || groupKey, 'qfb-ai-group-title');
                currentAiGroupKey = groupKey;
            }
            var searchText = getModuleSearchText(moduleItem);
            html += '<label class="qfb-ai-module-item" data-group-key="' + escapeHtml(groupKey) + '" data-module-name="' + escapeHtml(searchText) + '">';
            html += '<input type="checkbox" value="' + escapeHtml(moduleItem.id) + '" />';
            html += '<span>' + escapeHtml(moduleItem.name || moduleItem.id) + '</span>';
            html += '</label>';
        });
        html += '</div>';
        html += '</div>';

        html += '<div id="qfb-ai-readiness" class="qfb-ai-readiness"></div>';
        html += '<div class="qfb-ai-actions">';
        html += '<button type="button" class="button button-primary" id="qfb-ai-generate"' + disabledAttr + '>' + escapeHtml(getText('aiGenerate', '生成/优化当前单页')) + '</button>';
        html += '<button type="button" class="button" id="qfb-ai-optimize-module"' + disabledAttr + '>' + escapeHtml(getText('aiOptimizeModule', '优化当前模块')) + '</button>';
        html += '<button type="button" class="button" id="qfb-ai-localize-module"' + disabledAttr + '>' + escapeHtml(getText('aiLocalizeModule', '本地化当前模块')) + '</button>';
        html += '<button type="button" class="button" id="qfb-ai-localize-page"' + disabledAttr + '>' + escapeHtml(getText('aiLocalizePage', '本地化整页')) + '</button>';
        html += '</div>';
        html += '</details>';
        html += '<button type="button" class="button qfb-ai-undo" id="qfb-ai-undo" style="display:none;">' + escapeHtml(getText('aiUndoLastChange', '撤回本次修改')) + '</button>';
        html += '<div id="qfb-ai-status" class="qfb-ai-status"></div>';
        html += '<ul id="qfb-ai-warnings" class="qfb-ai-warning-list" style="display:none;"></ul>';
        html += '<div id="qfb-ai-diff" class="qfb-ai-diff" style="display:none;"></div>';
        html += '</div>';

        els.aiPane.className = 'qfb-settings qfb-ai-pane';
        els.aiPane.innerHTML = html;
        updateAiModelSuggestions();
        updateAiSelectedCount();
        updateAiUndoButton();
        updateAiBusyControls();
        renderAiPendingDiff();
        renderAiReadiness();
        renderAiPromptHistory();
        renderAiStyleRecommendations();
    }

    function updateAiModelSuggestions() {
        if (!els.aiPane || !aiBuilderService) {
            return;
        }

        var modelInput = els.aiPane.querySelector('#qfb-ai-model');
        var modelList = els.aiPane.querySelector('#qfb-ai-model-list');
        var connectionSelect = els.aiPane.querySelector('#qfb-ai-connection');
        if (!modelInput || !modelList || !connectionSelect) {
            return;
        }

        aiBuilderService.updateModelSuggestions({
            config: state.aiConfig,
            connectionId: String(connectionSelect.value || ''),
            datalist: modelList,
            modelInput: modelInput
        });
    }

    function getAiSelectedModules() {
        if (!els.aiPane || !aiBuilderService) {
            return [];
        }

        var checked = els.aiPane.querySelectorAll('#qfb-ai-module-list input[type="checkbox"]:checked');
        return aiBuilderService.getSelectedValues(checked);
    }

    function updateAiSelectedCount() {
        if (!els.aiPane) {
            return;
        }

        var countEl = els.aiPane.querySelector('#qfb-ai-selected-count');
        if (countEl) {
            countEl.textContent = String(getAiSelectedModules().length);
        }
        renderAiReadiness();
    }

    function setAiStatus(message, type) {
        if (!els.aiPane) {
            return;
        }

        var statusEl = els.aiPane.querySelector('#qfb-ai-status');
        if (!statusEl) {
            return;
        }

        statusEl.textContent = String(message || '');
        statusEl.className = 'qfb-ai-status';
        if (type) {
            statusEl.classList.add('is-' + type);
        }
    }

    function renderAiWarnings(warnings) {
        if (!els.aiPane) {
            return;
        }

        var warningsEl = els.aiPane.querySelector('#qfb-ai-warnings');
        if (!warningsEl) {
            return;
        }

        warnings = Array.isArray(warnings) ? warnings : [];
        if (!warnings.length) {
            warningsEl.style.display = 'none';
            warningsEl.innerHTML = '';
            return;
        }

        var html = '';
        warnings.forEach(function(warning) {
            if (!warning) {
                return;
            }
            html += '<li>' + escapeHtml(warning) + '</li>';
        });
        warningsEl.innerHTML = html;
        warningsEl.style.display = '';
    }

    function stringifyForDiff(value) {
        try {
            return JSON.stringify(value, function(key, item) {
                if (!item || typeof item !== 'object' || Array.isArray(item)) {
                    return item;
                }

                var sorted = {};
                Object.keys(item).sort().forEach(function(itemKey) {
                    sorted[itemKey] = item[itemKey];
                });
                return sorted;
            });
        } catch (e) {
            return String(value);
        }
    }

    function valuesDiffer(before, after) {
        return stringifyForDiff(before) !== stringifyForDiff(after);
    }

    function countNestedDiffs(before, after) {
        if (!valuesDiffer(before, after)) {
            return 0;
        }

        if (!before || !after || typeof before !== 'object' || typeof after !== 'object') {
            return 1;
        }

        if (Array.isArray(before) || Array.isArray(after)) {
            var beforeList = Array.isArray(before) ? before : [];
            var afterList = Array.isArray(after) ? after : [];
            var maxItems = Math.max(beforeList.length, afterList.length);
            var total = beforeList.length === afterList.length ? 0 : Math.abs(beforeList.length - afterList.length);
            for (var i = 0; i < maxItems; i++) {
                if (typeof beforeList[i] === 'undefined' || typeof afterList[i] === 'undefined') {
                    continue;
                }
                total += countNestedDiffs(beforeList[i], afterList[i]);
            }
            return Math.max(1, total);
        }

        var keys = Object.keys(before).concat(Object.keys(after)).filter(function(key, index, list) {
            return list.indexOf(key) === index;
        });

        return keys.reduce(function(total, key) {
            if (!Object.prototype.hasOwnProperty.call(before, key) || !Object.prototype.hasOwnProperty.call(after, key)) {
                return total + 1;
            }
            return total + countNestedDiffs(before[key], after[key]);
        }, 0);
    }

    function formatLimitedNames(names, limit) {
        names = Array.isArray(names) ? names.filter(Boolean) : [];
        limit = parseInt(limit, 10) || 6;
        if (!names.length) {
            return '';
        }

        var shown = names.slice(0, limit).join('、');
        if (names.length > limit) {
            shown += ' +' + (names.length - limit);
        }
        return shown;
    }

    function normalizeDiffModule(module) {
        if (!module || typeof module !== 'object') {
            return null;
        }

        var type = module.type ? String(module.type) : '';
        if (!type) {
            return null;
        }

        return {
            type: type,
            data: module.data && typeof module.data === 'object' ? module.data : {}
        };
    }

    function buildModulesDiffSummary(beforeModules, afterModules) {
        beforeModules = Array.isArray(beforeModules) ? beforeModules.map(normalizeDiffModule).filter(Boolean) : [];
        afterModules = Array.isArray(afterModules) ? afterModules.map(normalizeDiffModule).filter(Boolean) : [];

        var added = [];
        var removed = [];
        var replaced = [];
        var changed = [];
        var maxItems = Math.max(beforeModules.length, afterModules.length);

        for (var i = 0; i < maxItems; i++) {
            var before = beforeModules[i] || null;
            var after = afterModules[i] || null;

            if (!before && after) {
                added.push(getModuleName(after.type));
                continue;
            }
            if (before && !after) {
                removed.push(getModuleName(before.type));
                continue;
            }
            if (!before || !after) {
                continue;
            }
            if (before.type !== after.type) {
                replaced.push(getModuleName(before.type) + ' -> ' + getModuleName(after.type));
                continue;
            }
            if (valuesDiffer(before.data, after.data)) {
                changed.push(getModuleName(after.type) + ' ' + formatCountText(getText('aiDiffFieldCount', '(%d 项)'), countNestedDiffs(before.data, after.data)));
            }
        }

        return {
            beforeCount: beforeModules.length,
            afterCount: afterModules.length,
            added: added,
            removed: removed,
            replaced: replaced,
            changed: changed
        };
    }

    function getPageSettingsAfterPackage(baseSettings, packageData) {
        var next = baseSettings && typeof baseSettings === 'object' ? deepClone(baseSettings) : {};
        packageData = packageData && typeof packageData === 'object' ? packageData : {};

        if (packageData.page_template) {
            next.pageTemplate = String(packageData.page_template);
        }
        if (Object.prototype.hasOwnProperty.call(packageData, 'hide_page_header')) {
            next.hidePageHeader = !!packageData.hide_page_header;
        }
        if (Object.prototype.hasOwnProperty.call(packageData, 'transparent_header')) {
            next.transparentHeader = !!packageData.transparent_header;
        }
        if (Object.prototype.hasOwnProperty.call(packageData, 'enable_scroll_reveal')) {
            next.enableScrollReveal = !!packageData.enable_scroll_reveal;
        }
        if (Object.prototype.hasOwnProperty.call(packageData, 'design_preset') || Object.prototype.hasOwnProperty.call(packageData, 'designPreset')) {
            next.designPreset = normalizeDesignPresetKey(packageData.designPreset || packageData.design_preset || '');
        }
        if (packageData.seo && typeof packageData.seo === 'object') {
            next.seo = deepClone(packageData.seo);
        }
        if ((packageData.footer && typeof packageData.footer === 'object') || (packageData.footer_settings && typeof packageData.footer_settings === 'object')) {
            next.footer = normalizePageFooterSettings(packageData.footer || packageData.footer_settings);
        }
        if ((packageData.visual_style && typeof packageData.visual_style === 'object') || (packageData.visualStyle && typeof packageData.visualStyle === 'object')) {
            next.visualStyle = normalizePageVisualStyleSettings(packageData.visualStyle || packageData.visual_style);
        } else if (packageData.visual_skin || packageData.visualSkin) {
            next.visualStyle = normalizePageVisualStyleSettings({
                mode: 'custom',
                preset: packageData.visualSkin || packageData.visual_skin
            });
        }
        if ((packageData.page_design && typeof packageData.page_design === 'object') || (packageData.pageDesign && typeof packageData.pageDesign === 'object')) {
            next.design = normalizePageDesignState(packageData.pageDesign || packageData.page_design);
        } else if (next.designPreset) {
            next.design = buildPageDesignStateFromPreset(next.designPreset);
        }

        return next;
    }

    function buildPageSettingsDiffSummary(beforeSettings, afterSettings) {
        beforeSettings = beforeSettings && typeof beforeSettings === 'object' ? beforeSettings : {};
        afterSettings = afterSettings && typeof afterSettings === 'object' ? afterSettings : {};

        var changed = [];
        [
            ['pageTemplate', getText('aiDiffPageTemplate', '页面模板')],
            ['hidePageHeader', getText('aiDiffHideHeader', '隐藏页面标题')],
            ['transparentHeader', getText('aiDiffTransparentHeader', '透明头部')],
            ['enableScrollReveal', getText('aiDiffScrollReveal', '滚动动效')]
        ].forEach(function(pair) {
            if (valuesDiffer(beforeSettings[pair[0]], afterSettings[pair[0]])) {
                changed.push(pair[1]);
            }
        });

        if (valuesDiffer(beforeSettings.seo || {}, afterSettings.seo || {})) {
            changed.push(getText('aiDiffSeo', 'SEO 建议') + ' ' + formatCountText(getText('aiDiffFieldCount', '(%d 项)'), countNestedDiffs(beforeSettings.seo || {}, afterSettings.seo || {})));
        }

        if (valuesDiffer(beforeSettings.design || {}, afterSettings.design || {})) {
            changed.push(getText('aiDiffDesign', '页面设计') + ' ' + formatCountText(getText('aiDiffFieldCount', '(%d 项)'), countNestedDiffs(beforeSettings.design || {}, afterSettings.design || {})));
        }

        if (valuesDiffer(beforeSettings.visualStyle || {}, afterSettings.visualStyle || {})) {
            changed.push(getText('aiDiffVisualStyle', '页面视觉预设') + ' ' + formatCountText(getText('aiDiffFieldCount', '(%d 项)'), countNestedDiffs(beforeSettings.visualStyle || {}, afterSettings.visualStyle || {})));
        }

        return changed;
    }

    function buildAiPageStyleDiffLines(pending) {
        var lines = [];
        var beforeSettings = pending && pending.basePageSettings && typeof pending.basePageSettings === 'object'
            ? pending.basePageSettings
            : {};
        var beforeStyle = normalizePageVisualStyleSettings(beforeSettings.visualStyle || {});
        var afterStyle = normalizePageVisualStyleSettings(pending && pending.visualStyle ? pending.visualStyle : {});
        var changedCount = countNestedDiffs(beforeStyle, afterStyle);

        lines.push(getText('aiDiffLocalTask', '局部任务：') + String(pending.label || getText('aiDiffPageStyleTitle', '页面风格建议')));
        lines.push(getText('aiDiffPageStyleScope', '作用范围：仅当前页面视觉风格'));
        lines.push(getText('aiDiffPageStyleFields', '视觉字段变化：') + formatCountText(getText('aiDiffFieldCountPlain', '%d 项'), changedCount));
        if (afterStyle.colors && afterStyle.colors.primary) {
            lines.push(getText('aiDiffPageStylePrimary', '页面主色：') + String(afterStyle.colors.primary));
        }
        if (afterStyle.colors && afterStyle.colors.accent) {
            lines.push(getText('aiDiffPageStyleAccent', '页面辅助色：') + String(afterStyle.colors.accent));
        }
        if (afterStyle.buttons && afterStyle.buttons.background) {
            lines.push(getText('aiDiffPageStyleButton', '按钮颜色：') + String(afterStyle.buttons.background));
        }

        return lines;
    }

    function buildAiDiffLines(pending) {
        if (!pending || typeof pending !== 'object') {
            return [];
        }

        var lines = [];
        if (pending.kind === 'page_style') {
            return buildAiPageStyleDiffLines(pending);
        }

        if (pending.kind === 'module') {
            var targetIndex = parseInt(pending.targetIndex, 10);
            if (Number.isNaN(targetIndex)) {
                targetIndex = -1;
            }
            var beforeModule = Array.isArray(pending.baseModules) && targetIndex >= 0 ? normalizeDiffModule(pending.baseModules[targetIndex]) : null;
            var afterModule = normalizeDiffModule(pending.module);
            var beforeName = beforeModule ? getModuleName(beforeModule.type) : getText('aiDiffUnknownModule', '未知模块');
            var afterName = afterModule ? getModuleName(afterModule.type) : getText('aiDiffUnknownModule', '未知模块');

            if (!beforeModule || !afterModule) {
                lines.push(getText('aiDiffModuleInvalid', '模块结果不完整，建议放弃后重试。'));
                return lines;
            }

            lines.push(formatCountText(getText('aiDiffTargetModule', '目标模块：%d'), targetIndex + 1) + ' - ' + beforeName);
            if (pending.mode === 'localization' && pending.localization) {
                lines.push(getText('aiDiffLocalizationTarget', '本地化目标：') + String(pending.localization.target_language_label || pending.localization.target_language || ''));
            }
            if (beforeModule.type !== afterModule.type) {
                lines.push(getText('aiDiffModuleTypeChanged', '模块类型变化：') + beforeName + ' -> ' + afterName);
            } else if (valuesDiffer(beforeModule.data, afterModule.data)) {
                lines.push(getText('aiDiffModuleFieldsChanged', '模块字段变化：') + formatCountText(getText('aiDiffFieldCountPlain', '%d 项'), countNestedDiffs(beforeModule.data, afterModule.data)));
            } else {
                lines.push(getText('aiDiffNoMajorChange', '没有检测到明显字段变化。'));
            }
            return lines;
        }

        var modulesSummary = buildModulesDiffSummary(
            pending.baseModules || [],
            pending.packageData && Array.isArray(pending.packageData.modules) ? pending.packageData.modules : []
        );
        if (pending.mode === 'localization' && pending.localization) {
            lines.push(getText('aiDiffLocalizationTarget', '本地化目标：') + String(pending.localization.target_language_label || pending.localization.target_language || ''));
        }
        if (pending.localizationScore && typeof pending.localizationScore === 'object' && typeof pending.localizationScore.score !== 'undefined') {
            lines.push(getText('aiDiffLocalizationScore', '本地化评分：') + String(pending.localizationScore.score) + '/100');
        }
        if (pending.providerSync && typeof pending.providerSync === 'object') {
            lines.push(getText('aiDiffProviderSync', '启灵AI多语言：') + (pending.providerSync.success ? getText('aiDiffProviderSynced', '已同步') : String(pending.providerSync.message || getText('aiDiffProviderNotSynced', '未同步'))));
        }
        lines.push(getText('aiDiffModuleCount', '模块数量：') + modulesSummary.beforeCount + ' -> ' + modulesSummary.afterCount);

        if (modulesSummary.added.length) {
            lines.push(getText('aiDiffAddedModules', '新增模块：') + formatLimitedNames(modulesSummary.added));
        }
        if (modulesSummary.removed.length) {
            lines.push(getText('aiDiffRemovedModules', '删除模块：') + formatLimitedNames(modulesSummary.removed));
        }
        if (modulesSummary.replaced.length) {
            lines.push(getText('aiDiffReplacedModules', '替换模块：') + formatLimitedNames(modulesSummary.replaced, 4));
        }
        if (modulesSummary.changed.length) {
            lines.push(getText('aiDiffChangedModules', '修改模块：') + formatLimitedNames(modulesSummary.changed, 4));
        }

        var nextSettings = getPageSettingsAfterPackage(pending.basePageSettings || {}, pending.packageData || {});
        var settingsDiff = buildPageSettingsDiffSummary(pending.basePageSettings || {}, nextSettings);
        if (settingsDiff.length) {
            lines.push(getText('aiDiffPageSettings', '页面设置：') + formatLimitedNames(settingsDiff, 5));
        }

        if (lines.length === 1 && modulesSummary.beforeCount === modulesSummary.afterCount && !settingsDiff.length) {
            lines.push(getText('aiDiffNoMajorChange', '没有检测到明显字段变化。'));
        }

        return lines;
    }

    function containsAiReviewSignal(value, keywords) {
        var text = stringifyForDiff(value).toLowerCase();
        return keywords.some(function(keyword) {
            return text.indexOf(String(keyword || '').toLowerCase()) !== -1;
        });
    }

    function hasAiSeoPayload(packageData) {
        if (!packageData || !packageData.seo || typeof packageData.seo !== 'object') {
            return false;
        }

        return ['title', 'seo_title', 'description', 'seo_description', 'keywords'].some(function(key) {
            return !!String(packageData.seo[key] || '').trim();
        });
    }

    function getAiReviewItems(pending) {
        if (!pending || typeof pending !== 'object') {
            return [];
        }

        var items = [];
        var ctaKeywords = [
            'cta', 'button', 'contact', 'demo', 'quote', 'book', 'buy', '咨询', '联系', '预约', '报价', '购买', '立即', '了解', '提交', '免费'
        ];

        if (pending.kind === 'page_style') {
            items.push({
                label: getText('aiReviewResult', '结果'),
                status: pending.visualStyle ? 'ok' : 'error',
                detail: pending.visualStyle
                    ? getText('aiReviewPageStyleOk', '已生成当前页面视觉风格建议。')
                    : getText('aiReviewResultInvalid', '结果不完整，建议放弃后重试。')
            });
            items.push({
                label: getText('aiReviewScope', '范围'),
                status: 'ok',
                detail: getText('aiReviewPageStyleScopeOk', '只修改当前页面 visualStyle，不覆盖全站默认。')
            });
            items.push({
                label: getText('aiReviewPreview', '预览'),
                status: 'ok',
                detail: getText('aiReviewPreviewRequired', '当前仍是待应用状态，确认后才会同步到页面预览。')
            });
        } else if (pending.kind === 'module') {
            var targetIndex = parseInt(pending.targetIndex, 10);
            if (Number.isNaN(targetIndex)) {
                targetIndex = -1;
            }
            var beforeModule = Array.isArray(pending.baseModules) && targetIndex >= 0 ? normalizeDiffModule(pending.baseModules[targetIndex]) : null;
            var afterModule = normalizeDiffModule(pending.module);
            var typeMatches = !!(beforeModule && afterModule && beforeModule.type === afterModule.type);

            items.push({
                label: getText('aiReviewResult', '结果'),
                status: afterModule ? 'ok' : 'error',
                detail: afterModule
                    ? getText('aiReviewResultOk', 'AI 结果结构完整。')
                    : getText('aiReviewResultInvalid', '结果不完整，建议放弃后重试。')
            });
            items.push({
                label: getText('aiReviewModuleType', '模块类型'),
                status: typeMatches ? 'ok' : 'error',
                detail: typeMatches
                    ? getText('aiReviewModuleTypeOk', '模块类型保持不变。')
                    : getText('aiReviewModuleTypeChanged', '模块类型不一致，建议放弃后重试。')
            });
            items.push({
                label: getText('aiReviewModuleContent', '模块内容'),
                status: beforeModule && afterModule && valuesDiffer(beforeModule.data, afterModule.data) ? 'ok' : 'warning',
                detail: beforeModule && afterModule && valuesDiffer(beforeModule.data, afterModule.data)
                    ? getText('aiReviewModuleContentOk', '已检测到字段变化。')
                    : getText('aiReviewModuleContentSame', '未检测到明显内容变化。')
            });
            if (pending.mode === 'localization') {
                items.push({
                    label: getText('aiReviewLocalization', '本地化'),
                    status: 'ok',
                    detail: getText('aiReviewLocalizationOk', '仅按 text-only 白名单合并文案字段，保留原布局。')
                });
            }
            items.push({
                label: getText('aiReviewCta', '转化'),
                status: afterModule && containsAiReviewSignal(afterModule.data, ctaKeywords) ? 'ok' : 'warning',
                detail: afterModule && containsAiReviewSignal(afterModule.data, ctaKeywords)
                    ? getText('aiReviewCtaOk', '已检测到 CTA 或联系转化词。')
                    : getText('aiReviewCtaMissing', '未检测到明显 CTA，应用前建议确认。')
            });
        } else {
            var afterModules = pending.packageData && Array.isArray(pending.packageData.modules) ? pending.packageData.modules : [];
            var modulesSummary = buildModulesDiffSummary(pending.baseModules || [], afterModules);
            var hasModuleChanges = modulesSummary.added.length || modulesSummary.removed.length || modulesSummary.replaced.length || modulesSummary.changed.length || modulesSummary.beforeCount !== modulesSummary.afterCount;

            items.push({
                label: getText('aiReviewModuleStructure', '模块结构'),
                status: afterModules.length ? 'ok' : 'error',
                detail: afterModules.length
                    ? formatCountText(getText('aiReviewModuleCountOk', '已生成 %d 个模块。'), afterModules.length)
                    : getText('aiReviewModuleMissing', '未生成可用模块。')
            });
            items.push({
                label: getText('aiReviewModuleChange', '模块变化'),
                status: hasModuleChanges ? 'ok' : 'warning',
                detail: hasModuleChanges
                    ? getText('aiReviewModuleChangeOk', '已检测到模块变化。')
                    : getText('aiReviewModuleChangeNone', '模块结构变化很小，请确认是否符合预期。')
            });
            items.push({
                label: getText('aiReviewSeo', 'SEO'),
                status: hasAiSeoPayload(pending.packageData) ? 'ok' : 'warning',
                detail: hasAiSeoPayload(pending.packageData)
                    ? getText('aiReviewSeoOk', '已包含 SEO 标题或描述。')
                    : getText('aiReviewSeoMissing', '未检测到 SEO 建议，可应用后手动补充。')
            });
            items.push({
                label: getText('aiReviewCta', '转化'),
                status: containsAiReviewSignal(afterModules, ctaKeywords) ? 'ok' : 'warning',
                detail: containsAiReviewSignal(afterModules, ctaKeywords)
                    ? getText('aiReviewCtaOk', '已检测到 CTA 或联系转化词。')
                    : getText('aiReviewCtaMissing', '未检测到明显 CTA，应用前建议确认。')
            });
            items.push({
                label: getText('aiReviewDesign', '视觉'),
                status: pending.packageData && (pending.packageData.page_design || pending.packageData.pageDesign) ? 'ok' : 'warning',
                detail: pending.packageData && (pending.packageData.page_design || pending.packageData.pageDesign)
                    ? getText('aiReviewDesignOk', '已包含页面设计建议。')
                    : getText('aiReviewDesignInherited', '未包含页面设计覆盖，将沿用当前全局视觉。')
            });
        }

        items.push({
            label: getText('aiReviewGuardrail', '安全'),
            status: 'ok',
            detail: getText('aiReviewGuardrailOk', '仍处于待应用状态，确认后才会修改页面。')
        });

        return items;
    }

    function renderAiReviewChecklistHtml(pending) {
        var items = getAiReviewItems(pending);
        if (!items.length) {
            return '';
        }

        var errorCount = items.filter(function(item) {
            return item.status === 'error';
        }).length;
        var warningCount = items.filter(function(item) {
            return item.status === 'warning';
        }).length;
        var summary = errorCount
            ? formatCountText(getText('aiReviewError', '%d 项需要处理'), errorCount)
            : (warningCount
                ? formatCountText(getText('aiReviewWarning', '%d 项建议确认'), warningCount)
                : getText('aiReviewReady', '可以应用'));

        var html = '<div class="qfb-ai-review-checklist is-' + (errorCount ? 'error' : (warningCount ? 'warning' : 'ok')) + '">';
        html += '<div class="qfb-ai-review-head">';
        html += '<strong>' + escapeHtml(getText('aiReviewTitle', '应用前验收')) + '</strong>';
        html += '<span>' + escapeHtml(summary) + '</span>';
        html += '</div>';
        html += '<ul>';
        items.forEach(function(item) {
            html += '<li class="is-' + escapeHtml(item.status) + '">';
            html += '<span>' + escapeHtml(item.label) + '</span>';
            html += '<em>' + escapeHtml(item.detail) + '</em>';
            html += '</li>';
        });
        html += '</ul>';
        html += '</div>';
        return html;
    }

    function renderAiPendingDiff() {
        if (!els.aiPane) {
            return;
        }

        var diffEl = els.aiPane.querySelector('#qfb-ai-diff');
        if (!diffEl) {
            return;
        }

        var pending = state.aiPendingResult;
        if (!pending) {
            diffEl.style.display = 'none';
            diffEl.innerHTML = '';
            return;
        }

        var title = pending.kind === 'page_style'
            ? getText('aiDiffPageStyleTitle', '页面风格建议待应用')
            : (pending.kind === 'module'
            ? (pending.mode === 'localization'
                ? getText('aiDiffLocalizationTitle', '本地化结果待应用')
                : getText('aiDiffModuleTitle', '模块结果待应用'))
            : (pending.mode === 'localization'
                ? getText('aiDiffPageLocalizationTitle', '整页本地化结果')
                : getText('aiDiffPageTitle', '单页结果待应用')));
        var lines = buildAiDiffLines(pending);
        var html = '<div class="qfb-ai-diff-card">';
        html += '<strong>' + escapeHtml(title) + '</strong>';
        html += '<p>' + escapeHtml(pending.externalOnly
            ? getText('aiDiffExternalIntro', '结果已同步到多语言记录，当前页面不会被覆盖。')
            : getText('aiDiffIntro', '草稿还没有改动页面，确认后才会应用。')) + '</p>';
        if (pending.kind === 'page_style' && Array.isArray(pending.swatches) && pending.swatches.length) {
            html += '<div class="qfb-ai-diff-swatches">';
            pending.swatches.forEach(function(color) {
                html += '<span><i style="background:' + escapeHtml(color) + ';"></i><em>' + escapeHtml(color) + '</em></span>';
            });
            html += '</div>';
        }
        html += '<ul>';
        lines.forEach(function(line) {
            html += '<li>' + escapeHtml(line) + '</li>';
        });
        html += '</ul>';
        html += renderAiReviewChecklistHtml(pending);
        html += '<div class="qfb-ai-diff-actions">';
        html += '<button type="button" class="button button-primary" id="qfb-ai-apply-result">' + escapeHtml(pending.externalOnly ? getText('aiConfirmExternalResult', '确认完成') : getText('aiApplyPending', '应用 AI 结果')) + '</button>';
        html += '<button type="button" class="button" id="qfb-ai-discard-result">' + escapeHtml(getText('aiDiscardPending', '放弃结果')) + '</button>';
        html += '</div>';
        html += '</div>';

        diffEl.innerHTML = html;
        diffEl.style.display = '';
    }

    function setAiPendingResult(pending) {
        state.aiPendingResult = pending && typeof pending === 'object' ? pending : null;
        renderAiPendingDiff();
        renderAiReadiness();
    }

    function clearAiPendingResult() {
        state.aiPendingResult = null;
        renderAiPendingDiff();
        renderAiReadiness();
    }

    function discardPendingAiResult() {
        clearAiPendingResult();
        renderAiWarnings([]);
        setAiStatus(getText('aiPendingDiscarded', '已放弃本次生成结果，页面未被改动。'), 'warning');
    }

    function applyPendingAiResult() {
        var pending = state.aiPendingResult;
        if (!pending) {
            return;
        }

        if (pending.externalOnly) {
            var externalMessage = pending.successMessage || getText('aiPendingExternalDone', '本地化结果已同步到多语言记录。');
            clearAiPendingResult();
            setAiStatus(externalMessage, 'success');
            return;
        }

        var applied = false;
        if (pending.kind === 'page_style') {
            pushAiSnapshot('page_style');
            applied = applyAiPageStyleToState(pending.visualStyle);
            if (!applied) {
                restoreLatestAiSnapshot({ clearPending: false });
            }
        } else if (pending.kind === 'module') {
            var previousIndex = state.selectedIndex;
            state.selectedScope = 'module';
            state.selectedIndex = parseInt(pending.targetIndex, 10);
            if (Number.isNaN(state.selectedIndex)) {
                state.selectedIndex = previousIndex;
            }

            if (!isPendingAiModuleTargetCurrent(pending)) {
                state.selectedIndex = previousIndex;
                setAiStatus(getText('aiPendingTargetChanged', '目标模块已经变化，请放弃本次生成结果后重新生成。'), 'error');
                return;
            }

            pushAiSnapshot('module');
            applied = applyAiModuleToState(pending.module);
            if (!applied) {
                restoreLatestAiSnapshot({ clearPending: false });
            }
        } else {
            pushAiSnapshot('page');
            applied = applyAiPackageToState(pending.packageData);
            if (!applied) {
                restoreLatestAiSnapshot({ clearPending: false });
            }
        }

        if (!applied) {
            setAiStatus(getText('aiPendingApplyFailed', '结果应用失败，请放弃后重试。'), 'error');
            return;
        }

        var successMessage = pending.successMessage || getText('aiPendingApplySuccess', '结果已应用，请确认预览后保存。');
        clearAiPendingResult();
        setAiStatus(successMessage, 'success');
    }

    function applyAiPageStyleToState(visualStyle) {
        if (!visualStyle || typeof visualStyle !== 'object') {
            return false;
        }

        ensurePageSettingsState();
        state.pageSettings.visualStyle = normalizePageVisualStyleSettings(visualStyle);
        applyPageVisualStylePreview();

        if (state.panelMode === 'settings' && state.selectedScope === 'page') {
            renderSettings();
        } else {
            highlightSelectedWrapper();
        }

        queuePreviewRender(false);
        markDirty();
        setStatus(getText('pageVisualPreviewApplied', '页面视觉风格已同步到当前预览，保存后会正式生效。'), 'warning');
        return true;
    }

    function getAiModuleSignature(moduleData) {
        if (!moduleData || typeof moduleData !== 'object') {
            return '';
        }

        try {
            return JSON.stringify({
                type: String(moduleData.type || ''),
                data: moduleData.data && typeof moduleData.data === 'object' ? moduleData.data : {}
            });
        } catch (error) {
            return String(moduleData.type || '');
        }
    }

    function isPendingAiModuleTargetCurrent(pending) {
        if (!pending || pending.kind !== 'module') {
            return true;
        }

        var targetIndex = parseInt(pending.targetIndex, 10);
        if (Number.isNaN(targetIndex) || targetIndex < 0 || targetIndex >= state.modules.length) {
            return false;
        }

        var currentModule = state.modules[targetIndex];
        var expectedType = String(pending.targetType || (pending.module && pending.module.type) || '');
        if (!currentModule || !currentModule.type) {
            return false;
        }
        if (expectedType && String(currentModule.type) !== expectedType) {
            return false;
        }

        if (pending.targetModuleSignature && getAiModuleSignature(currentModule) !== pending.targetModuleSignature) {
            return false;
        }

        return true;
    }

    function applyAiPackageToState(packageData) {
        if (!packageData || !Array.isArray(packageData.modules)) {
            return false;
        }

        state.modules = packageData.modules.filter(function(item) {
            return item && typeof item === 'object' && item.type;
        }).map(function(item) {
            return {
                type: String(item.type),
                data: item.data && typeof item.data === 'object' ? item.data : {}
            };
        });

        if (packageData.page_template) {
            state.pageSettings.pageTemplate = String(packageData.page_template);
        }
        if (Object.prototype.hasOwnProperty.call(packageData, 'hide_page_header')) {
            state.pageSettings.hidePageHeader = !!packageData.hide_page_header;
        }
        if (Object.prototype.hasOwnProperty.call(packageData, 'transparent_header')) {
            state.pageSettings.transparentHeader = !!packageData.transparent_header;
        }
        if (Object.prototype.hasOwnProperty.call(packageData, 'enable_scroll_reveal')) {
            state.pageSettings.enableScrollReveal = !!packageData.enable_scroll_reveal;
        }
        if (Object.prototype.hasOwnProperty.call(packageData, 'design_preset') || Object.prototype.hasOwnProperty.call(packageData, 'designPreset')) {
            state.pageSettings.designPreset = normalizeDesignPresetKey(packageData.designPreset || packageData.design_preset || '');
        }
        if (packageData.seo && typeof packageData.seo === 'object') {
            state.pageSettings.seo = deepClone(packageData.seo);
        }
        if ((packageData.footer && typeof packageData.footer === 'object') || (packageData.footer_settings && typeof packageData.footer_settings === 'object')) {
            state.pageSettings.footer = normalizePageFooterSettings(packageData.footer || packageData.footer_settings);
        }
        if ((packageData.visual_style && typeof packageData.visual_style === 'object') || (packageData.visualStyle && typeof packageData.visualStyle === 'object')) {
            state.pageSettings.visualStyle = normalizePageVisualStyleSettings(packageData.visualStyle || packageData.visual_style);
        } else if (packageData.visual_skin || packageData.visualSkin) {
            state.pageSettings.visualStyle = normalizePageVisualStyleSettings({
                mode: 'custom',
                preset: packageData.visualSkin || packageData.visual_skin
            });
        }
        if ((packageData.page_design && typeof packageData.page_design === 'object') || (packageData.pageDesign && typeof packageData.pageDesign === 'object')) {
            state.pageSettings.design = normalizePageDesignState(packageData.pageDesign || packageData.page_design);
            applyPageDesignPreview();
        } else if (state.pageSettings.designPreset) {
            state.pageSettings.design = buildPageDesignStateFromPreset(state.pageSettings.designPreset);
            applyPageDesignPreview();
        }
        applyPageVisualStylePreview();

        state.selectedScope = state.modules.length ? 'module' : 'page';
        state.selectedIndex = state.modules.length ? 0 : -1;
        renderPageList();
        if (state.panelMode === 'settings') {
            renderSettings();
        } else {
            highlightSelectedWrapper();
        }
        queuePreviewRender(true);
        markDirty();
        setStatus(getText('pageSettingsPending', '页面模板与头部设置会在保存后一起生效。'), 'warning');
        return true;
    }

    function buildCurrentModuleAiPlan(prompt, currentModule) {
        currentModule = currentModule && typeof currentModule === 'object' ? currentModule : {};
        return {
            title: state.pageSettings && state.pageSettings.title ? String(state.pageSettings.title) : '',
            page_template: state.pageSettings && state.pageSettings.pageTemplate ? String(state.pageSettings.pageTemplate) : '',
            hide_page_header: !!(state.pageSettings && state.pageSettings.hidePageHeader),
            transparent_header: !!(state.pageSettings && state.pageSettings.transparentHeader),
            enable_scroll_reveal: !!(state.pageSettings && state.pageSettings.enableScrollReveal),
            design_preset: state.pageSettings && state.pageSettings.designPreset ? String(state.pageSettings.designPreset) : '',
            page_design: serializePageDesignForPackage(state.pageSettings && state.pageSettings.design ? state.pageSettings.design : {}),
            footer: normalizePageFooterSettings(state.pageSettings && state.pageSettings.footer ? state.pageSettings.footer : {}),
            visual_style: normalizePageVisualStyleSettings(state.pageSettings && state.pageSettings.visualStyle ? state.pageSettings.visualStyle : {}),
            seo: state.pageSettings && state.pageSettings.seo && typeof state.pageSettings.seo === 'object'
                ? deepClone(state.pageSettings.seo)
                : {},
            modules: [
                {
                    type: String(currentModule.type || ''),
                    goal: prompt || '优化当前模块'
                }
            ]
        };
    }

    function buildCurrentPageAiPackage() {
        return {
            title: state.pageSettings && state.pageSettings.title ? String(state.pageSettings.title) : '',
            page_template: state.pageSettings && state.pageSettings.pageTemplate ? String(state.pageSettings.pageTemplate) : '',
            hide_page_header: !!(state.pageSettings && state.pageSettings.hidePageHeader),
            transparent_header: !!(state.pageSettings && state.pageSettings.transparentHeader),
            enable_scroll_reveal: !!(state.pageSettings && state.pageSettings.enableScrollReveal),
            design_preset: state.pageSettings && state.pageSettings.designPreset ? String(state.pageSettings.designPreset) : '',
            page_design: serializePageDesignForPackage(state.pageSettings && state.pageSettings.design ? state.pageSettings.design : {}),
            footer: normalizePageFooterSettings(state.pageSettings && state.pageSettings.footer ? state.pageSettings.footer : {}),
            visual_style: normalizePageVisualStyleSettings(state.pageSettings && state.pageSettings.visualStyle ? state.pageSettings.visualStyle : {}),
            seo: state.pageSettings && state.pageSettings.seo && typeof state.pageSettings.seo === 'object'
                ? deepClone(state.pageSettings.seo)
                : {},
            modules: deepClone(state.modules || [])
        };
    }

    function applyAiModuleToState(moduleData) {
        if (!moduleData || typeof moduleData !== 'object' || !moduleData.type) {
            return false;
        }
        if (state.selectedIndex < 0 || state.selectedIndex >= state.modules.length) {
            return false;
        }

        var current = state.modules[state.selectedIndex];
        if (!current || String(current.type) !== String(moduleData.type)) {
            return false;
        }

        state.modules[state.selectedIndex] = {
            type: String(moduleData.type),
            data: moduleData.data && typeof moduleData.data === 'object' ? moduleData.data : {}
        };

        renderPageList();
        renderSettings();
        queueModulePreviewRender(state.selectedIndex, true);
        markDirty();
        return true;
    }

    function generateAiDraft() {
        if (!els.aiPane || !aiBuilderService) {
            setAiStatus(getText('aiServiceUnavailable', 'AI 服务未加载，请刷新后重试。'), 'error');
            return;
        }
        if (isAiBusy()) {
            setAiStatus(getText('aiBusy', 'AI 请求进行中，请稍候。'), 'warning');
            return;
        }

        var promptEl = els.aiPane.querySelector('#qfb-ai-prompt');
        var connectionEl = els.aiPane.querySelector('#qfb-ai-connection');
        var modelEl = els.aiPane.querySelector('#qfb-ai-model');
        var prompt = promptEl ? String(promptEl.value || '').trim() : '';
        var connectionId = connectionEl ? String(connectionEl.value || '') : '';
        var model = modelEl ? String(modelEl.value || '').trim() : '';
        var moduleIds = getAiSelectedModules();
        var requestArgs = aiBuilderService.validateGenerationRequest({
            config: state.aiConfig,
            prompt: prompt,
            connectionId: connectionId,
            model: model,
            moduleIds: moduleIds,
            hasExistingModules: state.modules.length > 0,
            confirmReplace: function(message) {
                return window.confirm(message);
            },
            messages: {
                unavailable: getText('aiUnavailable', 'AI 装修尚未配置完成，请先到主题设置中启用并配置连接。'),
                missingPrompt: getText('aiMissingPrompt', '请先输入装修需求。'),
                missingModules: getText('aiMissingModules', '请先选择候选模块。'),
                tooManyModules: getText('aiTooManyModules', '候选模块最多选择 ' + getAiMaxModules() + ' 个。'),
                disallowedSitePrompt: getText('aiDisallowedSitePrompt', '在线 AI 整站生成已关闭。请改为生成当前单页，或选中单个模块后做模块优化。'),
                replaceConfirm: getText('aiReplaceConfirm', 'AI 会先生成待应用草稿，确认应用时会替换当前页面模块列表。是否继续？')
            }
        });

        if (!requestArgs.ok) {
            if (requestArgs.message) {
                setAiStatus(requestArgs.message, 'error');
            }
            return;
        }

        pushAiPromptHistory({
            prompt: requestArgs.prompt,
            moduleIds: requestArgs.moduleIds,
            connectionId: requestArgs.connectionId,
            model: requestArgs.model
        });
        clearAiPendingResult();
        renderAiWarnings([]);
        setAiBusy(true);

        setAiStatus(getText('aiGenerating', 'AI 正在生成页面草稿，请稍候…'), 'warning');

        aiBuilderService.generatePagePackage({
            ajaxUrl: builderData.ajaxUrl,
            config: state.aiConfig,
            postId: builderData.postId,
            prompt: requestArgs.prompt,
            connectionId: requestArgs.connectionId,
            model: requestArgs.model,
            moduleIds: requestArgs.moduleIds,
            successMessage: getText('aiApplySuccess', '草稿已应用到当前页面，请确认预览后保存。'),
            generateFailedMessage: getText('aiGenerateFailed', '生成失败，请重试。'),
            timeoutMessage: getText('aiGenerateTimeout', 'AI 生成超时，请稍后重试，或缩小当前任务范围。'),
            networkErrorMessage: getText('aiGenerateFailed', '生成失败，请重试。')
        }).done(function(result) {
            renderAiWarnings(result.warnings);
            setAiPendingResult({
                kind: 'page',
                packageData: result.package,
                baseModules: deepClone(state.modules),
                basePageSettings: deepClone(state.pageSettings || {}),
                successMessage: result.successMessage
            });
            setAiStatus(getText('aiPendingReady', 'AI 草稿已生成，请查看差异后应用。'), 'success');
        }).fail(function(result) {
            setAiStatus(result && result.message ? result.message : getText('aiGenerateFailed', '生成失败，请重试。'), 'error');
        }).always(function() {
            setAiBusy(false);
        });
    }

    function optimizeCurrentModuleWithAi() {
        if (!els.aiPane || !aiBuilderService) {
            setAiStatus(getText('aiServiceUnavailable', 'AI 服务未加载，请刷新后重试。'), 'error');
            return;
        }
        if (isAiBusy()) {
            setAiStatus(getText('aiBusy', 'AI 请求进行中，请稍候。'), 'warning');
            return;
        }
        if (state.selectedIndex < 0 || state.selectedIndex >= state.modules.length) {
            setAiStatus(getText('aiNoSelectedModule', '请先在页面结构中选中一个模块，再使用当前模块优化。'), 'error');
            return;
        }

        var currentModule = state.modules[state.selectedIndex];
        if (!currentModule || !currentModule.type) {
            setAiStatus(getText('aiNoSelectedModule', '请先在页面结构中选中一个模块，再使用当前模块优化。'), 'error');
            return;
        }

        var promptEl = els.aiPane.querySelector('#qfb-ai-prompt');
        var connectionEl = els.aiPane.querySelector('#qfb-ai-connection');
        var modelEl = els.aiPane.querySelector('#qfb-ai-model');
        var prompt = promptEl ? String(promptEl.value || '').trim() : '';
        var connectionId = connectionEl ? String(connectionEl.value || '') : '';
        var model = modelEl ? String(modelEl.value || '').trim() : '';
        var moduleType = String(currentModule.type || '');
        var targetIndex = state.selectedIndex;
        var targetType = moduleType;
        var targetModuleSignature = getAiModuleSignature(currentModule);
        var baseModules = deepClone(state.modules);
        var basePageSettings = deepClone(state.pageSettings || {});
        var currentModuleData = currentModule.data && typeof currentModule.data === 'object'
            ? deepClone(currentModule.data)
            : {};
        var requestArgs = aiBuilderService.validateGenerationRequest({
            config: state.aiConfig,
            prompt: prompt,
            connectionId: connectionId,
            model: model,
            moduleIds: [moduleType],
            hasExistingModules: false,
            messages: {
                unavailable: getText('aiUnavailable', 'AI 装修尚未配置完成，请先到主题设置中启用并配置连接。'),
                missingPrompt: getText('aiMissingPrompt', '请先输入装修需求。'),
                missingModules: getText('aiMissingModules', '请先选择候选模块。'),
                tooManyModules: getText('aiTooManyModules', '候选模块最多选择 ' + getAiMaxModules() + ' 个。'),
                disallowedSitePrompt: getText('aiDisallowedSitePrompt', '在线 AI 整站生成已关闭。请改为生成当前单页，或选中单个模块后做模块优化。')
            }
        });

        if (!requestArgs.ok) {
            if (requestArgs.message) {
                setAiStatus(requestArgs.message, 'error');
            }
            return;
        }

        pushAiPromptHistory({
            prompt: requestArgs.prompt,
            moduleIds: [moduleType],
            connectionId: requestArgs.connectionId,
            model: requestArgs.model
        });
        clearAiPendingResult();
        renderAiWarnings([]);
        setAiBusy(true);
        setAiStatus(getText('aiModuleOptimizing', '正在优化当前模块，请稍候…'), 'warning');

        aiBuilderService.generatePageModule({
            ajaxUrl: builderData.ajaxUrl,
            config: state.aiConfig,
            postId: builderData.postId,
            prompt: requestArgs.prompt,
            connectionId: requestArgs.connectionId,
            model: requestArgs.model,
            moduleIds: [moduleType],
            currentModuleType: moduleType,
            currentModuleData: currentModuleData,
            plan: buildCurrentModuleAiPlan(requestArgs.prompt, currentModule),
            successMessage: getText('aiModuleApplySuccess', '当前模块已优化，请确认预览后保存。'),
            generateFailedMessage: getText('aiModuleFailed', '模块生成失败，请重试。'),
            timeoutMessage: getText('aiModuleTimeout', 'AI 模块优化超时，请稍后重试，或缩短需求描述。'),
            networkErrorMessage: getText('aiModuleFailed', '模块生成失败，请重试。')
        }).done(function(result) {
            renderAiWarnings(result.warnings);
            setAiPendingResult({
                kind: 'module',
                module: result.module,
                baseModules: baseModules,
                basePageSettings: basePageSettings,
                targetIndex: targetIndex,
                targetType: targetType,
                targetModuleSignature: targetModuleSignature,
                successMessage: result.successMessage
            });
            setAiStatus(getText('aiPendingReady', 'AI 草稿已生成，请查看差异后应用。'), 'success');
        }).fail(function(result) {
            setAiStatus(result && result.message ? result.message : getText('aiModuleFailed', '模块生成失败，请重试。'), 'error');
        }).always(function() {
            setAiBusy(false);
        });
    }

    function localizeCurrentModuleWithAi() {
        if (!els.aiPane || !aiBuilderService || typeof aiBuilderService.validateModuleLocalizationRequest !== 'function') {
            setAiStatus(getText('aiServiceUnavailable', 'AI 服务未加载，请刷新后重试。'), 'error');
            return;
        }
        if (isAiBusy()) {
            setAiStatus(getText('aiBusy', 'AI 请求进行中，请稍候。'), 'warning');
            return;
        }
        if (state.selectedIndex < 0 || state.selectedIndex >= state.modules.length) {
            setAiStatus(getText('aiNoSelectedModule', '请先在页面结构中选中一个模块，再使用当前模块优化。'), 'error');
            return;
        }

        var currentModule = state.modules[state.selectedIndex];
        if (!currentModule || !currentModule.type) {
            setAiStatus(getText('aiNoSelectedModule', '请先在页面结构中选中一个模块，再使用当前模块优化。'), 'error');
            return;
        }

        var promptEl = els.aiPane.querySelector('#qfb-ai-prompt');
        var connectionEl = els.aiPane.querySelector('#qfb-ai-connection');
        var modelEl = els.aiPane.querySelector('#qfb-ai-model');
        var prompt = promptEl ? String(promptEl.value || '').trim() : '';
        var connectionId = connectionEl ? String(connectionEl.value || '') : '';
        var model = modelEl ? String(modelEl.value || '').trim() : '';
        var moduleType = String(currentModule.type || '');
        var targetIndex = state.selectedIndex;
        var targetType = moduleType;
        var targetModuleSignature = getAiModuleSignature(currentModule);
        var baseModules = deepClone(state.modules);
        var basePageSettings = deepClone(state.pageSettings || {});
        var currentModuleData = currentModule.data && typeof currentModule.data === 'object'
            ? deepClone(currentModule.data)
            : {};
        var localization = getAiLocalizationOptions();
        var requestArgs = aiBuilderService.validateModuleLocalizationRequest({
            config: state.aiConfig,
            prompt: prompt,
            connectionId: connectionId,
            model: model,
            currentModuleType: moduleType,
            localization: localization,
            messages: {
                unavailable: getText('aiUnavailable', 'AI 装修尚未配置完成，请先到主题设置中启用并配置连接。'),
                missingModule: getText('aiNoSelectedModule', '请先在页面结构中选中一个模块，再使用当前模块优化。'),
                disallowedSitePrompt: getText('aiDisallowedSitePrompt', '在线 AI 整站生成已关闭。请改为生成当前单页，或选中单个模块后做模块优化。')
            }
        });

        if (!requestArgs.ok) {
            if (requestArgs.message) {
                setAiStatus(requestArgs.message, 'error');
            }
            return;
        }

        pushAiPromptHistory({
            prompt: requestArgs.prompt || getText('aiLocalizationHistoryPrompt', '本地化当前模块'),
            moduleIds: [moduleType],
            connectionId: requestArgs.connectionId,
            model: requestArgs.model
        });
        clearAiPendingResult();
        renderAiWarnings([]);
        setAiBusy(true);
        setAiStatus(getText('aiModuleLocalizing', '正在本地化当前模块，请稍候…'), 'warning');

        aiBuilderService.generatePageModule({
            ajaxUrl: builderData.ajaxUrl,
            config: state.aiConfig,
            postId: builderData.postId,
            mode: 'localization',
            prompt: requestArgs.prompt,
            connectionId: requestArgs.connectionId,
            model: requestArgs.model,
            moduleIds: [moduleType],
            currentModuleType: moduleType,
            currentModuleData: currentModuleData,
            localization: requestArgs.localization,
            plan: buildCurrentModuleAiPlan(requestArgs.prompt || getText('aiLocalizationPlanGoal', '本地化当前模块文案'), currentModule),
            successMessage: getText('aiModuleLocalizationApplySuccess', '当前模块已完成本地化，请确认预览后保存。'),
            generateFailedMessage: getText('aiModuleLocalizationFailed', '本地化失败，请重试。'),
            timeoutMessage: getText('aiModuleLocalizationTimeout', 'AI 模块本地化超时，请稍后重试。'),
            networkErrorMessage: getText('aiModuleLocalizationFailed', '本地化失败，请重试。')
        }).done(function(result) {
            renderAiWarnings(result.warnings);
            setAiPendingResult({
                kind: 'module',
                mode: 'localization',
                module: result.module,
                localization: result.data && result.data.localization ? result.data.localization : requestArgs.localization,
                baseModules: baseModules,
                basePageSettings: basePageSettings,
                targetIndex: targetIndex,
                targetType: targetType,
                targetModuleSignature: targetModuleSignature,
                successMessage: result.successMessage
            });
            setAiStatus(getText('aiPendingLocalizationReady', '本地化草稿已生成，请查看差异后应用。'), 'success');
        }).fail(function(result) {
            setAiStatus(result && result.message ? result.message : getText('aiModuleLocalizationFailed', '本地化失败，请重试。'), 'error');
        }).always(function() {
            setAiBusy(false);
        });
    }

    function localizeCurrentPageWithAi() {
        if (!els.aiPane || !aiBuilderService || typeof aiBuilderService.validatePageLocalizationRequest !== 'function' || typeof aiBuilderService.localizePagePackage !== 'function') {
            setAiStatus(getText('aiServiceUnavailable', 'AI 服务未加载，请刷新后重试。'), 'error');
            return;
        }
        if (isAiBusy()) {
            setAiStatus(getText('aiBusy', 'AI 请求进行中，请稍候。'), 'warning');
            return;
        }

        var promptEl = els.aiPane.querySelector('#qfb-ai-prompt');
        var connectionEl = els.aiPane.querySelector('#qfb-ai-connection');
        var modelEl = els.aiPane.querySelector('#qfb-ai-model');
        var prompt = promptEl ? String(promptEl.value || '').trim() : '';
        var connectionId = connectionEl ? String(connectionEl.value || '') : '';
        var model = modelEl ? String(modelEl.value || '').trim() : '';
        var currentPackage = buildCurrentPageAiPackage();
        var baseModules = deepClone(state.modules);
        var basePageSettings = deepClone(state.pageSettings || {});
        var localization = getAiLocalizationOptions();
        localization.scope = 'page';

        var requestArgs = aiBuilderService.validatePageLocalizationRequest({
            config: state.aiConfig,
            prompt: prompt,
            connectionId: connectionId,
            model: model,
            currentPackage: currentPackage,
            localization: localization,
            messages: {
                unavailable: getText('aiUnavailable', 'AI 装修尚未配置完成，请先到主题设置中启用并配置连接。'),
                missingPageModules: getText('aiNoPageModules', '当前页面没有可本地化的模块。'),
                disallowedSitePrompt: getText('aiDisallowedSitePrompt', '在线 AI 整站生成已关闭。请改为生成当前单页，或选中单个模块后做模块优化。')
            }
        });

        if (!requestArgs.ok) {
            if (requestArgs.message) {
                setAiStatus(requestArgs.message, 'error');
            }
            return;
        }

        pushAiPromptHistory({
            prompt: requestArgs.prompt || getText('aiPageLocalizationHistoryPrompt', '本地化当前整页'),
            moduleIds: (state.modules || []).map(function(moduleItem) {
                return moduleItem && moduleItem.type ? String(moduleItem.type) : '';
            }).filter(Boolean),
            connectionId: requestArgs.connectionId,
            model: requestArgs.model
        });
        clearAiPendingResult();
        renderAiWarnings([]);
        setAiBusy(true);
        setAiStatus(getText('aiPageLocalizing', '正在本地化当前整页，请稍候…'), 'warning');

        aiBuilderService.localizePagePackage({
            ajaxUrl: builderData.ajaxUrl,
            config: state.aiConfig,
            postId: builderData.postId,
            prompt: requestArgs.prompt,
            connectionId: requestArgs.connectionId,
            model: requestArgs.model,
            currentPackage: requestArgs.currentPackage,
            localization: requestArgs.localization,
            successMessage: requestArgs.localization.create_language_page
                ? getText('aiPageLocalizationExternalSuccess', '语言页面已生成或更新，可在启灵AI多语言中继续编辑。')
                : getText('aiPageLocalizationApplySuccess', '当前页面已完成本地化，请确认预览后保存。'),
            generateFailedMessage: getText('aiPageLocalizationFailed', '整页本地化失败，请重试。'),
            timeoutMessage: getText('aiPageLocalizationTimeout', 'AI 整页本地化超时，请稍后重试。'),
            networkErrorMessage: getText('aiPageLocalizationFailed', '整页本地化失败，请重试。')
        }).done(function(result) {
            renderAiWarnings(result.warnings);
            setAiPendingResult({
                kind: 'page',
                mode: 'localization',
                packageData: result.package,
                localization: result.data && result.data.localization ? result.data.localization : requestArgs.localization,
                localizationReview: result.localizationReview || {},
                localizationScore: result.localizationScore || {},
                providerSync: result.providerSync || null,
                baseModules: baseModules,
                basePageSettings: basePageSettings,
                externalOnly: !!(requestArgs.localization.create_language_page && result.providerSync && result.providerSync.success),
                successMessage: result.successMessage
            });
            setAiStatus(requestArgs.localization.create_language_page && result.providerSync && result.providerSync.success
                ? getText('aiPageLocalizationExternalReady', '语言页面已生成，请查看结果后确认。')
                : getText('aiPendingLocalizationReady', '本地化草稿已生成，请查看差异后应用。'), 'success');
        }).fail(function(result) {
            setAiStatus(result && result.message ? result.message : getText('aiPageLocalizationFailed', '整页本地化失败，请重试。'), 'error');
        }).always(function() {
            setAiBusy(false);
        });
    }

    function saveModules() {
        if (state.saving) {
            return;
        }
        syncVisibleRepeatersToSelectedModuleData();
        var modulesForTransport = prepareModulesForTransport(state.modules);
        var validation = validateModulesForTransport(modulesForTransport);
        if (!validation.valid) {
            setStatus(validation.message, 'error');
            return;
        }

        state.saving = true;
        setStatus(getText('loading', '加载中...'), 'warning');

        $.ajax({
            url: builderData.ajaxUrl,
            method: 'POST',
            dataType: 'json',
            cache: false,
            headers: {
                'Cache-Control': 'no-cache, no-store, must-revalidate',
                'Pragma': 'no-cache'
            },
            data: {
                action: 'qiling_frontend_builder_save_modules',
                nonce: builderData.nonce,
                post_id: builderData.postId,
                data_source: String(builderData.dataSource || 'theme'),
                modules: JSON.stringify(modulesForTransport),
                page_settings: JSON.stringify(state.pageSettings || {}),
                _t: Date.now()
            }
        }).done(function(response) {
            if (!response || !response.success) {
                var msg = response && response.data && response.data.message ? response.data.message : getText('saveFailed', '保存失败，请重试');
                setStatus(msg, 'error');
                return;
            }

            state.dirty = false;
            setStatus(getText('saveSuccess', '已保存，正在刷新预览...'), 'success');
            window.setTimeout(function() {
                window.location.reload();
            }, 350);
        }).fail(function() {
            setStatus(getText('saveFailed', '保存失败，请重试'), 'error');
        }).always(function() {
            state.saving = false;
        });
    }

    function handleLibraryListClick(event) {
        var tplBtn = event.target.closest('.qfb-template-add');
        if (tplBtn) {
            var tplItem = tplBtn.closest('.qfb-lib-item');
            if (!tplItem) {
                return;
            }
            var templateId = tplItem.getAttribute('data-template-id');
            if (templateId) {
                tplBtn.disabled = true;
                setStatus(getText('templateLoading', '正在加载模板内容...'), 'warning');
                addTemplateFromLibrary(templateId).always(function() {
                    tplBtn.disabled = false;
                });
            }
            return;
        }

        var addBtn = event.target.closest('.qfb-lib-add');
        if (!addBtn) {
            return;
        }
        var item = addBtn.closest('.qfb-lib-item');
        if (!item) {
            return;
        }
        var moduleId = item.getAttribute('data-module-id');
        if (moduleId) {
            addModule(moduleId);
        }
    }

    function bindEvents() {
        if (els.toggle && els.root) {
            els.toggle.addEventListener('click', function() {
                els.root.classList.toggle('qfb-collapsed');
                if (els.root.classList.contains('qfb-collapsed')) {
                    document.body.classList.add('qfb-builder-collapsed');
                } else {
                    document.body.classList.remove('qfb-builder-collapsed');
                }
            });
        }

        if (els.librarySearch) {
            els.librarySearch.addEventListener('input', function() {
                applyLibrarySearch(this.value);
            });
        }

        if (els.previewTools) {
            els.previewTools.addEventListener('click', function(event) {
                var modeButton = event.target.closest('[data-preview-mode]');
                if (!modeButton) {
                    return;
                }
                setPreviewMode(modeButton.getAttribute('data-preview-mode'));
            });
        }

        if (els.libraryFilters) {
            els.libraryFilters.addEventListener('click', function(event) {
                var filterButton = event.target.closest('[data-group-filter]');
                if (!filterButton) {
                    return;
                }
                state.libraryGroupFilter = filterButton.getAttribute('data-group-filter') || 'all';
                renderLibraryFilters();
                applyLibrarySearch(els.librarySearch ? els.librarySearch.value : '');
            });
        }

        if (els.libraryList) {
            els.libraryList.addEventListener('click', handleLibraryListClick);
        }

        if (els.myLibraryList) {
            els.myLibraryList.addEventListener('click', handleLibraryListClick);
        }

        if (els.pageList) {
            els.pageList.addEventListener('click', function(event) {
                var item = event.target.closest('.qfb-page-item');
                if (!item) {
                    return;
                }
                if (item.getAttribute('data-scope') === 'page') {
                    state.selectedScope = 'page';
                    renderPageList();
                    renderSettings();
                    return;
                }
                var index = parseInt(item.getAttribute('data-index'), 10);
                if (Number.isNaN(index)) {
                    return;
                }

                if (event.target.closest('.qfb-page-delete')) {
                    deleteModule(index);
                    return;
                }
                if (event.target.closest('.qfb-page-duplicate')) {
                    duplicateModule(index);
                    return;
                }
                state.selectedScope = 'module';
                state.selectedIndex = index;
                renderPageList();
                renderSettings();
            });
        }

        document.addEventListener('click', function(event) {
            if (event.target.closest('#qiling-frontend-builder')) {
                return;
            }

            var wrapper = event.target.closest('.qiling-builder-module');
            if (!wrapper) {
                return;
            }

            var index = parseInt(wrapper.getAttribute('data-builder-index'), 10);
            if (!Number.isNaN(index) && index >= 0 && index < state.modules.length) {
                state.selectedScope = 'module';
                if (state.selectedIndex !== index) {
                    state.selectedIndex = index;
                    renderPageList();
                    renderSettings();
                } else {
                    highlightSelectedWrapper();
                }
            }

            var link = event.target.closest('a[href]');
            if (link) {
                event.preventDefault();
            }
        }, true);

        if (els.settings) {
            els.settings.addEventListener('click', function(event) {
                var tokenButton = event.target.closest('.qfb-token-chip');
                if (tokenButton) {
                    applyTokenChipValue(tokenButton);
                    return;
                }

                var resetAllButton = event.target.closest('[data-page-design-reset="all"]');
                if (resetAllButton) {
                    resetPageDesignGroup('all');
                    return;
                }

                var resetGroupButton = event.target.closest('[data-page-design-reset-group]');
                if (resetGroupButton) {
                    resetPageDesignGroup(resetGroupButton.getAttribute('data-page-design-reset-group') || '');
                    return;
                }

                var resetComponentGroupButton = event.target.closest('[data-page-design-reset-component-group]');
                if (resetComponentGroupButton) {
                    resetPageDesignGroup('componentStyles', resetComponentGroupButton.getAttribute('data-page-design-reset-component-group') || '');
                    return;
                }

                var moduleVisualModeButton = event.target.closest('[data-qfb-module-visual-mode]');
                if (moduleVisualModeButton) {
                    applyModuleVisualMode(moduleVisualModeButton.getAttribute('data-qfb-module-visual-mode') || 'follow');
                    return;
                }

                var moduleVisualActionButton = event.target.closest('[data-qfb-module-visual-action]');
                if (moduleVisualActionButton) {
                    var moduleVisualAction = moduleVisualActionButton.getAttribute('data-qfb-module-visual-action') || '';
                    if (moduleVisualAction === 'sync-primary') {
                        syncModuleVisualWithPagePrimary();
                    } else {
                        applyModuleVisualMode('follow');
                    }
                    return;
                }

                var moduleVisualClearButton = event.target.closest('[data-qfb-module-visual-clear]');
                if (moduleVisualClearButton) {
                    if (state.selectedIndex >= 0 && state.selectedIndex < state.modules.length) {
                        var visualModule = state.modules[state.selectedIndex];
                        if (visualModule && visualModule.data && typeof visualModule.data === 'object') {
                            delete visualModule.data._ds_visual;
                            markDirty();
                            renderSettings();
                            queueModulePreviewRender(state.selectedIndex, false);
                            setStatus(getText('moduleVisualCleared', '已清空当前模块视觉设置，保存后继续继承页面风格。'), 'warning');
                        }
                    }
                    return;
                }

                var pageVisualAction = event.target.closest('[data-qfb-page-visual-action]');
                if (pageVisualAction) {
                    var action = pageVisualAction.getAttribute('data-qfb-page-visual-action') || '';
                    if (action === 'hydrate-preset') {
                        hydratePageVisualFieldsFromPreset();
                    } else if (action === 'clear-custom') {
                        clearPageVisualCustomValues();
                    }
                    return;
                }

                var addBtn = event.target.closest('.qfb-repeater-add');
                if (addBtn) {
                    var addFieldId = addBtn.getAttribute('data-field-id') || '';
                    var addRepeater = addBtn.closest('.qfb-repeater');
                    if (!addRepeater || !addFieldId) {
                        return;
                    }
                    var subFields = parseRepeaterSubFields(addRepeater);
                    var itemsWrap = addRepeater.querySelector('.qfb-repeater-items');
                    if (!itemsWrap) {
                        return;
                    }
                    var emptyEl = itemsWrap.querySelector('.qfb-repeater-empty');
                    if (emptyEl) {
                        emptyEl.parentNode.removeChild(emptyEl);
                    }
                    var nextIndex = itemsWrap.querySelectorAll('.qfb-repeater-item').length;
                    var maxItems = parseInt(addRepeater.getAttribute('data-max-items') || '0', 10);
                    if (maxItems > 0 && nextIndex >= maxItems) {
                        return;
                    }
                    var itemHtml = renderRepeaterItem(addFieldId, subFields, createRepeaterItemData(subFields), nextIndex);
                    itemsWrap.insertAdjacentHTML('beforeend', itemHtml);
                    refreshRepeaterItemIndexes(addRepeater);
                    updateRepeaterFieldData(addRepeater);
                    return;
                }

                var removeBtn = event.target.closest('.qfb-repeater-remove');
                if (removeBtn) {
                    var removeRepeater = removeBtn.closest('.qfb-repeater');
                    var removeItem = removeBtn.closest('.qfb-repeater-item');
                    if (!removeRepeater || !removeItem) {
                        return;
                    }
                    removeItem.parentNode.removeChild(removeItem);
                    refreshRepeaterItemIndexes(removeRepeater);

                    var removeItemsWrap = removeRepeater.querySelector('.qfb-repeater-items');
                    if (removeItemsWrap && !removeItemsWrap.querySelector('.qfb-repeater-item')) {
                        removeItemsWrap.insertAdjacentHTML('beforeend', '<div class="qfb-repeater-empty">' + escapeHtml(getText('repeaterEmpty', '暂无项目，请点击“添加项目”。')) + '</div>');
                    }

                    updateRepeaterFieldData(removeRepeater);
                }
            });

            var onChange = function(event) {
                var target = event.target;
                if (!target.classList) {
                    return;
                }

                if (target.classList.contains('qfb-dynamic-select')) {
                    setDynamicBindingForField(target.getAttribute('data-dynamic-field-id') || '', target.value || '');
                    return;
                }

                if (target.classList.contains('qfb-repeater-input')) {
                    if (target.getAttribute('data-sub-field-type') === 'range') {
                        var subRangeValueEl = target.parentNode ? target.parentNode.querySelector('.qfb-range-value') : null;
                        if (subRangeValueEl) {
                            subRangeValueEl.textContent = target.value;
                        }
                    }
                    var repeaterEl = target.closest('.qfb-repeater');
                    if (!repeaterEl) {
                        return;
                    }
                    updateRepeaterFieldData(repeaterEl);
                    var repeaterItem = target.closest('.qfb-repeater-item');
                    if (repeaterItem) {
                        repeaterItem.setAttribute('data-item-data', encodeBuilderData(collectRepeaterFieldValue(repeaterEl)[parseInt(repeaterItem.getAttribute('data-item-index') || '0', 10)] || {}));
                        refreshFieldDependencies(repeaterItem, decodeBuilderData(repeaterItem.getAttribute('data-item-data'), {}));
                    }
                    return;
                }

                if (!target.classList.contains('qfb-input')) {
                    return;
                }

                if (target.classList.contains('qfb-page-setting-input')) {
                    ensurePageSettingsState();
                    var pagePath = target.getAttribute('data-page-setting-path') || '';
                    var pageType = target.getAttribute('data-page-setting-type') || 'text';
                    if (!pagePath) {
                        return;
                    }

                    var pageValue = pageType === 'checkbox' ? !!target.checked : target.value;
                    setNestedValueByPath(state.pageSettings, pagePath, pageValue);
                    if (pagePath.indexOf('footer.') === 0) {
                        state.pageSettings.footer = normalizePageFooterSettings(state.pageSettings.footer);
                        markDirty();
                        setStatus(getText('pageSettingsPending', '页面模板、顶部和页脚设置会在保存后一起生效。'), 'warning');
                        if (pagePath === 'footer.mode') {
                            renderPageSettings();
                        }
                        return;
                    }
                    if (pagePath.indexOf('visualStyle.') === 0) {
                        if (pagePath !== 'visualStyle.mode' && normalizeFieldValue(pageValue).trim() !== '') {
                            state.pageSettings.visualStyle.mode = 'custom';
                        }
                        state.pageSettings.visualStyle = normalizePageVisualStyleSettings(state.pageSettings.visualStyle);
                        applyPageVisualStylePreview();
                        markDirty();
                        setStatus(getText('pageVisualPreviewApplied', '页面视觉风格已同步到当前预览，保存后会正式生效。'), 'warning');
                        if (pagePath === 'visualStyle.mode' || pagePath === 'visualStyle.preset') {
                            renderPageSettings();
                        }
                        return;
                    }
                    if (pagePath.indexOf('design.') === 0) {
                        state.pageSettings.design = normalizePageDesignState(state.pageSettings.design);
                        applyPageDesignPreview();
                        refreshPageDesignSummaryUI();
                        markDirty();
                        setStatus(getText('pageDesignPreviewApplied', '页面设计已同步到当前预览，保存后会正式生效。'), 'warning');
                    } else {
                        markDirty();
                        setStatus(getText('pageSettingsPending', '页面模板与头部设置会在保存后一起生效。'), 'warning');
                    }
                    return;
                }

                if (state.selectedIndex < 0 || state.selectedIndex >= state.modules.length) {
                    return;
                }

                var fieldId = target.getAttribute('data-field-id');
                var fieldType = target.getAttribute('data-field-type') || 'text';
                var isAdvancedInput = target.classList.contains('qfb-advanced-input');
                if (!isAdvancedInput && !fieldId) {
                    return;
                }

                var selectedModule = state.modules[state.selectedIndex];
                if (!selectedModule.data || typeof selectedModule.data !== 'object') {
                    selectedModule.data = {};
                }

                if (isAdvancedInput) {
                    var advancedRoot = target.getAttribute('data-advanced-root');
                    var advancedPath = target.getAttribute('data-advanced-path');
                    if (!advancedRoot || !advancedPath) {
                        return;
                    }

                    if (!selectedModule.data[advancedRoot] || typeof selectedModule.data[advancedRoot] !== 'object') {
                        selectedModule.data[advancedRoot] = {};
                    }

                    setNestedValueByPath(selectedModule.data[advancedRoot], advancedPath, target.value);
                    if (advancedRoot === '_ds_visual' && advancedPath !== 'base.mode') {
                        if (!selectedModule.data._ds_visual.base || typeof selectedModule.data._ds_visual.base !== 'object') {
                            selectedModule.data._ds_visual.base = {};
                        }
                        selectedModule.data._ds_visual.base.mode = 'custom';
                        selectedModule.data._ds_visual.base.inherit_page = '0';
                    }
                } else if (fieldType === 'checkbox') {
                    selectedModule.data[fieldId] = target.checked ? '1' : '0';
                } else {
                    selectedModule.data[fieldId] = target.value;
                }

                refreshFieldDependencies(els.settings, selectedModule.data);

                if (selectedModule.type === 'banner') {
                    syncVisibleRepeatersToSelectedModuleData();
                }

                if (fieldType === 'range') {
                    var rangeValueEl = target.parentNode ? target.parentNode.querySelector('.qfb-range-value') : null;
                    if (rangeValueEl) {
                        rangeValueEl.textContent = target.value;
                    }
                }

                markDirty();
                queueModulePreviewRender(state.selectedIndex, false);
            };

            els.settings.addEventListener('input', onChange);
            els.settings.addEventListener('change', onChange);
        }

        if (els.save) {
            els.save.addEventListener('click', function() {
                saveModules();
            });
        }

        if (els.snapshotsToggle && els.snapshotsPanel) {
            els.snapshotsToggle.addEventListener('click', function() {
                var willShow = els.snapshotsPanel.style.display === 'none' || !els.snapshotsPanel.style.display;
                els.snapshotsPanel.style.display = willShow ? '' : 'none';
                if (willShow) {
                    loadSnapshots(false);
                }
            });
            els.snapshotsPanel.addEventListener('click', function(event) {
                var restoreButton = event.target.closest('[data-qfb-restore-snapshot]');
                if (!restoreButton) {
                    return;
                }
                restoreSnapshot(restoreButton.getAttribute('data-qfb-restore-snapshot') || '');
            });

            // 统一修订管理页通过参数进入时，自动展开当前页面的保存历史。
            try {
                var builderUrl = new URL(window.location.href);
                if (builderUrl.searchParams.get('qiling_builder_snapshots') === '1') {
                    els.snapshotsPanel.style.display = '';
                    loadSnapshots(false);
                }
            } catch (error) {
                // 旧浏览器无法解析 URL 时不影响正常装修功能。
            }
        }

        if (els.aiToggle) {
            els.aiToggle.addEventListener('click', function() {
                setRightPaneMode(state.panelMode === 'ai' ? 'settings' : 'ai');
            });
        }

        if (els.aiPane) {
            els.aiPane.addEventListener('click', function(event) {
                var historyDeleteButton = event.target.closest('[data-ai-prompt-history-delete]');
                if (historyDeleteButton) {
                    deleteAiPromptHistory(historyDeleteButton.getAttribute('data-ai-prompt-history-delete') || '');
                    return;
                }
                if (event.target.closest('[data-ai-prompt-history-clear]')) {
                    clearAiPromptHistory();
                    return;
                }
                var historyButton = event.target.closest('[data-ai-prompt-history]');
                if (historyButton) {
                    applyAiPromptHistory(historyButton.getAttribute('data-ai-prompt-history') || '');
                    return;
                }
                var recipeButton = event.target.closest('[data-ai-prompt-recipe]');
                if (recipeButton) {
                    applyAiPromptRecipe(recipeButton.getAttribute('data-ai-prompt-recipe') || '');
                    return;
                }
                var localActionButton = event.target.closest('[data-ai-local-action]');
                if (localActionButton) {
                    handleAiLocalAction(localActionButton.getAttribute('data-ai-local-action') || '');
                    return;
                }
                var styleRecommendationButton = event.target.closest('[data-ai-style-recommendation]');
                if (styleRecommendationButton) {
                    if (isAiBusy()) {
                        setAiStatus(getText('aiBusy', 'AI 请求进行中，请稍候。'), 'warning');
                        return;
                    }
                    applyAiStyleRecommendation(styleRecommendationButton.getAttribute('data-ai-style-recommendation') || '');
                    return;
                }
                var bundleButton = event.target.closest('[data-ai-module-bundle]');
                if (bundleButton) {
                    applyAiModuleBundle(bundleButton.getAttribute('data-ai-module-bundle') || '');
                    return;
                }
                if (event.target.closest('#qfb-ai-generate')) {
                    generateAiDraft();
                }
                if (event.target.closest('#qfb-ai-optimize-module')) {
                    optimizeCurrentModuleWithAi();
                }
                if (event.target.closest('#qfb-ai-localize-module')) {
                    localizeCurrentModuleWithAi();
                }
                if (event.target.closest('#qfb-ai-localize-page')) {
                    localizeCurrentPageWithAi();
                }
                if (event.target.closest('#qfb-ai-apply-result')) {
                    applyPendingAiResult();
                }
                if (event.target.closest('#qfb-ai-discard-result')) {
                    discardPendingAiResult();
                }
                if (event.target.closest('#qfb-ai-undo')) {
                    restoreLatestAiSnapshot();
                }
            });

            els.aiPane.addEventListener('change', function(event) {
                if (event.target.closest('#qfb-ai-connection')) {
                    var modelInput = els.aiPane.querySelector('#qfb-ai-model');
                    if (modelInput) {
                        modelInput.value = '';
                    }
                    updateAiModelSuggestions();
                    renderAiReadiness();
                }
                if (event.target.closest('#qfb-ai-module-list input[type="checkbox"]')) {
                    updateAiSelectedCount();
                }
            });

            els.aiPane.addEventListener('input', function(event) {
                if (event.target.closest('#qfb-ai-prompt') || event.target.closest('#qfb-ai-model')) {
                    renderAiReadiness();
                }
                if (event.target.closest('#qfb-ai-module-search')) {
                    var keyword = String(event.target.value || '').toLowerCase();
                    var items = els.aiPane.querySelectorAll('.qfb-ai-module-item');
                    Array.prototype.forEach.call(items, function(item) {
                        var haystack = String(item.getAttribute('data-module-name') || '');
                        item.classList.toggle('is-hidden', !!keyword && haystack.indexOf(keyword) === -1);
                    });
                    syncGroupedListHeaders(
                        els.aiPane.querySelector('#qfb-ai-module-list'),
                        '.qfb-ai-module-item',
                        '.qfb-ai-group-title'
                    );
                }
            });
        }

        window.addEventListener('beforeunload', function(event) {
            if (!state.dirty) {
                return;
            }
            event.preventDefault();
            event.returnValue = '';
        });
    }

    function init() {
        initDomRefs();
        if (!els.root) {
            return;
        }

        document.body.classList.add('qfb-builder-mode');
        document.body.classList.remove('qfb-builder-collapsed');
        initLoadedScriptsCache();

        normalizeInitialData();
        loadAiSnapshots();
        loadAiPromptHistory();
        applyPageDesignPreview();
        applyPageVisualStylePreview();
        ensureExternalAssetsForModules(state.modules).catch(function() {
            setStatus(getText('externalAssetLoadFailed', '模块依赖资源加载失败，部分预览效果可能不可用。'), 'warning');
            return false;
        });
        renderMyLibrary();
        renderLibrary();
        renderDesignSystemSummary();
        setPreviewMode('desktop');
        initWrappers();
        var needsInitialPreview = false;

        if (state.modules.length > state.domWrappers.length && !isQilingShopSource()) {
            while (state.domWrappers.length < state.modules.length) {
                var missingIndex = state.domWrappers.length;
                var missing = state.modules[missingIndex] || { type: 'module' };
                var placeholder = createPlaceholderWrapper(missing.type, getModuleName(missing.type));
                state.domWrappers.push(placeholder);
            }
            reflowDomWrappers();
            needsInitialPreview = true;
        }

        state.selectedIndex = state.modules.length ? 0 : -1;
        renderPageList();
        renderAiPane();
        renderSettings();
        setRightPaneMode('settings');
        bindEvents();
        if (needsInitialPreview) {
            queuePreviewRender(true);
        }
        markSaved();
    }

    $(init);
})(jQuery);
