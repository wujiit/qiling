/**
 * 启灵主题公告系统脚本
 * 
 * 处理公告显示/隐藏、频率控制、用户偏好
 * 支持弹窗类型和底部横幅类型
 */

(function () {
    'use strict';

    function initAnnouncement() {
        // 检查底部横幅（独立处理）
        var bottomBanner = document.getElementById('ds-bottom-banner');
        if (bottomBanner) {
            initBottomBanner(bottomBanner);
            return;
        }

        // 检查弹窗公告
        var announcement = document.getElementById('ds-announcement');
        if (!announcement) return;

        var config = window.dsAnnouncement || {};
        var announcementId = config.announcementId || 'default';
        var frequency = config.frequency || 'always';
        var allowDismiss = config.allowDismiss !== false;

        // 检测是否为底部横幅类型（旧版兼容）
        var isBottomBanner = announcement.classList.contains('announcement-bottom_banner');

        // Cookie 工具函数
        var Cookie = {
            set: function (name, value, days) {
                var expires = '';
                if (days) {
                    var date = new Date();
                    date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
                    expires = '; expires=' + date.toUTCString();
                }
                document.cookie = name + '=' + encodeURIComponent(value) + expires + '; path=/';
            },
            get: function (name) {
                var nameEQ = name + '=';
                var ca = document.cookie.split(';');
                for (var i = 0; i < ca.length; i++) {
                    var c = ca[i].trim();
                    if (c.indexOf(nameEQ) === 0) {
                        return decodeURIComponent(c.substring(nameEQ.length));
                    }
                }
                return null;
            },
            delete: function (name) {
                this.set(name, '', -1);
            }
        };

        // 检查是否应该显示公告
        function shouldShow() {
            var cookieName = 'ds_ann_' + announcementId;
            var dismissed = Cookie.get(cookieName);

            if (frequency === 'once_day') {
                // 每天一次模式：检查今天是否已显示
                if (dismissed) {
                    var today = new Date().toDateString();
                    if (dismissed === today) {
                        return false;
                    }
                }
            } else if (frequency === 'always') {
                // 每次访问都显示，但检查用户是否选择了"今日不再显示"
                if (dismissed) {
                    var today = new Date().toDateString();
                    if (dismissed === today) {
                        return false;
                    }
                }
            }

            return true;
        }

        // 显示公告
        function showAnnouncement() {
            if (isBottomBanner) {
                // 底部横幅：不禁止滚动
                announcement.style.display = 'block';
            } else {
                // 弹窗类型
                announcement.style.display = 'flex';
                document.body.style.overflow = 'hidden';
            }

            // 记录显示（每天一次模式）
            if (frequency === 'once_day') {
                var cookieName = 'ds_ann_' + announcementId;
                var today = new Date().toDateString();
                Cookie.set(cookieName, today, 1);
            }
        }

        // 隐藏公告
        function hideAnnouncement() {
            announcement.style.display = 'none';
            if (!isBottomBanner) {
                document.body.style.overflow = '';
            }
        }

        // 关闭公告
        function closeAnnouncement() {
            var todayDismiss = document.getElementById('announcement-today-dismiss');

            // 检查是否勾选了"今日不再显示"
            if (todayDismiss && todayDismiss.checked) {
                var cookieName = 'ds_ann_' + announcementId;
                var today = new Date().toDateString();
                Cookie.set(cookieName, today, 1);
            }

            // 底部横幅关闭时：设置cookie记住今天不再显示
            if (isBottomBanner) {
                var cookieName = 'ds_ann_' + announcementId;
                var today = new Date().toDateString();
                Cookie.set(cookieName, today, 1);

                // 滑出动画
                announcement.style.animation = 'slideDownBanner 0.3s ease forwards';
                setTimeout(function () {
                    hideAnnouncement();
                }, 300);
                return;
            }

            // 弹窗类型：动画关闭
            var modal = announcement.querySelector('.announcement-modal');
            if (modal) {
                modal.style.animation = 'announcementSlideOut 0.3s ease forwards';
            }
            var overlay = announcement.querySelector('.announcement-overlay');
            if (overlay) {
                overlay.style.opacity = '0';
            }

            setTimeout(function () {
                hideAnnouncement();
            }, 300);
        }

        // 添加关闭动画
        var style = document.createElement('style');
        style.textContent = '@keyframes announcementSlideOut { to { opacity: 0; transform: scale(0.9) translateY(20px); } } @keyframes slideDownBanner { to { transform: translateY(100%); } }';
        document.head.appendChild(style);

        // 绑定关闭按钮事件 - 弹窗类型
        var closeBtn = announcement.querySelector('.announcement-close');
        if (closeBtn) {
            closeBtn.addEventListener('click', closeAnnouncement);
        }

        // 绑定关闭按钮事件 - 底部横幅类型
        var bottomCloseBtn = announcement.querySelector('.bottom-banner-close');
        if (bottomCloseBtn) {
            bottomCloseBtn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                closeAnnouncement();
            });
        }

        // 点击遮罩关闭（仅弹窗类型）
        var overlay = announcement.querySelector('.announcement-overlay');
        if (overlay) {
            overlay.addEventListener('click', closeAnnouncement);
        }

        // ESC 键关闭
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && announcement.style.display !== 'none') {
                closeAnnouncement();
            }
        });

        // 阻止模态框内点击冒泡（仅弹窗类型）
        var modal = announcement.querySelector('.announcement-modal');
        if (modal) {
            modal.addEventListener('click', function (e) {
                e.stopPropagation();
            });
        }

        // 初始化：检查并显示
        if (shouldShow()) {
            // 延迟显示，等待页面加载完成
            setTimeout(showAnnouncement, 500);
        }
    }

    // 兼容懒加载场景：脚本可能在 DOMContentLoaded 之后才注入
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAnnouncement);
    } else {
        initAnnouncement();
    }

    /**
     * 初始化底部横幅（独立逻辑）
     */
    function initBottomBanner(banner) {
        var config = window.dsAnnouncement || {};
        var announcementId = config.announcementId || banner.getAttribute('data-id') || 'default';
        var frequency = config.frequency || 'always';

        // Cookie 工具
        var Cookie = {
            set: function (name, value, days) {
                var expires = '';
                if (days) {
                    var date = new Date();
                    date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
                    expires = '; expires=' + date.toUTCString();
                }
                document.cookie = name + '=' + encodeURIComponent(value) + expires + '; path=/';
            },
            get: function (name) {
                var nameEQ = name + '=';
                var ca = document.cookie.split(';');
                for (var i = 0; i < ca.length; i++) {
                    var c = ca[i].trim();
                    if (c.indexOf(nameEQ) === 0) {
                        return decodeURIComponent(c.substring(nameEQ.length));
                    }
                }
                return null;
            }
        };

        // 检查是否应该显示
        function shouldShow() {
            var cookieName = 'ds_ann_' + announcementId;
            var dismissed = Cookie.get(cookieName);
            if (dismissed) {
                var today = new Date().toDateString();
                if (dismissed === today) {
                    return false;
                }
            }
            return true;
        }

        // 显示横幅
        function show() {
            banner.style.display = 'block';
        }

        // 关闭横幅
        function close() {
            var cookieName = 'ds_ann_' + announcementId;
            var today = new Date().toDateString();
            Cookie.set(cookieName, today, 1);

            banner.style.animation = 'dsBottomBannerSlideDown 0.3s ease forwards';
            setTimeout(function () {
                banner.style.display = 'none';
            }, 300);
        }

        // 添加关闭动画
        var style = document.createElement('style');
        style.textContent = '@keyframes dsBottomBannerSlideDown { to { transform: translateY(100%); } }';
        document.head.appendChild(style);

        // 绑定关闭按钮
        var closeBtn = banner.querySelector('.ds-bottom-banner-close');
        if (closeBtn) {
            closeBtn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                close();
            });
        }

        // 初始化显示
        if (shouldShow()) {
            setTimeout(show, 500);
        }
    }
})();
