/**
 * 启灵主题头部登录状态脚本
 *
 * 负责头部用户状态同步、登录态展示与下拉交互。
 */

(function () {
    'use strict';

    function initHeaderAuth() {
        if (window.__dsHeaderAuthInitialized) {
            return;
        }
        window.__dsHeaderAuthInitialized = true;

        function isAuthTemplatePage() {
            if (!document.body) {
                return false;
            }
            return document.body.classList.contains('page-template-template-login')
                || document.body.classList.contains('page-template-template-register')
                || document.body.classList.contains('page-template-template-forgot-password');
        }

        function shouldProbeAuthStatus() {
            if (document.getElementById('header-auth-wrapper')) {
                return true;
            }
            return isAuthTemplatePage();
        }

        function requestUserStatus() {
            var ajaxUrl = typeof developerStarterData !== 'undefined' ? developerStarterData.ajaxUrl : '/wp-admin/admin-ajax.php';
            var userStatusNonce = (typeof developerStarterData !== 'undefined' && developerStarterData.userStatusNonce) ? developerStarterData.userStatusNonce : '';
            var ts = Date.now();

            return fetch(ajaxUrl + '?_=' + ts, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'Cache-Control': 'no-cache, no-store, must-revalidate',
                    'Pragma': 'no-cache'
                },
                body: 'action=developer_starter_user_status&_nocache=' + ts + '&nonce=' + encodeURIComponent(userStatusNonce),
                credentials: 'same-origin',
                cache: 'no-store'
            })
                .then(function (response) { return response.json(); })
                .catch(function () { return null; });
        }

        function resolveUserStatusResponse(data) {
            if (data && data.success) {
                window.DS_AUTH_STATUS = data.data;
                return data.data;
            }
            return null;
        }

        function getUserStatus() {
            if (window.DS_AUTH_STATUS) {
                return Promise.resolve(window.DS_AUTH_STATUS);
            }
            if (window.DS_AUTH_STATUS_PROMISE) {
                return window.DS_AUTH_STATUS_PROMISE
                    .then(resolveUserStatusResponse)
                    .catch(function () { return null; })
                    .then(function (userData) {
                        if (userData) {
                            return userData;
                        }

                        window.DS_AUTH_STATUS_PROMISE = requestUserStatus();
                        return window.DS_AUTH_STATUS_PROMISE.then(resolveUserStatusResponse);
                    });
            }

            window.DS_AUTH_STATUS_PROMISE = requestUserStatus();
            return window.DS_AUTH_STATUS_PROMISE.then(resolveUserStatusResponse);
        }

        function updateHeaderAuth(userData) {
            var authWrapper = document.getElementById('header-auth-wrapper');
            if (!authWrapper || !userData) {
                return;
            }
            var loginArea = document.getElementById('header-login-area');
            var userArea = document.getElementById('header-user-area');
            if (!loginArea || !userArea) {
                authWrapper.setAttribute('data-auth-ready', 'true');
                return;
            }

            if (userData.logged_in) {
                loginArea.style.display = 'none';
                userArea.style.display = '';

                var userAvatar = document.getElementById('header-user-avatar');
                var dropdownAvatar = document.getElementById('dropdown-user-avatar');
                var userName = document.getElementById('dropdown-user-name');
                var userEmail = document.getElementById('dropdown-user-email');
                var accountLink = document.getElementById('dropdown-account-link');
                var adminLink = document.getElementById('dropdown-admin-link');
                var logoutLink = document.getElementById('dropdown-logout-link');
                var userToggle = document.getElementById('header-user-toggle');

                if (userAvatar) {
                    userAvatar.src = userData.avatar_32;
                    userAvatar.alt = userData.display_name;
                }
                if (dropdownAvatar) {
                    dropdownAvatar.src = userData.avatar_48;
                    dropdownAvatar.alt = userData.display_name;
                }

                if (userToggle) {
                    var toggleImgs = userToggle.querySelectorAll('img');
                    toggleImgs.forEach(function (img) {
                        if (!img.src || img.src === '' || img.src === window.location.href) {
                            img.src = userData.avatar_32;
                            img.alt = userData.display_name;
                        }
                    });
                }

                var dropdown = document.getElementById('user-dropdown');
                if (dropdown) {
                    var dropdownImgs = dropdown.querySelectorAll('.dropdown-header img');
                    dropdownImgs.forEach(function (img) {
                        if (!img.src || img.src === '' || img.src === window.location.href) {
                            img.src = userData.avatar_48;
                            img.alt = userData.display_name;
                        }
                    });
                }

                if (userName) {
                    userName.textContent = userData.display_name;
                }
                if (userEmail) {
                    userEmail.textContent = userData.email || '';
                }
                if (accountLink && userData.account_url) {
                    accountLink.href = userData.account_url;
                }
                if (userToggle && userData.account_url) {
                    userToggle.href = userData.account_url;
                }
                if (adminLink) {
                    if (userData.can_access_admin && userData.admin_url) {
                        adminLink.href = userData.admin_url;
                        adminLink.style.display = '';
                    } else {
                        adminLink.style.display = 'none';
                    }
                }
                if (logoutLink && userData.logout_url) {
                    logoutLink.href = userData.logout_url;
                }
            } else {
                loginArea.style.display = '';
                userArea.style.display = 'none';
            }

            authWrapper.setAttribute('data-auth-ready', 'true');
        }

        function applyGlobalAuthState(userData) {
            if (!userData) {
                return false;
            }

            if (userData.logged_in) {
                try {
                    document.cookie = 'ds_logged_in=1; path=/; max-age=21600; SameSite=Lax';
                } catch (e) {
                    // ignore
                }
                document.documentElement.classList.add('logged-in');
                if (document.body) {
                    document.body.classList.add('logged-in');
                }
            } else {
                try {
                    document.cookie = 'ds_logged_in=; path=/; expires=Thu, 01 Jan 1970 00:00:00 GMT; SameSite=Lax';
                } catch (e) {
                    // ignore
                }
                document.documentElement.classList.remove('logged-in');
                if (document.body) {
                    document.body.classList.remove('logged-in');
                }
            }

            if (isAuthTemplatePage() && userData.logged_in) {
                var redirectUrl = userData.account_url
                    || (typeof developerStarterData !== 'undefined' ? developerStarterData.homeUrl : '')
                    || '/';
                window.location.replace(redirectUrl);
                return true;
            }

            return false;
        }

        if (!shouldProbeAuthStatus()) {
            return;
        }

        getUserStatus()
            .then(function (userData) {
                if (!userData) {
                    return;
                }

                var interrupted = applyGlobalAuthState(userData);
                if (interrupted) {
                    return;
                }

                updateHeaderAuth(userData);

                try {
                    window.dispatchEvent(new CustomEvent('developer_starter_auth_synced', {
                        detail: userData
                    }));
                } catch (e) {
                    // ignore
                }
            })
            .catch(function () {
                var authWrapper = document.getElementById('header-auth-wrapper');
                if (authWrapper) {
                    authWrapper.setAttribute('data-auth-ready', 'true');
                }
            });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initHeaderAuth);
    } else {
        initHeaderAuth();
    }
})();
