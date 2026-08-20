/**
 * FAQ runtime
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
    // ===== FAQ Page Filter =====
    (function initFaqPageFilters() {
        var faqPages = document.querySelectorAll('.faq-page');

        if (!faqPages.length) {
            return;
        }

        faqPages.forEach(function (page) {
            var catButtons = page.querySelectorAll('.faq-cat-btn');
            var faqItems = page.querySelectorAll('.faq-item[data-categories]');

            if (!catButtons.length || !faqItems.length) {
                return;
            }

            catButtons.forEach(function (button) {
                button.addEventListener('click', function () {
                    var category = button.getAttribute('data-category') || 'all';

                    catButtons.forEach(function (otherButton) {
                        otherButton.classList.toggle('active', otherButton === button);
                    });

                    faqItems.forEach(function (item) {
                        var itemCategories = (item.getAttribute('data-categories') || '').split(',');
                        var shouldShow = category === 'all' || itemCategories.indexOf(category) !== -1;

                        if (shouldShow) {
                            item.style.display = 'block';
                            window.setTimeout(function () {
                                item.style.opacity = '1';
                                item.style.transform = 'translateY(0)';
                            }, 10);
                            return;
                        }

                        item.classList.remove('active');

                        var answer = item.querySelector('.faq-answer');
                        var trigger = item.querySelector('.faq-question');

                        if (answer) {
                            answer.style.maxHeight = '0px';
                            answer.setAttribute('aria-hidden', 'true');
                        }
                        if (trigger) {
                            trigger.setAttribute('aria-expanded', 'false');
                        }

                        item.style.opacity = '0';
                        item.style.transform = 'translateY(-10px)';
                        window.setTimeout(function () {
                            item.style.display = 'none';
                        }, 300);
                    });
                });
            });
        });
    })();

    // ===== FAQ Accordion =====
    (function initFaqAccordion() {
        var faqRoots = document.querySelectorAll('.module-faq, .faq-page');
        var faqIdSeed = 0;

        if (!faqRoots.length) {
            return;
        }

        function syncFaqItem(item, expanded) {
            var trigger = item.querySelector('.faq-question');
            var answer = item.querySelector('.faq-answer');

            if (!trigger || !answer) {
                return;
            }

            item.classList.toggle('active', expanded);
            trigger.setAttribute('aria-expanded', expanded ? 'true' : 'false');
            answer.setAttribute('aria-hidden', expanded ? 'false' : 'true');
            answer.style.maxHeight = expanded ? answer.scrollHeight + 'px' : '0px';
        }

        faqRoots.forEach(function (root) {
            var allowMultiple = root.getAttribute('data-faq-multiple') === '1';
            var items = root.querySelectorAll('.faq-item');

            if (!items.length) {
                return;
            }

            items.forEach(function (item) {
                var trigger = item.querySelector('.faq-question');
                var answer = item.querySelector('.faq-answer');

                if (!trigger || !answer || trigger.getAttribute('data-faq-bound') === 'true') {
                    return;
                }

                faqIdSeed += 1;
                trigger.setAttribute('data-faq-bound', 'true');
                trigger.setAttribute('aria-expanded', item.classList.contains('active') ? 'true' : 'false');

                if (!answer.id) {
                    answer.id = 'ds-faq-answer-' + faqIdSeed;
                }
                trigger.setAttribute('aria-controls', answer.id);

                if (trigger.tagName !== 'BUTTON') {
                    trigger.setAttribute('role', 'button');
                    trigger.setAttribute('tabindex', '0');
                }

                syncFaqItem(item, item.classList.contains('active'));

                trigger.addEventListener('click', function () {
                    var isExpanded = item.classList.contains('active');

                    if (!allowMultiple) {
                        items.forEach(function (otherItem) {
                            if (otherItem !== item) {
                                syncFaqItem(otherItem, false);
                            }
                        });
                    }

                    syncFaqItem(item, !isExpanded);
                });

                if (trigger.tagName !== 'BUTTON') {
                    trigger.addEventListener('keydown', function (event) {
                        if (event.key !== 'Enter' && event.key !== ' ') {
                            return;
                        }

                        event.preventDefault();
                        trigger.click();
                    });
                }
            });
        });

        window.addEventListener('resize', function () {
            document.querySelectorAll('.faq-item.active .faq-answer').forEach(function (answer) {
                answer.style.maxHeight = answer.scrollHeight + 'px';
            });
        });
    })();
    });
})(window, document);