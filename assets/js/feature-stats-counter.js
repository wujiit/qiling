/**
 * Stats counter runtime
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
    // ===== Stats Counter Animation =====
    var statNumbers = document.querySelectorAll('.stat-number');
    if (statNumbers.length > 0 && 'IntersectionObserver' in window) {
        var animateCounter = function (el) {
            var text = el.textContent.replace(/[^0-9]/g, '');
            var target = parseInt(text) || 0;
            if (target === 0) return;

            var duration = 2000;
            var startTime = null;

            function animate(timestamp) {
                if (!startTime) startTime = timestamp;
                var progress = Math.min((timestamp - startTime) / duration, 1);
                var current = Math.floor(progress * target);
                el.textContent = current + '+';

                if (progress < 1) {
                    requestAnimationFrame(animate);
                } else {
                    el.textContent = target + '+';
                }
            }

            requestAnimationFrame(animate);
        };

        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    animateCounter(entry.target);
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.5 });

        statNumbers.forEach(function (el) {
            observer.observe(el);
        });
    }
    });
})(window, document);