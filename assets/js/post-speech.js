/**
 * Post and comment speech controls powered by the browser Web Speech API.
 */
(function () {
    'use strict';

    var config = window.qilingPostSpeechConfig || {};
    var selectors = config.selectors || {};
    var i18n = config.i18n || {};
    var articleSelector = selectors.article || '.single-post .entry-content';
    var articleWidgetSelector = selectors.articleWidget || '.qiling-post-speech';
    var commentButtonSelector = selectors.commentButton || '.qiling-comment-speech-trigger';
    var commentTextSelector = selectors.commentText || '.comment-text';
    var synth = 'speechSynthesis' in window ? window.speechSynthesis : null;
    var cachedVoices = [];
    var voicesLoaded = false;
    var current = null;
    var tokenCounter = 0;
    var miniPlayer = null;
    var miniHideTimer = null;
    var userPrefs = loadUserPreferences();
    var unwantedSelector = [
        'script',
        'style',
        'noscript',
        'pre',
        'code',
        'iframe',
        'audio',
        'video',
        'button',
        'input',
        'select',
        'textarea',
        '.wp-block-code',
        '.code-copy-btn',
        '.qiling-post-speech',
        '.qiling-speech-player',
        '.gallery-pagination',
        '.gallery-nav-btn',
        '.gallery-click-area'
    ].join(',');

    function getText(key, fallback) {
        return i18n[key] || fallback;
    }

    function escapeHTML(text) {
        return String(text || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function toArray(list) {
        return Array.prototype.slice.call(list || []);
    }

    function clamp(value, min, max) {
        return Math.min(max, Math.max(min, value));
    }

    function readNumber(value, fallback, min, max) {
        var number = Number(value);

        if (!isFinite(number)) {
            number = fallback;
        }

        return clamp(number, min, max);
    }

    function getStorageKey() {
        return config.storageKey || 'qiling-post-speech-preferences';
    }

    function loadUserPreferences() {
        var raw = '';
        var parsed = null;

        try {
            raw = window.localStorage ? window.localStorage.getItem(getStorageKey()) : '';
            parsed = raw ? JSON.parse(raw) : null;
        } catch (error) {
            parsed = null;
        }

        return parsed && typeof parsed === 'object' ? parsed : {};
    }

    function saveUserPreferences() {
        try {
            if (window.localStorage) {
                window.localStorage.setItem(getStorageKey(), JSON.stringify(userPrefs));
            }
        } catch (error) {}
    }

    function getRate() {
        return readNumber(userPrefs.rate || config.rate || 1, 1, 0.6, 1.4);
    }

    function getPitch() {
        return readNumber(config.pitch || 1, 1, 0.6, 1.4);
    }

    function getVolume() {
        return readNumber(userPrefs.volume !== undefined ? userPrefs.volume : config.volume, 1, 0, 1);
    }

    function setUserPreference(key, value) {
        userPrefs[key] = value;
        saveUserPreferences();
        syncMiniSettings();
    }

    function refreshVoices() {
        if (!synth) {
            return [];
        }
        cachedVoices = synth.getVoices ? synth.getVoices() : [];
        voicesLoaded = true;
        return cachedVoices;
    }

    function getVoices() {
        if (!voicesLoaded || !cachedVoices.length) {
            return refreshVoices();
        }
        return cachedVoices;
    }

    function normalizeLanguage(language) {
        return String(language || 'zh-CN').toLowerCase();
    }

    function voiceMatchesLanguage(voice, language) {
        var voiceLang = normalizeLanguage(voice && voice.lang);
        var targetLang = normalizeLanguage(language);
        var targetBase = targetLang.split('-')[0];

        return voiceLang === targetLang || voiceLang.indexOf(targetBase + '-') === 0;
    }

    function findConfiguredVoice(voices, language) {
        var voiceURI = String(config.voiceURI || '').trim();
        var voiceName = String(config.voiceName || '').trim();
        var languageVoices = voices.filter(function (voice) {
            return voiceMatchesLanguage(voice, language);
        });
        var pool = languageVoices.length ? languageVoices : voices;
        var lowerName = voiceName.toLowerCase();

        if (voiceURI) {
            return pool.find(function (voice) {
                return String(voice.voiceURI || '') === voiceURI;
            }) || null;
        }

        if (voiceName) {
            return pool.find(function (voice) {
                return String(voice.name || '') === voiceName;
            }) || pool.find(function (voice) {
                return String(voice.name || '').toLowerCase() === lowerName;
            }) || null;
        }

        return null;
    }

    function getPreferredVoice(languageVoices, preference) {
        var preferredNames = {
            female: [
                'female', 'woman', 'girl', 'xiaoxiao', 'xiaoyi', 'xiaobei', 'xiaoxuan',
                'xiaomo', 'xiaoqiu', 'xiaoshuang', 'xiaohan', 'huihui', 'yaoyao',
                'ting-ting', 'tingting', 'mei-jia', 'meijia', 'jenny', 'aria',
                'susan', 'samantha', 'victoria', 'zira', 'hazel', 'eva', 'joana',
                'helena', 'karen', 'moira', 'tessa', 'veena'
            ],
            male: [
                'male', 'man', 'boy', 'yunxi', 'yunjian', 'yunyang', 'yunhao',
                'yunfeng', 'kangkang', 'guy', 'david', 'mark', 'alex', 'daniel',
                'fred', 'tom', 'jorge', 'paul', 'thomas'
            ]
        };
        var names = preferredNames[preference] || [];

        if (!names.length) {
            return null;
        }

        return languageVoices.find(function (voice) {
            var voiceText = String((voice.name || '') + ' ' + (voice.voiceURI || '')).toLowerCase();

            return names.some(function (name) {
                if (name === 'male') {
                    return /\bmale\b/.test(voiceText) && !/\bfemale\b/.test(voiceText);
                }
                if (name === 'man') {
                    return /\bman\b/.test(voiceText) && !/\bwoman\b/.test(voiceText);
                }
                if (name === 'female') {
                    return /\bfemale\b/.test(voiceText);
                }
                return voiceText.indexOf(name) !== -1;
            });
        }) || null;
    }

    function chooseVoice() {
        var voices = getVoices();
        var language = config.language || 'zh-CN';
        var preference = config.voicePreference || 'auto';
        var languageVoices = voices.filter(function (voice) {
            return voiceMatchesLanguage(voice, language);
        });
        var configuredVoice = findConfiguredVoice(voices, language);
        var preferredVoice = null;

        if (configuredVoice) {
            return configuredVoice;
        }

        if (languageVoices.length && preference !== 'auto') {
            preferredVoice = getPreferredVoice(languageVoices, preference);
        }

        return preferredVoice
            || languageVoices.find(function (voice) { return voice.default; })
            || languageVoices[0]
            || voices.find(function (voice) { return voice.default; })
            || voices[0]
            || null;
    }

    function normalizeTextBlock(text) {
        return String(text || '')
            .replace(/\r\n?/g, '\n')
            .replace(/[ \t\f\v]+/g, ' ')
            .replace(/\n[ \t]+/g, '\n')
            .replace(/[ \t]+\n/g, '\n')
            .replace(/\n{3,}/g, '\n\n')
            .trim();
    }

    function removeUnwantedNodes(root) {
        toArray(root.querySelectorAll(unwantedSelector)).forEach(function (node) {
            node.remove();
        });
    }

    function getElementSpeechText(element) {
        var clone = null;

        if (!element) {
            return '';
        }

        clone = element.cloneNode(true);
        removeUnwantedNodes(clone);

        return normalizeTextBlock(clone.innerText || clone.textContent || '');
    }

    function isElementVisible(element) {
        var styles = null;

        if (!element || element.getAttribute('aria-hidden') === 'true') {
            return false;
        }

        styles = window.getComputedStyle ? window.getComputedStyle(element) : null;
        if (styles && (styles.display === 'none' || styles.visibility === 'hidden')) {
            return false;
        }

        return true;
    }

    function shouldSkipBlock(block, root) {
        var tagName = block.tagName ? block.tagName.toLowerCase() : '';
        var parentListOrQuote = block.parentElement ? block.parentElement.closest('li, blockquote') : null;

        if (!isElementVisible(block)) {
            return true;
        }

        if (block.closest && block.closest(unwantedSelector)) {
            return true;
        }

        if (parentListOrQuote && parentListOrQuote !== block && root.contains(parentListOrQuote) && tagName !== 'li' && tagName !== 'blockquote') {
            return true;
        }

        return false;
    }

    function collectSpeechBlocks(root) {
        var blockSelector = 'h1,h2,h3,h4,h5,h6,p,li,blockquote,figcaption';
        var blocks = [];

        if (!root) {
            return blocks;
        }

        blocks = toArray(root.querySelectorAll(blockSelector)).filter(function (block) {
            return !shouldSkipBlock(block, root) && getElementSpeechText(block).length > 0;
        });

        if (!blocks.length && getElementSpeechText(root).length > 0) {
            blocks.push(root);
        }

        return blocks;
    }

    function pushLongTextChunks(chunks, text, maxLength) {
        var start = 0;

        while (start < text.length) {
            chunks.push(text.slice(start, start + maxLength));
            start += maxLength;
        }
    }

    function splitParagraph(paragraph, maxLength) {
        var chunks = [];
        var sentences = paragraph.match(/[^。！？!?;；.]+[。！？!?;；.]?/g);
        var buffer = '';

        if (!sentences || !sentences.length) {
            pushLongTextChunks(chunks, paragraph, maxLength);
            return chunks;
        }

        sentences.forEach(function (sentence) {
            sentence = sentence.trim();
            if (!sentence) {
                return;
            }

            if (sentence.length > maxLength) {
                if (buffer) {
                    chunks.push(buffer);
                    buffer = '';
                }
                pushLongTextChunks(chunks, sentence, maxLength);
                return;
            }

            if (buffer && (buffer + sentence).length > maxLength) {
                chunks.push(buffer);
                buffer = sentence;
            } else {
                buffer += sentence;
            }
        });

        if (buffer) {
            chunks.push(buffer);
        }

        return chunks;
    }

    function splitTextIntoChunks(text) {
        var maxLength = 180;
        var chunks = [];

        normalizeTextBlock(text).split(/\n+/).forEach(function (paragraph) {
            paragraph = paragraph.trim();
            if (!paragraph) {
                return;
            }
            chunks = chunks.concat(splitParagraph(paragraph, maxLength));
        });

        return chunks;
    }

    function buildSpeechSegments(element, fallbackHighlight) {
        var blocks = collectSpeechBlocks(element);
        var segments = [];

        blocks.forEach(function (block) {
            splitTextIntoChunks(getElementSpeechText(block)).forEach(function (chunk) {
                segments.push({
                    text: chunk,
                    element: fallbackHighlight || block
                });
            });
        });

        return segments;
    }

    function getArticleWidgets() {
        return toArray(document.querySelectorAll(articleWidgetSelector));
    }

    function getProgressInfo() {
        var total = current && current.segments ? current.segments.length : 0;
        var displayIndex = total ? clamp(current.index + 1, 1, total) : 0;

        return {
            total: total,
            index: displayIndex,
            text: total ? displayIndex + '/' + total : '0/0',
            percent: total ? (displayIndex / total) * 100 : 0
        };
    }

    function createMiniPlayer() {
        if (miniPlayer || !document.body) {
            return miniPlayer;
        }

        miniPlayer = document.createElement('div');
        miniPlayer.className = 'qiling-speech-player';
        miniPlayer.setAttribute('aria-live', 'polite');
        miniPlayer.innerHTML = ''
            + '<div class="qiling-speech-player__header">'
                + '<div>'
                    + '<div class="qiling-speech-player__title">' + escapeHTML(getText('miniTitle', '语音播放器')) + '</div>'
                    + '<div class="qiling-speech-player__status">' + escapeHTML(getText('ready', '准备朗读')) + '</div>'
                + '</div>'
                + '<div class="qiling-speech-player__progress-text" data-speech-player-progress>0/0</div>'
            + '</div>'
            + '<div class="qiling-speech-player__progress-track" aria-hidden="true"><div class="qiling-speech-player__progress-fill"></div></div>'
            + '<div class="qiling-speech-player__actions">'
                + '<button type="button" data-speech-action="pause">' + escapeHTML(getText('pauseButton', '暂停')) + '</button>'
                + '<button type="button" data-speech-action="resume">' + escapeHTML(getText('resumeButton', '继续')) + '</button>'
                + '<button type="button" data-speech-action="stop">' + escapeHTML(getText('stopButton', '停止')) + '</button>'
            + '</div>'
            + '<div class="qiling-speech-player__settings">'
                + '<label>' + escapeHTML(getText('rateLabel', '语速')) + '<input type="range" min="0.6" max="1.4" step="0.1" data-speech-setting="rate"><span data-speech-setting-value="rate"></span></label>'
                + '<label>' + escapeHTML(getText('volumeLabel', '音量')) + '<input type="range" min="0" max="1" step="0.1" data-speech-setting="volume"><span data-speech-setting-value="volume"></span></label>'
            + '</div>';

        document.body.appendChild(miniPlayer);
        syncMiniSettings();

        return miniPlayer;
    }

    function syncMiniSettings() {
        var player = miniPlayer;
        var rateInput = null;
        var volumeInput = null;
        var rateValue = null;
        var volumeValue = null;

        if (!player) {
            return;
        }

        rateInput = player.querySelector('[data-speech-setting="rate"]');
        volumeInput = player.querySelector('[data-speech-setting="volume"]');
        rateValue = player.querySelector('[data-speech-setting-value="rate"]');
        volumeValue = player.querySelector('[data-speech-setting-value="volume"]');

        if (rateInput) {
            rateInput.value = String(getRate());
        }
        if (volumeInput) {
            volumeInput.value = String(getVolume());
        }
        if (rateValue) {
            rateValue.textContent = getRate().toFixed(1) + 'x';
        }
        if (volumeValue) {
            volumeValue.textContent = Math.round(getVolume() * 100) + '%';
        }
    }

    function updateMiniPlayer(state, message) {
        var player = createMiniPlayer();
        var title = player ? player.querySelector('.qiling-speech-player__title') : null;
        var status = player ? player.querySelector('.qiling-speech-player__status') : null;
        var progressText = player ? player.querySelector('[data-speech-player-progress]') : null;
        var progressFill = player ? player.querySelector('.qiling-speech-player__progress-fill') : null;
        var progress = getProgressInfo();

        if (!player) {
            return;
        }

        window.clearTimeout(miniHideTimer);
        player.classList.toggle('is-visible', state !== 'idle' || !!message);
        player.classList.toggle('is-playing', state === 'playing');
        player.classList.toggle('is-paused', state === 'paused');

        if (title) {
            title.textContent = current && current.title ? current.title : getText('miniTitle', '语音播放器');
        }
        if (status && message) {
            status.textContent = message;
        }
        if (progressText) {
            progressText.textContent = progress.text;
        }
        if (progressFill) {
            progressFill.style.width = progress.percent.toFixed(2) + '%';
        }

        toArray(player.querySelectorAll('[data-speech-action="pause"]')).forEach(function (button) {
            button.disabled = state !== 'playing';
        });
        toArray(player.querySelectorAll('[data-speech-action="resume"]')).forEach(function (button) {
            button.disabled = state !== 'paused';
        });
        toArray(player.querySelectorAll('[data-speech-action="stop"]')).forEach(function (button) {
            button.disabled = state === 'idle';
        });

        if (state === 'idle') {
            miniHideTimer = window.setTimeout(function () {
                player.classList.remove('is-visible');
            }, 2200);
        }
    }

    function updateArticleWidgets(state, message) {
        var progress = getProgressInfo();

        getArticleWidgets().forEach(function (widget) {
            var status = widget.querySelector('.qiling-post-speech__status');
            var progressNode = widget.querySelector('[data-speech-progress]');
            var pauseButton = widget.querySelector('[data-speech-action="pause"]');
            var resumeButton = widget.querySelector('[data-speech-action="resume"]');
            var stopButton = widget.querySelector('[data-speech-action="stop"]');

            widget.classList.toggle('is-playing', state === 'playing');
            widget.classList.toggle('is-paused', state === 'paused');

            if (status && message) {
                status.textContent = message;
            }
            if (progressNode) {
                progressNode.hidden = !progress.total;
                progressNode.textContent = progress.text;
            }
            if (pauseButton) {
                pauseButton.disabled = state !== 'playing';
            }
            if (resumeButton) {
                resumeButton.disabled = state !== 'paused';
            }
            if (stopButton) {
                stopButton.disabled = state === 'idle';
            }
        });
    }

    function updatePlaybackUI(state, message) {
        updateArticleWidgets(state, message);
        updateMiniPlayer(state, message);
        document.body.classList.toggle('qiling-speech-active', !!current && state !== 'idle');
    }

    function clearHighlight() {
        toArray(document.querySelectorAll('.qiling-speech-current')).forEach(function (element) {
            element.classList.remove('qiling-speech-current');
        });
    }

    function setHighlight(element) {
        clearHighlight();
        if (element && element.classList) {
            element.classList.add('qiling-speech-current');
        }
    }

    function resetCommentButtons() {
        toArray(document.querySelectorAll(commentButtonSelector)).forEach(function (button) {
            var label = button.querySelector('span');

            button.classList.remove('is-speaking');
            button.setAttribute('aria-pressed', 'false');
            if (label) {
                label.textContent = getText('commentIdleLabel', '朗读');
            }
        });
    }

    function setActiveCommentButton(button) {
        var label = button ? button.querySelector('span') : null;

        resetCommentButtons();
        if (!button) {
            return;
        }

        button.classList.add('is-speaking');
        button.setAttribute('aria-pressed', 'true');
        if (label) {
            label.textContent = getText('commentActiveLabel', '朗读中');
        }
    }

    function setIdleStatus(message) {
        clearHighlight();
        resetCommentButtons();
        updatePlaybackUI('idle', message || getText('ready', '准备朗读'));
    }

    function setReadyState() {
        clearHighlight();
        resetCommentButtons();
        updateArticleWidgets('idle', getText('ready', '准备朗读'));
        updateMiniPlayer('idle', '');
    }

    function cancelCurrentSpeech(updateUI, message) {
        tokenCounter += 1;
        if (synth) {
            synth.cancel();
        }
        current = null;
        clearHighlight();
        resetCommentButtons();
        if (updateUI) {
            updatePlaybackUI('idle', message || getText('stopped', '已停止'));
        }
    }

    function finishSpeech(message) {
        current = null;
        setIdleStatus(message || getText('finished', '朗读完成'));
    }

    function stopSpeech(message) {
        cancelCurrentSpeech(true, message || getText('stopped', '已停止'));
    }

    function speakNext(token) {
        var utterance = null;
        var segment = null;
        var voice = null;

        if (!current || current.token !== token) {
            return;
        }

        if (current.index >= current.segments.length) {
            finishSpeech(getText('finished', '朗读完成'));
            return;
        }

        segment = current.segments[current.index];
        setHighlight(segment.element);
        updatePlaybackUI('playing', getText('playing', '正在朗读'));

        utterance = new SpeechSynthesisUtterance(segment.text);
        utterance.lang = config.language || 'zh-CN';
        utterance.rate = getRate();
        utterance.pitch = getPitch();
        utterance.volume = getVolume();
        voice = chooseVoice();
        if (voice) {
            utterance.voice = voice;
        }

        utterance.onend = function () {
            if (!current || current.token !== token) {
                return;
            }
            current.index += 1;
            window.setTimeout(function () {
                speakNext(token);
            }, 0);
        };

        utterance.onerror = function (event) {
            if (!current || current.token !== token) {
                return;
            }
            if (event && (event.error === 'interrupted' || event.error === 'canceled')) {
                return;
            }
            finishSpeech(getText('error', '朗读失败，请稍后重试'));
        };

        synth.speak(utterance);
    }

    function startSpeech(type, element, fallbackHighlight, triggerButton, title) {
        var segments = buildSpeechSegments(element, fallbackHighlight);
        var token = 0;

        if (!segments.length) {
            setIdleStatus(getText('emptyText', '暂无可朗读内容'));
            return;
        }

        cancelCurrentSpeech(false);
        tokenCounter += 1;
        token = tokenCounter;
        current = {
            token: token,
            type: type,
            segments: segments,
            index: 0,
            triggerButton: triggerButton || null,
            title: title || (type === 'comment' ? getText('commentLabel', '评论') : getText('articleLabel', '文章正文'))
        };

        if (type === 'comment') {
            setActiveCommentButton(triggerButton);
        } else {
            resetCommentButtons();
        }

        updatePlaybackUI('playing', getText('playing', '正在朗读'));

        window.setTimeout(function () {
            speakNext(token);
        }, 0);
    }

    function pauseSpeech() {
        if (!current || !synth || synth.paused) {
            return;
        }

        synth.pause();
        updatePlaybackUI('paused', getText('paused', '已暂停'));
    }

    function resumeSpeech() {
        if (!current || !synth || !synth.paused) {
            return;
        }

        synth.resume();
        updatePlaybackUI('playing', getText('playing', '正在朗读'));
    }

    function handleArticleAction(action) {
        var article = null;

        if (!synth) {
            return;
        }

        if (action === 'pause') {
            pauseSpeech();
            return;
        }

        if (action === 'resume') {
            resumeSpeech();
            return;
        }

        if (action === 'stop') {
            stopSpeech(getText('stopped', '已停止'));
            return;
        }

        if (action !== 'play') {
            return;
        }

        article = document.querySelector(articleSelector);
        startSpeech('article', article, null, null, getText('articleLabel', '文章正文'));
    }

    function handleCommentButton(button) {
        var commentItem = null;
        var textElement = null;

        if (!synth || !button) {
            return;
        }

        if (current && current.type === 'comment' && current.triggerButton === button) {
            stopSpeech(getText('stopped', '已停止'));
            return;
        }

        commentItem = button.closest ? button.closest('.comment-item') : null;
        textElement = commentItem ? commentItem.querySelector(commentTextSelector) : null;

        startSpeech('comment', textElement, commentItem || textElement, button, getText('commentLabel', '评论'));
    }

    function handleSettingInput(input) {
        var setting = input.getAttribute('data-speech-setting');
        var value = Number(input.value);

        if (!isFinite(value)) {
            return;
        }

        if (setting === 'rate') {
            setUserPreference('rate', clamp(value, 0.6, 1.4));
        }

        if (setting === 'volume') {
            setUserPreference('volume', clamp(value, 0, 1));
        }
    }

    function setUnsupportedState() {
        toArray(document.querySelectorAll(articleWidgetSelector)).forEach(function (widget) {
            var status = widget.querySelector('.qiling-post-speech__status');

            widget.classList.add('is-unsupported');
            if (status) {
                status.textContent = getText('unsupported', '当前浏览器不支持语音朗读');
            }
            toArray(widget.querySelectorAll('button')).forEach(function (button) {
                button.disabled = true;
            });
        });

        toArray(document.querySelectorAll(commentButtonSelector)).forEach(function (button) {
            button.hidden = true;
        });
    }

    function isScopedSpeechActionButton(button) {
        return !!(button && button.closest && button.closest(articleWidgetSelector + ', .qiling-speech-player'));
    }

    function bindEvents() {
        document.addEventListener('click', function (event) {
            var actionButton = event.target.closest ? event.target.closest('[data-speech-action]') : null;
            var commentButton = event.target.closest ? event.target.closest(commentButtonSelector) : null;

            if (actionButton && isScopedSpeechActionButton(actionButton)) {
                event.preventDefault();
                handleArticleAction(actionButton.getAttribute('data-speech-action') || '');
                return;
            }

            if (commentButton) {
                event.preventDefault();
                handleCommentButton(commentButton);
            }
        });

        document.addEventListener('input', function (event) {
            var settingInput = event.target && event.target.matches && event.target.matches('[data-speech-setting]')
                ? event.target
                : null;

            if (settingInput) {
                handleSettingInput(settingInput);
            }
        });

        document.addEventListener('visibilitychange', function () {
            if (config.pauseOnHidden && document.hidden && current) {
                pauseSpeech();
            }
        });

        window.addEventListener('beforeunload', function () {
            if (synth) {
                synth.cancel();
            }
        });
    }

    function init() {
        if (!synth || typeof window.SpeechSynthesisUtterance === 'undefined') {
            setUnsupportedState();
            return;
        }

        createMiniPlayer();
        refreshVoices();
        window.setTimeout(refreshVoices, 300);
        window.setTimeout(refreshVoices, 1200);

        if (typeof synth.addEventListener === 'function') {
            synth.addEventListener('voiceschanged', refreshVoices);
        }

        setReadyState();
        bindEvents();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
