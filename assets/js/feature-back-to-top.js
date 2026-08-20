/**
 * Back to top runtime
 *
 * Split from main.js so page-specific interactions can load only when needed.
 */
(function (window, document) {
    'use strict';

    function getGlobalData() {
        return window.developerStarterData || {};
    }

    function onReady(callback) {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', callback, { once: true });
            return;
        }

        callback();
    }

    onReady(function () {
    // ===== Back to Top =====
    var backToTop = document.getElementById('back-to-top');
    if (backToTop) {
        window.addEventListener('scroll', function () {
            if (window.scrollY > 300) {
                backToTop.style.display = 'flex';
            } else {
                backToTop.style.display = 'none';
            }
        });

        backToTop.addEventListener('click', function () {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    // Float widget hover is now handled by CSS
    });
})(window, document);