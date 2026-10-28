/**
 * 启灵主题验证码提供器脚本
 *
 * 统一封装主题滑块与阿里云验证码的加载、初始化和重置逻辑。
 */
(function (window, document) {
    'use strict';

    var instanceSeq = 0;
    var scriptLoader = null;
    var containerState = {};

    function getGlobalData() {
        return window.developerStarterData || {};
    }

    function getCaptchaData() {
        var globalData = getGlobalData();
        return globalData.captcha || {};
    }

    function getCaptchaText(key, fallback) {
        var captchaData = getCaptchaData();
        var i18n = captchaData && captchaData.i18n ? captchaData.i18n : {};
        if (typeof i18n[key] === 'string' && i18n[key] !== '') {
            return i18n[key];
        }
        return fallback;
    }

    function getProvider() {
        var captchaData = getCaptchaData();
        var provider = String(captchaData.provider || 'theme');
        return provider === 'aliyun' ? 'aliyun' : 'theme';
    }

    function getAjaxUrl() {
        var globalData = getGlobalData();
        if (globalData.ajaxUrl) {
            return String(globalData.ajaxUrl);
        }
        return '/wp-admin/admin-ajax.php';
    }

    function getScene(sceneType) {
        var captchaData = getCaptchaData();
        if (sceneType === 'search') {
            return String(captchaData.sceneSearch || '');
        }
        return String(captchaData.sceneAuth || '');
    }

    function getAliyunClientRegion() {
        var captchaData = getCaptchaData();
        var region = String(captchaData.aliyunRegion || '').toLowerCase();
        return region === 'sgp' ? 'sgp' : 'cn';
    }

    function setStatus(container, text) {
        if (!container) {
            return;
        }
        var statusNode = container.querySelector('.ds-aliyun-captcha-status');
        if (statusNode) {
            statusNode.textContent = text;
        }
    }

    function ensureAliyunScript(prefix, clientRegion) {
        var globalConfig = window.AliyunCaptchaConfig || {};
        if (prefix) {
            globalConfig.prefix = String(prefix);
        }
        globalConfig.region = clientRegion === 'sgp' ? 'sgp' : 'cn';
        window.AliyunCaptchaConfig = globalConfig;

        if (typeof window.initAliyunCaptcha === 'function') {
            return Promise.resolve(true);
        }
        if (scriptLoader) {
            return scriptLoader;
        }

        scriptLoader = new Promise(function (resolve, reject) {
            var captchaData = getCaptchaData();
            var src = String(captchaData.aliyunScript || 'https://o.alicdn.com/captcha-frontend/aliyunCaptcha/AliyunCaptcha.js');
            var script = document.createElement('script');
            script.src = src;
            script.async = true;
            script.onload = function () {
                if (typeof window.initAliyunCaptcha === 'function') {
                    resolve(true);
                } else {
                    scriptLoader = null;
                    reject(new Error('initAliyunCaptcha is unavailable'));
                }
            };
            script.onerror = function () {
                scriptLoader = null;
                reject(new Error('Failed to load Aliyun captcha script'));
            };
            document.head.appendChild(script);
        });

        return scriptLoader;
    }

    function normalizeCaptchaVerifyParam(rawValue) {
        if (rawValue === null || typeof rawValue === 'undefined') {
            return '';
        }
        if (typeof rawValue === 'string') {
            return rawValue;
        }
        if (typeof rawValue === 'object') {
            if (rawValue.captchaVerifyParam) {
                return String(rawValue.captchaVerifyParam);
            }
            if (rawValue.CaptchaVerifyParam) {
                return String(rawValue.CaptchaVerifyParam);
            }
            try {
                return JSON.stringify(rawValue);
            } catch (error) {
                return String(rawValue);
            }
        }
        return String(rawValue);
    }

    function verifyCaptchaOnServer(captchaVerifyParam, sceneId, nonceGetter) {
        var captchaData = getCaptchaData();
        var globalData = getGlobalData();
        var nonce = '';
        var normalizedVerifyParam = normalizeCaptchaVerifyParam(captchaVerifyParam);
        if (typeof nonceGetter === 'function') {
            nonce = String(nonceGetter() || '');
        }
        if (!nonce) {
            nonce = String(captchaData.verifyNonce || globalData.authNonce || '');
        }

        var formData = new FormData();
        formData.append('action', String(captchaData.verifyAction || 'developer_starter_captcha_verify'));
        formData.append('nonce', nonce);
        formData.append('captcha_verify_param', normalizedVerifyParam);
        formData.append('scene', sceneId || '');

        return fetch(getAjaxUrl(), {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        }).then(function (response) {
            return response.json();
        });
    }

    function createAliyunMarkup(container, buttonText, waitingText) {
        instanceSeq += 1;
        var buttonId = 'ds-aliyun-captcha-button-' + instanceSeq;
        var elementId = 'ds-aliyun-captcha-element-' + instanceSeq;
        var stateKey = 'ds-aliyun-captcha-state-' + instanceSeq;

        container.classList.add('ds-captcha-provider-aliyun');
        container.setAttribute('data-ds-captcha-key', stateKey);
        container.innerHTML = ''
            + '<div class="ds-aliyun-captcha-wrap">'
            + '  <button type="button" id="' + buttonId + '" class="ds-aliyun-captcha-button">' + buttonText + '</button>'
            + '  <span class="ds-aliyun-captcha-status">' + waitingText + '</span>'
            + '  <div id="' + elementId + '" class="ds-aliyun-captcha-element" style="display:none;"></div>'
            + '</div>';

        return {
            buttonId: buttonId,
            elementId: elementId,
            stateKey: stateKey
        };
    }

    function attachAliyunCaptcha(container, options) {
        options = options || {};
        if (!container || getProvider() !== 'aliyun') {
            return Promise.resolve(false);
        }

        var captchaData = getCaptchaData();
        var prefix = String(options.prefix || captchaData.aliyunPrefix || '');
        var clientRegion = String(options.clientRegion || getAliyunClientRegion());
        var sceneId = String(options.sceneId || getScene(options.sceneType || 'auth'));
        var verifiedInput = options.verifiedInput || null;
        if (!verifiedInput && options.form) {
            verifiedInput = options.form.querySelector('.captcha-verified-input');
        }
        if (!verifiedInput) {
            var parentForm = container.closest('form');
            if (parentForm) {
                verifiedInput = parentForm.querySelector('.captcha-verified-input');
            }
        }
        if (!verifiedInput) {
            return Promise.resolve(false);
        }

        if (!prefix || !sceneId) {
            setStatus(container, options.configErrorText || getCaptchaText('configErrorText', '验证码配置不完整'));
            return Promise.resolve(false);
        }

        var waitingText = String(options.waitingText || getCaptchaText('waitingText', 'Click to complete verification'));
        var successText = String(options.successText || getCaptchaText('successText', 'Verification successful'));
        var failedText = String(options.failedText || getCaptchaText('failedText', 'Verification failed, please try again'));
        var verifyingText = String(options.verifyingText || getCaptchaText('verifyingText', 'Verifying...'));
        var buttonText = String(options.buttonText || getCaptchaText('buttonText', 'Click to verify'));
        var loadFailedText = String(options.loadFailedText || getCaptchaText('loadFailedText', '验证码脚本加载失败，请检查网络'));

        var nodes = createAliyunMarkup(container, buttonText, waitingText);
        verifiedInput.value = 'false';
        container.classList.remove('verified');
        var buttonNode = document.getElementById(nodes.buttonId);
        if (!buttonNode) {
            return Promise.resolve(false);
        }

        containerState[nodes.stateKey] = {
            waitingText: waitingText,
            userTriggered: false,
            instance: null
        };
        buttonNode.addEventListener('click', function () {
            if (containerState[nodes.stateKey]) {
                containerState[nodes.stateKey].userTriggered = true;
            }
        });

        return ensureAliyunScript(prefix, clientRegion).then(function () {
            return new Promise(function (resolve) {
                try {
                    var handleServerVerify = function (captchaVerifyParam) {
                        var normalizedParam = normalizeCaptchaVerifyParam(captchaVerifyParam);
                        if (!normalizedParam || normalizedParam === '{}' || normalizedParam === 'null' || normalizedParam === '[object Object]') {
                            setStatus(container, waitingText);
                            return Promise.resolve({
                                captchaResult: false,
                                bizResult: false
                            });
                        }

                        setStatus(container, verifyingText);
                        return verifyCaptchaOnServer(normalizedParam, sceneId, options.nonceGetter).then(function (payload) {
                            var ok = !!(payload && payload.success && payload.data && payload.data.token);
                            if (ok) {
                                verifiedInput.value = String(payload.data.token);
                                container.classList.add('verified');
                                setStatus(container, successText);
                                if (typeof options.onVerified === 'function') {
                                    options.onVerified(String(payload.data.token), payload);
                                }
                                resolve(true);
                                return {
                                    captchaResult: true,
                                    bizResult: true
                                };
                            }

                            verifiedInput.value = 'false';
                            container.classList.remove('verified');
                            setStatus(container, (payload && payload.data && payload.data.message) ? String(payload.data.message) : failedText);
                            return {
                                captchaResult: false,
                                bizResult: false
                            };
                        }).catch(function () {
                            verifiedInput.value = 'false';
                            container.classList.remove('verified');
                            setStatus(container, failedText);
                            return {
                                captchaResult: false,
                                bizResult: false
                            };
                        });
                    };

                    var compatOptions = {
                        prefix: prefix,
                        sceneId: sceneId,
                        SceneId: sceneId,
                        mode: options.mode || 'popup',
                        element: '#' + nodes.elementId,
                        button: '#' + nodes.buttonId,
                        success: function (captchaVerifyParam) {
                            return handleServerVerify(captchaVerifyParam);
                        },
                        captchaVerifyCallback: function (captchaVerifyParam) {
                            return handleServerVerify(captchaVerifyParam);
                        },
                        fail: function (result) {
                            var state = containerState[nodes.stateKey] || null;
                            if (!state || !state.userTriggered) {
                                return;
                            }
                            var code = '';
                            if (result && typeof result === 'object' && result.code) {
                                code = String(result.code);
                            }
                            setStatus(container, code ? (failedText + ' (' + code + ')') : failedText);
                        },
                        onError: function (errorInfo) {
                            var code = '';
                            if (errorInfo && typeof errorInfo === 'object' && errorInfo.code) {
                                code = String(errorInfo.code);
                            }
                            setStatus(container, code ? (failedText + ' (' + code + ')') : failedText);
                        },
                        getInstance: function (instance) {
                            if (containerState[nodes.stateKey]) {
                                containerState[nodes.stateKey].instance = instance || null;
                            }
                        },
                        onBizResultCallback: function () {}
                    };
                    var cfg = {
                        SceneId: sceneId,
                        sceneId: sceneId,
                        prefix: prefix,
                        captchaOptions: compatOptions
                    };
                    for (var key in compatOptions) {
                        if (Object.prototype.hasOwnProperty.call(compatOptions, key)) {
                            cfg[key] = compatOptions[key];
                        }
                    }

                    window.initAliyunCaptcha(cfg, function (instance) {
                        if (containerState[nodes.stateKey]) {
                            containerState[nodes.stateKey].instance = instance || null;
                        }
                        if (instance && typeof instance.init === 'function') {
                            instance.init();
                        }
                    });
                    resolve(true);
                } catch (error) {
                    if (window.console && typeof window.console.error === 'function') {
                        window.console.error('[Captcha] initAliyunCaptcha error:', error);
                    }
                    verifiedInput.value = 'false';
                    container.classList.remove('verified');
                    setStatus(container, failedText);
                    resolve(false);
                }
            });
        }).catch(function (error) {
            if (window.console && typeof window.console.error === 'function') {
                window.console.error('[Captcha] load Aliyun script failed:', error);
            }
            setStatus(container, loadFailedText);
            return false;
        });
    }

    function resetContainer(container, verifiedInput) {
        if (!container) {
            return;
        }
        if (verifiedInput) {
            verifiedInput.value = 'false';
        }
        container.classList.remove('verified');
        var stateKey = container.getAttribute('data-ds-captcha-key') || '';
        var state = stateKey ? (containerState[stateKey] || null) : null;
        if (state) {
            state.userTriggered = false;
        }
        setStatus(container, state && state.waitingText ? state.waitingText : getCaptchaText('waitingText', 'Click to complete verification'));
    }

    function resetForm(form) {
        if (!form) {
            return;
        }
        var verifiedInputs = form.querySelectorAll('.captcha-verified-input');
        verifiedInputs.forEach(function (input) {
            input.value = 'false';
        });
        var containers = form.querySelectorAll('.slider-captcha.ds-captcha-provider-aliyun');
        containers.forEach(function (container) {
            resetContainer(container, null);
        });
    }

    window.DSProviderCaptcha = {
        isAliyunProvider: function () {
            return getProvider() === 'aliyun';
        },
        getProvider: getProvider,
        getScene: getScene,
        attachAliyunCaptcha: attachAliyunCaptcha,
        resetContainer: resetContainer,
        resetForm: resetForm
    };
})(window, document);
