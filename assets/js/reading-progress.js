/**
 * Article reading progress indicator.
 *
 * Loaded only when the article reading progress option is enabled.
 */
(function () {
    'use strict';

    var config = window.qilingReadingProgressConfig || {};
    var targetSelector = config.targetSelector || '.single-post .entry-content';
    var offset = Number(config.offset || 0);

    function clamp(value, min, max) {
        return Math.min(max, Math.max(min, value));
    }

    function getScrollTop() {
        return window.pageYOffset
            || document.documentElement.scrollTop
            || document.body.scrollTop
            || 0;
    }

    function getDocumentScrollEnd() {
        var doc = document.documentElement;
        var body = document.body;
        var scrollHeight = Math.max(
            doc ? doc.scrollHeight : 0,
            body ? body.scrollHeight : 0,
            doc ? doc.offsetHeight : 0,
            body ? body.offsetHeight : 0
        );

        return Math.max(0, scrollHeight - window.innerHeight);
    }

    function initReadingProgress() {
        var root = document.getElementById('qiling-reading-progress');
        if (!root) {
            return;
        }

        var fill = root.querySelector('.qiling-reading-progress__fill');
        var value = root.querySelector('.qiling-reading-progress__value');
        var target = document.querySelector(targetSelector)
            || document.querySelector('.single-post')
            || document.body;
        var ticking = false;

        function calculateProgress() {
            if (!target) {
                return 0;
            }

            var scrollTop = getScrollTop();
            var rect = target.getBoundingClientRect();
            var targetTop = rect.top + scrollTop;
            var targetHeight = Math.max(target.scrollHeight, target.offsetHeight, rect.height || 0);
            var start = Math.max(0, targetTop - offset);
            var end = targetTop + targetHeight - window.innerHeight;

            if (!isFinite(end) || end <= start) {
                start = 0;
                end = getDocumentScrollEnd();
            }

            if (end <= start) {
                return 100;
            }

            return clamp(((scrollTop - start) / (end - start)) * 100, 0, 100);
        }

        function updateProgress() {
            var progress = calculateProgress();
            var percent = progress.toFixed(2) + '%';
            var rounded = String(Math.round(progress));

            root.style.setProperty('--qiling-reading-progress-percent', percent);
            root.setAttribute('aria-valuenow', rounded);
            root.classList.toggle('is-visible', progress > 1);
            root.classList.toggle('is-complete', progress >= 99.5);

            if (fill) {
                fill.style.width = percent;
            }

            if (value) {
                value.textContent = rounded + '%';
            }
        }

        function requestUpdate() {
            if (ticking) {
                return;
            }

            ticking = true;
            window.requestAnimationFrame(function () {
                updateProgress();
                ticking = false;
            });
        }

        window.addEventListener('scroll', requestUpdate, { passive: true });
        window.addEventListener('resize', requestUpdate);
        window.addEventListener('orientationchange', requestUpdate);
        window.addEventListener('load', requestUpdate);

        if ('ResizeObserver' in window && target) {
            var resizeObserver = new ResizeObserver(requestUpdate);
            resizeObserver.observe(target);
        }

        requestUpdate();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initReadingProgress);
    } else {
        initReadingProgress();
    }
})();
