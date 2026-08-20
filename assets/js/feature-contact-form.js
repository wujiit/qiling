/**
 * Contact form runtime
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
    // ===== Contact Form =====
    var mainStrings = (typeof developerStarterData !== 'undefined' && developerStarterData.strings)
        ? developerStarterData.strings
        : {};
    var contactSendingText = mainStrings.sending || 'Sending...';
    var contactSuccessText = mainStrings.contactSuccess || '感谢您的留言，我们会尽快与您联系！';
    var contactErrorText = mainStrings.error || '网络错误，请稍后再试。';
    var contactNameRequiredText = mainStrings.contactNameRequired || 'Please enter your name';
    var contactMessageRequiredText = mainStrings.contactMessageRequired || 'Please enter your message';
    var contactPhoneOrEmailRequiredText = mainStrings.contactPhoneOrEmailRequired || 'Please provide a phone number or email';
    var contactLoginNowText = mainStrings.contactLoginNow || 'Log in now';

    var parseJsonResponse = function (response) {
        return response.text().then(function (text) {
            var normalizedText = (text || '').trim();
            if (!normalizedText) {
                return {};
            }

            return JSON.parse(normalizedText);
        });
    };

    var setContactFormMessage = function (form, type, message, loginUrl) {
        var messageEl = form ? form.querySelector('.form-message') : null;
        if (!messageEl) {
            return;
        }

        messageEl.innerHTML = '';
        messageEl.className = 'form-message' + (type ? (' ' + type) : '');

        if (message) {
            var textNode = document.createElement('span');
            textNode.textContent = message;
            messageEl.appendChild(textNode);
        }

        if (loginUrl) {
            var spacer = document.createTextNode(' ');
            var link = document.createElement('a');
            link.href = loginUrl;
            link.setAttribute('data-login-url', loginUrl);
            link.setAttribute('data-ds-login-trigger', 'modal');
            link.textContent = contactLoginNowText;
            link.style.marginLeft = '6px';
            messageEl.appendChild(spacer);
            messageEl.appendChild(link);
        }
    };

    document.addEventListener('submit', function (e) {
        var form = e.target;
        if (!(form instanceof HTMLFormElement) || form.dataset.dsMessageForm !== '1') {
            return;
        }

        e.preventDefault();

        if (form.dataset.dsSubmitting === '1') {
            return;
        }

        var nameInput = form.querySelector('input[name="name"]');
        var phoneInput = form.querySelector('input[name="phone"]');
        var emailInput = form.querySelector('input[name="email"]');
        var messageInput = form.querySelector('textarea[name="message"]');
        var submitBtn = form.querySelector('.btn-submit');

        if (nameInput && !nameInput.value.trim()) {
            setContactFormMessage(form, 'error', contactNameRequiredText);
            nameInput.focus();
            return;
        }

        if (messageInput && !messageInput.value.trim()) {
            setContactFormMessage(form, 'error', contactMessageRequiredText);
            messageInput.focus();
            return;
        }

        if (phoneInput && emailInput && !phoneInput.value.trim() && !emailInput.value.trim()) {
            setContactFormMessage(form, 'error', contactPhoneOrEmailRequiredText);
            phoneInput.focus();
            return;
        }

        if (!submitBtn) {
            return;
        }

        var originalText = submitBtn.dataset.originalText || submitBtn.textContent;
        submitBtn.dataset.originalText = originalText;
        submitBtn.textContent = contactSendingText;
        submitBtn.disabled = true;
        form.dataset.dsSubmitting = '1';
        setContactFormMessage(form, '', '');

        var formData = new FormData(form);
        if (!formData.get('action')) {
            formData.append('action', 'ds_submit_message');
        }

        fetch((typeof developerStarterData !== 'undefined' && developerStarterData.ajaxUrl) ? developerStarterData.ajaxUrl : form.action, {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        })
            .then(parseJsonResponse)
            .then(function (response) {
                if (response && response.success) {
                    form.reset();
                    setContactFormMessage(form, 'success', (response.data && response.data.message) ? response.data.message : contactSuccessText);
                    return;
                }

                var errorMessage = (response && response.data && response.data.message)
                    ? response.data.message
                    : contactErrorText;
                var loginUrl = (response && response.data && response.data.login_url)
                    ? response.data.login_url
                    : '';
                setContactFormMessage(form, 'error', errorMessage, loginUrl);
            })
            .catch(function () {
                setContactFormMessage(form, 'error', contactErrorText);
            })
            .finally(function () {
                form.dataset.dsSubmitting = '0';
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
            });
    });

    // ===== Smooth Scroll for Anchor Links =====
    });
})(window, document);
