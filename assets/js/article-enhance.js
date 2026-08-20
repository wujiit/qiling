/**
 * 启灵主题文章页增强脚本
 *
 * 负责目录交互、平滑滚动等阅读体验增强。
 */
(function () {
    'use strict';

    var config = window.articleEnhanceConfig || {};
    var copyButtonLabel = config.copyButtonLabel || 'Copy code';
    var COPY_ICON = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>';
    var CHECK_ICON = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>';

    function smoothScrollTo(targetY, duration) {
        var startY = window.pageYOffset;
        var distance = targetY - startY;
        var startTime = null;

        function easeInOutQuad(progress) {
            return progress < 0.5
                ? 2 * progress * progress
                : 1 - Math.pow(-2 * progress + 2, 2) / 2;
        }

        function step(timestamp) {
            if (startTime === null) {
                startTime = timestamp;
            }

            var elapsed = timestamp - startTime;
            var progress = duration > 0 ? Math.min(elapsed / duration, 1) : 1;
            var eased = easeInOutQuad(progress);

            window.scrollTo(0, startY + (distance * eased));

            if (progress < 1) {
                window.requestAnimationFrame(step);
            }
        }

        window.requestAnimationFrame(step);
    }

    var TOC = {
        toc: null,
        links: [],
        mobileLinks: [],
        toggle: null,
        mobileButton: null,
        mobilePanel: null,
        mobileBackdrop: null,
        headings: [],
        ticking: false,
        isMobilePanelOpen: false,

        init: function () {
            var toc = document.getElementById('article-toc');
            if (!toc) {
                return;
            }

            this.toc = toc;
            this.links = Array.prototype.slice.call(toc.querySelectorAll('.toc-link'));
            this.toggle = toc.querySelector('.toc-toggle');

            this.collectHeadings();
            this.bindEvents();
            this.createMobileToc();
            this.updateActiveLink();
        },

        getTargetElement: function (targetId) {
            if (!targetId || targetId.charAt(0) !== '#') {
                return null;
            }

            var rawId = targetId.slice(1);
            if (!rawId) {
                return null;
            }

            try {
                rawId = decodeURIComponent(rawId);
            } catch (error) {
                // Keep the raw id when decoding fails.
            }

            return document.getElementById(rawId);
        },

        collectHeadings: function () {
            var self = this;

            this.headings = this.links.map(function (link) {
                var targetId = link.getAttribute('href') || '';
                var target = self.getTargetElement(targetId);
                if (!target) {
                    return null;
                }

                return {
                    id: targetId,
                    target: target,
                    link: link
                };
            }).filter(function (item) {
                return !!item;
            });

            if (!self.headings.length) {
                this.links = [];
            }
        },

        bindLink: function (link) {
            var self = this;

            link.addEventListener('click', function (event) {
                var targetId = link.getAttribute('href') || '';
                var target = self.getTargetElement(targetId);
                if (!target) {
                    return;
                }

                event.preventDefault();

                var offset = 80;
                var targetY = target.getBoundingClientRect().top + window.pageYOffset - offset;
                smoothScrollTo(targetY, 500);

                self.setActiveHref(targetId);
                self.closeMobilePanel();
            });
        },

        bindEvents: function () {
            var self = this;

            this.links.forEach(function (link) {
                self.bindLink(link);
            });

            if (this.toggle) {
                this.toggle.addEventListener('click', function () {
                    self.setTocCollapsed(!self.toc.classList.contains('collapsed'));
                });
            }

            window.addEventListener('scroll', function () {
                if (self.ticking) {
                    return;
                }

                self.ticking = true;
                window.requestAnimationFrame(function () {
                    self.updateActiveLink();
                    self.ticking = false;
                });
            });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape' && self.isMobilePanelOpen) {
                    self.closeMobilePanel();
                }
            });
        },

        setTocCollapsed: function (collapsed) {
            if (!this.toggle || !this.toc) {
                return;
            }

            var expandLabel = config.tocExpandLabel || '展开目录';
            var collapseLabel = config.tocCollapseLabel || '收起目录';

            this.toggle.classList.toggle('collapsed', collapsed);
            this.toc.classList.toggle('collapsed', collapsed);
            this.toggle.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
            this.toggle.setAttribute('aria-label', collapsed ? expandLabel : collapseLabel);
        },

        createMobileToc: function () {
            var tocList = this.toc ? this.toc.querySelector('.toc-list') : null;
            if (!tocList || !this.links.length || document.getElementById('qiling-mobile-toc-panel')) {
                return;
            }

            var self = this;
            var buttonText = config.tocMobileButtonText || '目录';
            var openLabel = config.tocMobileOpenLabel || '打开目录';
            var closeLabel = config.tocMobileCloseLabel || '关闭目录';
            var title = this.toc.querySelector('.toc-title');
            var backdrop = document.createElement('button');
            var button = document.createElement('button');
            var panel = document.createElement('div');
            var header = document.createElement('div');
            var titleNode = document.createElement('span');
            var closeButton = document.createElement('button');
            var clonedList = tocList.cloneNode(true);

            backdrop.type = 'button';
            backdrop.className = 'qiling-mobile-toc-backdrop';
            backdrop.setAttribute('aria-label', closeLabel);
            backdrop.addEventListener('click', function () {
                self.closeMobilePanel();
            });

            button.type = 'button';
            button.className = 'qiling-mobile-toc-toggle';
            button.setAttribute('aria-controls', 'qiling-mobile-toc-panel');
            button.setAttribute('aria-expanded', 'false');
            button.setAttribute('aria-label', openLabel);
            button.innerHTML = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" focusable="false"><line x1="8" y1="6" x2="21" y2="6"></line><line x1="8" y1="12" x2="21" y2="12"></line><line x1="8" y1="18" x2="21" y2="18"></line><circle cx="3.5" cy="6" r="1"></circle><circle cx="3.5" cy="12" r="1"></circle><circle cx="3.5" cy="18" r="1"></circle></svg><span></span>';
            button.querySelector('span').textContent = buttonText;
            button.addEventListener('click', function () {
                self.setMobilePanelOpen(!self.isMobilePanelOpen);
            });

            panel.id = 'qiling-mobile-toc-panel';
            panel.className = 'qiling-mobile-toc-panel';
            panel.setAttribute('role', 'dialog');
            panel.setAttribute('aria-label', title ? title.textContent : buttonText);
            panel.setAttribute('aria-hidden', 'true');

            header.className = 'qiling-mobile-toc-header';
            titleNode.className = 'qiling-mobile-toc-title';
            titleNode.textContent = title ? title.textContent : buttonText;
            closeButton.type = 'button';
            closeButton.className = 'qiling-mobile-toc-close';
            closeButton.setAttribute('aria-label', closeLabel);
            closeButton.innerHTML = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" focusable="false"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>';
            closeButton.addEventListener('click', function () {
                self.closeMobilePanel();
            });

            clonedList.id = 'qiling-mobile-toc-list';
            clonedList.classList.add('qiling-mobile-toc-list');
            this.mobileLinks = Array.prototype.slice.call(clonedList.querySelectorAll('.toc-link'));
            this.mobileLinks.forEach(function (link) {
                self.bindLink(link);
            });

            header.appendChild(titleNode);
            header.appendChild(closeButton);
            panel.appendChild(header);
            panel.appendChild(clonedList);

            document.body.appendChild(backdrop);
            document.body.appendChild(panel);
            document.body.appendChild(button);
            document.body.classList.add('qiling-mobile-toc-ready');

            this.mobileBackdrop = backdrop;
            this.mobilePanel = panel;
            this.mobileButton = button;
        },

        setMobilePanelOpen: function (open) {
            if (!this.mobileButton || !this.mobilePanel || !this.mobileBackdrop) {
                return;
            }

            var openLabel = config.tocMobileOpenLabel || '打开目录';
            var closeLabel = config.tocMobileCloseLabel || '关闭目录';

            this.isMobilePanelOpen = open;
            this.mobilePanel.classList.toggle('is-open', open);
            this.mobileBackdrop.classList.toggle('is-open', open);
            document.body.classList.toggle('qiling-mobile-toc-open', open);
            this.mobilePanel.setAttribute('aria-hidden', open ? 'false' : 'true');
            this.mobileButton.setAttribute('aria-expanded', open ? 'true' : 'false');
            this.mobileButton.setAttribute('aria-label', open ? closeLabel : openLabel);
        },

        closeMobilePanel: function () {
            this.setMobilePanelOpen(false);
        },

        setActiveHref: function (currentId) {
            var allLinks = this.links.concat(this.mobileLinks);

            allLinks.forEach(function (link) {
                var item = link.closest ? link.closest('.toc-item') : link.parentElement;
                link.classList.remove('active');
                link.removeAttribute('aria-current');

                if (item) {
                    item.classList.remove('is-active');
                }
            });

            if (!currentId) {
                return;
            }

            allLinks.forEach(function (link) {
                var item = link.closest ? link.closest('.toc-item') : link.parentElement;

                if (link.getAttribute('href') === currentId) {
                    link.classList.add('active');
                    link.setAttribute('aria-current', 'true');

                    if (item) {
                        item.classList.add('is-active');
                    }
                }
            });
        },

        updateActiveLink: function () {
            var scrollTop = window.pageYOffset;
            var offset = 100;
            var currentId = null;

            for (var i = this.headings.length - 1; i >= 0; i -= 1) {
                var item = this.headings[i];
                var top = item.target.getBoundingClientRect().top + window.pageYOffset;
                if (scrollTop >= top - offset) {
                    currentId = item.id;
                    break;
                }
            }

            this.setActiveHref(currentId);
        }
    };

    var CodeBlock = {
        init: function () {
            this.removePrismCopyButtons();
            this.addCopyButtons();
            this.observeDOM();
        },

        removePrismCopyButtons: function () {
            document.querySelectorAll('.toolbar, .copy-to-clipboard-button, [data-copy-state]').forEach(function (el) {
                el.remove();
            });
        },

        observeDOM: function () {
            var self = this;
            var observer = new MutationObserver(function (mutations) {
                var hasAddedNodes = mutations.some(function (mutation) {
                    return mutation.addedNodes && mutation.addedNodes.length > 0;
                });

                if (!hasAddedNodes) {
                    return;
                }

                self.removePrismCopyButtons();
                self.addCopyButtons();
            });

            observer.observe(document.body, {
                childList: true,
                subtree: true
            });
        },

        addCopyButtons: function () {
            document.querySelectorAll('pre[class*="language-"], .wp-block-code pre').forEach(function (pre) {
                if (pre.querySelector('.code-copy-btn')) {
                    return;
                }

                var button = document.createElement('button');
                button.className = 'code-copy-btn';
                button.type = 'button';
                button.title = copyButtonLabel;
                button.setAttribute('aria-label', copyButtonLabel);
                button.innerHTML = COPY_ICON;

                pre.style.position = 'relative';
                pre.appendChild(button);

                button.addEventListener('click', function () {
                    var code = pre.querySelector('code');
                    if (!code || !navigator.clipboard) {
                        return;
                    }

                    navigator.clipboard.writeText(code.textContent || '').then(function () {
                        button.classList.add('copied');
                        button.innerHTML = CHECK_ICON;

                        window.setTimeout(function () {
                            button.classList.remove('copied');
                            button.title = copyButtonLabel;
                            button.setAttribute('aria-label', copyButtonLabel);
                            button.innerHTML = COPY_ICON;
                        }, 2000);
                    }).catch(function () {});
                });
            });
        }
    };

    function injectStyles() {
        if (document.getElementById('qiling-article-enhance-style')) {
            return;
        }

        var style = document.createElement('style');
        style.id = 'qiling-article-enhance-style';
        style.textContent = ''
            + '/* 完全隐藏 PrismJS 自带的所有 toolbar 和 copy 按钮 */'
            + '.code-toolbar > .toolbar,'
            + 'div.code-toolbar > .toolbar,'
            + 'pre[class*="language-"] > .toolbar,'
            + '.toolbar,'
            + '.toolbar-item,'
            + 'button.copy-to-clipboard-button,'
            + '[data-copy-state],'
            + '.prism-toolbar,'
            + '.copy-to-clipboard {'
            + 'display: none !important;'
            + 'opacity: 0 !important;'
            + 'visibility: hidden !important;'
            + 'pointer-events: none !important;'
            + '}'
            + '.code-copy-btn {'
            + 'position: absolute;'
            + 'top: 10px;'
            + 'right: 10px;'
            + 'padding: 8px 10px;'
            + 'background: rgba(255, 255, 255, 0.15);'
            + 'border: none;'
            + 'border-radius: 8px;'
            + 'cursor: pointer;'
            + 'color: rgba(255, 255, 255, 0.7);'
            + 'transition: all 0.2s;'
            + 'z-index: 100;'
            + 'display: flex;'
            + 'align-items: center;'
            + 'justify-content: center;'
            + '}'
            + '.code-copy-btn:hover {'
            + 'background: rgba(255, 255, 255, 0.25);'
            + 'color: #fff;'
            + '}'
            + '.code-copy-btn.copied {'
            + 'background: #10b981;'
            + 'color: #fff;'
            + '}'
            + 'pre[class*="language-"],'
            + '.wp-block-code pre {'
            + 'position: relative !important;'
            + '}';

        document.head.appendChild(style);
    }

    document.addEventListener('DOMContentLoaded', function () {
        injectStyles();
        TOC.init();
        CodeBlock.init();
    });
})();
