/**
 * 启灵主题移动端菜单脚本
 *
 * 负责移动端菜单开关、遮罩与子菜单交互。
 */

function initQilingMobileMenu() {
    'use strict';

    if (window.__dsMobileMenuInitialized) {
        return;
    }
    window.__dsMobileMenuInitialized = true;

    // ===== Mobile Menu Toggle =====
    var masthead = document.getElementById('masthead');
    var menuToggle = document.getElementById('mobile-menu-toggle') || document.querySelector('.mobile-menu-toggle');
    var mobileMenu = document.getElementById('mobile-menu');
    var mobileMenuClose = document.getElementById('mobile-menu-close');
    var mobileMenuOverlay = document.getElementById('mobile-menu-overlay');
    var mobileViewport = window.matchMedia ? window.matchMedia('(max-width: 992px)') : null;
    var previousBodyOverflow = '';
    var previousFocus = null;
    var focusableSelector = [
        'a[href]',
        'area[href]',
        'button:not([disabled])',
        'input:not([disabled]):not([type="hidden"])',
        'select:not([disabled])',
        'textarea:not([disabled])',
        'iframe',
        'object',
        'embed',
        '[contenteditable="true"]',
        '[tabindex]:not([tabindex="-1"])'
    ].join(',');

    function isMobileMenuOpen() {
        return !!(mobileMenu && mobileMenu.classList.contains('is-open'));
    }

    function setMenuInteractive(isInteractive) {
        if (!mobileMenu) return;

        if ('inert' in mobileMenu) {
            mobileMenu.inert = !isInteractive;
            return;
        }

        var focusable = mobileMenu.querySelectorAll(
            isInteractive ? focusableSelector + ', [data-qiling-mobile-original-tabindex]' : focusableSelector
        );
        focusable.forEach(function (element) {
            if (isInteractive) {
                if (!element.hasAttribute('data-qiling-mobile-original-tabindex')) {
                    return;
                }

                var originalTabIndex = element.getAttribute('data-qiling-mobile-original-tabindex');
                if (originalTabIndex === '') {
                    element.removeAttribute('tabindex');
                } else {
                    element.setAttribute('tabindex', originalTabIndex);
                }
                element.removeAttribute('data-qiling-mobile-original-tabindex');
                return;
            }

            if (element.hasAttribute('data-qiling-mobile-original-tabindex')) {
                return;
            }

            element.setAttribute(
                'data-qiling-mobile-original-tabindex',
                element.hasAttribute('tabindex') ? element.getAttribute('tabindex') : ''
            );
            element.setAttribute('tabindex', '-1');
        });
    }

    function syncMobileMenuA11y(isOpen) {
        if (menuToggle) {
            menuToggle.classList.toggle('is-active', isOpen);
            menuToggle.setAttribute('aria-label', isOpen ? '关闭菜单' : '打开菜单');
            menuToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            menuToggle.setAttribute('aria-haspopup', 'dialog');
            if (mobileMenu && mobileMenu.id) {
                menuToggle.setAttribute('aria-controls', mobileMenu.id);
            }
        }

        if (mobileMenu) {
            mobileMenu.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
        }

        if (mobileMenuOverlay) {
            mobileMenuOverlay.setAttribute('aria-hidden', 'true');
        }

        if (mobileMenuClose && mobileMenu && mobileMenu.id) {
            mobileMenuClose.setAttribute('aria-controls', mobileMenu.id);
        }

        setMenuInteractive(isOpen);
    }

    function getFocusableMenuItems() {
        if (!mobileMenu) return [];

        return Array.prototype.filter.call(mobileMenu.querySelectorAll(focusableSelector), function (element) {
            return !element.closest('[hidden]') && element.getClientRects().length > 0;
        });
    }

    function focusFirstMenuItem() {
        var focusTarget = mobileMenuClose || getFocusableMenuItems()[0] || mobileMenu;

        if (!focusTarget || typeof focusTarget.focus !== 'function') {
            return;
        }

        try {
            focusTarget.focus({ preventScroll: true });
        } catch (err) {
            focusTarget.focus();
        }
    }

    function openMobileMenu() {
        if (!mobileMenu || isMobileMenuOpen()) {
            return;
        }

        previousFocus = document.activeElement && document.activeElement.focus ? document.activeElement : null;
        previousBodyOverflow = document.body.style.overflow;

        mobileMenu.classList.add('is-open');
        if (mobileMenuOverlay) mobileMenuOverlay.classList.add('is-open');
        document.body.style.overflow = 'hidden';
        syncMobileMenuA11y(true);
        window.requestAnimationFrame(focusFirstMenuItem);
    }

    function closeMobileMenu(options) {
        if (!mobileMenu) {
            return;
        }

        var wasOpen = isMobileMenuOpen();
        var shouldRestoreFocus = !options || options.restoreFocus !== false;

        mobileMenu.classList.remove('is-open');
        if (mobileMenuOverlay) mobileMenuOverlay.classList.remove('is-open');
        if (wasOpen) {
            document.body.style.overflow = previousBodyOverflow;
        }
        syncMobileMenuA11y(false);

        if (wasOpen && shouldRestoreFocus && previousFocus && document.contains(previousFocus) && typeof previousFocus.focus === 'function') {
            try {
                previousFocus.focus({ preventScroll: true });
            } catch (err) {
                previousFocus.focus();
            }
        }

        previousFocus = null;
    }

    function ensureMobileLogoFallback() {
        if (!masthead) return;
        var branding = masthead.querySelector('.site-branding');
        if (!branding) return;

        var mobileLogo = branding.querySelector('.site-branding-logo-mobile');
        if (mobileLogo) return;

        var desktopLogo = branding.querySelector('.site-branding-logo-desktop');
        if (!desktopLogo) return;

        var clone = desktopLogo.cloneNode(true);
        clone.classList.remove('site-branding-logo-desktop');
        clone.classList.add('site-branding-logo-mobile', 'site-branding-logo-mobile-clone');
        branding.appendChild(clone);
    }

    function syncMobileHeaderState() {
        if (!masthead) return;

        var isMobile = mobileViewport ? mobileViewport.matches : window.innerWidth <= 992;
        masthead.classList.toggle('qiling-mobile-header-active', isMobile);
        document.body.classList.toggle('qiling-mobile-header-active', isMobile);

        if (!isMobile) {
            closeMobileMenu({ restoreFocus: false });
        }
    }

    syncMobileMenuA11y(false);
    ensureMobileLogoFallback();
    syncMobileHeaderState();
    if (mobileViewport && typeof mobileViewport.addEventListener === 'function') {
        mobileViewport.addEventListener('change', syncMobileHeaderState);
    } else {
        window.addEventListener('resize', syncMobileHeaderState);
        window.addEventListener('orientationchange', syncMobileHeaderState);
    }

    if (menuToggle) {
        menuToggle.addEventListener('click', function () {
            if (isMobileMenuOpen()) {
                closeMobileMenu();
            } else {
                openMobileMenu();
            }
        });
    }

    if (mobileMenuClose) {
        mobileMenuClose.addEventListener('click', closeMobileMenu);
    }

    if (mobileMenuOverlay) {
        mobileMenuOverlay.addEventListener('click', closeMobileMenu);
    }

    // Mobile submenu toggle
    var mobileMenuNav = document.querySelector('.mobile-menu-nav');
    if (mobileMenuNav) {
        var menuItemsWithChildren = mobileMenuNav.querySelectorAll('.menu-item-has-children');
        var getDirectChild = function (element, selector) {
            for (var i = 0; i < element.children.length; i++) {
                if (element.children[i].matches(selector)) {
                    return element.children[i];
                }
            }
            return null;
        };

        var isPlaceholderHref = function (href) {
            href = (href || '').trim().toLowerCase();
            return !href || href === '#' || href.indexOf('javascript:') === 0;
        };

        var setSubmenuState = function (item, button, submenu, isOpen) {
            var link = getDirectChild(item, 'a');
            var linkTogglesSubmenu = link && link.getAttribute('data-mobile-submenu-link') === 'toggle';

            item.classList.toggle('is-open', isOpen);
            button.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            button.setAttribute('aria-label', (isOpen ? '收起 ' : '展开 ') + (button.getAttribute('data-menu-label') || '子菜单'));
            submenu.setAttribute('aria-hidden', isOpen ? 'false' : 'true');

            if (linkTogglesSubmenu) {
                link.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            }

            if (isOpen) {
                submenu.removeAttribute('hidden');
            } else {
                submenu.setAttribute('hidden', '');
            }
        };

        menuItemsWithChildren.forEach(function (item, index) {
            var link = getDirectChild(item, 'a');
            var submenu = getDirectChild(item, '.sub-menu');

            if (!link || !submenu) {
                return;
            }

            if (!submenu.id) {
                submenu.id = 'mobile-sub-menu-' + index;
            }

            var button = getDirectChild(item, '.mobile-submenu-toggle');
            if (!button) {
                button = document.createElement('button');
                button.type = 'button';
                button.className = 'mobile-submenu-toggle';
                item.insertBefore(button, submenu);
            }

            var label = (link.textContent || '').replace(/\s+/g, ' ').trim() || '子菜单';
            var linkActsAsToggle = isPlaceholderHref(link.getAttribute('href'));
            var startsOpen = item.classList.contains('current-menu-ancestor')
                || item.classList.contains('current-menu-parent')
                || item.classList.contains('current-menu-item');

            button.setAttribute('data-menu-label', label);
            button.setAttribute('aria-controls', submenu.id);
            button.setAttribute('aria-haspopup', 'true');

            if (linkActsAsToggle) {
                link.setAttribute('role', 'button');
                link.setAttribute('aria-controls', submenu.id);
                link.setAttribute('data-mobile-submenu-link', 'toggle');
            }

            setSubmenuState(item, button, submenu, startsOpen);

            button.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                setSubmenuState(item, button, submenu, !item.classList.contains('is-open'));
            });

            link.addEventListener('click', function (e) {
                if (!linkActsAsToggle) {
                    return;
                }

                e.preventDefault();
                setSubmenuState(item, button, submenu, !item.classList.contains('is-open'));
            });

            link.addEventListener('keydown', function (e) {
                if (!linkActsAsToggle || (e.key !== ' ' && e.key !== 'Spacebar')) {
                    return;
                }

                e.preventDefault();
                setSubmenuState(item, button, submenu, !item.classList.contains('is-open'));
            });
        });
    }
    syncMobileMenuA11y(isMobileMenuOpen());

    // ESC key to close mobile menu
    document.addEventListener('keydown', function (e) {
        if (!isMobileMenuOpen()) {
            return;
        }

        if (e.key === 'Escape') {
            closeMobileMenu();
            return;
        }

        if (e.key !== 'Tab') {
            return;
        }

        var focusable = getFocusableMenuItems();
        if (!focusable.length) {
            e.preventDefault();
            return;
        }

        var firstItem = focusable[0];
        var lastItem = focusable[focusable.length - 1];

        if (!mobileMenu.contains(document.activeElement)) {
            e.preventDefault();
            firstItem.focus();
        } else if (e.shiftKey && document.activeElement === firstItem) {
            e.preventDefault();
            lastItem.focus();
        } else if (!e.shiftKey && document.activeElement === lastItem) {
            e.preventDefault();
            firstItem.focus();
        }
    });

    // ===== Mobile Bottom Navigation =====
    var mobileBottomMenu = document.querySelector('.mobile-bottom-menu');
    if (mobileBottomMenu) {
        mobileBottomMenu.addEventListener('click', function (e) {
            var menuItem = e.target.closest('li');
            if (!menuItem || !mobileBottomMenu.contains(menuItem)) {
                return;
            }

            // Keep native anchor behavior when directly tapping links.
            if (e.target.closest('a')) {
                return;
            }

            var link = null;
            try {
                link = menuItem.querySelector(':scope > a');
            } catch (err) {
                link = menuItem.querySelector('a');
            }

            if (!link || !link.href) {
                return;
            }

            link.click();
        });
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initQilingMobileMenu, { once: true });
} else {
    initQilingMobileMenu();
}
