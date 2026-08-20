/**
 * 启灵主题前端主脚本
 *
 * 负责站点通用交互、按需脚本加载与基础动效初始化。
 */

(function (window, document) {
    'use strict';

    function getGlobalData() {
        return window.developerStarterData || {};
    }

    function loadScriptOnce(url, marker) {
        return new Promise(function (resolve, reject) {
            if (!url) {
                resolve(false);
                return;
            }

            var existing = null;
            if (marker) {
                existing = document.querySelector('script[' + marker + '="1"]');
            }

            if (!existing) {
                var scriptNodes = document.querySelectorAll('script[src]');
                for (var i = 0; i < scriptNodes.length; i++) {
                    if ((scriptNodes[i].getAttribute('src') || '') === url) {
                        existing = scriptNodes[i];
                        break;
                    }
                }
            }

            if (existing) {
                if (
                    existing.getAttribute('data-ds-loaded') === '1'
                    || existing.readyState === 'complete'
                    || existing.readyState === 'loaded'
                ) {
                    resolve(true);
                    return;
                }
                var settled = false;
                var onLoaded = function () {
                    if (settled) {
                        return;
                    }
                    settled = true;
                    existing.setAttribute('data-ds-loaded', '1');
                    resolve(true);
                };
                var onError = function () {
                    if (settled) {
                        return;
                    }
                    settled = true;
                    reject(new Error('script load failed'));
                };

                existing.addEventListener('load', onLoaded, { once: true });
                existing.addEventListener('error', onError, { once: true });

                if (document.readyState !== 'loading') {
                    setTimeout(onLoaded, 0);
                }
                return;
            }

            var script = document.createElement('script');
            script.src = url;
            script.defer = true;
            if (marker) {
                script.setAttribute(marker, '1');
            }
            script.onload = function () {
                script.setAttribute('data-ds-loaded', '1');
                resolve(true);
            };
            script.onerror = function () {
                reject(new Error('script load failed'));
            };
            document.head.appendChild(script);
        });
    }

    window.DSLoadScriptOnce = function (url, marker) {
        return loadScriptOnce(url, marker);
    };

    if (typeof window.DSLoadCaptchaProvider !== 'function') {
        window.DSLoadCaptchaProvider = function (scene) {
            if (window.DSProviderCaptcha) {
                return Promise.resolve(true);
            }

            var globalData = getGlobalData();
            var captchaData = globalData.captcha || {};
            var provider = String(captchaData.provider || 'theme');
            if (provider !== 'aliyun') {
                return Promise.resolve(false);
            }

            var scriptUrl = String(globalData.captchaProviderScript || '');
            if (!scriptUrl) {
                return Promise.resolve(false);
            }

            if (window.__dsCaptchaProviderPromise) {
                return window.__dsCaptchaProviderPromise;
            }

            window.__dsCaptchaProviderLastScene = String(scene || '');
            window.__dsCaptchaProviderPromise = loadScriptOnce(scriptUrl, 'data-ds-captcha-provider-script')
                .then(function () {
                    if (window.DSProviderCaptcha) {
                        return true;
                    }
                    window.__dsCaptchaProviderPromise = null;
                    return false;
                })
                .catch(function () {
                    window.__dsCaptchaProviderPromise = null;
                    return false;
                });

            return window.__dsCaptchaProviderPromise;
        };
    }
})(window, document);

document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    function getGlobalData() {
        return window.developerStarterData || {};
    }

    var mobileMenuConfig = (typeof developerStarterData !== 'undefined' && developerStarterData.mobileMenu)
        ? developerStarterData.mobileMenu
        : {};
    var mobileMenuEnabled = !!mobileMenuConfig.enabled;
    var mobileMenuScript = String(mobileMenuConfig.script || '');
    var mobileMenuBreakpoint = parseInt(mobileMenuConfig.breakpoint, 10);
    var mobileMenuLoaded = !!window.__dsMobileMenuInitialized;
    var mobileMenuLoadingPromise = null;

    if (!mobileMenuBreakpoint || mobileMenuBreakpoint < 1) {
        mobileMenuBreakpoint = 992;
    }

    function hasMobileMenuTargets() {
        return !!(
            document.querySelector('.mobile-menu-toggle')
            || document.getElementById('mobile-menu')
            || document.querySelector('.mobile-bottom-menu')
        );
    }

    function shouldLoadMobileMenu() {
        if (!mobileMenuEnabled || !hasMobileMenuTargets()) {
            return false;
        }

        if (window.matchMedia) {
            return window.matchMedia('(max-width: ' + mobileMenuBreakpoint + 'px)').matches;
        }

        return window.innerWidth <= mobileMenuBreakpoint;
    }

    function loadMobileMenuScript() {
        if (!mobileMenuEnabled || mobileMenuLoaded || !mobileMenuScript) {
            return Promise.resolve(mobileMenuLoaded);
        }

        if (mobileMenuLoadingPromise) {
            return mobileMenuLoadingPromise;
        }

        mobileMenuLoadingPromise = window.DSLoadScriptOnce(mobileMenuScript, 'data-ds-mobile-menu-script')
            .then(function (loaded) {
                mobileMenuLoadingPromise = null;
                mobileMenuLoaded = !!loaded;
                return mobileMenuLoaded;
            })
            .catch(function () {
                mobileMenuLoadingPromise = null;
                return false;
            });

        return mobileMenuLoadingPromise;
    }

    if (mobileMenuEnabled && hasMobileMenuTargets()) {
        if (shouldLoadMobileMenu()) {
            loadMobileMenuScript();
        }

        if (window.matchMedia) {
            var mobileMenuViewport = window.matchMedia('(max-width: ' + mobileMenuBreakpoint + 'px)');
            if (typeof mobileMenuViewport.addEventListener === 'function') {
                mobileMenuViewport.addEventListener('change', function (event) {
                    if (event.matches) {
                        loadMobileMenuScript();
                    }
                });
            } else if (typeof mobileMenuViewport.addListener === 'function') {
                mobileMenuViewport.addListener(function (event) {
                    if (event.matches) {
                        loadMobileMenuScript();
                    }
                });
            }
        } else {
            window.addEventListener('resize', function () {
                if (shouldLoadMobileMenu()) {
                    loadMobileMenuScript();
                }
            });
            window.addEventListener('orientationchange', function () {
                if (shouldLoadMobileMenu()) {
                    loadMobileMenuScript();
                }
            });
        }
    }

    // ===== Dark Mode Toggle =====
    var darkModeToggle = document.getElementById('darkmode-toggle');
    var darkModeConfig = window.qilingDarkModeConfig || getGlobalData().darkMode || {};
    if (darkModeToggle || darkModeConfig.enabled) {
        var root = document.documentElement;
        var iconSun = darkModeToggle ? darkModeToggle.querySelector('.icon-sun') : null;
        var iconMoon = darkModeToggle ? darkModeToggle.querySelector('.icon-moon') : null;
        var storageKey = darkModeConfig.storageKey || 'qiling-theme-preference';
        var legacyStorageKey = darkModeConfig.legacyStorageKey || 'theme';
        var scheduleTimer = null;
        var mediaQuery = window.matchMedia ? window.matchMedia('(prefers-color-scheme: dark)') : null;

        function readThemeStorage(key) {
            try {
                return window.localStorage ? localStorage.getItem(key) : null;
            } catch (error) {
                return null;
            }
        }

        function writeThemeStorage(key, value) {
            try {
                if (window.localStorage) {
                    localStorage.setItem(key, value);
                }
            } catch (error) {
                // Storage can be blocked in private modes; the theme still applies for this page.
            }
        }

        function timeToMinutes(value, fallback) {
            var match = /^([01]?\d|2[0-3]):([0-5]\d)$/.exec(String(value || fallback || '00:00'));
            if (!match) {
                return 0;
            }

            return parseInt(match[1], 10) * 60 + parseInt(match[2], 10);
        }

        function scheduleWantsDark() {
            var sunrise = timeToMinutes(darkModeConfig.sunriseTime, '06:00');
            var sunset = timeToMinutes(darkModeConfig.sunsetTime, '18:00');
            var now = new Date();
            var minutes = now.getHours() * 60 + now.getMinutes();

            if (sunrise === sunset) {
                return false;
            }

            return sunset > sunrise
                ? (minutes >= sunset || minutes < sunrise)
                : (minutes >= sunset && minutes < sunrise);
        }

        function systemWantsDark() {
            return !!(mediaQuery && mediaQuery.matches);
        }

        function autoWantsDark() {
            var mode = darkModeConfig.mode || 'system_schedule';

            if (mode === 'system') {
                return systemWantsDark();
            }

            if (mode === 'schedule') {
                return scheduleWantsDark();
            }

            return mediaQuery ? systemWantsDark() : scheduleWantsDark();
        }

        function getPreference() {
            var preference = readThemeStorage(storageKey);
            if (preference === 'dark' || preference === 'light' || preference === 'auto') {
                return preference;
            }

            if (!darkModeConfig.autoEnabled) {
                var legacyTheme = readThemeStorage(legacyStorageKey);
                if (legacyTheme === 'dark' || legacyTheme === 'light') {
                    return legacyTheme;
                }
            }

            return '';
        }

        function resolveTheme() {
            var preference = getPreference();

            if (darkModeConfig.autoEnabled) {
                if (preference === 'dark' || preference === 'light') {
                    return { theme: preference, source: 'manual' };
                }

                return { theme: autoWantsDark() ? 'dark' : 'light', source: 'auto' };
            }

            if (preference === 'dark' || preference === 'light') {
                return { theme: preference, source: 'manual' };
            }

            return { theme: 'light', source: 'default' };
        }

        function updateDarkModeIcons(isDark) {
            if (iconSun) {
                iconSun.style.display = isDark ? 'none' : 'block';
            }

            if (iconMoon) {
                iconMoon.style.display = isDark ? 'block' : 'none';
            }
        }

        function applyTheme(theme, source, persist) {
            var isDark = theme === 'dark';
            var transitionEnabled = !!darkModeConfig.transition;

            if (transitionEnabled) {
                root.classList.add('qiling-theme-transitioning');
            }

            root.classList.toggle('dark-mode', isDark);
            root.classList.toggle('qiling-dark-auto', !!darkModeConfig.autoEnabled);
            root.classList.toggle('qiling-dark-image-dim', !!darkModeConfig.imageDim);
            root.setAttribute('data-theme', isDark ? 'dark' : 'light');
            root.setAttribute('data-theme-source', source || 'default');
            updateDarkModeIcons(isDark);

            if (persist) {
                writeThemeStorage(storageKey, isDark ? 'dark' : 'light');
                writeThemeStorage(legacyStorageKey, isDark ? 'dark' : 'light');
            }

            if (transitionEnabled) {
                window.clearTimeout(applyTheme.transitionTimer);
                applyTheme.transitionTimer = window.setTimeout(function () {
                    root.classList.remove('qiling-theme-transitioning');
                }, 360);
            }
        }

        function refreshAutoTheme() {
            var resolved = resolveTheme();
            applyTheme(resolved.theme, resolved.source, false);
        }

        function hasManualPreference() {
            var preference = getPreference();
            return preference === 'dark' || preference === 'light';
        }

        function scheduleAutoRefresh() {
            if (scheduleTimer) {
                window.clearInterval(scheduleTimer);
                scheduleTimer = null;
            }

            if (!darkModeConfig.autoEnabled || hasManualPreference()) {
                return;
            }

            scheduleTimer = window.setInterval(refreshAutoTheme, 60000);
        }

        refreshAutoTheme();
        scheduleAutoRefresh();

        if (mediaQuery && darkModeConfig.autoEnabled) {
            var mediaListener = function () {
                if (!hasManualPreference()) {
                    refreshAutoTheme();
                }
            };

            if (typeof mediaQuery.addEventListener === 'function') {
                mediaQuery.addEventListener('change', mediaListener);
            } else if (typeof mediaQuery.addListener === 'function') {
                mediaQuery.addListener(mediaListener);
            }
        }

        if (darkModeToggle) {
            darkModeToggle.addEventListener('click', function () {
                var isDark = root.classList.contains('dark-mode');
                applyTheme(isDark ? 'light' : 'dark', 'manual', true);
                scheduleAutoRefresh();
            });
        }
    }

    // ===== Native Scroll Animation (替代 AOS 库) =====
    if ('IntersectionObserver' in window) {
        var aosElements = document.querySelectorAll('[data-aos]');
        if (aosElements.length > 0) {
            var aosObserver = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) {
                        var el = entry.target;
                        var delay = parseInt(el.getAttribute('data-aos-delay')) || 0;

                        setTimeout(function () {
                            el.classList.add('aos-animate');
                        }, delay);

                        aosObserver.unobserve(el);
                    }
                });
            }, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });

            aosElements.forEach(function (el) {
                el.classList.add('aos-init');
                aosObserver.observe(el);
            });
        }
    } else {
        // 不支持 IntersectionObserver 的浏览器，直接显示所有元素
        document.querySelectorAll('[data-aos]').forEach(function (el) {
            el.classList.add('aos-animate');
        });
    }

    // ===== Initialize Swiper =====
    if (typeof Swiper !== 'undefined') {
        var bannerSwipers = document.querySelectorAll('.banner-swiper');
        bannerSwipers.forEach(function (el) {
            if (el.classList.contains('swiper-initialized')) {
                return;
            }
            new Swiper(el, {
                loop: true,
                autoplay: {
                    delay: 5000,
                    disableOnInteraction: false,
                },
                effect: 'fade',
                fadeEffect: { crossFade: true },
                pagination: {
                    el: '.swiper-pagination',
                    clickable: true,
                },
                navigation: {
                    nextEl: '.swiper-button-next',
                    prevEl: '.swiper-button-prev',
                },
            });
        });
    }

    // ===== Search Overlay (Lazy Load) =====
    var searchToggle = document.getElementById('search-toggle');
    var searchOverlayScript = (typeof developerStarterData !== 'undefined' && developerStarterData.searchOverlayScript)
        ? developerStarterData.searchOverlayScript
        : '';
    var searchCaptchaLazyLoad = !!(typeof developerStarterData !== 'undefined' && developerStarterData.searchCaptchaLazyLoad);
    var searchCaptchaAssets = (typeof developerStarterData !== 'undefined' && developerStarterData.searchCaptchaAssets)
        ? developerStarterData.searchCaptchaAssets
        : {};
    var searchScriptLoaded = false;
    var searchScriptLoading = false;
    var searchCaptchaLoaded = !!window.__dsSearchCaptchaInitialized;
    var searchCaptchaLoadingPromise = null;

    var loadScript = function (url, marker) {
        if (typeof window.DSLoadScriptOnce === 'function') {
            return window.DSLoadScriptOnce(url, marker);
        }
        return Promise.resolve(false);
    };

    var loadStyle = function (url, marker) {
        if (!url) {
            return;
        }

        var existing = document.querySelector('link[' + marker + '="1"]');
        if (existing) {
            return;
        }

        var link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = url;
        link.setAttribute(marker, '1');
        document.head.appendChild(link);
    };

    // ===== Search enhance runtime moved to feature-search-enhance.js =====

    var loginModalConfig = (typeof developerStarterData !== 'undefined' && developerStarterData.loginModal)
        ? developerStarterData.loginModal
        : {};
    var loginModalEnabled = !!loginModalConfig.enabled;
    var loginModalEndpoint = String(loginModalConfig.endpoint || '');
    var loginModalStyle = String(loginModalConfig.style || '');
    var loginModalAuthFlowScript = String(loginModalConfig.authFlowScript || '');
    var loginModalScript = String(loginModalConfig.script || '');
    var loginModalFallbackUrl = String(loginModalConfig.fallbackUrl || '');
    var loginModalLoaded = !!document.getElementById('login-modal');
    var loginModalLoadingPromise = null;
    var pendingLoginModalView = 'login';
    var pendingLoginModalFallbackUrl = '';
    var headerLoginToggle = document.getElementById('header-login-toggle');
    var headerLoginClickHandler = null;

    function markLoginModalLoaded() {
        loginModalLoaded = !!document.getElementById('login-modal');
        window.__dsLoginModalLoaded = loginModalLoaded;

        if (loginModalLoaded && headerLoginToggle && headerLoginClickHandler) {
            headerLoginToggle.removeEventListener('click', headerLoginClickHandler);
            headerLoginClickHandler = null;
        }

        return loginModalLoaded;
    }

    function appendLoginModalNode(node) {
        if (!node) {
            return;
        }

        if (node.nodeType === 3 && !String(node.textContent || '').trim()) {
            return;
        }

        if (node.nodeName === 'SCRIPT') {
            var script = document.createElement('script');
            var attrs = node.attributes || [];
            for (var i = 0; i < attrs.length; i++) {
                script.setAttribute(attrs[i].name, attrs[i].value);
            }
            if (node.src) {
                script.src = node.src;
            } else {
                script.text = node.textContent || '';
            }
            (document.body || document.documentElement).appendChild(script);
            return;
        }

        if (node.nodeName === 'STYLE' || (node.nodeName === 'LINK' && node.getAttribute('rel') === 'stylesheet')) {
            document.head.appendChild(node);
            return;
        }

        (document.body || document.documentElement).appendChild(node);
    }

    function injectLoginModalMarkup(html) {
        if (!html) {
            return false;
        }

        var template = document.createElement('template');
        template.innerHTML = String(html);

        var sourceNodes = template.content ? template.content.childNodes : template.childNodes;
        var nodes = [];
        for (var i = 0; i < sourceNodes.length; i++) {
            nodes.push(sourceNodes[i]);
        }

        nodes.forEach(function (node) {
            appendLoginModalNode(node);
        });

        return markLoginModalLoaded();
    }

    function loadLoginModalAuthFlow() {
        if (window.DSAuthFlow) {
            return Promise.resolve(true);
        }
        if (!loginModalAuthFlowScript || typeof window.DSLoadScriptOnce !== 'function') {
            return Promise.resolve(false);
        }
        return window.DSLoadScriptOnce(loginModalAuthFlowScript, 'data-ds-auth-flow-script')
            .then(function () {
                return !!window.DSAuthFlow;
            })
            .catch(function () {
                return false;
            });
    }

    function initLoadedLoginModal() {
        if (window.DSLoginModal && typeof window.DSLoginModal.init === 'function') {
            return !!window.DSLoginModal.init();
        }
        return typeof window.developerStarterShowLoginModal === 'function'
            && window.developerStarterShowLoginModal !== lazyLoginModalProxy;
    }

    function loadLoginModalScript() {
        if (initLoadedLoginModal()) {
            return Promise.resolve(true);
        }
        if (!loginModalScript || typeof window.DSLoadScriptOnce !== 'function') {
            return Promise.resolve(initLoadedLoginModal());
        }
        return window.DSLoadScriptOnce(loginModalScript, 'data-ds-login-modal-script')
            .then(function () {
                return initLoadedLoginModal();
            })
            .catch(function () {
                return initLoadedLoginModal();
            });
    }

    function loadLoginModalMarkup() {
        if (!loginModalEnabled || !loginModalEndpoint) {
            return Promise.resolve(false);
        }

        if (markLoginModalLoaded()) {
            return loadLoginModalScript();
        }

        if (loginModalLoadingPromise) {
            return loginModalLoadingPromise;
        }

        loadStyle(loginModalStyle, 'data-ds-login-modal-style');

        loginModalLoadingPromise = loadLoginModalAuthFlow()
            .then(function () {
                return fetch(loginModalEndpoint + '&_=' + Date.now(), {
                    credentials: 'same-origin',
                    cache: 'no-store'
                });
            })
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('login modal request failed');
                }
                return response.json();
            })
            .then(function (data) {
                if (!data || !data.success || !data.data || !data.data.html) {
                    throw new Error('invalid login modal response');
                }
                return injectLoginModalMarkup(data.data.html);
            })
            .then(function (loaded) {
                if (!loaded) {
                    return false;
                }
                return loadLoginModalScript();
            })
            .then(function (loaded) {
                loginModalLoadingPromise = null;
                return !!loaded;
            })
            .catch(function () {
                loginModalLoadingPromise = null;
                return false;
            });

        return loginModalLoadingPromise;
    }

    var lazyLoginModalProxy = function (view, fallbackUrl) {
        pendingLoginModalView = view === 'register' ? 'register' : 'login';
        pendingLoginModalFallbackUrl = String(fallbackUrl || loginModalFallbackUrl || '');

        if (!loginModalEnabled) {
            return false;
        }

        if (
            markLoginModalLoaded()
            && typeof window.developerStarterShowLoginModal === 'function'
            && window.developerStarterShowLoginModal !== lazyLoginModalProxy
        ) {
            window.developerStarterShowLoginModal(pendingLoginModalView);
            return true;
        }

        loadLoginModalMarkup().then(function (loaded) {
            if (
                loaded
                && typeof window.developerStarterShowLoginModal === 'function'
                && window.developerStarterShowLoginModal !== lazyLoginModalProxy
            ) {
                window.developerStarterShowLoginModal(pendingLoginModalView);
                return;
            }

            var modalFallbackUrl = pendingLoginModalFallbackUrl || loginModalFallbackUrl;
            if (modalFallbackUrl) {
                window.location.href = modalFallbackUrl;
            }
        });

        return true;
    };

    if (typeof window.developerStarterShowLoginModal !== 'function') {
        window.developerStarterShowLoginModal = lazyLoginModalProxy;
    }

    window.dsOpenLoginModal = function (view, fallbackUrl) {
        return lazyLoginModalProxy(view || 'login', fallbackUrl);
    };

    document.addEventListener('click', function (event) {
        var trigger = event.target && event.target.closest
            ? event.target.closest('[data-ds-login-trigger="modal"], .contact-form-login-hint a')
            : null;

        if (!trigger || !loginModalEnabled) {
            return;
        }

        var fallbackUrl = trigger.getAttribute('data-login-url') || trigger.getAttribute('href') || loginModalFallbackUrl;
        event.preventDefault();

        if (!lazyLoginModalProxy('login', fallbackUrl) && fallbackUrl) {
            window.location.href = fallbackUrl;
        }
    });

    if (loginModalEnabled && headerLoginToggle) {
        headerLoginClickHandler = function (event) {
            if (markLoginModalLoaded()) {
                return;
            }

            event.preventDefault();
            lazyLoginModalProxy('login');
        };

        headerLoginToggle.addEventListener('click', headerLoginClickHandler);
    }

    var loadSearchCaptchaAssets = function () {
        if (!searchCaptchaLazyLoad) {
            return Promise.resolve(false);
        }

        if (window.__dsSearchCaptchaInitialized) {
            searchCaptchaLoaded = true;
            return Promise.resolve(true);
        }

        if (searchCaptchaLoaded) {
            return Promise.resolve(true);
        }

        if (searchCaptchaLoadingPromise) {
            return searchCaptchaLoadingPromise;
        }

        var cssUrl = searchCaptchaAssets.style || '';
        var jsUrl = searchCaptchaAssets.script || '';
        var jqueryUrl = searchCaptchaAssets.jquery || '';
        loadStyle(cssUrl, 'data-ds-search-captcha-style');

        var ensureProvider = (typeof window.DSLoadCaptchaProvider === 'function')
            ? window.DSLoadCaptchaProvider('search')
            : Promise.resolve(false);

        searchCaptchaLoadingPromise = ensureProvider
            .catch(function () {
                return false;
            })
            .then(function () {
                if (window.jQuery) {
                    return true;
                }
                return loadScript(jqueryUrl, 'data-ds-search-captcha-jquery')
                    .then(function () {
                        return true;
                    })
                    .catch(function () {
                        return false;
                    });
            })
            .then(function (jqueryReady) {
                if (!jqueryReady || !jsUrl) {
                    return false;
                }
                return loadScript(jsUrl, 'data-ds-search-captcha-script')
                    .then(function () {
                        searchCaptchaLoaded = !!window.__dsSearchCaptchaInitialized;
                        return searchCaptchaLoaded;
                    })
                    .catch(function () {
                        return false;
                    });
            })
            .then(function (loaded) {
                searchCaptchaLoadingPromise = null;
                return loaded;
            });

        return searchCaptchaLoadingPromise;
    };

    var formNeedsSearchCaptcha = function (form) {
        if (!form || form.tagName !== 'FORM') {
            return false;
        }

        if (form.matches('.ds-enable-captcha')) {
            return true;
        }

        if (form.matches('form[role="search"]') || form.matches('form.search-form')) {
            return true;
        }

        if (form.classList.contains('contact-form') || form.id === 'ds-contact-form') {
            return true;
        }

        return !!(form.closest && form.closest('.widget_search'));
    };

    if (searchCaptchaLazyLoad) {
        document.addEventListener('submit', function (e) {
            var form = e.target;
            if (!formNeedsSearchCaptcha(form)) {
                return;
            }

            if (form.dataset.dsSearchCaptchaBootstrap === '1') {
                delete form.dataset.dsSearchCaptchaBootstrap;
                return;
            }

            if (form.dataset.dsSearchCaptchaSkipOnce === '1') {
                delete form.dataset.dsSearchCaptchaSkipOnce;
                return;
            }

            if (window.__dsSearchCaptchaInitialized) {
                searchCaptchaLoaded = true;
                return;
            }

            e.preventDefault();
            e.stopPropagation();
            if (typeof e.stopImmediatePropagation === 'function') {
                e.stopImmediatePropagation();
            }

            var submitter = typeof e.submitter !== 'undefined' ? e.submitter : null;
            loadSearchCaptchaAssets().then(function (loaded) {
                if (!loaded) {
                    if (typeof form.requestSubmit === 'function') {
                        form.dataset.dsSearchCaptchaSkipOnce = '1';
                        setTimeout(function () {
                            if (submitter && submitter.isConnected) {
                                form.requestSubmit(submitter);
                            } else {
                                form.requestSubmit();
                            }
                        }, 0);
                        return;
                    }

                    setTimeout(function () {
                        form.submit();
                    }, 0);
                    return;
                }

                form.dataset.dsSearchCaptchaBootstrap = '1';
                setTimeout(function () {
                    if (typeof form.requestSubmit === 'function') {
                        if (submitter && submitter.isConnected) {
                            form.requestSubmit(submitter);
                        } else {
                            form.requestSubmit();
                        }
                        return;
                    }

                    form.dispatchEvent(new Event('submit', { bubbles: true, cancelable: true }));
                }, 0);
            });
        }, true);
    }

    if (searchToggle) {
        var loadSearchScriptAndOpen = function (e) {
            if (e) e.preventDefault();

            if (searchScriptLoaded && window.developerStarterSearchOverlay && typeof window.developerStarterSearchOverlay.open === 'function') {
                window.developerStarterSearchOverlay.open();
                return;
            }

            if (!searchOverlayScript) {
                var fallbackOverlayNoScript = document.getElementById('search-overlay');
                if (fallbackOverlayNoScript) {
                    fallbackOverlayNoScript.classList.add('active');
                    var fallbackInputNoScript = fallbackOverlayNoScript.querySelector('input[type="search"], input[name="s"]');
                    if (fallbackInputNoScript) fallbackInputNoScript.focus();
                }
                return;
            }

            if (searchScriptLoading) {
                return;
            }

            searchScriptLoading = true;
            var script = document.createElement('script');
            script.src = searchOverlayScript;
            script.defer = true;

            script.onload = function () {
                searchScriptLoading = false;
                searchScriptLoaded = true;
                if (window.developerStarterSearchOverlay && typeof window.developerStarterSearchOverlay.open === 'function') {
                    window.developerStarterSearchOverlay.open();
                }
            };

            script.onerror = function () {
                searchScriptLoading = false;
                // 回退：即使懒加载失败，也尽量保证搜索弹层可用
                var fallbackOverlay = document.getElementById('search-overlay');
                if (fallbackOverlay) {
                    fallbackOverlay.classList.add('active');
                    var input = fallbackOverlay.querySelector('input[type="search"], input[name="s"]');
                    if (input) input.focus();
                }
            };

            document.head.appendChild(script);
        };

        searchToggle.addEventListener('click', loadSearchScriptAndOpen);
    }

    // ===== Lazy embed runtime moved to feature-lazy-embeds.js =====

    // ===== FAQ runtime moved to feature-faq.js =====

    // ===== Stats counter runtime moved to feature-stats-counter.js =====

    // ===== Back to top runtime moved to feature-back-to-top.js =====

    // ===== Contact form runtime moved to feature-contact-form.js =====

    document.querySelectorAll('a[href^="#"]').forEach(function (anchor) {
        anchor.addEventListener('click', function (e) {
            var href = this.getAttribute('href');
            if (href === '#' || href === '#top') {
                e.preventDefault();
                window.scrollTo({ top: 0, behavior: 'smooth' });
                return;
            }

            try {
                var target = document.querySelector(href);
                if (target) {
                    e.preventDefault();
                    var headerHeight = header ? header.offsetHeight : 0;
                    var targetPosition = target.offsetTop - headerHeight - 20;
                    window.scrollTo({ top: targetPosition, behavior: 'smooth' });
                }
            } catch (err) {
                // Invalid selector
            }
        });
    });

    // ===== Language switcher runtime moved to feature-language-switcher.js =====

});
