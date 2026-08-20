/**
 * Header login modal behavior.
 *
 * PHP prints only inert JSON configuration. This file owns the executable
 * modal flow so the AJAX template does not need inline JavaScript.
 */
(function (window, document) {
    'use strict';

    var configCache = null;
    var dsModalSmsNonce = '';

    function getGlobalData() {
        return window.developerStarterData || {};
    }

    function getLoginModalConfig() {
        if (configCache) {
            return configCache;
        }

        var configNode = document.getElementById('ds-login-modal-config');
        var config = {};
        if (configNode) {
            try {
                config = JSON.parse(configNode.textContent || '{}') || {};
            } catch (error) {
                config = {};
            }
        }

        var globalData = getGlobalData();
        if (!config.ajaxUrl && globalData.ajaxUrl) {
            config.ajaxUrl = globalData.ajaxUrl;
        }
        if (config.authFlow) {
            globalData.authFlow = config.authFlow;
        }
        if (config.captcha) {
            globalData.captcha = config.captcha;
        }
        if (config.captchaProviderScript) {
            globalData.captchaProviderScript = config.captchaProviderScript;
        }

        config.i18n = config.i18n || {};
        config.emailDomainWhitelist = Array.isArray(config.emailDomainWhitelist)
            ? config.emailDomainWhitelist
            : [];
        configCache = config;
        return configCache;
    }

    function text(key, fallback) {
        var i18n = getLoginModalConfig().i18n || {};
        return typeof i18n[key] === 'string' && i18n[key] !== '' ? i18n[key] : fallback;
    }

    function boolConfig(key) {
        var value = getLoginModalConfig()[key];
        return value === true || value === 1 || value === '1';
    }

    function hasHanCharacters(value) {
        return /[\u3400-\u9fff\uf900-\ufaff]/.test(String(value || ''));
    }

    function getAjaxUrl() {
        var config = getLoginModalConfig();
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

    function setMessage(messageEl, type, message) {
        if (window.DSAuthFlow && typeof window.DSAuthFlow.setMessage === 'function') {
            window.DSAuthFlow.setMessage(messageEl, type, message, 'modal-form-message');
            return;
        }
        if (!messageEl) {
            return;
        }
        messageEl.className = 'modal-form-message' + (type ? ' ' + type : '');
        messageEl.textContent = message || '';
    }

    function setButtonLoading(button, loading) {
        if (window.DSAuthFlow && typeof window.DSAuthFlow.setButtonLoading === 'function') {
            window.DSAuthFlow.setButtonLoading(button, loading, {
                textSelector: '.btn-text',
                loadingSelector: '.modal-btn-loading'
            });
            return;
        }
        if (!button) {
            return;
        }
        var textNode = button.querySelector('.btn-text');
        var loadingNode = button.querySelector('.modal-btn-loading');
        button.disabled = !!loading;
        if (textNode) {
            textNode.style.display = loading ? 'none' : 'inline';
        }
        if (loadingNode) {
            loadingNode.style.display = loading ? 'inline-flex' : 'none';
        }
    }

    function postAjax(formData) {
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

    function startCountdown(button, seconds) {
        if (window.DSAuthFlow && typeof window.DSAuthFlow.startCountdown === 'function') {
            return window.DSAuthFlow.startCountdown(button, seconds, text('sendCode', 'Get code'));
        }

        var countdown = parseInt(seconds, 10);
        if (!countdown || countdown < 1) {
            countdown = 60;
        }
        button.disabled = true;
        button.textContent = countdown + 's';
        var timer = window.setInterval(function () {
            countdown--;
            if (countdown > 0) {
                button.textContent = countdown + 's';
                return;
            }
            window.clearInterval(timer);
            button.disabled = false;
            button.textContent = text('sendCode', 'Get code');
        }, 1000);
        return timer;
    }

    function getDeviceFingerprint() {
        return window.DSAuthFlow ? window.DSAuthFlow.getDeviceFingerprint() : '';
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
        var whitelist = getLoginModalConfig().emailDomainWhitelist || [];
        return window.DSAuthFlow
            ? window.DSAuthFlow.isEmailDomainAllowed(email, whitelist)
            : (whitelist.length === 0 || whitelist.indexOf('@' + String(email || '').split('@').pop().toLowerCase()) !== -1);
    }

    function getFieldValue(selector, fallback) {
        var field = document.querySelector(selector);
        return field && field.value ? field.value : (fallback || '');
    }

    function getModalAuthNonce() {
        var config = getLoginModalConfig();
        if (window.DSAuthFlow) {
            return window.DSAuthFlow.getAuthNonce({
                selectors: ['[name="header_auth_nonce"]', '[name="reg_auth_nonce"]'],
                fallback: config.authNonce || ''
            });
        }
        return getFieldValue('[name="header_auth_nonce"]', getFieldValue('[name="reg_auth_nonce"]', config.authNonce || ''));
    }

    function resetModalCaptcha(containerId, verifiedInputId) {
        var container = document.getElementById(containerId);
        if (window.DSAuthFlow) {
            window.DSAuthFlow.resetCaptcha(container, {
                verifiedInputId: verifiedInputId,
                nonceGetter: getModalAuthNonce,
                iconSize: 16
            });
        }
    }

    function initLoginModal() {
        var loginModal = document.getElementById('login-modal');
        if (!loginModal) {
            return false;
        }
        if (loginModal.dataset.dsLoginModalInitialized === '1') {
            return true;
        }
        loginModal.dataset.dsLoginModalInitialized = '1';

        var config = getLoginModalConfig();
        window.DS_MODAL_AUTH_NONCE = String(config.authNonce || window.DS_MODAL_AUTH_NONCE || '');
        dsModalSmsNonce = String(config.smsNonce || dsModalSmsNonce || '');

        var loginBtn = document.getElementById('header-login-toggle');
        var loginOverlay = document.getElementById('login-modal-overlay');
        var loginForm = document.getElementById('header-login-form');
        var phoneForm = document.getElementById('header-phone-form');
        var regForm = document.getElementById('header-register-form');
        var redirectToInput = document.getElementById('header-redirect-to');
        var modalTitle = document.getElementById('modal-title');
        var loginPanel = document.getElementById('login-panel');
        var registerPanel = document.getElementById('register-panel');
        var weixinPanel = document.getElementById('weixin-login-panel');
        var weixinToggleBtn = document.getElementById('login-weixin-toggle');
        var weixinFooterBtn = document.getElementById('login-weixin-btn');
        var weixinBackBtn = document.getElementById('weixin-back-btn');
        var showRegisterBtn = document.getElementById('show-register-panel');
        var showLoginInlineBtn = document.getElementById('show-login-inline-panel');
        var showLoginBtn = document.getElementById('show-login-panel');
        var forgotLink = document.getElementById('login-modal-forgot-link');
        var phoneSubmitText = document.getElementById('header-phone-submit-text');
        var weixinFooterBtnText = document.getElementById('login-weixin-btn-text');
        var modalTabs = loginModal.querySelectorAll('.modal-tab');
        var currentUrl = window.location.href;

        var defaultPhone = boolConfig('defaultPhone');
        var smsPhoneOnly = boolConfig('smsPhoneOnly');
        var defaultWeixin = boolConfig('defaultWeixin');
        var canEmailRegister = boolConfig('canEmailRegister');
        var canPhoneRegister = boolConfig('canPhoneRegister');
        var canWeixinRegister = boolConfig('canWeixinRegister');
        var registerCodeEnabled = boolConfig('registerCodeEnabled');
        var registerEmailCodeEnabled = boolConfig('registerEmailCodeEnabled');
        var registerUsernameChinesePolicy = String(config.registerUsernameChinesePolicy || 'allow');
        var registerEmailCodeInterval = parseInt(config.registerEmailCodeInterval, 10) || 60;
        var emailDomainWhitelistText = String(config.emailDomainWhitelistText || '');

        function setModalAuthMode(mode) {
            var registerLikeMode = mode !== 'login';

            if (showRegisterBtn) {
                showRegisterBtn.classList.toggle('hidden', registerLikeMode);
            }
            if (showLoginInlineBtn) {
                showLoginInlineBtn.classList.toggle('hidden', !registerLikeMode);
            }
            if (forgotLink) {
                forgotLink.classList.toggle('hidden', registerLikeMode);
            }
            if (phoneSubmitText) {
                phoneSubmitText.textContent = mode === 'register-phone'
                    ? text('registerSubmit', 'Register')
                    : text('loginSubmit', 'Log in');
            }
            if (weixinFooterBtnText) {
                weixinFooterBtnText.textContent = mode === 'register-weixin'
                    ? text('weixinRegister', 'Register with WeChat')
                    : text('weixinLogin', 'Log in with WeChat');
            }
        }

        function refreshNonce() {
            if (!window.DSAuthFlow) {
                return;
            }
            window.DSAuthFlow.refreshNonces({
                authSelectors: ['[name="header_auth_nonce"]', '[name="reg_auth_nonce"]'],
                onSuccess: function (data) {
                    if (data && data.sms_nonce) {
                        dsModalSmsNonce = data.sms_nonce;
                    }
                }
            });
        }

        refreshNonce();
        window.setInterval(refreshNonce, 15 * 60 * 1000);

        function closeModal() {
            loginModal.classList.remove('active');
            if (loginOverlay) {
                loginOverlay.classList.remove('active');
            }
            document.body.style.overflow = '';
            if (weixinPanel) {
                weixinPanel.style.display = 'none';
            }
            if (loginPanel) {
                loginPanel.style.display = 'block';
            }
            loginModal.classList.remove('weixin-active');
        }

        function ensureVisibleModalCaptcha() {
            var activeForm = null;
            if (registerPanel && registerPanel.style.display !== 'none') {
                activeForm = regForm;
            } else if (loginPanel && loginPanel.style.display !== 'none') {
                activeForm = (phoneForm && !phoneForm.classList.contains('hidden')) ? phoneForm : loginForm;
            }
            if (!activeForm) {
                return;
            }

            var captchaContainer = activeForm.querySelector('.slider-captcha');
            var verifiedInput = activeForm.querySelector('input[name="captcha_verified"]');
            if (!captchaContainer || !verifiedInput || captchaContainer.dataset.initPending === '1') {
                return;
            }
            if (window.DSAuthFlow) {
                window.DSAuthFlow.ensureCaptcha(captchaContainer, {
                    form: activeForm,
                    verifiedInput: verifiedInput,
                    nonceGetter: getModalAuthNonce,
                    iconSize: 16
                });
            }
        }

        function appendVerifiedModalCaptcha(form, formData, messageEl) {
            var captchaInput = form ? form.querySelector('input[name="captcha_verified"]') : null;
            if (!captchaInput) {
                return true;
            }

            var captchaValue = String(captchaInput.value || '').trim();
            if (!captchaValue || captchaValue === 'false') {
                ensureVisibleModalCaptcha();
                var captchaContainer = form ? form.querySelector('.slider-captcha') : null;
                if (captchaContainer && typeof captchaContainer.scrollIntoView === 'function') {
                    captchaContainer.scrollIntoView({ block: 'center', behavior: 'smooth' });
                }
                setMessage(messageEl, 'error', text('captchaRequired', '请先完成验证，再获取验证码'));
                return false;
            }

            formData.append('captcha_verified', captchaValue);
            return true;
        }

        function setLoginTab(targetTab) {
            modalTabs.forEach(function (tab) {
                tab.classList.toggle('active', tab.dataset.tab === targetTab);
            });
            if (targetTab === 'phone') {
                if (phoneForm) {
                    phoneForm.classList.remove('hidden');
                }
                if (loginForm) {
                    loginForm.classList.add('hidden');
                }
            } else {
                if (loginForm) {
                    loginForm.classList.remove('hidden');
                }
                if (phoneForm) {
                    phoneForm.classList.add('hidden');
                }
            }
            window.setTimeout(ensureVisibleModalCaptcha, 0);
        }

        function showLogin() {
            if (loginPanel) {
                loginPanel.style.display = 'block';
            }
            if (registerPanel) {
                registerPanel.style.display = 'none';
            }
            if (weixinPanel) {
                weixinPanel.style.display = 'none';
            }
            if (modalTitle) {
                modalTitle.textContent = text('loginTitle', 'User login');
            }
            loginModal.classList.remove('weixin-active');
            window.__qilingWeixinPreferred = false;
            setModalAuthMode('login');
            if (smsPhoneOnly || defaultPhone) {
                setLoginTab('phone');
            } else {
                setLoginTab('account');
            }
        }

        function showPhoneRegister() {
            if (!loginPanel || !phoneForm) {
                showLogin();
                return;
            }
            loginPanel.style.display = 'block';
            if (registerPanel) {
                registerPanel.style.display = 'none';
            }
            if (weixinPanel) {
                weixinPanel.style.display = 'none';
            }
            if (modalTitle) {
                modalTitle.textContent = text('phoneRegisterTitle', 'Phone register');
            }
            loginModal.classList.remove('weixin-active');
            window.__qilingWeixinPreferred = false;
            setModalAuthMode('register-phone');
            setLoginTab('phone');
            window.setTimeout(ensureVisibleModalCaptcha, 0);
        }

        function showWeixin(mode) {
            if (!weixinPanel) {
                return;
            }
            if (loginPanel) {
                loginPanel.style.display = 'none';
            }
            if (registerPanel) {
                registerPanel.style.display = 'none';
            }
            weixinPanel.style.display = 'block';
            if (modalTitle) {
                modalTitle.textContent = mode === 'register'
                    ? text('weixinRegister', 'Register with WeChat')
                    : text('weixinLogin', 'Log in with WeChat');
            }
            loginModal.classList.add('weixin-active');
            window.__qilingWeixinPreferred = true;
            setModalAuthMode(mode === 'register' ? 'register-weixin' : 'login');
            if (window.qilingWeixinStartQr) {
                var box = weixinPanel.querySelector('.qiling-weixin-qr');
                if (box) {
                    window.qilingWeixinStartQr(box);
                }
            }
        }

        function showRegister() {
            if (canEmailRegister && registerPanel) {
                if (loginPanel) {
                    loginPanel.style.display = 'none';
                }
                registerPanel.style.display = 'block';
                if (weixinPanel) {
                    weixinPanel.style.display = 'none';
                }
                if (modalTitle) {
                    modalTitle.textContent = text('registerTitle', 'Create account');
                }
                loginModal.classList.remove('weixin-active');
                window.__qilingWeixinPreferred = false;
                setModalAuthMode('register-email');
                window.setTimeout(ensureVisibleModalCaptcha, 0);
                return;
            }

            if (canPhoneRegister && phoneForm) {
                showPhoneRegister();
                return;
            }

            if (canWeixinRegister && weixinPanel) {
                showWeixin('register');
                return;
            }

            showLogin();
        }

        function openModal(view) {
            if (redirectToInput) {
                redirectToInput.value = currentUrl;
            }
            var regRedirect = document.getElementById('reg-redirect-to');
            if (regRedirect) {
                regRedirect.value = currentUrl;
            }
            var phoneRedirect = document.getElementById('header-phone-redirect-to');
            if (phoneRedirect) {
                phoneRedirect.value = currentUrl;
            }

            loginModal.classList.add('active');
            if (loginOverlay) {
                loginOverlay.classList.add('active');
            }
            document.body.style.overflow = 'hidden';

            var preferWeixin = typeof window.__qilingWeixinPreferred !== 'undefined'
                ? window.__qilingWeixinPreferred
                : defaultWeixin;

            if (view === 'register') {
                showRegister();
            } else if (preferWeixin) {
                showWeixin('login');
            } else {
                showLogin();
            }
            window.setTimeout(ensureVisibleModalCaptcha, 0);
        }

        window.developerStarterShowLoginModal = function (view) {
            openModal(view || 'login');
            return true;
        };

        if (showRegisterBtn) {
            showRegisterBtn.addEventListener('click', function (event) {
                event.preventDefault();
                showRegister();
            });
        }
        if (showLoginBtn) {
            showLoginBtn.addEventListener('click', function (event) {
                event.preventDefault();
                showLogin();
            });
        }
        if (showLoginInlineBtn) {
            showLoginInlineBtn.addEventListener('click', function (event) {
                event.preventDefault();
                showLogin();
            });
        }
        if (weixinToggleBtn) {
            weixinToggleBtn.addEventListener('click', function (event) {
                event.preventDefault();
                if (loginModal.classList.contains('weixin-active')) {
                    showLogin();
                } else {
                    showWeixin('login');
                }
            });
        }
        if (weixinFooterBtn) {
            weixinFooterBtn.addEventListener('click', function (event) {
                event.preventDefault();
                showWeixin('login');
            });
        }
        if (weixinBackBtn) {
            weixinBackBtn.addEventListener('click', function (event) {
                event.preventDefault();
                showLogin();
            });
        }
        if (loginBtn) {
            loginBtn.addEventListener('click', function (event) {
                event.preventDefault();
                openModal('login');
            });
        }
        if (loginOverlay) {
            loginOverlay.addEventListener('click', closeModal);
        }
        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && loginModal.classList.contains('active')) {
                closeModal();
            }
        });

        var userToggle = document.getElementById('header-user-toggle');
        var userDropdown = document.getElementById('user-dropdown');
        if (userToggle && userDropdown) {
            userToggle.addEventListener('click', function (event) {
                event.stopPropagation();
                userDropdown.classList.toggle('active');
            });
            document.addEventListener('click', function () {
                userDropdown.classList.remove('active');
            });
        }

        modalTabs.forEach(function (tab) {
            tab.addEventListener('click', function () {
                setLoginTab(this.dataset.tab);
            });
        });

        if (loginForm) {
            loginForm.addEventListener('submit', function (event) {
                event.preventDefault();
                var submitBtn = document.getElementById('header-login-submit');
                var message = document.getElementById('header-form-message');
                var formData = new FormData(loginForm);
                formData.append('action', 'developer_starter_login');
                formData.append('nonce', getModalAuthNonce());

                setButtonLoading(submitBtn, true);
                postAjax(formData)
                    .then(function (data) {
                        if (data && data.success) {
                            setMessage(message, 'success', getMessage(data));
                            window.setTimeout(function () {
                                if (data.data && data.data.redirect) {
                                    window.location.href = data.data.redirect;
                                } else {
                                    window.location.reload();
                                }
                            }, 1000);
                            return;
                        }

                        setMessage(message, 'error', getMessage(data));
                        resetModalCaptcha('header-slider-captcha', 'header-captcha-verified');
                        setButtonLoading(submitBtn, false);
                    })
                    .catch(function () {
                        setMessage(message, 'error', text('networkErrorText', '网络错误，请稍后再试'));
                        resetModalCaptcha('header-slider-captcha', 'header-captcha-verified');
                        setButtonLoading(submitBtn, false);
                    });
            });
        }

        if (phoneForm) {
            var phoneInput = document.getElementById('header-phone');
            var codeInput = document.getElementById('header-sms-code');
            var sendBtn = document.getElementById('header-sms-btn');
            var phoneSubmitBtn = document.getElementById('header-phone-submit');
            var phoneMessage = document.getElementById('header-phone-message');
            var phoneRedirect = document.getElementById('header-phone-redirect-to');

            if (sendBtn) {
                sendBtn.addEventListener('click', function () {
                    var phone = phoneInput ? phoneInput.value.trim() : '';
                    if (!isValidPhone(phone)) {
                        setMessage(phoneMessage, 'error', text('phoneInvalid', 'Please enter a valid phone number'));
                        return;
                    }

                    var formData = new FormData();
                    formData.append('action', 'sms_send_code');
                    formData.append('nonce', dsModalSmsNonce);
                    formData.append('phone', phone);
                    formData.append('device_fingerprint', getDeviceFingerprint());
                    if (!appendVerifiedModalCaptcha(phoneForm, formData, phoneMessage)) {
                        return;
                    }

                    sendBtn.disabled = true;
                    sendBtn.textContent = text('sending', 'Sending...');

                    postAjax(formData)
                        .then(function (data) {
                            if (data && data.success) {
                                setMessage(phoneMessage, 'success', getMessage(data));
                                startCountdown(sendBtn, 60);
                                return;
                            }

                            setMessage(phoneMessage, 'error', getMessage(data));
                            resetModalCaptcha('header-phone-slider-captcha', 'header-phone-captcha-verified');
                            sendBtn.disabled = false;
                            sendBtn.textContent = text('sendCode', 'Get code');
                        })
                        .catch(function () {
                            setMessage(phoneMessage, 'error', text('networkError', '网络错误'));
                            resetModalCaptcha('header-phone-slider-captcha', 'header-phone-captcha-verified');
                            sendBtn.disabled = false;
                            sendBtn.textContent = text('sendCode', 'Get code');
                        });
                });
            }

            phoneForm.addEventListener('submit', function (event) {
                event.preventDefault();
                var phone = phoneInput ? phoneInput.value.trim() : '';
                var code = codeInput ? codeInput.value.trim() : '';
                var registrationCodeInput = document.getElementById('header-registration-code');
                var registrationCode = registrationCodeInput ? registrationCodeInput.value.trim() : '';

                if (!isValidPhone(phone)) {
                    setMessage(phoneMessage, 'error', text('phoneInvalid', 'Please enter a valid phone number'));
                    return;
                }
                if (!isSixDigitCode(code)) {
                    setMessage(phoneMessage, 'error', text('smsCodeInvalid', 'Please enter a 6-digit code'));
                    return;
                }

                setButtonLoading(phoneSubmitBtn, true);

                var formData = new FormData();
                formData.append('action', 'sms_phone_login');
                formData.append('nonce', dsModalSmsNonce);
                formData.append('phone', phone);
                formData.append('code', code);
                formData.append('device_fingerprint', getDeviceFingerprint());
                if (registerCodeEnabled) {
                    formData.append('registration_code', registrationCode);
                }
                formData.append('redirect_to', phoneRedirect ? phoneRedirect.value : currentUrl);

                var phoneCaptchaInput = document.getElementById('header-phone-captcha-verified');
                if (phoneCaptchaInput) {
                    formData.append('captcha_verified', phoneCaptchaInput.value);
                }

                postAjax(formData)
                    .then(function (data) {
                        if (data && data.success) {
                            setMessage(phoneMessage, 'success', getMessage(data));
                            window.setTimeout(function () {
                                if (data.data && data.data.redirect) {
                                    window.location.href = data.data.redirect;
                                } else {
                                    window.location.reload();
                                }
                            }, 1000);
                            return;
                        }

                        setMessage(phoneMessage, 'error', getMessage(data));
                        setButtonLoading(phoneSubmitBtn, false);
                    })
                    .catch(function () {
                        setMessage(phoneMessage, 'error', text('networkError', '网络错误'));
                        setButtonLoading(phoneSubmitBtn, false);
                    });
            });
        }

        if (regForm) {
            var regMessage = document.getElementById('reg-form-message');
            var regEmailInput = document.getElementById('reg-email');
            var regEmailCodeInput = document.getElementById('reg-email-code');
            var regEmailSendBtn = document.getElementById('reg-email-send-btn');

            function resetRegEmailSendButton() {
                if (!regEmailSendBtn) {
                    return;
                }
                regEmailSendBtn.disabled = false;
                regEmailSendBtn.textContent = text('sendCode', 'Get code');
            }

            function resetRegisterModalCaptchaForEmailFlow() {
                if (registerEmailCodeEnabled) {
                    return;
                }
                resetModalCaptcha('reg-slider-captcha', 'reg-captcha-verified');
            }

            if (registerEmailCodeEnabled && regEmailSendBtn && regEmailInput) {
                regEmailSendBtn.addEventListener('click', function () {
                    if (regEmailSendBtn.disabled) {
                        return;
                    }

                    var emailValue = regEmailInput.value.trim().toLowerCase();
                    if (!emailValue || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailValue)) {
                        setMessage(regMessage, 'error', text('emailInvalid', 'Please enter a valid email address'));
                        return;
                    }
                    if (!isEmailDomainAllowed(emailValue)) {
                        setMessage(
                            regMessage,
                            'error',
                            emailDomainWhitelistText
                                ? text('emailWhitelistPrefix', 'Allowed email domains: ') + emailDomainWhitelistText
                                : text('emailNotAllowed', 'Email domain is not allowed')
                        );
                        return;
                    }

                    var codeFormData = new FormData();
                    codeFormData.append('action', 'developer_starter_send_register_email_code');
                    codeFormData.append('nonce', getModalAuthNonce());
                    codeFormData.append('email', emailValue);
                    if (!appendVerifiedModalCaptcha(regForm, codeFormData, regMessage)) {
                        return;
                    }

                    regEmailSendBtn.disabled = true;
                    regEmailSendBtn.textContent = text('sending', 'Sending...');

                    postAjax(codeFormData)
                        .then(function (data) {
                            if (data && data.success) {
                                setMessage(regMessage, 'success', getMessage(data));
                                startCountdown(regEmailSendBtn, (data.data && data.data.retry_after) || registerEmailCodeInterval);
                                return;
                            }

                            setMessage(regMessage, 'error', getMessage(data, text('sendFailed', 'Send failed, please try again later')));
                            resetModalCaptcha('reg-slider-captcha', 'reg-captcha-verified');
                            resetRegEmailSendButton();
                        })
                        .catch(function () {
                            setMessage(regMessage, 'error', text('networkErrorText', '网络错误，请稍后再试'));
                            resetModalCaptcha('reg-slider-captcha', 'reg-captcha-verified');
                            resetRegEmailSendButton();
                        });
                });
            }

            regForm.addEventListener('submit', function (event) {
                event.preventDefault();

                var submitBtn = document.getElementById('reg-submit-btn');
                var usernameInput = document.getElementById('reg-username');
                var emailInput = document.getElementById('reg-email');
                var secretField = document.getElementById('reg-password');
                var confirmInput = document.getElementById('reg-password-confirm');
                var registrationCodeInput = document.getElementById('reg-registration-code');

                var username = usernameInput ? usernameInput.value.trim() : '';
                var email = emailInput ? emailInput.value.trim().toLowerCase() : '';
                var password = secretField ? secretField.value : '';
                var passwordConfirm = confirmInput ? confirmInput.value : '';
                var emailCode = regEmailCodeInput ? regEmailCodeInput.value.trim() : '';
                var registrationCode = registrationCodeInput ? registrationCodeInput.value.trim() : '';

                if (username.length < 3) {
                    setMessage(regMessage, 'error', text('usernameLength', 'Username must be at least 3 characters'));
                    resetRegisterModalCaptchaForEmailFlow();
                    return;
                }
                if (registerUsernameChinesePolicy === 'deny' && hasHanCharacters(username)) {
                    setMessage(regMessage, 'error', text('usernameChineseDisallowed', 'Username cannot contain Chinese characters'));
                    resetRegisterModalCaptchaForEmailFlow();
                    return;
                }
                if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                    setMessage(regMessage, 'error', text('emailInvalid', 'Please enter a valid email address'));
                    resetRegisterModalCaptchaForEmailFlow();
                    return;
                }
                if (!isEmailDomainAllowed(email)) {
                    setMessage(
                        regMessage,
                        'error',
                        emailDomainWhitelistText
                            ? text('emailWhitelistPrefix', 'Allowed email domains: ') + emailDomainWhitelistText
                            : text('emailNotAllowed', 'Email domain is not allowed')
                    );
                    resetRegisterModalCaptchaForEmailFlow();
                    return;
                }
                if (registerEmailCodeEnabled && !isSixDigitCode(emailCode)) {
                    setMessage(regMessage, 'error', text('emailCodeInvalid', 'Please enter a 6-digit email code'));
                    resetRegisterModalCaptchaForEmailFlow();
                    return;
                }
                if (password.length < 6) {
                    setMessage(regMessage, 'error', text('passwordLength', 'Password must be at least 6 characters'));
                    resetRegisterModalCaptchaForEmailFlow();
                    return;
                }
                if (password !== passwordConfirm) {
                    setMessage(regMessage, 'error', text('passwordMismatch', 'Passwords do not match'));
                    resetRegisterModalCaptchaForEmailFlow();
                    return;
                }
                if (registerCodeEnabled && !registrationCode) {
                    setMessage(regMessage, 'error', text('registrationCodeRequired', 'Please enter registration code'));
                    resetRegisterModalCaptchaForEmailFlow();
                    return;
                }

                var formData = new FormData(regForm);
                formData.append('action', 'developer_starter_register');
                formData.append('nonce', getModalAuthNonce());

                setButtonLoading(submitBtn, true);
                postAjax(formData)
                    .then(function (data) {
                        if (data && data.success) {
                            setMessage(regMessage, 'success', getMessage(data));
                            window.setTimeout(function () {
                                if (data.data && data.data.redirect) {
                                    window.location.href = data.data.redirect;
                                } else {
                                    window.location.reload();
                                }
                            }, 1000);
                            return;
                        }

                        setMessage(regMessage, 'error', getMessage(data));
                        resetRegisterModalCaptchaForEmailFlow();
                        setButtonLoading(submitBtn, false);
                    })
                    .catch(function () {
                        setMessage(regMessage, 'error', text('networkErrorText', '网络错误，请稍后再试'));
                        resetRegisterModalCaptchaForEmailFlow();
                        setButtonLoading(submitBtn, false);
                    });
            });
        }

        if (defaultWeixin) {
            showWeixin('login');
        }

        return true;
    }

    function openModal(view) {
        if (typeof window.developerStarterShowLoginModal === 'function') {
            return window.developerStarterShowLoginModal(view || 'login');
        }
        return false;
    }

    window.DSLoginModal = {
        init: initLoginModal,
        open: openModal,
        getAuthNonce: getModalAuthNonce,
        resetCaptcha: resetModalCaptcha
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initLoginModal);
    } else {
        initLoginModal();
    }

    window.dsOpenLoginModal = function (view) {
        return openModal(view || 'login');
    };
})(window, document);
