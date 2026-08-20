/**
 * Progressive placeholder behavior for native lazy-loaded images.
 *
 * Loaded only when image lazy loading and progressive placeholders are enabled.
 */
(function () {
    'use strict';

    var config = window.qilingLazyImagePlaceholderConfig || {};
    var selector = config.selector || 'img[loading="lazy"], img.qiling-progressive-image';
    var enhancedAttr = 'data-qiling-progressive-bound';
    var progressiveClass = 'qiling-progressive-image';
    var pendingClass = 'qiling-image-pending';
    var loadedClass = 'qiling-image-loaded';
    var errorClass = 'qiling-image-error';

    function isSmallUiImage(img) {
        var width = parseInt(img.getAttribute('width') || '', 10);
        var height = parseInt(img.getAttribute('height') || '', 10);

        if (width > 0 && height > 0) {
            return width < 48 || height < 48;
        }

        return !!img.closest('.site-header, .mobile-menu, .language-switcher, .qs-lang-switcher, .avatar');
    }

    function markLoaded(img) {
        img.classList.remove(pendingClass);
        img.classList.remove(errorClass);
        img.classList.add(loadedClass);
        img.setAttribute('data-qiling-image-state', 'loaded');
    }

    function markError(img) {
        img.classList.remove(pendingClass);
        img.classList.remove(loadedClass);
        img.classList.add(errorClass);
        img.setAttribute('data-qiling-image-state', 'error');
    }

    function prepareImage(img) {
        if (!img || img.nodeType !== 1 || img.getAttribute(enhancedAttr) === '1') {
            return;
        }

        if (isSmallUiImage(img)) {
            return;
        }

        img.setAttribute(enhancedAttr, '1');
        img.classList.add(progressiveClass);

        if (img.complete && img.naturalWidth > 0) {
            markLoaded(img);
            return;
        }

        if (img.complete && img.naturalWidth === 0) {
            markError(img);
            return;
        }

        img.classList.add(pendingClass);
        img.setAttribute('data-qiling-image-state', 'pending');
        img.addEventListener('load', function () {
            markLoaded(img);
        }, { once: true });
        img.addEventListener('error', function () {
            markError(img);
        }, { once: true });
    }

    function prepareAll(root) {
        var scope = root && root.querySelectorAll ? root : document;
        var images = scope.querySelectorAll(selector);

        Array.prototype.forEach.call(images, prepareImage);
    }

    function observeNewImages() {
        if (!('MutationObserver' in window)) {
            return;
        }

        var observer = new MutationObserver(function (mutations) {
            mutations.forEach(function (mutation) {
                Array.prototype.forEach.call(mutation.addedNodes || [], function (node) {
                    if (!node || node.nodeType !== 1) {
                        return;
                    }

                    if (node.matches && node.matches(selector)) {
                        prepareImage(node);
                    }

                    prepareAll(node);
                });
            });
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }

    function init() {
        prepareAll(document);
        observeNewImages();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
