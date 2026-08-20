/**
 * Search enhance runtime.
 *
 * Handles local search history and Ajax autocomplete result cards.
 */
(function (window, document) {
    'use strict';

    function getGlobalData() {
        return window.developerStarterData || {};
    }

    function onReady(callback) {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', callback, { once: true });
            return;
        }

        callback();
    }

    onReady(function () {
        (function initSearchEnhance() {
            var globalData = getGlobalData();
            var searchEnhance = globalData.searchEnhance || {};
            if (searchEnhance.enabled === false) {
                return;
            }

            var storageKey = searchEnhance.storageKey || 'qiling-search-history';
            var maxHistory = parseInt(searchEnhance.maxHistory, 10) || 12;
            var maxSuggestions = parseInt(searchEnhance.maxSuggestions, 10) || 6;
            var minChars = parseInt(searchEnhance.minChars, 10) || 2;
            var debounceDelay = parseInt(searchEnhance.debounce, 10) || 250;
            var strings = searchEnhance.strings || {};
            var ajaxUrl = globalData.ajaxUrl || searchEnhance.ajaxUrl || '';
            var autocompleteAction = searchEnhance.autocompleteAction || 'developer_starter_search_autocomplete';
            var autocompleteNonce = searchEnhance.autocompleteNonce || '';
            var autocompleteEnabled = searchEnhance.autocompleteEnabled !== false && ajaxUrl && autocompleteAction && window.fetch && window.URLSearchParams;
            var showThumbnail = searchEnhance.showThumbnail !== false;
            var showExcerpt = searchEnhance.showExcerpt !== false;
            var showPrice = searchEnhance.showPrice !== false;
            var forms = Array.prototype.slice.call(document.querySelectorAll('form[role="search"], form.search-form, .qiling-search-enhanced'));
            var activePanel = null;
            var pendingTimer = null;
            var requestSeq = 0;
            var memoryCache = {};

            if (!forms.length) {
                return;
            }

            function normalizeTerm(value) {
                return String(value || '').replace(/\s+/g, ' ').trim().slice(0, 80);
            }

            function readHistory() {
                try {
                    var raw = window.localStorage ? localStorage.getItem(storageKey) : '';
                    var parsed = raw ? JSON.parse(raw) : [];
                    return Array.isArray(parsed) ? parsed.filter(function (item) {
                        return item && normalizeTerm(item.term);
                    }) : [];
                } catch (error) {
                    return [];
                }
            }

            function writeHistory(items) {
                try {
                    if (window.localStorage) {
                        localStorage.setItem(storageKey, JSON.stringify(items.slice(0, maxHistory)));
                    }
                } catch (error) {
                    // Search still works when storage is blocked.
                }
            }

            function sortHistory(items) {
                return items.sort(function (a, b) {
                    var countDiff = (parseInt(b.count, 10) || 0) - (parseInt(a.count, 10) || 0);
                    if (countDiff !== 0) {
                        return countDiff;
                    }

                    return (parseInt(b.lastUsed, 10) || 0) - (parseInt(a.lastUsed, 10) || 0);
                });
            }

            function saveSearchTerm(term) {
                term = normalizeTerm(term);
                if (!term) {
                    return;
                }

                var history = readHistory();
                var lower = term.toLocaleLowerCase();
                var found = false;
                history = history.map(function (item) {
                    if (normalizeTerm(item.term).toLocaleLowerCase() === lower) {
                        found = true;
                        return {
                            term: term,
                            count: (parseInt(item.count, 10) || 0) + 1,
                            lastUsed: Date.now()
                        };
                    }

                    return item;
                });

                if (!found) {
                    history.unshift({
                        term: term,
                        count: 1,
                        lastUsed: Date.now()
                    });
                }

                writeHistory(sortHistory(history));
                renderHistoryBlocks();
            }

            function getInput(form) {
                return form ? form.querySelector('input[type="search"], input[name="s"]') : null;
            }

            function getScope(form) {
                var scope = form ? form.querySelector('[data-qiling-search-scope], [name="search_scope"]') : null;
                return scope && scope.value ? String(scope.value).replace(/[^a-z0-9_-]/gi, '').toLowerCase() : (searchEnhance.currentScope || 'all');
            }

            function getHistorySuggestions(value) {
                var query = normalizeTerm(value).toLocaleLowerCase();
                var history = sortHistory(readHistory());

                if (!query) {
                    return history.slice(0, maxSuggestions);
                }

                return history.filter(function (item) {
                    return normalizeTerm(item.term).toLocaleLowerCase().indexOf(query) !== -1;
                }).slice(0, maxSuggestions);
            }

            function getPanelHost(form, input) {
                return input.closest('.search-form-input-group')
                    || form.closest('.header-search, .search-overlay-inner, .search-form-wrap')
                    || form.parentElement
                    || form;
            }

            function getPanel(form, input) {
                var host = getPanelHost(form, input);
                var panel = null;
                var children = host ? host.children : [];
                for (var i = 0; i < children.length; i++) {
                    if (children[i].classList && children[i].classList.contains('qiling-search-suggestions')) {
                        panel = children[i];
                        break;
                    }
                }

                if (!panel) {
                    panel = document.createElement('div');
                    panel.className = 'qiling-search-suggestions';
                    panel.hidden = true;
                    host.appendChild(panel);
                }

                return panel;
            }

            function hidePanel() {
                if (pendingTimer) {
                    window.clearTimeout(pendingTimer);
                    pendingTimer = null;
                }
                if (activePanel) {
                    activePanel.hidden = true;
                    activePanel = null;
                }
            }

            function submitSearchForm(form) {
                if (!form) {
                    return;
                }

                if (typeof form.requestSubmit === 'function') {
                    form.requestSubmit();
                } else {
                    form.submit();
                }
            }

            function clearPanel(panel) {
                while (panel.firstChild) {
                    panel.removeChild(panel.firstChild);
                }
            }

            function appendPanelTitle(panel, text) {
                var title = document.createElement('div');
                title.className = 'qiling-search-suggestions__title';
                title.textContent = text;
                panel.appendChild(title);
            }

            function appendStatus(panel, text, modifier) {
                var status = document.createElement('div');
                status.className = 'qiling-search-suggestions__status';
                if (modifier) {
                    status.className += ' is-' + modifier;
                }
                status.textContent = text;
                panel.appendChild(status);
            }

            function getHighlightTerms(term) {
                return normalizeTerm(term).split(/\s+/).filter(function (part) {
                    return part.length > 0;
                }).sort(function (a, b) {
                    return b.length - a.length;
                });
            }

            function appendHighlightedText(parent, text, term) {
                text = String(text || '');
                if (!text) {
                    return;
                }

                var terms = getHighlightTerms(term);
                if (!terms.length) {
                    parent.appendChild(document.createTextNode(text));
                    return;
                }

                var lower = text.toLocaleLowerCase();
                var index = 0;

                while (index < text.length) {
                    var nextIndex = -1;
                    var matchedTerm = '';

                    terms.forEach(function (candidate) {
                        var foundAt = lower.indexOf(candidate.toLocaleLowerCase(), index);
                        if (foundAt !== -1 && (nextIndex === -1 || foundAt < nextIndex || (foundAt === nextIndex && candidate.length > matchedTerm.length))) {
                            nextIndex = foundAt;
                            matchedTerm = candidate;
                        }
                    });

                    if (nextIndex === -1) {
                        parent.appendChild(document.createTextNode(text.slice(index)));
                        break;
                    }

                    if (nextIndex > index) {
                        parent.appendChild(document.createTextNode(text.slice(index, nextIndex)));
                    }

                    var mark = document.createElement('mark');
                    mark.className = 'search-highlight';
                    mark.textContent = text.slice(nextIndex, nextIndex + matchedTerm.length);
                    parent.appendChild(mark);
                    index = nextIndex + matchedTerm.length;
                }
            }

            function buildSearchUrl(form, term, scope, fallbackUrl) {
                if (fallbackUrl) {
                    return fallbackUrl;
                }

                var action = form.getAttribute('action') || window.location.href;
                var url;
                try {
                    url = new URL(action, window.location.origin);
                    url.searchParams.set('s', term);
                    if (scope && scope !== 'all') {
                        url.searchParams.set('search_scope', scope);
                    }
                    return url.toString();
                } catch (error) {
                    return action;
                }
            }

            function renderHistorySuggestions(form, input) {
                var panel = getPanel(form, input);
                var suggestions = getHistorySuggestions(input.value);

                clearPanel(panel);
                if (!suggestions.length) {
                    panel.hidden = true;
                    if (activePanel === panel) {
                        activePanel = null;
                    }
                    return;
                }

                appendPanelTitle(panel, normalizeTerm(input.value) ? (strings.suggestionsTitle || '搜索建议') : (strings.historyTitle || '搜索历史'));
                suggestions.forEach(function (item) {
                    var button = document.createElement('button');
                    button.type = 'button';
                    button.className = 'qiling-search-suggestions__item';
                    button.textContent = normalizeTerm(item.term);
                    button.addEventListener('mousedown', function (event) {
                        event.preventDefault();
                    });
                    button.addEventListener('click', function () {
                        input.value = normalizeTerm(item.term);
                        hidePanel();
                        submitSearchForm(form);
                    });
                    panel.appendChild(button);
                });

                panel.hidden = false;
                activePanel = panel;
            }

            function renderSuggestions(form, input) {
                renderHistorySuggestions(form, input);
            }

            function renderLoading(form, input) {
                var panel = getPanel(form, input);
                clearPanel(panel);
                appendPanelTitle(panel, strings.suggestionsTitle || '搜索建议');
                appendStatus(panel, strings.loading || '正在搜索...', 'loading');
                panel.hidden = false;
                activePanel = panel;
            }

            function renderRemoteError(form, input) {
                var panel = getPanel(form, input);
                clearPanel(panel);
                appendPanelTitle(panel, strings.suggestionsTitle || '搜索建议');
                appendStatus(panel, strings.networkError || '搜索加载失败，请稍后再试', 'error');
                panel.hidden = false;
                activePanel = panel;
            }

            function renderRemoteResults(form, input, payload) {
                var panel = getPanel(form, input);
                var term = normalizeTerm(input.value);
                var scope = getScope(form);
                var items = payload && Array.isArray(payload.items) ? payload.items : [];
                var searchUrl = buildSearchUrl(form, term, scope, payload ? payload.search_url : '');

                clearPanel(panel);
                appendPanelTitle(panel, strings.suggestionsTitle || '搜索建议');

                if (!items.length) {
                    appendStatus(panel, strings.noResults || '没有找到相关内容', 'empty');
                }

                items.forEach(function (item) {
                    var link = document.createElement('a');
                    link.className = 'qiling-search-result';
                    link.href = item.url || '#';
                    link.addEventListener('mousedown', function (event) {
                        event.preventDefault();
                    });
                    link.addEventListener('click', function () {
                        saveSearchTerm(term);
                    });

                    if (showThumbnail && item.thumbnail) {
                        var thumb = document.createElement('span');
                        thumb.className = 'qiling-search-result__thumb';
                        var img = document.createElement('img');
                        img.src = item.thumbnail;
                        img.alt = '';
                        img.loading = 'lazy';
                        img.decoding = 'async';
                        thumb.appendChild(img);
                        link.appendChild(thumb);
                    }

                    var body = document.createElement('span');
                    body.className = 'qiling-search-result__body';

                    var meta = document.createElement('span');
                    meta.className = 'qiling-search-result__meta';
                    var type = document.createElement('span');
                    type.className = 'qiling-search-result__type';
                    type.textContent = item.type_label || item.type || '';
                    meta.appendChild(type);

                    if (showPrice && item.price) {
                        var price = document.createElement('span');
                        price.className = 'qiling-search-result__price';
                        price.textContent = item.price;
                        meta.appendChild(price);
                    }
                    body.appendChild(meta);

                    var title = document.createElement('span');
                    title.className = 'qiling-search-result__title';
                    appendHighlightedText(title, item.title || '', term);
                    body.appendChild(title);

                    if (showExcerpt && item.excerpt) {
                        var excerpt = document.createElement('span');
                        excerpt.className = 'qiling-search-result__excerpt';
                        appendHighlightedText(excerpt, item.excerpt, term);
                        body.appendChild(excerpt);
                    }

                    link.appendChild(body);
                    panel.appendChild(link);
                });

                if (searchUrl && term) {
                    var all = document.createElement('a');
                    all.className = 'qiling-search-result-all';
                    all.href = searchUrl;
                    all.textContent = strings.viewAll || '查看全部结果';
                    all.addEventListener('click', function () {
                        saveSearchTerm(term);
                    });
                    panel.appendChild(all);
                }

                panel.hidden = false;
                activePanel = panel;
            }

            function fetchRemoteSuggestions(term, scope) {
                var key = scope + '|' + term.toLocaleLowerCase();
                if (memoryCache[key]) {
                    return Promise.resolve(memoryCache[key]);
                }

                var params = new URLSearchParams();
                params.set('action', autocompleteAction);
                params.set('nonce', autocompleteNonce);
                params.set('term', term);
                params.set('scope', scope || 'all');

                return window.fetch(ajaxUrl, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
                    },
                    body: params.toString()
                }).then(function (response) {
                    if (!response.ok) {
                        throw new Error('bad_status');
                    }
                    return response.json();
                }).then(function (json) {
                    if (!json || json.success !== true) {
                        throw new Error('bad_payload');
                    }
                    memoryCache[key] = json.data || {};
                    return memoryCache[key];
                });
            }

            function requestRemoteSuggestions(form, input) {
                var term = normalizeTerm(input.value);
                var scope = getScope(form);
                var modeField = form.querySelector('[name="qiling_search_mode"]');
                var mode = modeField ? String(modeField.value || 'all') : 'all';

                // Mode-specific suggestions require their own query adapters. Until then,
                // avoid showing comprehensive suggestions in article/video-only forms.
                if (!autocompleteEnabled || mode !== 'all' || term.length < minChars) {
                    renderHistorySuggestions(form, input);
                    return;
                }

                if (pendingTimer) {
                    window.clearTimeout(pendingTimer);
                }

                renderLoading(form, input);
                pendingTimer = window.setTimeout(function () {
                    var currentSeq = ++requestSeq;
                    fetchRemoteSuggestions(term, scope).then(function (payload) {
                        if (currentSeq !== requestSeq || normalizeTerm(input.value) !== term) {
                            return;
                        }
                        renderRemoteResults(form, input, payload);
                    }).catch(function () {
                        if (currentSeq !== requestSeq || normalizeTerm(input.value) !== term) {
                            return;
                        }
                        renderRemoteError(form, input);
                    });
                }, debounceDelay);
            }

            function renderHistoryBlocks() {
                var blocks = Array.prototype.slice.call(document.querySelectorAll('[data-qiling-search-history]'));
                if (!blocks.length) {
                    return;
                }

                var history = sortHistory(readHistory()).slice(0, maxSuggestions);
                blocks.forEach(function (block) {
                    block.innerHTML = '';

                    if (!history.length) {
                        var empty = document.createElement('span');
                        empty.className = 'qiling-search-history__empty';
                        empty.textContent = strings.emptyHistory || '暂无搜索历史';
                        block.appendChild(empty);
                        return;
                    }

                    var label = document.createElement('span');
                    label.className = 'qiling-search-history__label';
                    label.textContent = strings.historyTitle || '搜索历史';
                    block.appendChild(label);

                    history.forEach(function (item) {
                        var chip = document.createElement('button');
                        chip.type = 'button';
                        chip.className = 'qiling-search-history__chip';
                        chip.textContent = normalizeTerm(item.term);
                        chip.addEventListener('click', function () {
                            var form = block.closest('form');
                            var input = getInput(form);
                            if (!form || !input) {
                                return;
                            }
                            input.value = normalizeTerm(item.term);
                            submitSearchForm(form);
                        });
                        block.appendChild(chip);
                    });

                    var clear = document.createElement('button');
                    clear.type = 'button';
                    clear.className = 'qiling-search-history__clear';
                    clear.textContent = strings.clearHistory || '清空';
                    clear.addEventListener('click', function () {
                        writeHistory([]);
                        hidePanel();
                        renderHistoryBlocks();
                    });
                    block.appendChild(clear);
                });
            }

            forms.forEach(function (form) {
                var input = getInput(form);
                if (!input) {
                    return;
                }

                input.setAttribute('autocomplete', 'off');
                input.addEventListener('focus', function () {
                    requestRemoteSuggestions(form, input);
                });
                input.addEventListener('input', function () {
                    requestRemoteSuggestions(form, input);
                });
                var modeField = form.querySelector('[name="qiling_search_mode"]');
                if (modeField && modeField.tagName === 'SELECT') {
                    modeField.addEventListener('change', function () {
                        hidePanel();
                        requestRemoteSuggestions(form, input);
                    });
                }
                input.addEventListener('keydown', function (event) {
                    if (event.key === 'Escape') {
                        hidePanel();
                    }
                });
                form.addEventListener('submit', function () {
                    var term = normalizeTerm(input.value);
                    if (!term || form.dataset.qilingSearchHistorySaved === term) {
                        return;
                    }

                    form.dataset.qilingSearchHistorySaved = term;
                    window.setTimeout(function () {
                        if (form.dataset.qilingSearchHistorySaved === term) {
                            delete form.dataset.qilingSearchHistorySaved;
                        }
                    }, 1500);
                    saveSearchTerm(term);
                });
            });

            document.addEventListener('click', function (event) {
                if (!activePanel) {
                    return;
                }

                if (event.target && event.target.closest && event.target.closest('.qiling-search-suggestions, form[role="search"], form.search-form, .qiling-search-enhanced')) {
                    return;
                }

                hidePanel();
            });

            if (searchEnhance.currentQuery) {
                saveSearchTerm(searchEnhance.currentQuery);
            } else {
                renderHistoryBlocks();
            }
        })();
    });
})(window, document);
