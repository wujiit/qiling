(function() {
    'use strict';

    var instances = [];
    var readyClassAdded = false;

    function toInt(value, fallback) {
        var parsed = parseInt(value, 10);
        return Number.isFinite(parsed) ? parsed : fallback;
    }

    function getText(key, fallback) {
        var i18n = window.qilingInfiniteScrollI18n || {};
        return i18n[key] || fallback;
    }

    function setControlState(instance, state) {
        if (!instance.control) {
            return;
        }

        instance.control.classList.remove('is-loading', 'is-done', 'is-error');
        if (state) {
            instance.control.classList.add('is-' + state);
        }

        if (instance.button) {
            instance.button.disabled = state === 'loading';
            instance.button.textContent = state === 'error'
                ? getText('retry', '重试加载')
                : getText('loadMore', '加载更多');
        }
    }

    function hasMore(instance) {
        return instance.currentPage < instance.maxPages;
    }

    function updateDoneState(instance) {
        if (!hasMore(instance)) {
            instance.done = true;
            setControlState(instance, 'done');
        } else if (!instance.loading) {
            instance.done = false;
            setControlState(instance, '');
        }
    }

    function appendHtml(instance, html) {
        if (!instance.container || !html) {
            return 0;
        }

        var before = instance.container.children.length;
        instance.container.insertAdjacentHTML('beforeend', html);
        return Math.max(0, instance.container.children.length - before);
    }

    function getNextUrlFromDocument(doc, paginationSelector, scopeRoot) {
        var searchRoot = scopeRoot || doc;
        var scope = paginationSelector ? searchRoot.querySelector(paginationSelector) : null;
        var next = scope ? scope.querySelector('a.next, a.next.page-numbers') : null;
        if (!next) {
            next = searchRoot.querySelector('a.next, a.next.page-numbers');
        }

        return next && next.href ? next.href : '';
    }

    function refreshAnimations(root) {
        if (window.AOS && typeof window.AOS.refreshHard === 'function') {
            window.AOS.refreshHard();
        } else if (window.AOS && typeof window.AOS.refresh === 'function') {
            window.AOS.refresh();
        }

        document.dispatchEvent(new CustomEvent('qiling:infinite-scroll:loaded', {
            detail: {
                root: root
            }
        }));
    }

    function loadByUrl(instance) {
        if (!instance.nextUrl) {
            updateDoneState(instance);
            return Promise.resolve();
        }

        return fetch(instance.nextUrl, {
            method: 'GET',
            credentials: 'same-origin'
        })
            .then(function(response) {
                if (!response.ok) {
                    throw new Error('request_failed');
                }
                return response.text();
            })
            .then(function(html) {
                var doc = new DOMParser().parseFromString(html, 'text/html');
                var nextRoot = doc.querySelector('[data-qiling-infinite-scroll="1"][data-context="' + instance.context + '"]') || doc;
                var nextContainer = nextRoot.querySelector(instance.containerSelector);
                if (!nextContainer) {
                    throw new Error('missing_container');
                }

                var added = appendHtml(instance, nextContainer.innerHTML);
                instance.currentPage += 1;
                instance.nextUrl = getNextUrlFromDocument(doc, instance.paginationSelector, nextRoot);
                if (instance.currentPage >= instance.maxPages || !instance.nextUrl || added === 0) {
                    instance.currentPage = Math.max(instance.currentPage, instance.maxPages);
                }
            });
    }

    function getAdvancedFilterState() {
        var state = window.qilingCategoryAdvancedFilterState || {};
        return {
            filters: state.filters && typeof state.filters === 'object' ? state.filters : {},
            sort: state.sort || 'latest'
        };
    }

    function loadByAdvancedFilter(instance) {
        var nextPage = instance.currentPage + 1;
        var filterState = getAdvancedFilterState();
        var formData = new FormData();

        formData.append('action', 'ds_adv_category_filter');
        formData.append('category_id', instance.root.getAttribute('data-category-id') || '');
        formData.append('sort', filterState.sort || 'latest');
        formData.append('paged', String(nextPage));
        formData.append('nonce', instance.root.getAttribute('data-nonce') || '');

        Object.keys(filterState.filters).forEach(function(key) {
            formData.append('filters[' + key + ']', filterState.filters[key]);
        });

        return fetch(instance.ajaxUrl, {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        })
            .then(function(response) {
                if (!response.ok) {
                    throw new Error('request_failed');
                }
                return response.json();
            })
            .then(function(payload) {
                if (!payload || !payload.success || !payload.data) {
                    throw new Error('invalid_response');
                }

                var added = appendHtml(instance, payload.data.html || '');
                instance.currentPage = toInt(payload.data.current_page, nextPage);
                instance.maxPages = toInt(payload.data.max_num_pages, instance.maxPages);

                if (!payload.data.has_more || added === 0) {
                    instance.currentPage = Math.max(instance.currentPage, instance.maxPages);
                }
            });
    }

    function loadNext(instance) {
        if (!instance || instance.loading || instance.done || !hasMore(instance)) {
            updateDoneState(instance);
            return;
        }

        instance.loading = true;
        setControlState(instance, 'loading');

        var request = instance.mode === 'advanced-filter'
            ? loadByAdvancedFilter(instance)
            : loadByUrl(instance);

        request
            .then(function() {
                updateDoneState(instance);
                refreshAnimations(instance.root);
            })
            .catch(function() {
                instance.done = false;
                setControlState(instance, 'error');
            })
            .finally(function() {
                instance.loading = false;
            });
    }

    function createObserver(instance) {
        if (!('IntersectionObserver' in window) || !instance.control) {
            return null;
        }

        return new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    loadNext(instance);
                }
            });
        }, {
            rootMargin: '360px 0px'
        });
    }

    function refreshInstance(instance, options) {
        options = options || {};
        instance.currentPage = toInt(options.currentPage, instance.currentPage);
        instance.maxPages = toInt(options.maxPages, instance.maxPages);
        instance.nextUrl = typeof options.nextUrl === 'string' ? options.nextUrl : instance.nextUrl;
        instance.loading = false;
        instance.done = false;
        updateDoneState(instance);
    }

    function initRoot(root) {
        var containerSelector = root.getAttribute('data-item-container') || '';
        var container = containerSelector ? root.querySelector(containerSelector) : null;
        var control = root.querySelector('[data-qiling-infinite-control]');

        if (!container || !control) {
            return;
        }

        var instance = {
            root: root,
            container: container,
            containerSelector: containerSelector,
            paginationSelector: root.getAttribute('data-pagination') || '',
            control: control,
            button: control.querySelector('[data-qiling-infinite-load-more]'),
            currentPage: toInt(root.getAttribute('data-current-page'), 1),
            maxPages: toInt(root.getAttribute('data-max-pages'), 1),
            nextUrl: root.getAttribute('data-next-url') || '',
            ajaxUrl: root.getAttribute('data-ajax-url') || '',
            context: root.getAttribute('data-context') || 'archive',
            mode: root.getAttribute('data-adv-filter') === '1' ? 'advanced-filter' : 'url',
            loading: false,
            done: false,
            observer: null
        };

        if (!readyClassAdded) {
            document.documentElement.classList.add('qiling-infinite-ready');
            readyClassAdded = true;
        }

        root.__qilingInfiniteScroll = instance;
        instances.push(instance);

        if (instance.button) {
            instance.button.addEventListener('click', function() {
                loadNext(instance);
            });
        }

        instance.observer = createObserver(instance);
        if (instance.observer) {
            instance.observer.observe(control);
        }

        updateDoneState(instance);
    }

    function init() {
        document.querySelectorAll('[data-qiling-infinite-scroll="1"]').forEach(initRoot);
    }

    window.QilingInfiniteScroll = {
        refresh: function(root, options) {
            var target = root && root.__qilingInfiniteScroll ? root.__qilingInfiniteScroll : null;
            if (target) {
                refreshInstance(target, options || {});
            }
        },
        loadNext: function(root) {
            var target = root && root.__qilingInfiniteScroll ? root.__qilingInfiniteScroll : null;
            if (target) {
                loadNext(target);
            }
        },
        instances: instances
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
