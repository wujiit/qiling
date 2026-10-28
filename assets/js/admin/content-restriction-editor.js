(function() {
    tinymce.PluginManager.add('content_restriction', function(editor, url) {
        var i18n = window.qilingContentRestrictionI18n || {};
        var text = function (key, fallback) {
            return typeof i18n[key] === 'string' && i18n[key] !== '' ? i18n[key] : fallback;
        };
        
        // 登录可见按钮
        editor.addButton('login_to_view_btn', {
            title: text('loginVisible', 'Members only'),
            icon: 'lock',
            onclick: function() {
                var selectedText = editor.selection.getContent({format: 'text'});
                if (selectedText) {
                    editor.insertContent('[login_to_view]' + selectedText + '[/login_to_view]');
                } else {
                    editor.insertContent('[login_to_view]' + text('loginVisibleText', 'This content is visible after login') + '[/login_to_view]');
                }
            }
        });
        
        // 回复可见按钮
        editor.addButton('reply_to_view_btn', {
            title: text('replyVisible', 'Reply to view'),
            icon: 'bubble',
            onclick: function() {
                var selectedText = editor.selection.getContent({format: 'text'});
                if (selectedText) {
                    editor.insertContent('[reply_to_view]' + selectedText + '[/reply_to_view]');
                } else {
                    editor.insertContent('[reply_to_view]' + text('replyVisibleText', 'This content is visible after replying') + '[/reply_to_view]');
                }
            }
        });
    });
})();
