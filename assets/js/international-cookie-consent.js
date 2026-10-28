(function () {
    'use strict';

    var config = window.qilingInternationalConsent || {};
    var cookieName = config.cookieName || 'qiling_international_cookie_consent';
    var consentVersion = config.version || '2.0';
    var acceptedValue = config.accepted || 'accepted';
    var rejectedValue = config.rejected || 'rejected';
    var categoryConfig = config.categories || {};
    var categoryKeys = Object.keys(categoryConfig);
    var defaultOptionalConsent = !!config.defaultOptionalConsent;
    var maxAgeDays = parseInt(config.maxAgeDays, 10);
    if (!maxAgeDays || maxAgeDays < 1) {
        maxAgeDays = 180;
    }
    if (categoryKeys.indexOf('necessary') === -1) {
        categoryKeys.unshift('necessary');
    }

    function getCookie(name) {
        var pairs = document.cookie ? document.cookie.split(';') : [];
        var prefix = name + '=';
        for (var i = 0; i < pairs.length; i += 1) {
            var item = pairs[i].trim();
            if (item.indexOf(prefix) === 0) {
                return decodeURIComponent(item.slice(prefix.length));
            }
        }
        return '';
    }

    function setCookie(name, value) {
        var maxAge = maxAgeDays * 24 * 60 * 60;
        var cookie = name + '=' + encodeURIComponent(value) + '; path=/; max-age=' + maxAge + '; SameSite=Lax';
        if (window.location.protocol === 'https:') {
            cookie += '; Secure';
        }
        document.cookie = cookie;
    }

    function buildCategories(allowOptional) {
        var categories = {};
        categoryKeys.forEach(function (category) {
            categories[category] = category === 'necessary' ? true : !!allowOptional;
        });
        return categories;
    }

    function normalizeConsent(payload) {
        var categories = {};
        if (!payload || typeof payload !== 'object') {
            return null;
        }
        if (payload.version && payload.version !== consentVersion) {
            return null;
        }
        categoryKeys.forEach(function (category) {
            categories[category] = category === 'necessary' ? true : !!(payload.categories && payload.categories[category]);
        });
        return {
            version: payload.version || consentVersion,
            timestamp: payload.timestamp || new Date().toISOString(),
            categories: categories
        };
    }

    function parseConsent(value) {
        var parsed;
        if (!value) {
            return null;
        }
        if (value === acceptedValue) {
            return normalizeConsent({
                version: consentVersion,
                timestamp: '',
                categories: buildCategories(true)
            });
        }
        if (value === rejectedValue) {
            return normalizeConsent({
                version: consentVersion,
                timestamp: '',
                categories: buildCategories(false)
            });
        }
        try {
            parsed = JSON.parse(value);
        } catch (error) {
            return null;
        }
        return normalizeConsent(parsed);
    }

    function makeConsent(categories) {
        return normalizeConsent({
            version: consentVersion,
            timestamp: new Date().toISOString(),
            categories: categories
        });
    }

    function saveConsent(consent) {
        setCookie(cookieName, JSON.stringify(consent));
    }

    function getConsent() {
        return parseConsent(getCookie(cookieName));
    }

    function isCategoryAllowed(category, consent) {
        category = category || 'custom';
        if (category === 'necessary') {
            return true;
        }
        return !!(consent && consent.categories && consent.categories[category]);
    }

    function decodeSnippet(encoded) {
        try {
            return decodeURIComponent(escape(window.atob(encoded)));
        } catch (error) {
            try {
                return window.atob(encoded);
            } catch (ignored) {
                return '';
            }
        }
    }

    function cloneScript(node) {
        var script = document.createElement('script');
        for (var i = 0; i < node.attributes.length; i += 1) {
            var attr = node.attributes[i];
            script.setAttribute(attr.name, attr.value);
        }
        script.text = node.text || node.textContent || node.innerHTML || '';
        return script;
    }

    function appendNode(target, node) {
        if (node.nodeType !== 1) {
            target.appendChild(node.cloneNode(true));
            return;
        }

        if (node.tagName && node.tagName.toLowerCase() === 'script') {
            target.appendChild(cloneScript(node));
            return;
        }

        target.appendChild(node.cloneNode(true));
    }

    function injectSnippet(code, position) {
        var target = position === 'head' ? document.head : document.body;
        if (!target || !code) {
            return;
        }

        if (code.toLowerCase().indexOf('<script') === -1 && code.indexOf('<') === -1) {
            var inlineScript = document.createElement('script');
            inlineScript.text = code;
            target.appendChild(inlineScript);
            return;
        }

        var container = document.createElement('div');
        container.innerHTML = code;
        var children = Array.prototype.slice.call(container.childNodes);
        children.forEach(function (child) {
            appendNode(target, child);
        });
    }

    function loadDeferredCodes(consent) {
        var templates = document.querySelectorAll('script[type="application/json"][data-qiling-international-code]');
        Array.prototype.forEach.call(templates, function (template) {
            var category = template.getAttribute('data-category') || 'custom';
            if (template.getAttribute('data-loaded') === '1') {
                return;
            }
            if (!isCategoryAllowed(category, consent)) {
                return;
            }
            var code = decodeSnippet((template.textContent || '').trim());
            var position = template.getAttribute('data-position') === 'head' ? 'head' : 'footer';
            template.setAttribute('data-loaded', '1');
            injectSnippet(code, position);
            if (template.parentNode) {
                template.parentNode.removeChild(template);
            }
        });
    }

    function removeDeferredCodes(consent) {
        void consent;
        var templates = document.querySelectorAll('script[type="application/json"][data-qiling-international-code]');
        Array.prototype.forEach.call(templates, function (template) {
            if (template.getAttribute('data-loaded') === '1' && template.parentNode) {
                template.parentNode.removeChild(template);
            }
        });
    }

    function hideNotice(notice) {
        if (!notice) {
            return;
        }
        notice.setAttribute('data-hidden', '1');
    }

    function showNotice(notice) {
        if (!notice) {
            return;
        }
        notice.removeAttribute('data-hidden');
    }

    function setSettingsVisible(notice, visible) {
        var panel = notice ? notice.querySelector('[data-qiling-cookie-settings]') : null;
        if (!panel) {
            return;
        }
        panel.hidden = !visible;
        notice.setAttribute('data-settings-open', visible ? '1' : '0');
    }

    function syncInputs(notice, consent) {
        var inputs = notice ? notice.querySelectorAll('[data-qiling-cookie-category]') : [];
        var normalized = consent || makeConsent(buildCategories(defaultOptionalConsent));
        Array.prototype.forEach.call(inputs, function (input) {
            var category = input.getAttribute('data-qiling-cookie-category') || 'custom';
            input.checked = isCategoryAllowed(category, normalized);
            if (category === 'necessary') {
                input.checked = true;
                input.disabled = true;
            }
        });
    }

    function readSettings(notice) {
        var categories = buildCategories(false);
        var inputs = notice ? notice.querySelectorAll('[data-qiling-cookie-category]') : [];
        Array.prototype.forEach.call(inputs, function (input) {
            var category = input.getAttribute('data-qiling-cookie-category') || 'custom';
            categories[category] = category === 'necessary' ? true : !!input.checked;
        });
        return makeConsent(categories);
    }

    function applyConsent(consent) {
        saveConsent(consent);
        loadDeferredCodes(consent);
        removeDeferredCodes(consent);
    }

    function bindNotice(notice) {
        if (!notice || notice.getAttribute('data-cookie-bound') === '1') {
            return;
        }
        notice.setAttribute('data-cookie-bound', '1');

        var accept = notice.querySelector('[data-qiling-cookie-accept]');
        var reject = notice.querySelector('[data-qiling-cookie-reject]');
        var customize = notice.querySelector('[data-qiling-cookie-customize]');
        var save = notice.querySelector('[data-qiling-cookie-save]');

        syncInputs(notice, getConsent());

        if (accept) {
            accept.addEventListener('click', function () {
                var consent = makeConsent(buildCategories(true));
                applyConsent(consent);
                hideNotice(notice);
            });
        }

        if (reject) {
            reject.addEventListener('click', function () {
                var consent = makeConsent(buildCategories(false));
                applyConsent(consent);
                hideNotice(notice);
            });
        }

        if (customize) {
            customize.addEventListener('click', function () {
                syncInputs(notice, getConsent());
                setSettingsVisible(notice, notice.getAttribute('data-settings-open') !== '1');
            });
        }

        if (save) {
            save.addEventListener('click', function () {
                var consent = readSettings(notice);
                applyConsent(consent);
                hideNotice(notice);
            });
        }
    }

    function boot() {
        var consent = getConsent();
        var notice = document.querySelector('[data-qiling-cookie-consent]');
        if (notice) {
            bindNotice(notice);
        }

        if (consent) {
            loadDeferredCodes(consent);
            removeDeferredCodes(consent);
            hideNotice(notice);
            return;
        }

        if (!notice) {
            return;
        }

        showNotice(notice);
    }

    window.qilingOpenCookieSettings = function () {
        var notice = document.querySelector('[data-qiling-cookie-consent]');
        if (!notice) {
            return false;
        }
        syncInputs(notice, getConsent());
        showNotice(notice);
        setSettingsVisible(notice, true);
        return true;
    };

    window.addEventListener('qiling:openCookieSettings', function () {
        window.qilingOpenCookieSettings();
    });

    document.addEventListener('click', function (event) {
        var target = event.target && event.target.closest ? event.target.closest('[data-qiling-open-cookie-settings]') : null;
        if (!target) {
            return;
        }
        event.preventDefault();
        window.qilingOpenCookieSettings();
    });

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})();
