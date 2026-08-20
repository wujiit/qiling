/**
 * Admin Mega Menu Scripts
 */
(function ($) {
    'use strict';

    function getText(key, fallback) {
        var cfg = window.dsMegaMenuAdmin || {};
        if (cfg && typeof cfg[key] === 'string' && cfg[key] !== '') {
            return cfg[key];
        }
        return fallback;
    }

    function renderPreview($preview, url) {
        $('<img>', {
            src: url,
            alt: '',
            class: 'ds-menu-image-preview-img'
        }).appendTo($preview.empty());
    }

    $(document).on('click', '.ds-menu-image-upload', function (e) {
        e.preventDefault();
        e.stopPropagation();

        if (typeof wp === 'undefined' || typeof wp.media === 'undefined') {
            window.alert(getText('mediaError', '媒体库未加载，请刷新页面重试。'));
            return;
        }

        var $button = $(this);
        var inputId = $button.data('input');
        var previewId = $button.data('preview');
        var $input = $('#' + inputId);
        var $preview = $('#' + previewId);
        var $remove = $button.siblings('.ds-menu-image-remove');

        var frame = wp.media({
            title: getText('title', '选择图片'),
            button: { text: getText('buttonText', '使用此图片') },
            multiple: false
        });

        frame.on('select', function () {
            var attachment = frame.state().get('selection').first();
            if (!attachment) {
                return;
            }
            var data = attachment.toJSON();
            if (!data || !data.url) {
                return;
            }
            $input.val(data.url).trigger('change');
            renderPreview($preview, data.url);
            $remove.show();
        });

        frame.open();
    });

    $(document).on('click', '.ds-menu-image-remove', function (e) {
        e.preventDefault();
        e.stopPropagation();

        var $button = $(this);
        var inputId = $button.data('input');
        var previewId = $button.data('preview');
        $('#' + inputId).val('').trigger('change');
        $('#' + previewId).empty();
        $button.hide();
    });
})(jQuery);
