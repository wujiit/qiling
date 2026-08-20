/**
 * Lazy embed runtime
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
    // ===== Third-party Video Embed (Lazy Inject) =====
    (function initLazyEmbedVideos() {
        var lazyEmbeds = document.querySelectorAll('.ds-lazy-embed[data-src]');
        if (lazyEmbeds.length === 0) return;

        var appendAutoplay = function (url) {
            if (!url) return '';
            if (/[?&]autoplay=/.test(url)) return url;
            return url + (url.indexOf('?') === -1 ? '?' : '&') + 'autoplay=1';
        };

        var activateEmbed = function (container) {
            if (!container || container.dataset.loaded === '1') return;
            var src = container.getAttribute('data-src') || '';
            if (!src) return;

            if (container.getAttribute('data-autoplay') === '1') {
                src = appendAutoplay(src);
            }

            var iframe = document.createElement('iframe');
            iframe.className = 'ds-lazy-embed-frame';
            iframe.src = src;
            iframe.allowFullscreen = true;
            iframe.loading = 'lazy';
            iframe.setAttribute('allow', 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share');
            iframe.setAttribute('referrerpolicy', 'strict-origin-when-cross-origin');

            container.innerHTML = '';
            container.appendChild(iframe);
            container.dataset.loaded = '1';
            container.classList.add('is-loaded');
        };

        document.addEventListener('click', function (e) {
            var clickTarget = e.target;
            var trigger = clickTarget && clickTarget.closest ? clickTarget.closest('.ds-lazy-embed-trigger') : null;
            if (!trigger) return;

            var container = trigger.closest('.ds-lazy-embed');
            if (!container) return;

            e.preventDefault();
            activateEmbed(container);
        }, true);

        document.addEventListener('keydown', function (e) {
            if (e.key !== 'Enter' && e.key !== ' ') return;
            var trigger = e.target && e.target.closest ? e.target.closest('.ds-lazy-embed-trigger') : null;
            if (!trigger) return;

            var container = trigger.closest('.ds-lazy-embed');
            if (!container) return;

            e.preventDefault();
            activateEmbed(container);
        }, true);
    })();
    });
})(window, document);