/**
 * 启灵主题搜索弹层脚本
 *
 * 负责搜索弹层的打开/关闭与懒加载初始化。
 */
(function () {
    'use strict';

    if (window.developerStarterSearchOverlay && typeof window.developerStarterSearchOverlay.open === 'function') {
        return;
    }

    var searchToggle = document.getElementById('search-toggle');
    var searchOverlay = document.getElementById('search-overlay');
    var searchClose = document.getElementById('search-close');

    if (!searchOverlay) {
        window.developerStarterSearchOverlay = {
            open: function () {}
        };
        return;
    }

    var openOverlay = function () {
        searchOverlay.classList.add('active');
        var input = searchOverlay.querySelector('input[type="search"], input[name="s"]');
        if (input) input.focus();
    };

    var closeOverlay = function () {
        searchOverlay.classList.remove('active');
    };

    if (searchToggle) {
        searchToggle.addEventListener('click', function (e) {
            e.preventDefault();
            openOverlay();
        });
    }

    if (searchClose) {
        searchClose.addEventListener('click', closeOverlay);
    }

    searchOverlay.addEventListener('click', function (e) {
        if (e.target === searchOverlay) {
            closeOverlay();
        }
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && searchOverlay.classList.contains('active')) {
            closeOverlay();
        }
    });

    window.developerStarterSearchOverlay = {
        open: openOverlay,
        close: closeOverlay
    };
})();
