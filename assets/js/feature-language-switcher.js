/**
 * Language switcher runtime
 *
 * Split from main.js so page-specific interactions can load only when needed.
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
    // ===== Language Switcher Modal =====
    var translateConfig = (typeof developerStarterData !== 'undefined' && developerStarterData.translate)
        ? developerStarterData.translate
        : {};
    var languageSwitcherConfig = (typeof developerStarterData !== 'undefined' && developerStarterData.languageSwitcher)
        ? developerStarterData.languageSwitcher
        : {};
    var translateLoaderPromise = null;

    function isMultilingualContentMode() {
        return (languageSwitcherConfig.mode || translateConfig.mode || '') === 'multilingual_content';
    }

    function getLanguageSwitcherCodes() {
        return (languageSwitcherConfig.languages && languageSwitcherConfig.languages.length)
            ? languageSwitcherConfig.languages
            : [];
    }

    function getLanguageSwitcherHomePrefix() {
        var homePath = String(languageSwitcherConfig.homePath || '').replace(/^\/+|\/+$/g, '');
        return homePath ? ('/' + homePath) : '';
    }

    function getLanguageSwitcherCookieValue(name) {
        var cookieName = String(name || '');
        if (!cookieName) {
            return '';
        }

        var escapedName = cookieName.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        var matches = document.cookie.match(new RegExp('(?:^|;\\s*)' + escapedName + '=([^;]*)'));

        return matches && typeof matches[1] !== 'undefined'
            ? decodeURIComponent(matches[1])
            : '';
    }

    function isLanguageSwitcherCookieVersionMatched() {
        var versionName = String(languageSwitcherConfig.cookieVersionName || '');
        var expectedVersion = String(languageSwitcherConfig.cookieVersion || '');
        if (!versionName || !expectedVersion) {
            return false;
        }

        var savedVersion = getLanguageSwitcherCookieValue(versionName);
        if (!savedVersion) {
            return false;
        }

        return savedVersion === expectedVersion;
    }

    function getCurrentFrontendLang() {
        var langCodes = getLanguageSwitcherCodes();
        var path = window.location.pathname || '/';
        var homePrefix = getLanguageSwitcherHomePrefix();
        if (homePrefix && path.indexOf(homePrefix) === 0) {
            path = path.slice(homePrefix.length) || '/';
        }

        path = path.replace(/^\/+/, '');
        var segments = path.split('/').filter(function (seg) { return seg !== ''; });
        if (segments.length && langCodes.indexOf(segments[0]) !== -1) {
            return segments[0];
        }

        if (isLanguageSwitcherCookieVersionMatched()) {
            var cookieLang = getLanguageSwitcherCookieValue(languageSwitcherConfig.cookieName || 'developer_starter_front_lang');
            if (cookieLang && langCodes.indexOf(cookieLang) !== -1) {
                return cookieLang;
            }
        }

        var currentLang = languageSwitcherConfig.currentLang || '';
        if (currentLang && langCodes.indexOf(currentLang) !== -1) {
            return currentLang;
        }

        return languageSwitcherConfig.defaultLang || '';
    }

    function isDocumentRelativeUrl(rawUrl) {
        var value = String(rawUrl || '').trim();
        if (!value) {
            return false;
        }

        if (/^[a-z][a-z0-9+.-]*:/i.test(value) || value.indexOf('//') === 0) {
            return false;
        }

        return value.charAt(0) !== '/';
    }

    function shouldSkipMultilingualValue(rawUrl) {
        if (!rawUrl) {
            return true;
        }

        if (isDocumentRelativeUrl(rawUrl)) {
            return true;
        }

        return /^(#|mailto:|tel:|javascript:)/i.test(rawUrl);
    }

    function shouldSkipMultilingualPath(path) {
        var normalized = String(path || '').replace(/^\/+/, '');
        if (!normalized) {
            return false;
        }

        return /^(wp-admin\/|wp-login\.php|wp-json(?:\/|$)|xmlrpc\.php|wp-content\/|wp-includes\/)/i.test(normalized);
    }

    function shouldSkipMultilingualElement(el) {
        if (!el) {
            return true;
        }

        if (
            el.getAttribute('data-no-lang-rewrite') === '1'
            || el.hasAttribute('data-switch-url')
            || el.hasAttribute('data-lang')
            || el.hasAttribute('hreflang')
        ) {
            return true;
        }

        var className = el.className || '';
        if (typeof className === 'string' && /\btranslate-lang-item\b|\bxb-aifanyi-language-switcher\b|\bxb-aifanyi-language-switcher__link\b/.test(className)) {
            return true;
        }

        return false;
    }

    function buildCurrentLanguageUrl(rawUrl) {
        if (!isMultilingualContentMode() || shouldSkipMultilingualValue(rawUrl)) {
            return rawUrl;
        }

        var langCodes = getLanguageSwitcherCodes();
        var defaultLang = languageSwitcherConfig.defaultLang || '';
        var currentLang = getCurrentFrontendLang();
        if (!langCodes.length || !defaultLang || !currentLang) {
            return rawUrl;
        }

        var parsedUrl;
        try {
            parsedUrl = new URL(rawUrl, window.location.origin);
        } catch (err) {
            return rawUrl;
        }

        if (parsedUrl.origin !== window.location.origin) {
            return rawUrl;
        }

        var homePrefix = getLanguageSwitcherHomePrefix();
        var pathname = parsedUrl.pathname || '/';
        if (homePrefix) {
            if (pathname === homePrefix) {
                pathname = '/';
            } else if (pathname.indexOf(homePrefix + '/') === 0) {
                pathname = pathname.slice(homePrefix.length) || '/';
            } else {
                return rawUrl;
            }
        }

        if (shouldSkipMultilingualPath(pathname)) {
            return rawUrl;
        }

        var hadTrailingSlash = /\/$/.test(pathname) || pathname === '/';
        var cleanPath = pathname.replace(/^\/+|\/+$/g, '');
        var segments = cleanPath ? cleanPath.split('/').filter(function (seg) { return seg !== ''; }) : [];
        if (segments.length && langCodes.indexOf(segments[0]) !== -1) {
            segments.shift();
        }

        var relativePath = segments.join('/');
        var targetPath = relativePath;
        if (currentLang !== defaultLang) {
            targetPath = currentLang + (relativePath ? ('/' + relativePath) : '');
        }

        var finalPath = homePrefix + (targetPath ? ('/' + targetPath) : '/');
        if (hadTrailingSlash && finalPath.slice(-1) !== '/') {
            finalPath += '/';
        }
        if (!hadTrailingSlash && targetPath && /\/$/.test(finalPath)) {
            finalPath = finalPath.replace(/\/+$/, '');
        }

        parsedUrl.pathname = finalPath || '/';
        parsedUrl.searchParams.delete('xb_lang');

        return parsedUrl.toString();
    }

    function rewriteCurrentLanguageUrlForElement(el) {
        if (!el || shouldSkipMultilingualElement(el)) {
            return;
        }

        var attrName = el.tagName === 'FORM' ? 'action' : 'href';
        var rawUrl = el.getAttribute(attrName);
        var translatedUrl = buildCurrentLanguageUrl(rawUrl);

        if (translatedUrl && translatedUrl !== rawUrl) {
            el.setAttribute(attrName, translatedUrl);
        }
    }

    function rewriteCurrentLanguageUrls(root) {
        if (!isMultilingualContentMode() || !root) {
            return;
        }

        if (root.nodeType === 1) {
            if (root.matches && (root.matches('a[href]') || root.matches('form[action]'))) {
                rewriteCurrentLanguageUrlForElement(root);
            }

            if (root.querySelectorAll) {
                root.querySelectorAll('a[href], form[action]').forEach(rewriteCurrentLanguageUrlForElement);
            }
            return;
        }

        if (root.querySelectorAll) {
            root.querySelectorAll('a[href], form[action]').forEach(rewriteCurrentLanguageUrlForElement);
        }
    }

    if (isMultilingualContentMode() && 'MutationObserver' in window) {
        // 首屏 HTML 已由服务端统一改写，这里只兜底处理后续动态插入的链接节点。
        var multilingualObserver = new MutationObserver(function (mutations) {
            mutations.forEach(function (mutation) {
                Array.prototype.forEach.call(mutation.addedNodes || [], function (node) {
                    if (node && node.nodeType === 1) {
                        rewriteCurrentLanguageUrls(node);
                    }
                });
            });
        });

        multilingualObserver.observe(document.body || document.documentElement, {
            childList: true,
            subtree: true
        });
    }

    function initTranslateRuntime() {
        if (typeof translate === 'undefined') {
            return false;
        }

        if (!window.__dsTranslateInitialized) {
            try {
                if (translate.language && typeof translate.language.setLocal === 'function') {
                    translate.language.setLocal(translateConfig.local || 'chinese_simplified');
                }
                if (translate.service && typeof translate.service.use === 'function') {
                    translate.service.use('client.edge');
                }
                if (translate.listener && typeof translate.listener.start === 'function') {
                    translate.listener.start();
                }
                if (translate.selectLanguageTag) {
                    translate.selectLanguageTag.show = false;
                }
                window.__dsTranslateInitialized = true;
            } catch (err) {
                return false;
            }
        }

        return true;
    }

    function ensureTranslateReady() {
        if (!translateConfig || !translateConfig.enabled || translateConfig.mode !== 'translate_js') {
            return Promise.resolve(false);
        }

        if (initTranslateRuntime()) {
            return Promise.resolve(true);
        }

        if (translateLoaderPromise) {
            return translateLoaderPromise;
        }

        var scriptUrl = translateConfig.scriptUrl || '';
        if (!scriptUrl) {
            return Promise.resolve(false);
        }

        translateLoaderPromise = new Promise(function (resolve) {
            var existing = document.querySelector('script[data-ds-translate-script="1"]');
            if (!existing) {
                var scriptNodes = document.querySelectorAll('script[src]');
                for (var i = 0; i < scriptNodes.length; i++) {
                    if ((scriptNodes[i].getAttribute('src') || '') === scriptUrl) {
                        existing = scriptNodes[i];
                        break;
                    }
                }
            }
            if (existing) {
                if (initTranslateRuntime()) {
                    resolve(true);
                    return;
                }
                existing.addEventListener('load', function () {
                    resolve(initTranslateRuntime());
                }, { once: true });
                existing.addEventListener('error', function () {
                    resolve(false);
                }, { once: true });
                return;
            }

            var script = document.createElement('script');
            script.src = scriptUrl;
            script.defer = true;
            script.setAttribute('data-ds-translate-script', '1');
            script.onload = function () {
                resolve(initTranslateRuntime());
            };
            script.onerror = function () {
                resolve(false);
            };
            document.head.appendChild(script);
        }).then(function (ready) {
            translateLoaderPromise = null;
            return ready;
        });

        return translateLoaderPromise;
    }

    function openTranslateModal() {
        var modal = document.getElementById('translate-modal');
        var overlay = document.getElementById('translate-modal-overlay');
        if (modal && overlay) {
            modal.classList.add('show');
            overlay.classList.add('show');
            document.body.style.overflow = 'hidden';
        }
    }

    function closeTranslateModal() {
        var modal = document.getElementById('translate-modal');
        var overlay = document.getElementById('translate-modal-overlay');
        if (modal && overlay) {
            modal.classList.remove('show');
            overlay.classList.remove('show');
            document.body.style.overflow = '';
        }
    }

    // Toggle button click - use event delegation
    document.addEventListener('click', function (e) {
        // Open modal
        var toggleBtn = e.target.closest('#translate-toggle');
        if (toggleBtn) {
            var currentMode = translateConfig.mode || languageSwitcherConfig.mode || '';
            if (!currentMode) {
                return;
            }
            e.stopPropagation();
            openTranslateModal();
            if ((languageSwitcherConfig.mode || translateConfig.mode) === 'translate_js') {
                ensureTranslateReady();
            }
            return;
        }

        // Close button
        if (e.target.closest('#translate-modal-close')) {
            closeTranslateModal();
            return;
        }

        // Overlay click
        if (e.target.id === 'translate-modal-overlay') {
            closeTranslateModal();
            return;
        }

        // Language item click
        var langItem = e.target.closest('.translate-lang-item');
        if (langItem) {
            var targetUrl = langItem.getAttribute('href');
            var switchMode = langItem.getAttribute('data-mode') || languageSwitcherConfig.mode || translateConfig.mode || '';
            var targetLang = langItem.getAttribute('data-lang');
            var langCookieName = languageSwitcherConfig.cookieName || 'developer_starter_front_lang';
            var cookiePath = (languageSwitcherConfig.homePath ? ('/' + languageSwitcherConfig.homePath.replace(/^\/+|\/+$/g, '')) : '/') || '/';

            if (switchMode === 'multilingual_content' && (!targetUrl || targetUrl === '#') && targetLang) {
                var langCodes = (languageSwitcherConfig.languages && languageSwitcherConfig.languages.length)
                    ? languageSwitcherConfig.languages
                    : [];
                var defaultLang = languageSwitcherConfig.defaultLang || '';
                var homePath = languageSwitcherConfig.homePath || '';

                if (langCodes.length && defaultLang) {
                    var path = window.location.pathname || '/';
                    var homePrefix = homePath ? ('/' + homePath) : '';

                    if (homePrefix && path.indexOf(homePrefix) === 0) {
                        path = path.slice(homePrefix.length);
                        if (path === '') {
                            path = '/';
                        }
                    }

                    path = path.replace(/^\/+/, '');
                    var segments = path.split('/').filter(function (seg) { return seg !== ''; });
                    if (segments.length && langCodes.indexOf(segments[0]) !== -1) {
                        segments.shift();
                    }

                    var relPath = segments.join('/');
                    var prefixed = relPath ? ('/' + relPath) : '';
                    if (targetLang !== defaultLang) {
                        prefixed = '/' + targetLang + prefixed;
                    }

                    targetUrl = (homePrefix || '') + (prefixed || '/');
                    if (targetUrl === '') {
                        targetUrl = '/';
                    }

                    targetUrl = targetUrl + window.location.search + window.location.hash;
                }
            }

            if (targetUrl && targetUrl !== '#') {
                if (switchMode === 'multilingual_content' && targetLang) {
                    document.cookie = langCookieName + '=' + encodeURIComponent(targetLang)
                        + '; path=' + cookiePath
                        + '; max-age=31536000; SameSite=Lax'
                        + (window.location.protocol === 'https:' ? '; Secure' : '');
                    var langVersionCookieName = languageSwitcherConfig.cookieVersionName || 'developer_starter_front_lang_ver';
                    var langVersionCookieValue = languageSwitcherConfig.cookieVersion || '';
                    if (langVersionCookieValue) {
                        document.cookie = langVersionCookieName + '=' + encodeURIComponent(langVersionCookieValue)
                            + '; path=' + cookiePath
                            + '; max-age=31536000; SameSite=Lax'
                            + (window.location.protocol === 'https:' ? '; Secure' : '');
                    }
                }
                e.preventDefault();
                closeTranslateModal();
                window.location.href = targetUrl;
                return;
            }

            if (switchMode === 'multilingual_content') {
                e.preventDefault();
                closeTranslateModal();
                return;
            }

            e.preventDefault();
            var lang = langItem.getAttribute('data-lang');
            ensureTranslateReady().then(function () {
                if (typeof translate !== 'undefined' && typeof translate.changeLanguage === 'function') {
                    translate.changeLanguage(lang);
                }

                // Update active state
                document.querySelectorAll('.translate-lang-item').forEach(function (opt) {
                    opt.classList.remove('active');
                });
                langItem.classList.add('active');

                // Close modal
                closeTranslateModal();
            });
            return;
        }
    });

    // ESC key to close modal
    document.addEventListener('keydown', function (e) {
        var modal = document.getElementById('translate-modal');
        if (e.key === 'Escape' && modal && modal.classList.contains('show')) {
            closeTranslateModal();
        }
    });
    });
})(window, document);