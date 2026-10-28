/**
 * 启灵主题装修生成共用服务
 *
 * 统一前台装修与后台编辑器中的校验、连接建议与生成请求流程。
 */
(function(window, $) {
    'use strict';

    if (!window || !$) {
        return;
    }

    function normalizeString(value) {
        return String(typeof value === 'undefined' || value === null ? '' : value);
    }

    function normalizeModuleIds(moduleIds) {
        if (!Array.isArray(moduleIds)) {
            return [];
        }

        var normalized = [];
        moduleIds.forEach(function(moduleId) {
            moduleId = normalizeString(moduleId).trim();
            if (!moduleId || normalized.indexOf(moduleId) !== -1) {
                return;
            }
            normalized.push(moduleId);
        });

        return normalized;
    }

    function getConnections(config) {
        return config && Array.isArray(config.connections) ? config.connections : [];
    }

    function getConnectionMap(config) {
        var map = {};
        getConnections(config).forEach(function(connection) {
            if (!connection || !connection.id) {
                return;
            }
            map[normalizeString(connection.id)] = connection;
        });
        return map;
    }

    function getMaxModules(config) {
        var rawValue = config && config.defaultMaxModules
            ? parseInt(config.defaultMaxModules, 10)
            : 10;

        if (Number.isNaN(rawValue) || rawValue < 1) {
            return 10;
        }
        if (rawValue > 10) {
            return 10;
        }

        return rawValue;
    }

    function getScopePolicy(config) {
        return config && config.scopePolicy && typeof config.scopePolicy === 'object'
            ? config.scopePolicy
            : {
                site_generation_allowed: false,
                max_pages_per_request: 1,
                allowed_scopes: ['module', 'page'],
                disallowed_scopes: ['site', 'whole_site', 'multi_page_site', 'site_package']
            };
    }

    function getLocalizationConfig(config) {
        var defaults = {
            enabled: true,
            mode: 'localization',
            preserveLayout: true,
            defaultCurrency: 'USD',
            languages: {
                en: '英文',
                ja: '日文',
                ko: '韩文',
                fr: '法文',
                de: '德文',
                es: '西班牙文'
            },
            tones: {
                professional: '专业可信',
                friendly: '自然友好',
                concise: '简洁直接',
                premium: '高端克制',
                technical: '技术清晰'
            },
            industryTonePacks: {},
            batchContentTypes: {},
            supportsPage: true,
            supportsBatch: true,
            supportsLanguagePage: true,
            providerAvailable: false
        };
        var incoming = config && config.localization && typeof config.localization === 'object'
            ? config.localization
            : {};

        return $.extend(true, {}, defaults, incoming, { preserveLayout: true });
    }

    function normalizeTermList(value) {
        if (typeof value === 'string') {
            return value.split(/[\n,]+/).map(function(item) {
                return normalizeString(item).trim();
            }).filter(Boolean);
        }

        if (!Array.isArray(value)) {
            return [];
        }

        return value.map(function(item) {
            if (item && typeof item === 'object') {
                return normalizeString(item.term || item.value || '').trim();
            }
            return normalizeString(item).trim();
        }).filter(Boolean);
    }

    function normalizeTranslationPairs(value) {
        if (typeof value === 'string') {
            return value.split(/\n+/).map(function(line) {
                line = normalizeString(line).trim();
                if (!line) {
                    return null;
                }

                var separator = line.indexOf('=') !== -1 ? '=' : (line.indexOf(':') !== -1 ? ':' : '');
                if (!separator) {
                    return null;
                }

                var parts = line.split(separator);
                return {
                    source: normalizeString(parts.shift()).trim(),
                    target: normalizeString(parts.join(separator)).trim()
                };
            }).filter(function(item) {
                return item && item.source && item.target;
            });
        }

        if (!Array.isArray(value)) {
            return [];
        }

        return value.map(function(item) {
            if (!item || typeof item !== 'object') {
                return null;
            }
            return {
                source: normalizeString(item.source).trim(),
                target: normalizeString(item.target).trim()
            };
        }).filter(function(item) {
            return item && item.source && item.target;
        });
    }

    function normalizeLocalizationOptions(options, config) {
        options = options && typeof options === 'object' ? options : {};
        var localizationConfig = getLocalizationConfig(config);
        var languages = localizationConfig.languages && typeof localizationConfig.languages === 'object'
            ? localizationConfig.languages
            : {};
        var tones = localizationConfig.tones && typeof localizationConfig.tones === 'object'
            ? localizationConfig.tones
            : {};
        var tonePacks = localizationConfig.industryTonePacks && typeof localizationConfig.industryTonePacks === 'object'
            ? localizationConfig.industryTonePacks
            : {};
        var targetLanguage = normalizeString(options.target_language || options.targetLanguage || 'en').trim();
        var tone = normalizeString(options.tone || 'professional').trim();
        var industryTonePack = normalizeString(options.industry_tone_pack || options.industryTonePack || '').trim();
        var currency = normalizeString(options.currency || localizationConfig.defaultCurrency || 'USD').trim().toUpperCase().replace(/[^A-Z]/g, '');

        if (!Object.prototype.hasOwnProperty.call(languages, targetLanguage)) {
            targetLanguage = 'en';
        }
        if (!Object.prototype.hasOwnProperty.call(tones, tone)) {
            tone = 'professional';
        }
        if (industryTonePack && !Object.prototype.hasOwnProperty.call(tonePacks, industryTonePack)) {
            industryTonePack = '';
        }
        if (currency.length !== 3) {
            currency = {
                en: 'USD',
                ja: 'JPY',
                ko: 'KRW',
                fr: 'EUR',
                de: 'EUR',
                es: 'EUR'
            }[targetLanguage] || 'USD';
        }

        return {
            scope: normalizeString(options.scope || 'module').trim() || 'module',
            target_language: targetLanguage,
            target_market: normalizeString(options.target_market || options.targetMarket || '').trim(),
            tone: tone,
            currency: currency,
            industry: normalizeString(options.industry || '').trim(),
            industry_tone_pack: industryTonePack,
            fixed_translations: normalizeTranslationPairs(options.fixed_translations || options.fixedTranslations || ''),
            forbidden_words: normalizeTermList(options.forbidden_words || options.forbiddenWords || ''),
            product_terms: normalizeTermList(options.product_terms || options.productTerms || ''),
            create_language_page: !!(options.create_language_page || options.createLanguagePage),
            sync_provider: !!(options.sync_provider || options.syncProvider || options.create_language_page || options.createLanguagePage),
            batch_content_types: normalizeTermList(options.batch_content_types || options.batchContentTypes || ['page']),
            batch_limit: parseInt(options.batch_limit || options.batchLimit || 5, 10) || 5,
            preserve_layout: true
        };
    }

    function isSiteGenerationAllowed(config) {
        var policy = getScopePolicy(config);
        return !!policy.site_generation_allowed;
    }

    function getDisallowedSitePromptMessage(config, messages) {
        var policy = getScopePolicy(config);
        return (messages && messages.disallowedSitePrompt) ||
            policy.notice ||
            '当前工具用于当前单页或当前模块。';
    }

    function looksLikeDisallowedSiteGeneration(prompt, config) {
        if (isSiteGenerationAllowed(config)) {
            return false;
        }

        var text = normalizeString(prompt).toLowerCase();
        if (!text) {
            return false;
        }

        return [
            '整站',
            '全站',
            '站点包',
            '网站包',
            '整套网站',
            '完整网站',
            '全套页面',
            '多个页面',
            '多页面',
            '所有页面',
            '批量页面',
            '一键建站',
            'whole site',
            'full website',
            'entire site',
            'site package',
            'multi-page',
            'multiple pages',
            'all pages'
        ].some(function(needle) {
            return text.indexOf(String(needle).toLowerCase()) !== -1;
        });
    }

    function getModelSuggestions(config, connectionId) {
        var connection = getConnectionMap(config)[normalizeString(connectionId)] || null;
        if (!connection) {
            return [];
        }

        var models = Array.isArray(connection.models) ? connection.models.slice() : [];
        if (connection.default_model && models.indexOf(connection.default_model) === -1) {
            models.unshift(connection.default_model);
        }

        return models.filter(function(modelName, index) {
            modelName = normalizeString(modelName).trim();
            return !!modelName && models.indexOf(modelName) === index;
        });
    }

    function clearOptions(target) {
        if (!target) {
            return;
        }

        if (target.jquery) {
            target.empty();
            return;
        }

        target.innerHTML = '';
    }

    function appendOption(target, value) {
        value = normalizeString(value).trim();
        if (!target || !value) {
            return;
        }

        if (target.jquery) {
            target.append('<option value="' + $('<div>').text(value).html() + '"></option>');
            return;
        }

        var option = document.createElement('option');
        option.value = value;
        target.appendChild(option);
    }

    function readValue(target) {
        if (!target) {
            return '';
        }

        if (target.jquery) {
            return normalizeString(target.val());
        }

        return normalizeString(target.value);
    }

    function writeValue(target, value) {
        if (!target) {
            return;
        }

        if (target.jquery) {
            target.val(value);
            return;
        }

        target.value = value;
    }

    function updateModelSuggestions(args) {
        args = args && typeof args === 'object' ? args : {};

        var models = getModelSuggestions(args.config, args.connectionId);
        clearOptions(args.datalist);
        models.forEach(function(modelName) {
            appendOption(args.datalist, modelName);
        });

        if (!readValue(args.modelInput)) {
            var connection = getConnectionMap(args.config)[normalizeString(args.connectionId)] || null;
            if (connection && connection.default_model) {
                writeValue(args.modelInput, normalizeString(connection.default_model));
            }
        }

        return models;
    }

    function getSelectedValues(source) {
        var values = [];

        function pushValue(value) {
            value = normalizeString(value).trim();
            if (!value || values.indexOf(value) !== -1) {
                return;
            }
            values.push(value);
        }

        if (!source) {
            return values;
        }

        if (typeof source === 'string') {
            $(source).each(function() {
                pushValue(this && this.value);
            });
            return values;
        }

        if (source.jquery) {
            source.each(function() {
                pushValue(this && this.value);
            });
            return values;
        }

        if (typeof source.length === 'number') {
            Array.prototype.forEach.call(source, function(item) {
                pushValue(item && item.value);
            });
            return values;
        }

        pushValue(source.value);
        return values;
    }

    function validateGenerationRequest(args) {
        args = args && typeof args === 'object' ? args : {};

        var config = args.config && typeof args.config === 'object' ? args.config : {};
        var messages = args.messages && typeof args.messages === 'object' ? args.messages : {};
        var prompt = normalizeString(args.prompt).trim();
        var connectionId = normalizeString(args.connectionId);
        var model = normalizeString(args.model).trim();
        var moduleIds = normalizeModuleIds(args.moduleIds);
        var maxModules = getMaxModules(config);

        if (!config.enabled) {
            return {
                ok: false,
                code: 'unavailable',
                message: messages.unavailable || 'AI 装修尚未配置完成，请先到主题设置中启用并配置连接。'
            };
        }

        if (!prompt) {
            return {
                ok: false,
                code: 'missing_prompt',
                message: messages.missingPrompt || '请先输入装修需求。'
            };
        }

        if (looksLikeDisallowedSiteGeneration(prompt, config)) {
            return {
                ok: false,
                code: 'site_generation_disallowed',
                message: getDisallowedSitePromptMessage(config, messages)
            };
        }

        if (!moduleIds.length) {
            return {
                ok: false,
                code: 'missing_modules',
                message: messages.missingModules || '请先选择候选模块。'
            };
        }

        if (moduleIds.length > maxModules) {
            return {
                ok: false,
                code: 'too_many_modules',
                message: messages.tooManyModules || ('候选模块最多选择 ' + maxModules + ' 个。')
            };
        }

        if (args.hasExistingModules && typeof args.confirmReplace === 'function') {
            var confirmed = args.confirmReplace(messages.replaceConfirm || 'AI 会先生成待应用草稿，确认应用时会替换当前模块列表。是否继续？');
            if (!confirmed) {
                return {
                    ok: false,
                    code: 'cancelled',
                    message: ''
                };
            }
        }

        return {
            ok: true,
            prompt: prompt,
            connectionId: connectionId,
            model: model,
            moduleIds: moduleIds,
            maxModules: maxModules
        };
    }

    function validateModuleLocalizationRequest(args) {
        args = args && typeof args === 'object' ? args : {};

        var config = args.config && typeof args.config === 'object' ? args.config : {};
        var messages = args.messages && typeof args.messages === 'object' ? args.messages : {};
        var prompt = normalizeString(args.prompt).trim();
        var connectionId = normalizeString(args.connectionId);
        var model = normalizeString(args.model).trim();
        var moduleType = normalizeString(args.currentModuleType).trim();
        var localization = normalizeLocalizationOptions(args.localization, config);

        if (!config.enabled) {
            return {
                ok: false,
                code: 'unavailable',
                message: messages.unavailable || 'AI 装修尚未配置完成，请先到主题设置中启用并配置连接。'
            };
        }

        if (!moduleType) {
            return {
                ok: false,
                code: 'missing_module',
                message: messages.missingModule || '请先选中一个模块。'
            };
        }

        if (prompt && looksLikeDisallowedSiteGeneration(prompt, config)) {
            return {
                ok: false,
                code: 'site_generation_disallowed',
                message: getDisallowedSitePromptMessage(config, messages)
            };
        }

        return {
            ok: true,
            prompt: prompt,
            connectionId: connectionId,
            model: model,
            moduleIds: [moduleType],
            currentModuleType: moduleType,
            localization: localization
        };
    }

    function validatePageLocalizationRequest(args) {
        args = args && typeof args === 'object' ? args : {};

        var config = args.config && typeof args.config === 'object' ? args.config : {};
        var messages = args.messages && typeof args.messages === 'object' ? args.messages : {};
        var prompt = normalizeString(args.prompt).trim();
        var connectionId = normalizeString(args.connectionId);
        var model = normalizeString(args.model).trim();
        var currentPackage = args.currentPackage && typeof args.currentPackage === 'object' ? args.currentPackage : {};
        var modules = Array.isArray(currentPackage.modules) ? currentPackage.modules : [];
        var localization = normalizeLocalizationOptions($.extend({}, args.localization, { scope: 'page' }), config);

        if (!config.enabled) {
            return {
                ok: false,
                code: 'unavailable',
                message: messages.unavailable || 'AI 装修尚未配置完成，请先到主题设置中启用并配置连接。'
            };
        }

        if (!modules.length) {
            return {
                ok: false,
                code: 'missing_page_modules',
                message: messages.missingPageModules || '当前页面没有可本地化的模块。'
            };
        }

        if (prompt && looksLikeDisallowedSiteGeneration(prompt, config)) {
            return {
                ok: false,
                code: 'site_generation_disallowed',
                message: getDisallowedSitePromptMessage(config, messages)
            };
        }

        return {
            ok: true,
            prompt: prompt,
            connectionId: connectionId,
            model: model,
            currentPackage: currentPackage,
            localization: localization
        };
    }

    function normalizeWarnings(warnings) {
        if (!Array.isArray(warnings)) {
            return [];
        }

        return warnings.map(function(warning) {
            return normalizeString(warning).trim();
        }).filter(Boolean);
    }

    function getRequestTimeout(args, config) {
        var rawValue = parseInt(
            args && args.timeout ? args.timeout : (config && config.timeout ? config.timeout : 90000),
            10
        );

        if (Number.isNaN(rawValue) || rawValue < 10000) {
            return 90000;
        }
        return rawValue;
    }

    function getNetworkFailureMessage(args, fallback, textStatus) {
        if (textStatus === 'timeout') {
            return args.timeoutMessage || 'AI 请求超时，请稍后重试。';
        }
        return args.networkErrorMessage || args.generateFailedMessage || fallback;
    }

    function generatePagePackage(args) {
        args = args && typeof args === 'object' ? args : {};

        var deferred = $.Deferred();
        var config = args.config && typeof args.config === 'object' ? args.config : {};

        $.ajax({
            url: args.ajaxUrl || window.ajaxurl || '',
            method: 'POST',
            dataType: 'json',
            timeout: getRequestTimeout(args, config),
            data: {
                action: args.ajaxAction || config.ajaxAction || 'qiling_ai_generate_page_package',
                nonce: args.nonce || config.nonce || '',
                post_id: args.postId || 0,
                scope: 'page',
                prompt: normalizeString(args.prompt).trim(),
                connection_id: normalizeString(args.connectionId),
                model: normalizeString(args.model).trim(),
                module_ids: normalizeModuleIds(args.moduleIds)
            }
        }).done(function(response) {
            if (!(response && response.success && response.data && response.data.package)) {
                deferred.reject({
                    type: 'response',
                    response: response,
                    message: response && response.data && response.data.message
                        ? response.data.message
                        : (args.generateFailedMessage || '生成失败，请重试。')
                });
                return;
            }

            deferred.resolve({
                response: response,
                data: response.data,
                package: response.data.package,
                warnings: normalizeWarnings(response.data.warnings),
                successMessage: response.data && response.data.message
                    ? response.data.message
                    : (args.successMessage || 'AI 草稿已生成。')
            });
        }).fail(function(xhr, textStatus, errorThrown) {
            deferred.reject({
                type: 'network',
                xhr: xhr,
                textStatus: textStatus,
                errorThrown: errorThrown,
                message: getNetworkFailureMessage(args, '生成失败，请重试。', textStatus)
            });
        });

        return deferred.promise();
    }

    function generatePageModule(args) {
        args = args && typeof args === 'object' ? args : {};

        var deferred = $.Deferred();
        var config = args.config && typeof args.config === 'object' ? args.config : {};

        $.ajax({
            url: args.ajaxUrl || window.ajaxurl || '',
            method: 'POST',
            dataType: 'json',
            timeout: getRequestTimeout(args, config),
            data: {
                action: args.moduleAction || config.moduleAction || 'qiling_ai_generate_page_module',
                nonce: args.nonce || config.nonce || '',
                post_id: args.postId || 0,
                scope: 'module',
                mode: normalizeString(args.mode).trim(),
                prompt: normalizeString(args.prompt).trim(),
                connection_id: normalizeString(args.connectionId),
                model: normalizeString(args.model).trim(),
                module_ids: normalizeModuleIds(args.moduleIds),
                plan: JSON.stringify(args.plan || {}),
                current_module_type: normalizeString(args.currentModuleType).trim(),
                current_module_data: JSON.stringify(args.currentModuleData || {}),
                completed_modules: JSON.stringify(args.completedModules || []),
                localization: JSON.stringify(normalizeLocalizationOptions(args.localization, config))
            }
        }).done(function(response) {
            if (!(response && response.success && response.data && response.data.module)) {
                deferred.reject({
                    type: 'response',
                    response: response,
                    message: response && response.data && response.data.message
                        ? response.data.message
                        : (args.generateFailedMessage || '模块生成失败，请重试。')
                });
                return;
            }

            deferred.resolve({
                response: response,
                data: response.data,
                module: response.data.module,
                summary: response.data.summary || '',
                warnings: normalizeWarnings(response.data.warnings),
                successMessage: response.data && response.data.message
                    ? response.data.message
                    : (args.successMessage || 'AI 模块草稿已生成。')
            });
        }).fail(function(xhr, textStatus, errorThrown) {
            deferred.reject({
                type: 'network',
                xhr: xhr,
                textStatus: textStatus,
                errorThrown: errorThrown,
                message: getNetworkFailureMessage(args, '模块生成失败，请重试。', textStatus)
            });
        });

        return deferred.promise();
    }

    function localizePagePackage(args) {
        args = args && typeof args === 'object' ? args : {};

        var deferred = $.Deferred();
        var config = args.config && typeof args.config === 'object' ? args.config : {};

        $.ajax({
            url: args.ajaxUrl || window.ajaxurl || '',
            method: 'POST',
            dataType: 'json',
            timeout: getRequestTimeout(args, config),
            data: {
                action: args.localizePageAction || config.localizePageAction || 'qiling_ai_localize_page_package',
                nonce: args.nonce || config.nonce || '',
                post_id: args.postId || 0,
                scope: 'page',
                prompt: normalizeString(args.prompt).trim(),
                connection_id: normalizeString(args.connectionId),
                model: normalizeString(args.model).trim(),
                current_package: JSON.stringify(args.currentPackage || {}),
                localization: JSON.stringify(normalizeLocalizationOptions($.extend({}, args.localization, { scope: 'page' }), config))
            }
        }).done(function(response) {
            if (!(response && response.success && response.data && response.data.package)) {
                deferred.reject({
                    type: 'response',
                    response: response,
                    message: response && response.data && response.data.message
                        ? response.data.message
                        : (args.generateFailedMessage || '整页本地化失败，请重试。')
                });
                return;
            }

            deferred.resolve({
                response: response,
                data: response.data,
                package: response.data.package,
                warnings: normalizeWarnings(response.data.warnings),
                localizationReview: response.data.localizationReview || {},
                localizationScore: response.data.localizationScore || {},
                providerSync: response.data.providerSync || null,
                successMessage: response.data && response.data.message
                    ? response.data.message
                    : (args.successMessage || 'AI 整页本地化草稿已生成。')
            });
        }).fail(function(xhr, textStatus, errorThrown) {
            deferred.reject({
                type: 'network',
                xhr: xhr,
                textStatus: textStatus,
                errorThrown: errorThrown,
                message: getNetworkFailureMessage(args, '整页本地化失败，请重试。', textStatus)
            });
        });

        return deferred.promise();
    }

    function batchLocalizeContent(args) {
        args = args && typeof args === 'object' ? args : {};

        var deferred = $.Deferred();
        var config = args.config && typeof args.config === 'object' ? args.config : {};

        $.ajax({
            url: args.ajaxUrl || window.ajaxurl || '',
            method: 'POST',
            dataType: 'json',
            timeout: getRequestTimeout(args, config),
            data: {
                action: args.batchLocalizeAction || config.batchLocalizeAction || 'qiling_ai_batch_localize_content',
                nonce: args.nonce || config.nonce || '',
                prompt: normalizeString(args.prompt).trim(),
                connection_id: normalizeString(args.connectionId),
                model: normalizeString(args.model).trim(),
                post_ids: JSON.stringify(args.postIds || []),
                localization: JSON.stringify(normalizeLocalizationOptions($.extend({}, args.localization, { scope: 'batch' }), config))
            }
        }).done(function(response) {
            if (!(response && response.success && response.data)) {
                deferred.reject({
                    type: 'response',
                    response: response,
                    message: response && response.data && response.data.message
                        ? response.data.message
                        : (args.generateFailedMessage || '批量本地化失败，请重试。')
                });
                return;
            }

            deferred.resolve({
                response: response,
                data: response.data,
                results: response.data.results || [],
                successMessage: response.data && response.data.message
                    ? response.data.message
                    : (args.successMessage || '批量本地化已完成。')
            });
        }).fail(function(xhr, textStatus, errorThrown) {
            deferred.reject({
                type: 'network',
                xhr: xhr,
                textStatus: textStatus,
                errorThrown: errorThrown,
                message: getNetworkFailureMessage(args, '批量本地化失败，请重试。', textStatus)
            });
        });

        return deferred.promise();
    }

    window.QilingAiBuilderService = {
        getConnectionMap: getConnectionMap,
        getMaxModules: getMaxModules,
        getScopePolicy: getScopePolicy,
        getLocalizationConfig: getLocalizationConfig,
        normalizeLocalizationOptions: normalizeLocalizationOptions,
        isSiteGenerationAllowed: isSiteGenerationAllowed,
        looksLikeDisallowedSiteGeneration: looksLikeDisallowedSiteGeneration,
        getModelSuggestions: getModelSuggestions,
        updateModelSuggestions: updateModelSuggestions,
        getSelectedValues: getSelectedValues,
        validateGenerationRequest: validateGenerationRequest,
        validateModuleLocalizationRequest: validateModuleLocalizationRequest,
        validatePageLocalizationRequest: validatePageLocalizationRequest,
        generatePagePackage: generatePagePackage,
        generatePageModule: generatePageModule,
        localizePagePackage: localizePagePackage,
        batchLocalizeContent: batchLocalizeContent
    };
})(window, window.jQuery);
