/**
 * 启灵主题搜索验证码脚本
 *
 * 负责搜索提交前的人机验证弹层与验证流程。
 */

(function($) {
    'use strict';

    if (!$ || !$.fn) {
        if (typeof window !== 'undefined') {
            window.__dsSearchCaptchaInitialized = false;
        }
        return;
    }

    var i18n = (typeof developerStarterData !== 'undefined' && developerStarterData.searchCaptcha) ? developerStarterData.searchCaptcha : {};
    var text = function(key, fallback) {
        if (i18n && Object.prototype.hasOwnProperty.call(i18n, key)) {
            return i18n[key];
        }
        return fallback;
    };
    var waitSeconds = parseInt((i18n && i18n.waitSeconds) ? i18n.waitSeconds : 0, 10);
    if (isNaN(waitSeconds) || waitSeconds < 0) {
        waitSeconds = 0;
    }
    var waitTextTemplate = String(text('waitText', '请稍候 %d 秒'));

    function formatWaitText(seconds) {
        return waitTextTemplate.replace('%d', seconds);
    }

    function getProviderApi() {
        if (typeof window === 'undefined') {
            return null;
        }
        return window.DSProviderCaptcha || null;
    }

    function isAliyunProvider() {
        var api = getProviderApi();
        return !!(api && typeof api.isAliyunProvider === 'function' && api.isAliyunProvider());
    }

    function ensureProviderReady() {
        var globalData = (typeof developerStarterData !== 'undefined') ? developerStarterData : {};
        var captchaData = globalData.captcha || {};
        var provider = String((i18n && i18n.provider) || captchaData.provider || 'theme');

        if (provider !== 'aliyun') {
            return Promise.resolve(false);
        }
        if (isAliyunProvider()) {
            return Promise.resolve(true);
        }

        if (typeof window.DSLoadCaptchaProvider === 'function') {
            return window.DSLoadCaptchaProvider('search').catch(function () {
                return false;
            });
        }

        var providerScript = String(globalData.captchaProviderScript || '');
        if (!providerScript || typeof window.DSLoadScriptOnce !== 'function') {
            return Promise.resolve(false);
        }

        return window.DSLoadScriptOnce(providerScript, 'data-ds-captcha-provider-script')
            .then(function () {
                return isAliyunProvider();
            })
            .catch(function () {
                return false;
            });
    }

    var DeveloperStarterSearchCaptcha = {
        // 当前正在尝试提交的表单
        currentForm: null,
        isVerified: false,
        currentToken: '',
        waitTimer: null,
        waitRemaining: 0,

        init: function() {
            if (window.__dsSearchCaptchaInitialized) {
                return;
            }
            window.__dsSearchCaptchaInitialized = true;
            this.bindSearchForms();
            this.createModal();
            this.bindEvents();
        },

        bindSearchForms: function() {
            var self = this;

            // 使用原生事件捕获，以确保在所有其他事件处理程序之前拦截
            document.addEventListener('submit', function(e) {
                var form = e.target;
                var needsCaptcha = false;

                if (form.matches && form.matches('.ds-enable-captcha')) {
                    needsCaptcha = true;
                } else if (form.matches && (form.matches('form[role="search"]') || form.matches('form.search-form'))) {
                    needsCaptcha = true;
                } else if ($(form).closest('.widget_search').length > 0) {
                    needsCaptcha = true;
                }

                if (needsCaptcha && !self.isVerified) {
                    e.preventDefault();
                    e.stopPropagation();
                    e.stopImmediatePropagation();

                    self.currentForm = $(form);
                    self.showModal();
                }
            }, true);
        },

        createModal: function() {
            if ($('.ds-search-captcha-overlay').length) {
                return;
            }

            var captchaBody = '';
            if (isAliyunProvider()) {
                captchaBody = ''
                    + '<div class="ds-search-aliyun-wrap">'
                    + '  <div class="ds-search-aliyun-container slider-captcha" id="ds-search-aliyun-captcha"></div>'
                    + '  <input type="hidden" id="ds-search-aliyun-verified" value="false" class="captcha-verified-input" />'
                    + '</div>';
            } else {
                captchaBody = ''
                    + '<div class="ds-search-slider-container">'
                    + '  <div class="ds-search-slider-bg"></div>'
                    + '  <div class="ds-search-slider-text">' + text('dragText', '按住滑块 拖动到最右侧') + '</div>'
                    + '  <div class="ds-search-slider-handler"></div>'
                    + '</div>';
            }

            var html = ''
                + '<div class="ds-search-captcha-overlay">'
                + '  <div class="ds-search-captcha-modal">'
                + '    <button class="ds-search-captcha-close" type="button" aria-label="' + text('closeLabel', '关闭') + '"></button>'
                + '    <div class="ds-search-captcha-title">' + text('title', '安全验证') + '</div>'
                + captchaBody
                + '  </div>'
                + '</div>';

            $('body').append(html);
        },

        bindEvents: function() {
            var self = this;

            $(document).on('click', '.ds-search-captcha-close, .ds-search-captcha-overlay', function(e) {
                if (e.target === this) {
                    self.hideModal();
                }
            });

            if (isAliyunProvider()) {
                return;
            }

            var $slider = $('.ds-search-slider-container');
            var $handler = $('.ds-search-slider-handler');
            var $bg = $('.ds-search-slider-bg');
            var isDragging = false;
            var startX = 0;
            var maxMove = 0;

            $handler.on('mousedown touchstart', function(e) {
                if ($slider.hasClass('success')) return;
                if ($slider.hasClass('is-waiting')) return;

                isDragging = true;
                var pageX = e.type === 'touchstart' ? e.originalEvent.touches[0].pageX : e.pageX;
                startX = pageX - parseInt($handler.css('left'), 10);
                maxMove = $slider.width() - $handler.width() - 4;

                $('body').css('user-select', 'none');
            });

            $(document).on('mousemove touchmove', function(e) {
                if (!isDragging) return;

                var pageX = e.type === 'touchmove' ? e.originalEvent.touches[0].pageX : e.pageX;
                var moveX = pageX - startX;

                if (moveX < 0) moveX = 0;
                if (moveX > maxMove) moveX = maxMove;

                $handler.css('left', moveX + 'px');
                $bg.css('width', moveX + 'px');
            });

            $(document).on('mouseup touchend', function() {
                if (!isDragging) return;

                isDragging = false;
                $('body').css('user-select', '');

                var currentLeft = parseInt($handler.css('left'), 10);
                if (maxMove > 10 && currentLeft >= maxMove - 2) {
                    self.verifySuccess();
                } else {
                    $handler.animate({ left: 0 }, 200);
                    $bg.animate({ width: 0 }, 200);
                }
            });
        },

        mountAliyunCaptcha: function() {
            if (!isAliyunProvider()) {
                return;
            }

            var self = this;
            var api = getProviderApi();
            var container = document.getElementById('ds-search-aliyun-captcha');
            var verifiedInput = document.getElementById('ds-search-aliyun-verified');

            if (!container || !verifiedInput || !api || typeof api.attachAliyunCaptcha !== 'function') {
                return;
            }

            api.attachAliyunCaptcha(container, {
                verifiedInput: verifiedInput,
                sceneType: 'search',
                waitingText: text('aliyunWaitingText', '点击完成验证'),
                buttonText: text('aliyunButtonText', '点击验证'),
                verifyingText: text('aliyunVerifyingText', '正在验证...'),
                successText: text('successText', '验证通过'),
                failedText: text('aliyunFailedText', '验证失败，请重试'),
                configErrorText: text('aliyunConfigErrorText', '验证码配置不完整，请联系管理员'),
                onVerified: function(token) {
                    self.currentToken = token || '';
                    self.verifySuccess();
                }
            });
        },

        verifySuccess: function() {
            var self = this;

            if (!isAliyunProvider()) {
                var $container = $('.ds-search-slider-container');
                var $text = $('.ds-search-slider-text');
                $container.addClass('success');
                $text.text(text('successText', '验证通过'));
            }

            setTimeout(function() {
                self.isVerified = true;
                self.hideModal();

                if (self.currentForm) {
                    if (self.currentForm.hasClass('ds-enable-captcha') || self.currentForm.hasClass('contact-form') || self.currentForm.attr('id') === 'ds-contact-form') {
                        if (self.currentForm[0]) {
                            var event = new Event('submit', {
                                bubbles: true,
                                cancelable: true
                            });
                            self.currentForm[0].dispatchEvent(event);
                        }
                    } else {
                        self.currentForm.off('submit');
                        self.currentForm.submit();
                    }

                    setTimeout(function() {
                        self.isVerified = false;
                        self.currentForm = null;
                        self.currentToken = '';
                        if (isAliyunProvider()) {
                            self.resetAliyun();
                        } else {
                            self.resetSlider();
                        }
                    }, 1000);
                }
            }, isAliyunProvider() ? 0 : 600);
        },

        showModal: function() {
            $('.ds-search-captcha-overlay').addClass('active');

            if (isAliyunProvider()) {
                this.mountAliyunCaptcha();
            } else {
                this.resetSlider();
                this.startWaitCountdown();
            }
        },

        hideModal: function() {
            $('.ds-search-captcha-overlay').removeClass('active');
            if (!isAliyunProvider()) {
                this.clearWaitTimer();
            }
        },

        resetAliyun: function() {
            var api = getProviderApi();
            var container = document.getElementById('ds-search-aliyun-captcha');
            var verifiedInput = document.getElementById('ds-search-aliyun-verified');

            if (api && typeof api.resetContainer === 'function' && container) {
                api.resetContainer(container, verifiedInput || null);
            } else if (verifiedInput) {
                verifiedInput.value = 'false';
            }
        },

        resetSlider: function() {
            var $container = $('.ds-search-slider-container');
            var $handler = $('.ds-search-slider-handler');
            var $bg = $('.ds-search-slider-bg');
            var $text = $('.ds-search-slider-text');

            $container.removeClass('success');
            this.clearWaitTimer();
            $handler.css('left', '2px');
            $bg.css('width', '0');
            $text.text(text('dragText', '按住滑块 拖动到最右侧'));
        },

        clearWaitTimer: function() {
            if (this.waitTimer) {
                clearInterval(this.waitTimer);
                this.waitTimer = null;
            }
            this.waitRemaining = 0;
            var $container = $('.ds-search-slider-container');
            var $handler = $('.ds-search-slider-handler');
            $container.removeClass('is-waiting');
            $handler.css('pointer-events', '');
        },

        startWaitCountdown: function() {
            var self = this;
            if (waitSeconds <= 0) {
                return;
            }

            var $container = $('.ds-search-slider-container');
            var $handler = $('.ds-search-slider-handler');
            var $text = $('.ds-search-slider-text');

            self.waitRemaining = waitSeconds;
            $container.addClass('is-waiting');
            $handler.css('pointer-events', 'none');
            $text.text(formatWaitText(self.waitRemaining));

            if (self.waitTimer) {
                clearInterval(self.waitTimer);
            }

            self.waitTimer = setInterval(function() {
                self.waitRemaining -= 1;
                if (self.waitRemaining <= 0) {
                    self.clearWaitTimer();
                    $text.text(text('dragText', '按住滑块 拖动到最右侧'));
                    return;
                }
                $text.text(formatWaitText(self.waitRemaining));
            }, 1000);
        }
    };

    $(document).ready(function() {
        if (typeof developerStarterData !== 'undefined') {
            ensureProviderReady().then(function() {
                DeveloperStarterSearchCaptcha.init();
            });
        }
    });

})(window.jQuery);
