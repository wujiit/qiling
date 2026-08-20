/**
 * Shared auth flow helpers.
 *
 * Keeps the normal auth pages and the header modal as separate entry points,
 * while sharing nonce refresh, request helpers, device fingerprinting and
 * captcha lifecycle code.
 */
(function (window, document) {
    'use strict';

    function getGlobalData() {
        return window.developerStarterData || {};
    }

    function getAuthFlowData() {
        var globalData = getGlobalData();
        return globalData.authFlow || {};
    }

    function getCaptchaData() {
        var globalData = getGlobalData();
        return globalData.captcha || {};
    }

    function text(key, fallback) {
        var authFlow = getAuthFlowData();
        var authI18n = authFlow.i18n || {};
        var captchaI18n = getCaptchaData().i18n || {};
        if (typeof authI18n[key] === 'string' && authI18n[key] !== '') {
            return authI18n[key];
        }
        if (typeof captchaI18n[key] === 'string' && captchaI18n[key] !== '') {
            return captchaI18n[key];
        }
        return fallback;
    }

    function getAjaxUrl() {
        var globalData = getGlobalData();
        if (globalData.ajaxUrl) {
            return String(globalData.ajaxUrl);
        }
        return '/wp-admin/admin-ajax.php';
    }

    function getFirstFieldValue(selectors) {
        selectors = Array.isArray(selectors) ? selectors : [];
        for (var i = 0; i < selectors.length; i++) {
            var field = document.querySelector(selectors[i]);
            if (field && field.value) {
                return field.value;
            }
        }
        return '';
    }

    function getAuthNonce(options) {
        options = options || {};
        var selectors = options.selectors || [
            '[name="auth_nonce"]',
            '[name="header_auth_nonce"]',
            '[name="reg_auth_nonce"]'
        ];
        var fieldValue = getFirstFieldValue(selectors);
        if (fieldValue) {
            return fieldValue;
        }
        if (window.DS_MODAL_AUTH_NONCE) {
            return String(window.DS_MODAL_AUTH_NONCE);
        }
        var captchaData = getCaptchaData();
        var globalData = getGlobalData();
        return String(options.fallback || captchaData.verifyNonce || globalData.authNonce || '');
    }

    function applyNonceToSelectors(selectors, value) {
        selectors = Array.isArray(selectors) ? selectors : [];
        selectors.forEach(function (selector) {
            document.querySelectorAll(selector).forEach(function (field) {
                field.value = value;
            });
        });
    }

    function refreshNonces(options) {
        options = options || {};
        var authSelectors = options.authSelectors || [
            '[name="auth_nonce"]',
            '[name="header_auth_nonce"]',
            '[name="reg_auth_nonce"]'
        ];
        var smsSelectors = options.smsSelectors || [
            '#sms_nonce_field'
        ];
        var url = getAjaxUrl() + '?action=developer_starter_refresh_nonce&_=' + Date.now();

        return fetch(url, {
            credentials: 'same-origin',
            cache: 'no-store'
        })
            .then(function (response) {
                return response.json();
            })
            .then(function (payload) {
                if (!payload || !payload.success || !payload.data) {
                    return null;
                }

                var data = payload.data;
                var globalData = getGlobalData();
                if (data.auth_nonce) {
                    applyNonceToSelectors(authSelectors, data.auth_nonce);
                    globalData.authNonce = data.auth_nonce;
                    if (globalData.captcha) {
                        globalData.captcha.verifyNonce = data.auth_nonce;
                    }
                    window.DS_MODAL_AUTH_NONCE = data.auth_nonce;
                }
                if (data.sms_nonce) {
                    applyNonceToSelectors(smsSelectors, data.sms_nonce);
                }
                if (typeof options.onSuccess === 'function') {
                    options.onSuccess(data);
                }
                return data;
            })
            .catch(function () {
                return null;
            });
    }

    function startNonceRefresh(options) {
        options = options || {};
        var interval = parseInt(options.interval, 10);
        if (!interval || interval < 1) {
            interval = 15 * 60 * 1000;
        }
        refreshNonces(options);
        return window.setInterval(function () {
            refreshNonces(options);
        }, interval);
    }

    function getDeviceFingerprint() {
        try {
            var key = 'ds_device_fingerprint_v1';
            var fp = window.localStorage.getItem(key) || '';
            if (!fp) {
                fp = (window.crypto && window.crypto.randomUUID)
                    ? window.crypto.randomUUID()
                    : ('ds-' + Date.now().toString(36) + '-' + Math.random().toString(36).slice(2));
                window.localStorage.setItem(key, fp);
            }
            return String(fp || '').trim().toLowerCase();
        } catch (error) {
            return '';
        }
    }

    function getRedirectTo(blockedPathParts) {
        var redirectTo = '';
        blockedPathParts = Array.isArray(blockedPathParts) && blockedPathParts.length
            ? blockedPathParts
            : ['login', 'register', 'forgot-password', 'wp-login'];

        try {
            var urlParams = new URLSearchParams(window.location.search);
            redirectTo = urlParams.get('redirect_to') || '';
        } catch (error) {}

        if (!redirectTo) {
            try {
                redirectTo = window.localStorage.getItem('auth_redirect_to') || '';
            } catch (error) {}
        }

        if (!redirectTo && document.referrer) {
            try {
                var referrerUrl = new URL(document.referrer);
                if (referrerUrl.hostname === window.location.hostname) {
                    var path = referrerUrl.pathname.toLowerCase();
                    var blocked = blockedPathParts.some(function (part) {
                        return path.indexOf(String(part).toLowerCase()) !== -1;
                    });
                    if (!blocked) {
                        redirectTo = document.referrer;
                    }
                }
            } catch (error) {}
        }

        try {
            window.localStorage.removeItem('auth_redirect_to');
        } catch (error) {}

        return redirectTo || '';
    }

    function getEmailSuffix(email) {
        var value = String(email || '').trim().toLowerCase();
        var at = value.lastIndexOf('@');
        if (at === -1 || at === value.length - 1) {
            return '';
        }
        return '@' + value.substring(at + 1);
    }

    function isEmailDomainAllowed(email, whitelist) {
        if (!Array.isArray(whitelist) || whitelist.length === 0) {
            return true;
        }
        var suffix = getEmailSuffix(email);
        if (!suffix) {
            return false;
        }
        return whitelist.indexOf(suffix) !== -1;
    }

    function isValidPhone(phone) {
        return /^1[3-9]\d{9}$/.test(String(phone || '').trim());
    }

    function isSixDigitCode(code) {
        return /^\d{6}$/.test(String(code || '').trim());
    }

    function setMessage(messageEl, type, message, baseClass) {
        if (!messageEl) {
            return;
        }
        messageEl.className = (baseClass || 'form-message') + (type ? ' ' + type : '');
        messageEl.textContent = message || '';
    }

    function setButtonLoading(button, loading, options) {
        options = options || {};
        if (!button) {
            return;
        }
        var textNode = button.querySelector(options.textSelector || '.btn-text');
        var loadingNode = button.querySelector(options.loadingSelector || '.btn-loading, .modal-btn-loading');
        button.disabled = !!loading;
        if (textNode) {
            textNode.style.display = loading ? 'none' : (options.textDisplay || 'inline');
        }
        if (loadingNode) {
            loadingNode.style.display = loading ? 'inline-flex' : 'none';
        }
    }

    function startCountdown(button, seconds, restoreText) {
        if (!button) {
            return null;
        }
        if (button.__dsAuthCountdownTimer) {
            window.clearInterval(button.__dsAuthCountdownTimer);
        }
        var countdown = parseInt(seconds, 10);
        if (!countdown || countdown < 1) {
            countdown = 60;
        }
        button.disabled = true;
        button.textContent = countdown + 's';
        button.__dsAuthCountdownTimer = window.setInterval(function () {
            countdown--;
            if (countdown > 0) {
                button.textContent = countdown + 's';
                return;
            }
            window.clearInterval(button.__dsAuthCountdownTimer || 0);
            button.__dsAuthCountdownTimer = null;
            button.disabled = false;
            button.textContent = restoreText || text('sendCodeText', 'Get code');
        }, 1000);
        return button.__dsAuthCountdownTimer;
    }

    function postForm(formData, options) {
        options = options || {};
        return fetch(options.url || getAjaxUrl(), {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        }).then(function (response) {
            return response.json();
        });
    }

    function isAliyunCaptchaProvider() {
        if (window.DSProviderCaptcha && window.DSProviderCaptcha.isAliyunProvider) {
            return !!window.DSProviderCaptcha.isAliyunProvider();
        }
        return String(getCaptchaData().provider || 'theme') === 'aliyun';
    }

    function ensureCaptchaProvider(scene) {
        if (typeof window.DSLoadCaptchaProvider === 'function') {
            return window.DSLoadCaptchaProvider(scene || 'auth').catch(function () {
                return false;
            });
        }
        return Promise.resolve(isAliyunCaptchaProvider());
    }

    function requestCaptchaChallenge(container, options) {
        options = options || {};
        if (!container || isAliyunCaptchaProvider()) {
            return Promise.resolve(false);
        }

        var authFlow = getAuthFlowData();
        var formData = new FormData();
        formData.append('action', options.action || authFlow.captchaChallengeAction || 'developer_starter_captcha_challenge');
        formData.append('nonce', typeof options.nonceGetter === 'function' ? options.nonceGetter() : getAuthNonce(options));

        return postForm(formData)
            .then(function (payload) {
                if (payload && payload.success && payload.data) {
                    container.dataset.challengeId = payload.data.challenge_id || '';
                    container.dataset.challengeSignature = payload.data.challenge_signature || '';
                    container.dataset.challengeIssued = String(payload.data.challenge_issued || '');
                    return true;
                }
                return false;
            })
            .catch(function () {
                return false;
            });
    }

    function findVerifiedInput(container, options) {
        options = options || {};
        if (options.verifiedInput) {
            return options.verifiedInput;
        }
        if (options.verifiedInputId) {
            return document.getElementById(options.verifiedInputId);
        }
        if (options.form) {
            return options.form.querySelector('.captcha-verified-input, input[name="captcha_verified"]');
        }
        var form = container ? container.closest('form') : null;
        return form ? form.querySelector('.captcha-verified-input, input[name="captcha_verified"]') : null;
    }

    function getCaptchaIcon(size, success) {
        size = parseInt(size, 10);
        if (!size || size < 1) {
            size = 20;
        }
        if (success) {
            return '<svg width="' + size + '" height="' + size + '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>';
        }
        return '<svg width="' + size + '" height="' + size + '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>';
    }

    function initCaptcha(container, options) {
        options = options || {};
        if (!container) {
            return Promise.resolve(false);
        }

        var form = options.form || container.closest('form');
        var verified = findVerifiedInput(container, options);
        var slider = container.querySelector('.captcha-slider');
        var progress = container.querySelector('.captcha-progress');
        var textNode = container.querySelector('.captcha-text');
        var track = container.querySelector('.captcha-track');

        if (!verified) {
            return Promise.resolve(false);
        }

        if (textNode && !container.dataset.dsCaptchaResetText) {
            container.dataset.dsCaptchaResetText = textNode.textContent || text('dragText', 'Slide right to verify');
        }

        if (isAliyunCaptchaProvider()) {
            if (!window.DSProviderCaptcha || !window.DSProviderCaptcha.attachAliyunCaptcha) {
                return Promise.resolve(false);
            }
            return window.DSProviderCaptcha.attachAliyunCaptcha(container, {
                form: form,
                verifiedInput: verified,
                sceneType: options.sceneType || 'auth',
                nonceGetter: options.nonceGetter || function () {
                    return getAuthNonce(options);
                },
                waitingText: text('waitingText', 'Click to complete verification'),
                buttonText: text('buttonText', 'Click to verify'),
                verifyingText: text('verifyingText', 'Verifying...'),
                successText: text('successText', 'Verification successful'),
                failedText: text('failedText', 'Verification failed, please try again'),
                configErrorText: text('configErrorText', '验证码配置不完整'),
                loadFailedText: text('loadFailedText', '验证码脚本加载失败，请检查网络')
            });
        }

        if (!form || !slider || !progress || !textNode || !track) {
            return Promise.resolve(false);
        }

        if (container.dataset.dsAuthCaptchaBound === '1') {
            if (!container.dataset.challengeId) {
                requestCaptchaChallenge(container, options);
            }
            return Promise.resolve(true);
        }
        container.dataset.dsAuthCaptchaBound = '1';

        var isDragging = false;
        var hasMoved = false;
        var startX = 0;
        var sliderLeft = 0;
        var sliderWidth = slider.offsetWidth || parseInt(options.sliderWidth, 10) || 44;
        var trackWidth = 0;
        var dragStartedAt = 0;
        var moveCount = 0;
        var maxLeft = 0;

        function updateTrackWidth() {
            sliderWidth = slider.offsetWidth || sliderWidth;
            trackWidth = track.offsetWidth - sliderWidth;
        }

        function getClientX(event) {
            return event.touches ? event.touches[0].clientX : event.clientX;
        }

        function handleStart(event) {
            if (verified.value && verified.value !== 'false') {
                return;
            }

            updateTrackWidth();
            if (trackWidth <= 0) {
                trackWidth = 200;
            }
            isDragging = true;
            hasMoved = false;
            startX = getClientX(event);
            sliderLeft = parseInt(slider.style.left, 10) || 0;
            dragStartedAt = Date.now();
            moveCount = 0;
            maxLeft = sliderLeft;

            slider.style.transition = 'none';
            progress.style.transition = 'none';

            event.preventDefault();
        }

        function handleMove(event) {
            if (!isDragging) {
                return;
            }

            var deltaX = getClientX(event) - startX;
            var nextLeft = Math.max(0, Math.min(sliderLeft + deltaX, trackWidth));
            if (Math.abs(deltaX) > 5) {
                hasMoved = true;
            }
            moveCount++;
            if (nextLeft > maxLeft) {
                maxLeft = nextLeft;
            }

            slider.style.left = nextLeft + 'px';
            progress.style.width = (nextLeft + sliderWidth) + 'px';
            event.preventDefault();
        }

        function handleEnd() {
            if (!isDragging) {
                return;
            }
            isDragging = false;

            slider.style.transition = 'left 0.3s';
            progress.style.transition = 'width 0.3s';

            var currentLeft = parseInt(slider.style.left, 10) || 0;
            if (!hasMoved || currentLeft < trackWidth - 5 || trackWidth <= 0) {
                slider.style.left = '0';
                progress.style.width = sliderWidth + 'px';
                return;
            }

            var challengeId = container.dataset.challengeId || '';
            var challengeSignature = container.dataset.challengeSignature || '';
            var challengeIssued = container.dataset.challengeIssued || '';
            if (!challengeId || !challengeSignature || !challengeIssued) {
                textNode.textContent = text('captchaInitFailedText', '验证初始化失败，请重试');
                window.setTimeout(function () {
                    resetCaptcha(container, options);
                }, 500);
                return;
            }

            textNode.textContent = text('verifyingText', 'Verifying...');
            slider.style.pointerEvents = 'none';

            var authFlow = getAuthFlowData();
            var formData = new FormData();
            formData.append('action', options.verifyAction || authFlow.captchaVerifyAction || 'developer_starter_captcha_verify');
            formData.append('nonce', typeof options.nonceGetter === 'function' ? options.nonceGetter() : getAuthNonce(options));
            formData.append('challenge_id', challengeId);
            formData.append('challenge_signature', challengeSignature);
            formData.append('challenge_issued', challengeIssued);
            formData.append('drag_duration', String(Math.max(0, Date.now() - dragStartedAt)));
            formData.append('move_count', String(moveCount));
            formData.append('drag_distance', String(Math.max(currentLeft, maxLeft)));

            postForm(formData)
                .then(function (payload) {
                    if (payload && payload.success && payload.data && payload.data.token) {
                        verified.value = payload.data.token;
                        container.classList.add('verified');
                        textNode.textContent = text('successText', 'Verification successful');
                        slider.innerHTML = getCaptchaIcon(options.iconSize, true);
                        return;
                    }

                    textNode.textContent = text('failedText', 'Verification failed, please try again');
                    window.setTimeout(function () {
                        resetCaptcha(container, options);
                    }, 1000);
                })
                .catch(function () {
                    textNode.textContent = text('networkErrorShort', '网络错误');
                    window.setTimeout(function () {
                        resetCaptcha(container, options);
                    }, 1000);
                });
        }

        slider.addEventListener('mousedown', handleStart);
        document.addEventListener('mousemove', handleMove);
        document.addEventListener('mouseup', handleEnd);
        slider.addEventListener('touchstart', handleStart, { passive: false });
        document.addEventListener('touchmove', handleMove, { passive: false });
        document.addEventListener('touchend', handleEnd);

        requestCaptchaChallenge(container, options);
        return Promise.resolve(true);
    }

    function ensureCaptcha(container, options) {
        options = options || {};
        if (!container || container.dataset.initPending === '1') {
            return Promise.resolve(false);
        }
        container.dataset.initPending = '1';
        return ensureCaptchaProvider(options.sceneType || 'auth')
            .then(function () {
                if (!isAliyunCaptchaProvider() && container.dataset.initialized === 'true') {
                    return true;
                }
                return initCaptcha(container, options).then(function (initialized) {
                    if (initialized && !isAliyunCaptchaProvider()) {
                        container.dataset.initialized = 'true';
                    }
                    return initialized;
                });
            })
            .catch(function () {
                return false;
            })
            .then(function (result) {
                container.dataset.initPending = '';
                return result;
            });
    }

    function resetCaptcha(container, options) {
        options = options || {};
        if (!container) {
            return;
        }

        var verified = findVerifiedInput(container, options);
        if (!verified) {
            return;
        }

        if (isAliyunCaptchaProvider() && window.DSProviderCaptcha && window.DSProviderCaptcha.resetContainer) {
            window.DSProviderCaptcha.resetContainer(container, verified);
            return;
        }

        var slider = container.querySelector('.captcha-slider');
        var progress = container.querySelector('.captcha-progress');
        var textNode = container.querySelector('.captcha-text');
        if (!slider || !progress || !textNode) {
            return;
        }

        verified.value = 'false';
        container.classList.remove('verified');
        slider.innerHTML = getCaptchaIcon(options.iconSize, false);
        slider.style.pointerEvents = 'auto';
        slider.style.left = '0';
        progress.style.width = slider.offsetWidth + 'px';
        textNode.textContent = container.dataset.dsCaptchaResetText || text('dragText', 'Slide right to verify');
        container.dataset.challengeId = '';
        container.dataset.challengeSignature = '';
        container.dataset.challengeIssued = '';
        requestCaptchaChallenge(container, options);
    }

    function resetFormCaptcha(form, options) {
        options = options || {};
        if (!form) {
            return;
        }
        form.querySelectorAll('.slider-captcha').forEach(function (container) {
            resetCaptcha(container, Object.assign({}, options, {
                form: form,
                verifiedInput: findVerifiedInput(container, { form: form })
            }));
        });
    }

    window.DSAuthFlow = {
        getAjaxUrl: getAjaxUrl,
        getAuthNonce: getAuthNonce,
        refreshNonces: refreshNonces,
        startNonceRefresh: startNonceRefresh,
        getDeviceFingerprint: getDeviceFingerprint,
        getRedirectTo: getRedirectTo,
        getEmailSuffix: getEmailSuffix,
        isEmailDomainAllowed: isEmailDomainAllowed,
        isValidPhone: isValidPhone,
        isSixDigitCode: isSixDigitCode,
        setMessage: setMessage,
        setButtonLoading: setButtonLoading,
        startCountdown: startCountdown,
        postForm: postForm,
        isAliyunCaptchaProvider: isAliyunCaptchaProvider,
        ensureCaptchaProvider: ensureCaptchaProvider,
        requestCaptchaChallenge: requestCaptchaChallenge,
        initCaptcha: initCaptcha,
        ensureCaptcha: ensureCaptcha,
        resetCaptcha: resetCaptcha,
        resetFormCaptcha: resetFormCaptcha
    };
})(window, document);
