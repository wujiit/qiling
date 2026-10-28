(function () {
    tinymce.PluginManager.add('developer_starter_code', function (editor, url) {
        var i18n = window.qilingCodeEditorI18n || {};
        var text = function (key, fallback) {
            return typeof i18n[key] === 'string' && i18n[key] !== '' ? i18n[key] : fallback;
        };

        // 添加代码高亮按钮
        editor.addButton('developer_starter_code', {
            title: text('insertHighlightedCodeBlock', 'Insert highlighted code block'),
            icon: 'code',
            onclick: function () {
                // 打开一个输入窗口让用户选择语言和输入代码
                editor.windowManager.open({
                    title: text('insertHighlightedCode', 'Insert highlighted code'),
                    body: [
                        {
                            type: 'listbox',
                            name: 'language',
                            label: text('languageLabel', '语言'),
                            values: [
                                { text: 'PHP', value: 'php' },
                                { text: 'JavaScript', value: 'javascript' },
                                { text: 'HTML/XML', value: 'markup' },
                                { text: 'CSS', value: 'css' },
                                { text: 'Python', value: 'python' },
                                { text: 'Java', value: 'java' },
                                { text: 'C/C++', value: 'cpp' },
                                { text: 'C#', value: 'csharp' },
                                { text: 'Go', value: 'go' },
                                { text: 'SQL', value: 'sql' },
                                { text: 'JSON', value: 'json' },
                                { text: 'Bash/Shell', value: 'bash' },
                                { text: text('plainTextLabel', 'Plain Text'), value: 'none' }
                            ],
                            value: 'php' // 默认值
                        },
                        {
                            type: 'textbox',
                            name: 'code',
                            label: text('codeLabel', 'Code'),
                            multiline: true,
                            minWidth: 500,
                            minHeight: 300
                        }
                    ],
                    onsubmit: function (e) {
                        // 插入 <pre><code class="language-xxx">...</code></pre>
                        var lang = e.data.language;
                        var codeContent = e.data.code;

                        // 防止空插入
                        if (!codeContent) return;

                        // 对代码进行极简HTML转义，防止破坏编辑器结构
                        var escapedCode = codeContent
                            .replace(/&/g, '&amp;')
                            .replace(/</g, '&lt;')
                            .replace(/>/g, '&gt;');

                        var html = '<pre><code class="language-' + lang + '">' + escapedCode + '</code></pre><p><br data-mce-bogus="1"></p>';
                        editor.insertContent(html);
                    }
                });
            }
        });
    });
})();
