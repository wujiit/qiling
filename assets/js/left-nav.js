/**
 * 启灵主题左侧导航脚本
 *
 * 负责左侧导航展开/收起、悬停展开和交互状态控制。
 */

document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    var leftNav = document.getElementById('qiling-left-nav');
    if (!leftNav) {
        return;
    }

    var compactMedia = window.matchMedia('(max-width: 1024px)');
    var touchMedia = window.matchMedia('(hover: none), (pointer: coarse)');
    if (compactMedia.matches || touchMedia.matches) {
        return;
    }

    var shell = document.getElementById('qiling-left-nav-shell');
    if (!shell) {
        shell = leftNav.closest('.qiling-left-nav-shell');
    }
    var toggleButton = document.getElementById('qiling-left-nav-toggle');
    var toggleOpenState = toggleButton ? toggleButton.querySelector('.qiling-left-nav-toggle-state-open') : null;
    var toggleCloseState = toggleButton ? toggleButton.querySelector('.qiling-left-nav-toggle-state-close') : null;

    var setExpanded = function (expanded) {
        if (!shell || !toggleButton) {
            return;
        }

        shell.classList.toggle('is-expanded', expanded);
        leftNav.classList.toggle('is-expanded', expanded);
        toggleButton.setAttribute('aria-expanded', expanded ? 'true' : 'false');

        if (toggleOpenState) {
            toggleOpenState.setAttribute('aria-hidden', expanded ? 'true' : 'false');
        }
        if (toggleCloseState) {
            toggleCloseState.setAttribute('aria-hidden', expanded ? 'false' : 'true');
        }
    };

    if (shell && toggleButton) {
        var defaultOpen = shell.getAttribute('data-default-open') === '1';
        var autoOpenLarge = shell.getAttribute('data-auto-open-large') !== '0';
        var minWidthRaw = parseInt(shell.getAttribute('data-auto-open-large-min-width'), 10);
        var autoOpenLargeMinWidth = isNaN(minWidthRaw) ? 1440 : minWidthRaw;
        if (autoOpenLargeMinWidth < 1025) {
            autoOpenLargeMinWidth = 1025;
        }
        var largeScreenMedia = window.matchMedia('(min-width: ' + autoOpenLargeMinWidth + 'px)');
        var hasManualToggle = false;

        var getDefaultExpandedState = function () {
            if (!autoOpenLarge) {
                return defaultOpen;
            }
            return defaultOpen || largeScreenMedia.matches;
        };

        setExpanded(getDefaultExpandedState());

        toggleButton.addEventListener('click', function (event) {
            event.preventDefault();
            hasManualToggle = true;
            var isExpanded = shell.classList.contains('is-expanded');
            setExpanded(!isExpanded);
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && shell.classList.contains('is-expanded')) {
                hasManualToggle = true;
                setExpanded(false);
            }
        });

        document.addEventListener('click', function (event) {
            if (!shell.classList.contains('is-expanded')) {
                return;
            }
            if (shell.contains(event.target)) {
                return;
            }
            hasManualToggle = true;
            setExpanded(false);
        });

        if (autoOpenLarge) {
            var handleLargeScreenChange = function () {
                if (hasManualToggle) {
                    return;
                }
                setExpanded(getDefaultExpandedState());
            };
            if (typeof largeScreenMedia.addEventListener === 'function') {
                largeScreenMedia.addEventListener('change', handleLargeScreenChange);
            } else if (typeof largeScreenMedia.addListener === 'function') {
                largeScreenMedia.addListener(handleLargeScreenChange);
            }
        }
    }

    var menu = leftNav.querySelector('.qiling-left-nav-menu');
    if (!menu) {
        return;
    }

    // 优化中英混排标签：优先在字母缩写和中文之间断行，避免生硬拆字。
    // 若标签里已经有用户自定义 HTML，则尽量保留原始结构，避免重写后丢失内联样式。
    var resolveMixedLabelTarget = function (labelNode) {
        if (!labelNode) {
            return null;
        }

        var explicitTextNode = labelNode.querySelector('.qiling-menu-title-text');
        if (explicitTextNode) {
            return explicitTextNode;
        }

        var childElements = [];
        Array.prototype.forEach.call(labelNode.children || [], function (child) {
            if (child.tagName !== 'WBR') {
                childElements.push(child);
            }
        });

        if (!childElements.length) {
            return labelNode;
        }

        var hasStandaloneText = false;
        Array.prototype.forEach.call(labelNode.childNodes || [], function (node) {
            if (node.nodeType === 3 && (node.textContent || '').replace(/\s+/g, '') !== '') {
                hasStandaloneText = true;
            }
        });

        if (!hasStandaloneText && childElements.length === 1) {
            return childElements[0];
        }

        return null;
    };

    var optimizeMixedLabel = function (labelNode) {
        if (!labelNode) {
            return;
        }

        var textNode = resolveMixedLabelTarget(labelNode);
        if (!textNode || typeof textNode.getAttribute !== 'function') {
            return;
        }

        if (textNode.getAttribute('data-qiling-mixed-optimized') === '1') {
            return;
        }

        var rawText = (textNode.textContent || '').replace(/\s+/g, '').trim();
        var mixedMatch = rawText.match(/^([A-Za-z0-9&+._-]{1,8})([\u4e00-\u9fff]{2,8})$/);
        if (!mixedMatch) {
            return;
        }

        var prefixSpan = document.createElement('span');
        prefixSpan.className = 'qiling-mixed-prefix';
        prefixSpan.textContent = mixedMatch[1];

        var suffixSpan = document.createElement('span');
        suffixSpan.className = 'qiling-mixed-cjk';
        suffixSpan.textContent = mixedMatch[2];

        textNode.textContent = '';
        textNode.classList.add('qiling-title-mixed');
        textNode.appendChild(prefixSpan);
        textNode.appendChild(document.createElement('wbr'));
        textNode.appendChild(suffixSpan);
        textNode.setAttribute('data-qiling-mixed-optimized', '1');
    };

    var topLevelLabels = [];
    try {
        topLevelLabels = menu.querySelectorAll(':scope > li > a .qiling-left-nav-label');
    } catch (err) {
        topLevelLabels = menu.querySelectorAll('li > a .qiling-left-nav-label');
    }
    topLevelLabels.forEach(function (labelNode) {
        optimizeMixedLabel(labelNode);
    });

    var parents = menu.querySelectorAll('.menu-item-has-children');
    if (!parents.length) {
        return;
    }

    var closeTimers = new WeakMap();
    var closeDelay = 220;

    var clearCloseTimer = function (item) {
        var timer = closeTimers.get(item);
        if (timer) {
            window.clearTimeout(timer);
            closeTimers.delete(item);
        }
    };

    var closeSiblings = function (target) {
        parents.forEach(function (item) {
            if (item !== target) {
                clearCloseTimer(item);
                item.classList.remove('is-open');
            }
        });
    };

    var scheduleClose = function (item) {
        clearCloseTimer(item);
        closeTimers.set(
            item,
            window.setTimeout(function () {
                item.classList.remove('is-open');
                closeTimers.delete(item);
            }, closeDelay)
        );
    };

    parents.forEach(function (item) {
        var link = null;
        var submenu = null;
        try {
            link = item.querySelector(':scope > a');
            submenu = item.querySelector(':scope > .sub-menu');
        } catch (err) {
            link = item.querySelector('a');
            submenu = item.querySelector('.sub-menu');
        }
        if (!link || !submenu) {
            return;
        }

        item.addEventListener('mouseenter', function () {
            clearCloseTimer(item);
            closeSiblings(item);
            item.classList.add('is-open');
        });

        item.addEventListener('mouseleave', function () {
            scheduleClose(item);
        });

        submenu.addEventListener('mouseenter', function () {
            clearCloseTimer(item);
            item.classList.add('is-open');
        });

        submenu.addEventListener('mouseleave', function () {
            scheduleClose(item);
        });

        item.addEventListener('focusin', function () {
            clearCloseTimer(item);
            closeSiblings(item);
            item.classList.add('is-open');
        });

        item.addEventListener('focusout', function (event) {
            var nextTarget = event.relatedTarget;
            if (nextTarget && item.contains(nextTarget)) {
                return;
            }
            scheduleClose(item);
        });

        var href = (link.getAttribute('href') || '').trim();
        if (href === '' || href === '#') {
            link.addEventListener('click', function (event) {
                event.preventDefault();
                var willOpen = !item.classList.contains('is-open');
                clearCloseTimer(item);
                closeSiblings(item);
                if (willOpen) {
                    item.classList.add('is-open');
                } else {
                    item.classList.remove('is-open');
                }
            });
        }
    });
});
