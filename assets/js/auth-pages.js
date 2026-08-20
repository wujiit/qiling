/**
 * Authentication page behavior.
 *
 * Keeps login/register/forgot-password templates mostly as markup and routes
 * shared flow work through assets/js/auth-flow.js.
 */
(function (window, document) {
    'use strict';

    var configCache = null;
    var smsNonce = '';

    function getConfig() {
        if (configCache) {
            return configCache;
        }

        var node = document.getElementById('ds-auth-page-config');
        var config = {};
        if (node) {
            try {
                config = JSON.parse(node.textContent || '{}') || {};
            } catch (error) {
                config = {};
            }
        }

        config.i18n = config.i18n || {};
        config.emailDomainWhitelist = Array.isArray(config.emailDomainWhitelist)
            ? config.emailDomainWhitelist
            : [];
        smsNonce = String(config.smsNonce || '');
        configCache = config;
        return configCache;
    }

    function text(key, fallback) {
        var i18n = getConfig().i18n || {};
        return typeof i18n[key] === 'string' && i18n[key] !== '' ? i18n[key] : fallback;
    }

    function boolConfig(key) {
        var value = getConfig()[key];
        return value === true || value === 1 || value === '1';
    }

    function hasHanCharacters(value) {
        return /[\u3400-\u9fff\uf900-\ufaff]/.test(String(value || ''));
    }

    function getAjaxUrl() {
        var config = getConfig();
        if (config.ajaxUrl) {
            return String(config.ajaxUrl);
        }
        return window.DSAuthFlow ? window.DSAuthFlow.getAjaxUrl() : '/wp-admin/admin-ajax.php';
    }

    function getMessage(payload, fallback) {
        if (payload && payload.data && payload.data.message) {
            return payload.data.message;
        }
        return fallback || '';
    }

    function setMessage(element, type, message) {
        if (window.DSAuthFlow && typeof window.DSAuthFlow.setMessage === 'function') {
            window.DSAuthFlow.setMessage(element, type, message, 'form-message');
            return;
        }
        if (!element) {
            return;
        }
        element.className = 'form-message' + (type ? ' ' + type : '');
        element.textContent = message || '';
    }

    function setButtonLoading(button, loading) {
        if (window.DSAuthFlow && typeof window.DSAuthFlow.setButtonLoading === 'function') {
            window.DSAuthFlow.setButtonLoading(button, loading);
            return;
        }
        if (!button) {
            return;
        }
        var textNode = button.querySelector('.btn-text');
        var loadingNode = button.querySelector('.btn-loading');
        button.disabled = !!loading;
        if (textNode) {
            textNode.style.display = loading ? 'none' : 'inline';
        }
        if (loadingNode) {
            loadingNode.style.display = loading ? 'inline-flex' : 'none';
        }
    }

    function postForm(formData) {
        if (window.DSAuthFlow && typeof window.DSAuthFlow.postForm === 'function') {
            return window.DSAuthFlow.postForm(formData, { url: getAjaxUrl() });
        }
        return fetch(getAjaxUrl(), {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        }).then(function (response) {
            return response.json();
        });
    }

    function getAuthNonceValue() {
        var config = getConfig();
        if (window.DSAuthFlow) {
            return window.DSAuthFlow.getAuthNonce({
                selectors: ['[name="auth_nonce"]'],
                fallback: config.authNonce || ''
            });
        }
        var field = document.querySelector('[name="auth_nonce"]');
        return field && field.value ? field.value : String(config.authNonce || '');
    }

    function refreshNonces(options) {
        options = options || {};
        if (!window.DSAuthFlow) {
            return;
        }
        window.DSAuthFlow.startNonceRefresh({
            authSelectors: ['[name="auth_nonce"]'],
            smsSelectors: options.smsSelectors || [],
            onSuccess: function (data) {
                if (data && data.sms_nonce) {
                    smsNonce = data.sms_nonce;
                }
            }
        });
    }

    function getDeviceFingerprint() {
        return window.DSAuthFlow ? window.DSAuthFlow.getDeviceFingerprint() : '';
    }

    function getRedirectTo(blocked) {
        return window.DSAuthFlow ? window.DSAuthFlow.getRedirectTo(blocked) : '';
    }

    function isValidPhone(phone) {
        return window.DSAuthFlow
            ? window.DSAuthFlow.isValidPhone(phone)
            : /^1[3-9]\d{9}$/.test(String(phone || '').trim());
    }

    function isSixDigitCode(code) {
        return window.DSAuthFlow
            ? window.DSAuthFlow.isSixDigitCode(code)
            : /^\d{6}$/.test(String(code || '').trim());
    }

    function isEmailDomainAllowed(email) {
        var whitelist = getConfig().emailDomainWhitelist || [];
        return window.DSAuthFlow
            ? window.DSAuthFlow.isEmailDomainAllowed(email, whitelist)
            : whitelist.length === 0;
    }

    function startCountdown(button, seconds) {
        if (window.DSAuthFlow && typeof window.DSAuthFlow.startCountdown === 'function') {
            return window.DSAuthFlow.startCountdown(button, seconds, text('sendCode', 'Get code'));
        }
        return null;
    }

    function ensureVisibleFormCaptcha(form) {
        if (!form || form.classList.contains('hidden')) {
            return;
        }
        var captchaContainer = form.querySelector('.slider-captcha');
        if (!captchaContainer || captchaContainer.dataset.initPending === '1') {
            return;
        }
        if (window.DSAuthFlow) {
            window.DSAuthFlow.ensureCaptcha(captchaContainer, {
                form: form,
                nonceGetter: getAuthNonceValue,
                iconSize: 20
            });
        }
    }

    function initVisibleCaptchas() {
        document.querySelectorAll('.auth-form').forEach(function (form) {
            if (!form.classList.contains('hidden')) {
                ensureVisibleFormCaptcha(form);
            }
        });
    }

    function resetFormCaptcha(form) {
        if (window.DSAuthFlow) {
            window.DSAuthFlow.resetFormCaptcha(form, {
                nonceGetter: getAuthNonceValue,
                iconSize: 20
            });
        }
    }

    function appendVerifiedCaptcha(form, formData, message) {
        var captchaInput = form ? form.querySelector('.captcha-verified-input, input[name="captcha_verified"]') : null;
        if (!captchaInput) {
            return true;
        }

        var captchaValue = String(captchaInput.value || '').trim();
        if (!captchaValue || captchaValue === 'false') {
            ensureVisibleFormCaptcha(form);
            var captchaContainer = form ? form.querySelector('.slider-captcha') : null;
            if (captchaContainer && typeof captchaContainer.scrollIntoView === 'function') {
                captchaContainer.scrollIntoView({ block: 'center', behavior: 'smooth' });
            }
            setMessage(message, 'error', text('captchaRequired', '请先完成验证，再获取验证码'));
            return false;
        }

        formData.append('captcha_verified', captchaValue);
        return true;
    }

    function initPasswordToggles() {
        document.querySelectorAll('.toggle-password').forEach(function (button) {
            if (button.dataset.dsTogglePasswordInitialized === '1') {
                return;
            }
            button.dataset.dsTogglePasswordInitialized = '1';
            button.addEventListener('click', function () {
                var input = button.parentElement ? button.parentElement.querySelector('input') : null;
                if (!input) {
                    return;
                }
                var visible = input.type === 'password';
                input.type = visible ? 'text' : 'password';
                button.classList.toggle('is-visible', visible);
            });
        });
    }

    function initPasswordStrength(inputId, strengthId, listId) {
        var input = document.getElementById(inputId);
        var strengthContainer = document.getElementById(strengthId);
        var checkList = document.getElementById(listId);
        if (!input || !strengthContainer || !checkList || input.dataset.dsStrengthInitialized === '1') {
            return;
        }

        input.dataset.dsStrengthInitialized = '1';
        var items = checkList.querySelectorAll('li');
        input.addEventListener('focus', function () {
            checkList.style.display = 'block';
        });

        input.addEventListener('input', function () {
            var password = input.value;
            var validCount = 0;
            var totalCount = items.length;
            if (!totalCount) {
                return;
            }

            items.forEach(function (item) {
                var rule = item.dataset.rule;
                var valid = false;
                switch (rule) {
                    case 'min-6':
                        valid = password.length >= 6;
                        break;
                    case 'min-8':
                        valid = password.length >= 8;
                        break;
                    case 'min-10':
                        valid = password.length >= 10;
                        break;
                    case 'letter':
                        valid = /[A-Za-z]/.test(password);
                        break;
                    case 'number':
                        valid = /[0-9]/.test(password);
                        break;
                    case 'upper':
                        valid = /[A-Z]/.test(password);
                        break;
                    case 'lower':
                        valid = /[a-z]/.test(password);
                        break;
                    case 'special':
                        valid = /[!@#$%^&*(),.?":{}|<>]/.test(password);
                        break;
                }

                item.classList.toggle('valid', valid);
                item.classList.toggle('invalid', !valid && password.length > 0);
                item.classList.toggle('pending', !valid && password.length === 0);
                var icon = item.querySelector('.icon');
                if (icon) {
                    icon.textContent = valid ? '✓' : '○';
                }
                if (valid) {
                    validCount++;
                }
            });

            var bar = strengthContainer.querySelector('.strength-bar span');
            var label = strengthContainer.querySelector('.strength-text');
            if (!bar || !label) {
                return;
            }

            bar.className = '';
            label.className = 'strength-text';
            if (password.length === 0) {
                bar.style.width = '0';
                label.textContent = '';
                return;
            }

            var percentage = (validCount / totalCount) * 100;
            bar.style.width = Math.max(10, percentage) + '%';
            if (validCount === totalCount) {
                bar.className = 'strong';
                label.className = 'strength-text strong';
                label.textContent = text('passwordPerfect', 'Strong');
            } else if (percentage > 50) {
                bar.className = 'medium';
                label.className = 'strength-text medium';
                label.textContent = text('passwordMedium', 'Medium');
            } else {
                bar.className = 'weak';
                label.className = 'strength-text weak';
                label.textContent = text('passwordWeak', 'Weak');
            }
        });
    }

    function submitAjaxForm(form, action, message, button, options) {
        options = options || {};
        if (!form || !message || !button) {
            return;
        }

        var formData = new FormData(form);
        formData.append('action', action);
        formData.append('nonce', options.nonce || getAuthNonceValue());
        if (options.deviceFingerprint) {
            formData.append('device_fingerprint', getDeviceFingerprint());
        }

        setButtonLoading(button, true);
        postForm(formData)
            .then(function (payload) {
                if (payload && payload.success) {
                    setMessage(message, 'success', getMessage(payload));
                    if (options.redirectDelay !== false && payload.data && payload.data.redirect) {
                        window.setTimeout(function () {
                            window.location.href = payload.data.redirect;
                        }, options.redirectDelay || 1000);
                        return;
                    }
                    if (!options.keepLoadingOnSuccess) {
                        setButtonLoading(button, false);
                    }
                    return;
                }
                setMessage(message, 'error', getMessage(payload));
                if (!options.skipCaptchaReset) {
                    resetFormCaptcha(form);
                }
                setButtonLoading(button, false);
            })
            .catch(function () {
                setMessage(message, 'error', text('networkErrorText', '网络错误，请稍后再试'));
                if (!options.skipCaptchaReset) {
                    resetFormCaptcha(form);
                }
                setButtonLoading(button, false);
            });
    }

    function initLoginPage() {
        refreshNonces({ smsSelectors: ['#sms_nonce_field'] });
        var redirectTo = getRedirectTo();
        document.querySelectorAll('[name="redirect_to"]').forEach(function (input) {
            input.value = redirectTo;
        });

        var currentTab = String(getConfig().defaultTab || 'account');
        document.querySelectorAll('.auth-tab').forEach(function (tab) {
            tab.addEventListener('click', function () {
                currentTab = tab.dataset.tab || 'account';
                document.querySelectorAll('.auth-tab').forEach(function (item) {
                    item.classList.toggle('active', item === tab);
                });
                document.querySelectorAll('.auth-form').forEach(function (form) {
                    form.classList.add('hidden');
                });
                var targetForm = document.getElementById(currentTab === 'phone' ? 'phone-login-form' : 'login-form');
                if (targetForm) {
                    targetForm.classList.remove('hidden');
                    ensureVisibleFormCaptcha(targetForm);
                }
            });
        });

        var weixinPanel = document.getElementById('weixin-login-panel');
        var authTabs = document.querySelector('.auth-tabs');
        var authSocial = document.querySelector('.auth-social');
        var authCard = document.querySelector('.auth-card');

        function showWeixinPanel() {
            if (!weixinPanel) {
                return;
            }
            document.querySelectorAll('.auth-form').forEach(function (form) {
                form.classList.add('hidden');
            });
            if (authTabs) {
                authTabs.style.display = 'none';
            }
            if (authSocial) {
                authSocial.style.display = 'none';
            }
            weixinPanel.classList.remove('hidden');
            if (authCard) {
                authCard.classList.add('weixin-active');
            }
            window.__qilingWeixinPreferred = true;
            if (window.qilingWeixinStartQr) {
                var box = weixinPanel.querySelector('.qiling-weixin-qr');
                if (box) {
                    window.qilingWeixinStartQr(box);
                }
            }
        }

        function showDefaultPanel() {
            if (weixinPanel) {
                weixinPanel.classList.add('hidden');
            }
            if (authTabs) {
                authTabs.style.display = '';
            }
            if (authSocial) {
                authSocial.style.display = '';
            }
            if (authCard) {
                authCard.classList.remove('weixin-active');
            }
            window.__qilingWeixinPreferred = false;
            var targetForm = document.getElementById(currentTab === 'phone' ? 'phone-login-form' : 'login-form');
            if (targetForm) {
                targetForm.classList.remove('hidden');
                ensureVisibleFormCaptcha(targetForm);
            }
        }

        var weixinToggle = document.getElementById('auth-weixin-toggle');
        var weixinButton = document.getElementById('auth-weixin-btn');
        var weixinBack = document.getElementById('auth-weixin-back');
        if (weixinToggle) {
            weixinToggle.addEventListener('click', function () {
                if (authCard && authCard.classList.contains('weixin-active')) {
                    showDefaultPanel();
                } else {
                    showWeixinPanel();
                }
            });
        }
        if (weixinButton) {
            weixinButton.addEventListener('click', showWeixinPanel);
        }
        if (weixinBack) {
            weixinBack.addEventListener('click', showDefaultPanel);
        }
        if (boolConfig('defaultWeixin')) {
            showWeixinPanel();
        }

        var loginForm = document.getElementById('login-form');
        if (loginForm) {
            loginForm.addEventListener('submit', function (event) {
                event.preventDefault();
                submitAjaxForm(
                    loginForm,
                    'developer_starter_login',
                    document.getElementById('form-message'),
                    document.getElementById('submit-btn')
                );
            });
        }

        var phoneForm = document.getElementById('phone-login-form');
        if (phoneForm) {
            var sendButton = document.getElementById('sms-send-btn');
            var phoneInput = document.getElementById('sms-phone');
            var codeInput = document.getElementById('sms-code');
            var phoneMessage = document.getElementById('phone-form-message');
            var phoneSubmit = document.getElementById('phone-submit-btn');

            if (sendButton) {
                sendButton.addEventListener('click', function () {
                    var phone = phoneInput ? phoneInput.value.trim() : '';
                    if (!isValidPhone(phone)) {
                        setMessage(phoneMessage, 'error', text('phoneInvalid', 'Please enter a valid phone number'));
                        return;
                    }
                    sendButton.disabled = true;
                    sendButton.textContent = text('sending', 'Sending...');

                    var formData = new FormData();
                    formData.append('action', 'sms_send_code');
                    formData.append('nonce', document.getElementById('sms_nonce_field') ? document.getElementById('sms_nonce_field').value : smsNonce);
                    formData.append('phone', phone);
                    formData.append('device_fingerprint', getDeviceFingerprint());
                    var captchaInput = phoneForm.querySelector('.captcha-verified-input');
                    if (captchaInput) {
                        formData.append('captcha_verified', captchaInput.value);
                    }

                    postForm(formData)
                        .then(function (payload) {
                            if (payload && payload.success) {
                                setMessage(phoneMessage, 'success', getMessage(payload));
                                startCountdown(sendButton, 60);
                                return;
                            }
                            setMessage(phoneMessage, 'error', getMessage(payload));
                            resetFormCaptcha(phoneForm);
                            sendButton.disabled = false;
                            sendButton.textContent = text('sendCode', 'Get code');
                        })
                        .catch(function () {
                            setMessage(phoneMessage, 'error', text('networkErrorText', '网络错误，请稍后再试'));
                            resetFormCaptcha(phoneForm);
                            sendButton.disabled = false;
                            sendButton.textContent = text('sendCode', 'Get code');
                        });
                });
            }

            phoneForm.addEventListener('submit', function (event) {
                event.preventDefault();
                var phone = phoneInput ? phoneInput.value.trim() : '';
                var code = codeInput ? codeInput.value.trim() : '';
                if (!isValidPhone(phone)) {
                    setMessage(phoneMessage, 'error', text('phoneInvalid', 'Please enter a valid phone number'));
                    return;
                }
                if (!isSixDigitCode(code)) {
                    setMessage(phoneMessage, 'error', text('smsCodeInvalid', 'Please enter a 6-digit code'));
                    return;
                }
                submitAjaxForm(phoneForm, 'sms_phone_login', phoneMessage, phoneSubmit, {
                    nonce: document.getElementById('sms_nonce_field') ? document.getElementById('sms_nonce_field').value : smsNonce,
                    deviceFingerprint: true
                });
            });
        }
    }

    function initRegisterPage() {
        refreshNonces();
        document.querySelectorAll('[name="redirect_to"]').forEach(function (input) {
            input.value = getRedirectTo(['login', 'register', 'forgot-password']);
        });

        if (boolConfig('isPhoneOnly')) {
            initPhoneRegister();
        } else {
            initEmailRegister();
        }
    }

    function initPhoneRegister() {
        var form = document.getElementById('phone-register-form');
        var sendButton = document.getElementById('reg-send-btn');
        var phoneInput = document.getElementById('reg-phone');
        var codeInput = document.getElementById('reg-code');
        var button = document.getElementById('phone-submit-btn');
        var message = document.getElementById('phone-form-message');
        if (!form || !sendButton || !phoneInput || !codeInput || !button || !message) {
            return;
        }

        initVisibleCaptchas();

        sendButton.addEventListener('click', function () {
            var phone = phoneInput.value.trim();
            if (!isValidPhone(phone)) {
                setMessage(message, 'error', text('phoneInvalid', 'Please enter a valid phone number'));
                return;
            }

            var formData = new FormData();
            formData.append('action', 'sms_send_code');
            formData.append('nonce', smsNonce);
            formData.append('phone', phone);
            formData.append('device_fingerprint', getDeviceFingerprint());
            if (!appendVerifiedCaptcha(form, formData, message)) {
                return;
            }

            sendButton.disabled = true;
            sendButton.textContent = text('sending', 'Sending...');

            postForm(formData)
                .then(function (payload) {
                    if (payload && payload.success) {
                        setMessage(message, 'success', getMessage(payload));
                        startCountdown(sendButton, 60);
                        return;
                    }
                    setMessage(message, 'error', getMessage(payload));
                    resetFormCaptcha(form);
                    sendButton.disabled = false;
                    sendButton.textContent = text('sendCode', 'Get code');
                })
                .catch(function () {
                    setMessage(message, 'error', text('networkErrorShort', '网络错误'));
                    resetFormCaptcha(form);
                    sendButton.disabled = false;
                    sendButton.textContent = text('sendCode', 'Get code');
                });
        });

        form.addEventListener('submit', function (event) {
            event.preventDefault();
            var phone = phoneInput.value.trim();
            var code = codeInput.value.trim();
            if (!isValidPhone(phone)) {
                setMessage(message, 'error', text('phoneInvalid', 'Please enter a valid phone number'));
                return;
            }
            if (!isSixDigitCode(code)) {
                setMessage(message, 'error', text('smsCodeInvalid', 'Please enter a 6-digit code'));
                return;
            }
            submitAjaxForm(form, 'sms_phone_login', message, button, {
                nonce: smsNonce,
                deviceFingerprint: true
            });
        });
    }

    function initEmailRegister() {
        var form = document.getElementById('register-form');
        var button = document.getElementById('submit-btn');
        var message = document.getElementById('form-message');
        var password = document.getElementById('password');
        var usernameInput = document.getElementById('username');
        var emailInput = document.getElementById('email');
        var emailCodeInput = document.getElementById('email_code');
        var emailSendButton = document.getElementById('email-send-btn');
        if (!form || !button || !message || !password) {
            return;
        }

        initPasswordStrength('password', 'password-strength', 'password-check-list');
        initVisibleCaptchas();

        function resetEmailButton() {
            if (!emailSendButton) {
                return;
            }
            emailSendButton.disabled = false;
            emailSendButton.textContent = text('sendCode', 'Get code');
        }

        function resetRegisterCaptchaForEmailFlow() {
            if (boolConfig('registerEmailCodeEnabled')) {
                return;
            }
            resetFormCaptcha(form);
        }

        if (boolConfig('registerEmailCodeEnabled') && emailSendButton && emailInput) {
            emailSendButton.addEventListener('click', function () {
                if (emailSendButton.disabled) {
                    return;
                }
                var email = emailInput.value.trim().toLowerCase();
                if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                    setMessage(message, 'error', text('emailInvalid', 'Please enter a valid email address'));
                    return;
                }
                if (!isEmailDomainAllowed(email)) {
                    setMessage(message, 'error', getEmailWhitelistMessage());
                    return;
                }

                var formData = new FormData();
                formData.append('action', 'developer_starter_send_register_email_code');
                formData.append('nonce', getAuthNonceValue());
                formData.append('email', email);
                if (!appendVerifiedCaptcha(form, formData, message)) {
                    return;
                }

                emailSendButton.disabled = true;
                emailSendButton.textContent = text('sending', 'Sending...');

                postForm(formData)
                    .then(function (payload) {
                        if (payload && payload.success) {
                            setMessage(message, 'success', getMessage(payload));
                            startCountdown(emailSendButton, (payload.data && payload.data.retry_after) || getConfig().registerEmailCodeInterval || 60);
                            return;
                        }
                        setMessage(message, 'error', getMessage(payload, text('sendFailed', 'Send failed, please try again later')));
                        resetFormCaptcha(form);
                        resetEmailButton();
                    })
                    .catch(function () {
                        setMessage(message, 'error', text('networkErrorText', '网络错误，请稍后再试'));
                        resetFormCaptcha(form);
                        resetEmailButton();
                    });
            });
        }

        form.addEventListener('submit', function (event) {
            event.preventDefault();
            var username = usernameInput ? usernameInput.value.trim() : '';
            var email = emailInput ? emailInput.value.trim().toLowerCase() : '';
            if (String(getConfig().registerUsernameChinesePolicy || 'allow') === 'deny' && hasHanCharacters(username)) {
                setMessage(message, 'error', text('usernameChineseDisallowed', 'Username cannot contain Chinese characters'));
                resetRegisterCaptchaForEmailFlow();
                return;
            }
            if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                setMessage(message, 'error', text('emailInvalid', 'Please enter a valid email address'));
                resetRegisterCaptchaForEmailFlow();
                return;
            }
            if (!isEmailDomainAllowed(email)) {
                setMessage(message, 'error', getEmailWhitelistMessage());
                resetRegisterCaptchaForEmailFlow();
                return;
            }
            if (boolConfig('registerEmailCodeEnabled')) {
                var emailCode = emailCodeInput ? emailCodeInput.value.trim() : '';
                if (!isSixDigitCode(emailCode)) {
                    setMessage(message, 'error', text('emailCodeInvalid', 'Please enter a 6-digit email code'));
                    resetRegisterCaptchaForEmailFlow();
                    return;
                }
            }
            if (boolConfig('registerCodeEnabled')) {
                var registrationCodeInput = document.getElementById('registration_code');
                if (!registrationCodeInput || !registrationCodeInput.value.trim()) {
                    setMessage(message, 'error', text('registrationCodeRequired', 'Please enter registration code'));
                    resetRegisterCaptchaForEmailFlow();
                    return;
                }
            }
            submitAjaxForm(form, 'developer_starter_register', message, button, {
                skipCaptchaReset: boolConfig('registerEmailCodeEnabled')
            });
        });
    }

    function getEmailWhitelistMessage() {
        var whitelistText = String(getConfig().emailDomainWhitelistText || '');
        return whitelistText
            ? text('emailWhitelistPrefix', 'Allowed email domains: ') + whitelistText
            : text('emailNotAllowed', 'Email domain is not allowed');
    }

    function initForgotPage() {
        refreshNonces();
        initPasswordStrength('password', 'password-strength', 'password-check-list');
        initPasswordStrength('forgot-new-password', 'phone-password-strength', 'phone-password-check-list');
        initVisibleCaptchas();

        if (boolConfig('smsEnable')) {
            var subtitle = document.getElementById('forgot-subtitle');
            document.querySelectorAll('.auth-tab').forEach(function (tab) {
                tab.addEventListener('click', function () {
                    var target = tab.dataset.tab || 'email';
                    document.querySelectorAll('.auth-tab').forEach(function (item) {
                        item.classList.toggle('active', item === tab);
                    });
                    var forgotForm = document.getElementById('forgot-form');
                    var phoneForm = document.getElementById('phone-forgot-form');
                    if (forgotForm) {
                        forgotForm.classList.toggle('hidden', target === 'phone');
                    }
                    if (phoneForm) {
                        phoneForm.classList.toggle('hidden', target !== 'phone');
                    }
                    ensureVisibleFormCaptcha(target === 'phone' ? phoneForm : forgotForm);
                    if (subtitle) {
                        subtitle.textContent = target === 'phone'
                            ? text('forgotPhoneSubtitle', 'Reset password with phone code')
                            : text('forgotEmailSubtitle', 'Enter your email and we will send a reset link');
                    }
                });
            });
        }

        var resetForm = document.getElementById('reset-form');
        if (resetForm) {
            resetForm.addEventListener('submit', function (event) {
                event.preventDefault();
                submitAjaxForm(resetForm, 'developer_starter_reset_password', document.getElementById('form-message'), document.getElementById('submit-btn'), { redirectDelay: 2000 });
            });
        }

        var forgotForm = document.getElementById('forgot-form');
        if (forgotForm) {
            forgotForm.addEventListener('submit', function (event) {
                event.preventDefault();
                submitAjaxForm(forgotForm, 'developer_starter_forgot_password', document.getElementById('form-message'), document.getElementById('submit-btn'), { redirectDelay: 2000 });
            });
        }

        initPhoneForgot();
    }

    function initPhoneForgot() {
        var form = document.getElementById('phone-forgot-form');
        if (!form) {
            return;
        }
        var sendButton = document.getElementById('forgot-send-btn');
        var phoneInput = document.getElementById('forgot-phone');
        var codeInput = document.getElementById('forgot-code');
        var passwordInput = document.getElementById('forgot-new-password');
        var button = document.getElementById('phone-submit-btn');
        var message = document.getElementById('phone-form-message');
        if (!sendButton || !phoneInput || !codeInput || !passwordInput || !button || !message) {
            return;
        }

        sendButton.addEventListener('click', function () {
            var phone = phoneInput.value.trim();
            if (!isValidPhone(phone)) {
                setMessage(message, 'error', text('phoneInvalid', 'Please enter a valid phone number'));
                return;
            }
            sendButton.disabled = true;
            sendButton.textContent = text('sending', 'Sending...');

            var formData = new FormData();
            formData.append('action', 'sms_send_code');
            formData.append('nonce', smsNonce);
            formData.append('phone', phone);
            formData.append('device_fingerprint', getDeviceFingerprint());
            var captchaInput = form.querySelector('.captcha-verified-input');
            if (captchaInput) {
                formData.append('captcha_verified', captchaInput.value);
            }

            postForm(formData)
                .then(function (payload) {
                    if (payload && payload.success) {
                        setMessage(message, 'success', getMessage(payload));
                        startCountdown(sendButton, 60);
                        return;
                    }
                    setMessage(message, 'error', getMessage(payload));
                    resetFormCaptcha(form);
                    sendButton.disabled = false;
                    sendButton.textContent = text('sendCode', 'Get code');
                })
                .catch(function () {
                    setMessage(message, 'error', text('networkErrorShort', '网络错误'));
                    resetFormCaptcha(form);
                    sendButton.disabled = false;
                    sendButton.textContent = text('sendCode', 'Get code');
                });
        });

        form.addEventListener('submit', function (event) {
            event.preventDefault();
            var phone = phoneInput.value.trim();
            var code = codeInput.value.trim();
            var password = passwordInput.value;
            if (!isValidPhone(phone)) {
                setMessage(message, 'error', text('phoneInvalid', 'Please enter a valid phone number'));
                return;
            }
            if (!isSixDigitCode(code)) {
                setMessage(message, 'error', text('smsCodeInvalid', 'Please enter a 6-digit code'));
                return;
            }
            if (password.length < 6) {
                setMessage(message, 'error', text('phonePasswordMin', 'Password must be at least 6 characters'));
                return;
            }

            var formData = new FormData();
            formData.append('action', 'sms_phone_reset_password');
            formData.append('nonce', smsNonce);
            formData.append('phone', phone);
            formData.append('code', code);
            formData.append('device_fingerprint', getDeviceFingerprint());
            formData.append('new_password', password);

            setButtonLoading(button, true);
            postForm(formData)
                .then(function (payload) {
                    if (payload && payload.success) {
                        setMessage(message, 'success', getMessage(payload));
                        window.setTimeout(function () {
                            window.location.href = getConfig().loginUrl || getConfig().homeUrl || '/';
                        }, 2000);
                        return;
                    }
                    setMessage(message, 'error', getMessage(payload));
                    setButtonLoading(button, false);
                })
                .catch(function () {
                    setMessage(message, 'error', text('networkErrorShort', '网络错误'));
                    setButtonLoading(button, false);
                });
        });
    }

    function init() {
        var config = getConfig();
        if (!config.page) {
            return;
        }
        initPasswordToggles();
        if (config.page === 'login') {
            initLoginPage();
        } else if (config.page === 'register') {
            initRegisterPage();
        } else if (config.page === 'forgot') {
            initForgotPage();
        }
        initVisibleCaptchas();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    window.DSAuthPages = {
        init: init,
        getAuthNonce: getAuthNonceValue,
        ensureVisibleFormCaptcha: ensureVisibleFormCaptcha,
        resetFormCaptcha: resetFormCaptcha
    };
})(window, document);
