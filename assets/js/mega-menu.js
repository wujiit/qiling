/**
 * 启灵主题超级菜单前端脚本
 *
 * 负责大菜单悬停延迟、预览图与下拉布局定位。
 */
(function () {
    'use strict';

    var HOVER_DELAY_OPEN = 100;
    var HOVER_DELAY_CLOSE = 200;
    var timers = new WeakMap();
    var layoutRaf = null;

    function cssEscape(value) {
        if (window.CSS && typeof window.CSS.escape === 'function') {
            return window.CSS.escape(value);
        }
        return String(value).replace(/["\\]/g, '\\$&');
    }

    function getTimerState(menuItem) {
        if (!timers.has(menuItem)) {
            timers.set(menuItem, { open: null, close: null });
        }
        return timers.get(menuItem);
    }

    function getDirectDropdown(menuItem) {
        for (var i = 0; i < menuItem.children.length; i++) {
            var child = menuItem.children[i];
            if (child.classList && child.classList.contains('mega-menu-dropdown')) {
                return child;
            }
        }
        return null;
    }

    function showMegaMenu(menuItem) {
        var dropdown = getDirectDropdown(menuItem);
        if (!dropdown) {
            return;
        }
        dropdown.classList.add('is-visible');
        scheduleLayoutFix();
    }

    function hideMegaMenu(menuItem) {
        var dropdown = getDirectDropdown(menuItem);
        if (!dropdown) {
            return;
        }
        dropdown.classList.remove('is-visible');
    }

    function clearTimer(timerId) {
        if (timerId) {
            clearTimeout(timerId);
        }
    }

    function handleMenuItemEnter(menuItem) {
        var state = getTimerState(menuItem);
        clearTimer(state.close);
        state.close = null;

        clearTimer(state.open);
        state.open = setTimeout(function () {
            showMegaMenu(menuItem);
            state.open = null;
        }, HOVER_DELAY_OPEN);
    }

    function handleMenuItemLeave(menuItem) {
        var state = getTimerState(menuItem);
        clearTimer(state.open);
        state.open = null;

        clearTimer(state.close);
        state.close = setTimeout(function () {
            hideMegaMenu(menuItem);
            state.close = null;
        }, HOVER_DELAY_CLOSE);
    }

    function handleDropdownEnter(dropdown) {
        var menuItem = dropdown.closest('.has-mega-menu');
        if (!menuItem) {
            return;
        }
        var state = getTimerState(menuItem);
        clearTimer(state.close);
        state.close = null;
        dropdown.classList.add('is-visible');
    }

    function handleDropdownLeave(dropdown) {
        var menuItem = dropdown.closest('.has-mega-menu');
        if (!menuItem) {
            return;
        }
        var state = getTimerState(menuItem);
        clearTimer(state.close);
        state.close = setTimeout(function () {
            dropdown.classList.remove('is-visible');
            state.close = null;
        }, HOVER_DELAY_CLOSE);
    }

    function fixMegaMenuLayout() {
        layoutRaf = null;

        var header = document.getElementById('masthead')
            || document.querySelector('.site-header')
            || document.querySelector('header');

        if (!header) {
            return;
        }

        var headerBottom = header.getBoundingClientRect().bottom;
        var dropdowns = document.querySelectorAll('.has-mega-menu .mega-menu-dropdown');

        for (var i = 0; i < dropdowns.length; i++) {
            var dropdown = dropdowns[i];
            dropdown.style.position = 'fixed';
            dropdown.style.top = headerBottom + 'px';
            dropdown.style.left = '0';
            dropdown.style.width = '100%';
            dropdown.style.right = '0';
            dropdown.style.margin = '0';
            dropdown.style.maxWidth = 'none';
            dropdown.style.boxSizing = 'border-box';
            dropdown.style.zIndex = '999999';

            var innerContainer = null;
            for (var j = 0; j < dropdown.children.length; j++) {
                var child = dropdown.children[j];
                if (child.classList && child.classList.contains('container')) {
                    innerContainer = child;
                    break;
                }
            }

            if (innerContainer) {
                innerContainer.style.margin = '0 auto';
                innerContainer.style.width = '100%';
                innerContainer.style.maxWidth = '1400px';
                innerContainer.style.paddingLeft = '20px';
                innerContainer.style.paddingRight = '20px';
                innerContainer.style.boxSizing = 'border-box';
            }
        }
    }

    function scheduleLayoutFix() {
        if (layoutRaf !== null) {
            return;
        }
        layoutRaf = window.requestAnimationFrame(fixMegaMenuLayout);
    }

    function initSidebarMode() {
        var sidebarItems = document.querySelectorAll('.mega-mode-sidebar .mega-menu-list > li');
        var sidebarLists = document.querySelectorAll('.mega-mode-sidebar .mega-menu-list');

        for (var i = 0; i < sidebarItems.length; i++) {
            sidebarItems[i].addEventListener('mouseenter', function () {
                var li = this;
                var imageUrl = li.getAttribute('data-preview-image');
                var dropdown = li.closest('.mega-menu-dropdown');
                if (!dropdown) {
                    return;
                }

                var previewContainer = dropdown.querySelector('.mega-menu-preview');
                if (!previewContainer) {
                    return;
                }

                var siblings = li.parentElement ? li.parentElement.children : [];
                for (var k = 0; k < siblings.length; k++) {
                    siblings[k].classList.remove('hover-active');
                }
                li.classList.add('hover-active');

                if (imageUrl) {
                    var selector = '.preview-image-box[data-src="' + cssEscape(imageUrl) + '"]';
                    var existingImg = previewContainer.querySelector(selector);
                    if (existingImg && existingImg.classList.contains('active')) {
                        return;
                    }

                    var activeBoxes = previewContainer.querySelectorAll('.preview-image-box.active');
                    for (var a = 0; a < activeBoxes.length; a++) {
                        activeBoxes[a].classList.remove('active');
                    }

                    var newBox = document.createElement('div');
                    newBox.className = 'preview-image-box active';
                    newBox.setAttribute('data-src', imageUrl);

                    var previewImage = document.createElement('img');
                    previewImage.src = imageUrl;
                    previewImage.alt = '';
                    previewImage.loading = 'lazy';
                    previewImage.decoding = 'async';

                    var previewWidth = li.getAttribute('data-preview-width');
                    var previewHeight = li.getAttribute('data-preview-height');
                    if (/^\d+$/.test(previewWidth || '')) {
                        previewImage.width = parseInt(previewWidth, 10);
                    }
                    if (/^\d+$/.test(previewHeight || '')) {
                        previewImage.height = parseInt(previewHeight, 10);
                    }

                    newBox.appendChild(previewImage);
                    previewContainer.appendChild(newBox);

                    setTimeout(function () {
                        var boxes = previewContainer.querySelectorAll('.preview-image-box');
                        for (var b = 0; b < boxes.length; b++) {
                            var box = boxes[b];
                            if (!box.classList.contains('active') && !box.classList.contains('default-active')) {
                                box.remove();
                            }
                        }
                    }, 300);
                } else {
                    var currentActiveBoxes = previewContainer.querySelectorAll('.preview-image-box.active');
                    for (var c = 0; c < currentActiveBoxes.length; c++) {
                        currentActiveBoxes[c].classList.remove('active');
                    }
                }
            });
        }

        for (var j = 0; j < sidebarLists.length; j++) {
            sidebarLists[j].addEventListener('mouseleave', function () {
                var list = this;
                var listItems = list.querySelectorAll('li');
                for (var i2 = 0; i2 < listItems.length; i2++) {
                    listItems[i2].classList.remove('hover-active');
                }

                var dropdown = list.closest('.mega-menu-dropdown');
                if (!dropdown) {
                    return;
                }
                var previewContainer = dropdown.querySelector('.mega-menu-preview');
                if (!previewContainer) {
                    return;
                }

                var activeBoxes = previewContainer.querySelectorAll('.preview-image-box.active');
                for (var a2 = 0; a2 < activeBoxes.length; a2++) {
                    activeBoxes[a2].classList.remove('active');
                }

                setTimeout(function () {
                    var boxes = previewContainer.querySelectorAll('.preview-image-box');
                    for (var b2 = 0; b2 < boxes.length; b2++) {
                        var box = boxes[b2];
                        if (!box.classList.contains('default-active')) {
                            box.remove();
                        }
                    }
                }, 300);
            });
        }
    }

    function initHoverOpenClose() {
        var menuItems = document.querySelectorAll('.primary-navigation .has-mega-menu');
        var dropdowns = document.querySelectorAll('.primary-navigation .mega-menu-dropdown');

        for (var i = 0; i < menuItems.length; i++) {
            menuItems[i].addEventListener('mouseenter', function () {
                handleMenuItemEnter(this);
            });
            menuItems[i].addEventListener('mouseleave', function () {
                handleMenuItemLeave(this);
            });
        }

        for (var j = 0; j < dropdowns.length; j++) {
            dropdowns[j].addEventListener('mouseenter', function () {
                handleDropdownEnter(this);
            });
            dropdowns[j].addEventListener('mouseleave', function () {
                handleDropdownLeave(this);
            });
        }
    }

    function init() {
        initSidebarMode();
        initHoverOpenClose();

        window.addEventListener('scroll', scheduleLayoutFix, { passive: true });
        window.addEventListener('resize', scheduleLayoutFix);
        fixMegaMenuLayout();
        setTimeout(scheduleLayoutFix, 500);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
