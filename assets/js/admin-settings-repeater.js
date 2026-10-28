(function () {
    'use strict';

    document.addEventListener('click', function (event) {
        var target = event.target && event.target.nodeType === 1 ? event.target : event.target.parentElement;
        if (!target || typeof target.closest !== 'function') {
            return;
        }

        var addButton = target.closest('.ds-repeater-add');
        if (addButton) {
            event.preventDefault();
            var wrapper = addButton.closest('.ds-repeater-wrap');
            var list = wrapper ? wrapper.querySelector('.ds-repeater-list') : null;
            var template = wrapper ? wrapper.querySelector('.ds-repeater-tpl') : null;
            var markup = template ? String(template.getAttribute('data-template') || '') : '';
            if (!list || !markup) {
                return;
            }

            var index = Array.prototype.filter.call(list.children, function (child) {
                return child.classList && child.classList.contains('ds-repeater-item');
            }).length;
            list.insertAdjacentHTML('beforeend', markup.replace(/__IDX__/g, String(index)));
            return;
        }

        var removeButton = target.closest('.ds-repeater-remove');
        if (removeButton) {
            event.preventDefault();
            var item = removeButton.closest('.ds-repeater-item');
            if (item) {
                item.remove();
            }
        }
    });
})();
