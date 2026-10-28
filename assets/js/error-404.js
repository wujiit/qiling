(function () {
    'use strict';

    document.addEventListener('click', function (event) {
        var trigger = event.target && event.target.closest ? event.target.closest('[data-qiling-404-back]') : null;
        if (!trigger) {
            return;
        }

        event.preventDefault();

        var fallbackUrl = trigger.getAttribute('data-fallback-url') || '/';
        if (window.history.length > 1) {
            window.history.back();
            return;
        }

        window.location.href = fallbackUrl;
    });
}());
