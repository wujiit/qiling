(function () {
    'use strict';

    function openLoginModal(loginUrl) {
        if (typeof window.developerStarterShowLoginModal === 'function') {
            window.developerStarterShowLoginModal('login');
            return true;
        }

        if (typeof window.dsOpenLoginModal === 'function') {
            window.dsOpenLoginModal();
            return true;
        }

        var headerLoginBtn = document.getElementById('header-login-toggle');
        if (headerLoginBtn) {
            headerLoginBtn.click();
            return true;
        }

        if (loginUrl) {
            window.location.href = loginUrl;
            return true;
        }

        return false;
    }

    document.addEventListener('click', function (event) {
        var target = event.target && event.target.closest ? event.target.closest('.comment-reply-login, .js-comment-login') : null;
        if (!target) {
            return;
        }

        var loginUrl = target.getAttribute('data-login-url') || target.getAttribute('href') || '';
        if (openLoginModal(loginUrl)) {
            event.preventDefault();
        }
    });
}());
