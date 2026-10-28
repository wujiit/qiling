/**
 * 启灵主题文章海报脚本
 *
 * 负责文章分享海报弹层、二维码与下载交互。
 */
(function () {
    'use strict';

    function getConfig() {
        return window.dsPostPosterConfig || {};
    }

    function getTip(key, fallback) {
        var tips = getConfig().tips || {};
        return tips[key] || fallback;
    }

    function getLabel(key, fallback) {
        var labels = getConfig().labels || {};
        return labels[key] || fallback;
    }

    function getActions() {
        return getConfig().actions || {};
    }

    function getAssetUrl(key) {
        var assets = getConfig().assets || {};
        return assets[key] || '';
    }

    function getAjaxUrl() {
        return getConfig().ajaxUrl || '';
    }

    function canSaveCache() {
        return getConfig().canSaveCache !== false;
    }

    function loadScriptOnce(url, marker) {
        if (!url) {
            return Promise.resolve(false);
        }

        if (typeof window.DSLoadScriptOnce === 'function') {
            return window.DSLoadScriptOnce(url, marker);
        }

        return new Promise(function (resolve, reject) {
            var existing = null;

            if (marker) {
                existing = document.querySelector('script[' + marker + '="1"]');
            }

            if (!existing) {
                var scripts = document.querySelectorAll('script[src]');
                for (var i = 0; i < scripts.length; i += 1) {
                    if ((scripts[i].getAttribute('src') || '') === url) {
                        existing = scripts[i];
                        break;
                    }
                }
            }

            if (existing) {
                if (
                    existing.getAttribute('data-ds-loaded') === '1'
                    || existing.readyState === 'complete'
                    || existing.readyState === 'loaded'
                ) {
                    resolve(true);
                    return;
                }

                existing.addEventListener('load', function () {
                    existing.setAttribute('data-ds-loaded', '1');
                    resolve(true);
                }, { once: true });
                existing.addEventListener('error', function () {
                    reject(new Error('script load failed'));
                }, { once: true });
                return;
            }

            var script = document.createElement('script');
            script.src = url;
            script.defer = true;
            if (marker) {
                script.setAttribute(marker, '1');
            }
            script.onload = function () {
                script.setAttribute('data-ds-loaded', '1');
                resolve(true);
            };
            script.onerror = function () {
                reject(new Error('script load failed'));
            };
            document.head.appendChild(script);
        });
    }

    var qrcodeLibPromise = null;

    function ensureQrCodeLib() {
        if (window.jQuery && window.jQuery.fn && window.jQuery.fn.qrcode) {
            return Promise.resolve(true);
        }

        if (qrcodeLibPromise) {
            return qrcodeLibPromise;
        }

        var jqueryUrl = getAssetUrl('jquery');
        var qrcodeUrl = getAssetUrl('qrcode');

        qrcodeLibPromise = (window.jQuery
            ? Promise.resolve(true)
            : loadScriptOnce(jqueryUrl, 'data-ds-post-poster-jquery')
        ).then(function () {
            if (window.jQuery && window.jQuery.fn && window.jQuery.fn.qrcode) {
                return true;
            }

            return loadScriptOnce(qrcodeUrl, 'data-ds-post-poster-qrcode').then(function () {
                return !!(window.jQuery && window.jQuery.fn && window.jQuery.fn.qrcode);
            });
        }).catch(function () {
            return false;
        }).then(function (ready) {
            if (!ready) {
                qrcodeLibPromise = null;
            }
            return ready;
        });

        return qrcodeLibPromise;
    }

    function requestAjax(action, payload) {
        var url = getAjaxUrl();
        if (!url || !action) {
            return Promise.resolve(null);
        }

        var formData = new FormData();
        formData.append('action', action);
        Object.keys(payload || {}).forEach(function (key) {
            formData.append(key, payload[key]);
        });

        return fetch(url, {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        }).then(function (res) {
            return res.text();
        }).then(function (text) {
            if (!text) {
                return null;
            }
            try {
                return JSON.parse(text);
            } catch (err) {
                return null;
            }
        }).catch(function () {
            return null;
        });
    }

    function buildServerDownloadUrl(data) {
        var ajaxUrl = getAjaxUrl();
        var action = getActions().download;
        if (!ajaxUrl || !action || !data.postId || !data.cacheKey || !data.nonce) {
            return '';
        }

        try {
            var u = new URL(ajaxUrl, window.location.origin);
            u.searchParams.set('action', action);
            u.searchParams.set('post_id', data.postId);
            u.searchParams.set('cache_key', data.cacheKey);
            u.searchParams.set('nonce', data.nonce);
            return u.toString();
        } catch (err) {
            return '';
        }
    }

    function setDownloadAsServer(downloadEl, url, fileName) {
        downloadEl.href = url || '#';
        downloadEl.hidden = !url;
        downloadEl.removeAttribute('download');
        downloadEl.dataset.mode = 'server';
        downloadEl.dataset.fileName = fileName || 'poster.png';
    }

    function setDownloadAsClient(downloadEl, url, fileName) {
        downloadEl.href = url || '#';
        downloadEl.hidden = !url;
        downloadEl.setAttribute('download', fileName || 'poster.png');
        downloadEl.dataset.mode = 'client';
        downloadEl.dataset.fileName = fileName || 'poster.png';
    }

    function roundRectPath(ctx, x, y, w, h, r) {
        var radius = Math.max(0, Math.min(r, Math.min(w, h) / 2));
        ctx.beginPath();
        ctx.moveTo(x + radius, y);
        ctx.arcTo(x + w, y, x + w, y + h, radius);
        ctx.arcTo(x + w, y + h, x, y + h, radius);
        ctx.arcTo(x, y + h, x, y, radius);
        ctx.arcTo(x, y, x + w, y, radius);
        ctx.closePath();
    }

    function drawRoundedImage(ctx, img, x, y, w, h, radius) {
        ctx.save();
        roundRectPath(ctx, x, y, w, h, radius);
        ctx.clip();

        var imgRatio = img.width / img.height;
        var boxRatio = w / h;
        var drawW;
        var drawH;
        var drawX;
        var drawY;

        if (imgRatio > boxRatio) {
            drawH = h;
            drawW = h * imgRatio;
            drawX = x - (drawW - w) / 2;
            drawY = y;
        } else {
            drawW = w;
            drawH = w / imgRatio;
            drawX = x;
            drawY = y - (drawH - h) / 2;
        }

        ctx.drawImage(img, drawX, drawY, drawW, drawH);
        ctx.restore();
    }

    function loadImage(url, useCors) {
        return new Promise(function (resolve, reject) {
            if (!url) {
                reject(new Error('empty image url'));
                return;
            }
            var img = new Image();
            if (useCors) {
                img.crossOrigin = 'anonymous';
                img.referrerPolicy = 'no-referrer';
            }
            img.onload = function () {
                resolve(img);
            };
            img.onerror = function () {
                reject(new Error('load image failed'));
            };
            img.src = url;
        });
    }

    function wrapText(ctx, text, maxWidth, maxLines) {
        var source = String(text || '').replace(/\s+/g, ' ').trim();
        if (!source) {
            return [''];
        }

        var chars = source.split('');
        var lines = [];
        var line = '';

        for (var i = 0; i < chars.length; i += 1) {
            var test = line + chars[i];
            if (ctx.measureText(test).width > maxWidth && line) {
                lines.push(line);
                line = chars[i];
            } else {
                line = test;
            }
        }

        if (line) {
            lines.push(line);
        }

        if (lines.length > maxLines) {
            lines = lines.slice(0, maxLines);
            var last = lines[maxLines - 1];
            while (last.length && ctx.measureText(last + '...').width > maxWidth) {
                last = last.slice(0, -1);
            }
            lines[maxLines - 1] = last + '...';
        }

        return lines;
    }

    function truncateMiddle(text, maxLen) {
        var str = String(text || '');
        if (str.length <= maxLen) {
            return str;
        }
        var keep = Math.floor((maxLen - 3) / 2);
        return str.slice(0, keep) + '...' + str.slice(str.length - keep);
    }

    function normalizeData(trigger) {
        return {
            title: trigger.getAttribute('data-poster-title') || document.title || '',
            url: trigger.getAttribute('data-poster-url') || window.location.href || '',
            cover: trigger.getAttribute('data-poster-cover') || '',
            excerpt: trigger.getAttribute('data-poster-excerpt') || '',
            postId: trigger.getAttribute('data-post-id') || '',
            cacheKey: trigger.getAttribute('data-cache-key') || '',
            nonce: trigger.getAttribute('data-cache-nonce') || ''
        };
    }

    function drawFallbackQr(ctx, x, y, size) {
        ctx.save();
        ctx.fillStyle = '#f1f5f9';
        ctx.fillRect(x, y, size, size);
        ctx.strokeStyle = '#cbd5e1';
        ctx.lineWidth = 2;
        ctx.strokeRect(x, y, size, size);
        ctx.fillStyle = '#64748b';
        ctx.font = '22px sans-serif';
        ctx.textAlign = 'center';
        ctx.fillText('QR', x + size / 2, y + size / 2 + 7);
        ctx.restore();
    }

    function createLocalQrCanvas(text, size) {
        return ensureQrCodeLib().then(function (ready) {
            if (!(ready && window.jQuery && window.jQuery.fn && window.jQuery.fn.qrcode)) {
                throw new Error('qrcode lib missing');
            }

            return new Promise(function (resolve, reject) {
                var holder = document.createElement('div');
                holder.style.position = 'fixed';
                holder.style.left = '-99999px';
                holder.style.top = '-99999px';
                holder.style.width = '1px';
                holder.style.height = '1px';
                holder.style.overflow = 'hidden';
                holder.style.opacity = '0';
                document.body.appendChild(holder);

                try {
                    window.jQuery(holder).empty().qrcode({
                        render: 'canvas',
                        width: size,
                        height: size,
                        text: text || ''
                    });
                    var qrCanvas = holder.querySelector('canvas');
                    if (!qrCanvas) {
                        throw new Error('qrcode canvas empty');
                    }

                    var out = document.createElement('canvas');
                    out.width = size;
                    out.height = size;
                    var outCtx = out.getContext('2d');
                    outCtx.drawImage(qrCanvas, 0, 0, size, size);
                    resolve(out);
                } catch (error) {
                    reject(error);
                } finally {
                    holder.remove();
                }
            });
        });
    }

    async function drawCachedPoster(canvas, url) {
        var ctx = canvas.getContext('2d');
        var img = await loadImage(url, false);
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
    }

    async function getPosterCache(data) {
        if (!data.postId || !data.cacheKey || !data.nonce) {
            return '';
        }
        var actions = getActions();
        var res = await requestAjax(actions.getCache, {
            post_id: data.postId,
            cache_key: data.cacheKey,
            nonce: data.nonce
        });
        if (res && res.success && res.data && res.data.url) {
            return String(res.data.url);
        }
        return '';
    }

    async function savePosterCache(data, canvas) {
        if (!canSaveCache()) {
            return '';
        }

        if (!data.postId || !data.cacheKey || !data.nonce) {
            return '';
        }
        var actions = getActions();
        if (!actions.saveCache) {
            return '';
        }

        var imageData = '';
        try {
            imageData = canvas.toDataURL('image/png');
        } catch (e) {
            return '';
        }

        var res = await requestAjax(actions.saveCache, {
            post_id: data.postId,
            cache_key: data.cacheKey,
            nonce: data.nonce,
            image_data: imageData
        });

        if (res && res.success && res.data && res.data.url) {
            return String(res.data.url);
        }
        return '';
    }

    async function renderPoster(canvas, data, tipEl, downloadEl) {
        var ctx = canvas.getContext('2d');
        var width = canvas.width;
        var height = canvas.height;
        var side = 34;
        var hasCover = false;

        ctx.clearRect(0, 0, width, height);
        ctx.fillStyle = '#f3f7ff';
        ctx.fillRect(0, 0, width, height);

        ctx.shadowColor = 'rgba(15, 23, 42, 0.10)';
        ctx.shadowBlur = 26;
        ctx.shadowOffsetY = 8;
        ctx.fillStyle = '#ffffff';
        roundRectPath(ctx, 16, 16, width - 32, height - 32, 18);
        ctx.fill();
        ctx.shadowColor = 'transparent';

        var coverHeight = 250;
        if (data.cover) {
            try {
                var coverImg = await loadImage(data.cover, true);
                drawRoundedImage(ctx, coverImg, side, 44, width - side * 2, coverHeight, 14);
                hasCover = true;
            } catch (e) {
                hasCover = false;
            }
        }

        var titleTop = hasCover ? 328 : 92;
        ctx.fillStyle = '#0f172a';
        ctx.font = hasCover ? 'bold 34px sans-serif' : 'bold 32px sans-serif';
        ctx.textAlign = 'left';
        var titleLines = wrapText(ctx, data.title, width - side * 2, hasCover ? 3 : 3);
        var titleLineHeight = hasCover ? 46 : 42;
        for (var i = 0; i < titleLines.length; i += 1) {
            ctx.fillText(titleLines[i], side, titleTop + i * titleLineHeight);
        }

        var afterTitleY = titleTop + titleLines.length * titleLineHeight + 12;

        if (!hasCover && data.excerpt) {
            ctx.fillStyle = '#475569';
            ctx.font = '22px sans-serif';
            var excerptLines = wrapText(ctx, data.excerpt, width - side * 2, 5);
            for (var j = 0; j < excerptLines.length; j += 1) {
                ctx.fillText(excerptLines[j], side, afterTitleY + j * 32);
            }
            afterTitleY += excerptLines.length * 32 + 10;
        }

        var dividerTop = Math.max(afterTitleY + 10, hasCover ? 500 : 512);
        ctx.strokeStyle = '#e2e8f0';
        ctx.lineWidth = 2;
        ctx.beginPath();
        ctx.moveTo(side, dividerTop);
        ctx.lineTo(width - side, dividerTop);
        ctx.stroke();

        var qrSize = hasCover ? 180 : 148;
        var qrX = Math.round((width - qrSize) / 2);
        var qrY = dividerTop + (hasCover ? 28 : 24);

        ctx.fillStyle = '#ffffff';
        roundRectPath(ctx, qrX - 10, qrY - 10, qrSize + 20, qrSize + 20, hasCover ? 14 : 12);
        ctx.fill();

        try {
            var qrCanvas = await createLocalQrCanvas(data.url, qrSize);
            ctx.drawImage(qrCanvas, qrX, qrY, qrSize, qrSize);
        } catch (e2) {
            drawFallbackQr(ctx, qrX, qrY, qrSize);
        }

        ctx.fillStyle = '#334155';
        ctx.font = hasCover ? '26px sans-serif' : '22px sans-serif';
        ctx.textAlign = 'center';
        ctx.fillText(getLabel('scanToRead', '扫码阅读全文'), width / 2, qrY + qrSize + (hasCover ? 44 : 34));

        ctx.fillStyle = '#94a3b8';
        ctx.font = hasCover ? '18px sans-serif' : '16px sans-serif';
        ctx.fillText(truncateMiddle(data.url, hasCover ? 46 : 40), width / 2, height - (hasCover ? 42 : 34));

        var savedUrl = await savePosterCache(data, canvas);
        var serverDownloadUrl = savedUrl ? buildServerDownloadUrl(data) : '';

        if (serverDownloadUrl) {
            setDownloadAsServer(downloadEl, serverDownloadUrl, createFileName(data.title));
            tipEl.textContent = getTip('saved', '海报已缓存，下次可直接复用');
            return;
        }

        try {
            var clientDataUrl = canvas.toDataURL('image/png');
            setDownloadAsClient(downloadEl, clientDataUrl, createFileName(data.title));
            tipEl.textContent = getTip('ready', '海报已生成，可下载保存');
        } catch (e4) {
            downloadEl.hidden = true;
            tipEl.textContent = getTip('fallback', '海报已生成。若无法下载，请长按或截图保存。');
        }
    }

    function createFileName(title) {
        var base = String(title || 'post-poster')
            .replace(/[\\/:*?"<>|]/g, '')
            .trim()
            .slice(0, 40);
        return (base || 'post-poster') + '-poster.png';
    }

    function init() {
        var trigger = document.querySelector('.ds-post-poster-trigger');
        var modal = document.getElementById('ds-post-poster-modal');
        var closeBtn = modal ? modal.querySelector('.ds-post-poster-close') : null;
        var canvas = document.getElementById('ds-post-poster-canvas');
        var tipEl = document.getElementById('ds-post-poster-tip');
        var downloadEl = document.getElementById('ds-post-poster-download');

        if (!trigger || !modal || !closeBtn || !canvas || !tipEl || !downloadEl) {
            return;
        }

        modal.hidden = true;
        modal.style.display = 'none';

        var isRendering = false;
        var renderedKey = '';

        function openModal() {
            modal.hidden = false;
            modal.style.display = 'flex';
            document.documentElement.classList.add('ds-poster-open');
            document.body.classList.add('ds-poster-open');
            triggerPoster();
        }

        function closeModal() {
            modal.hidden = true;
            modal.style.display = 'none';
            document.documentElement.classList.remove('ds-poster-open');
            document.body.classList.remove('ds-poster-open');
        }

        async function triggerPoster() {
            if (isRendering) {
                return;
            }

            var data = normalizeData(trigger);
            var key = [data.title, data.url, data.cover, data.excerpt, data.cacheKey].join('||');
            if (key === renderedKey) {
                return;
            }

            isRendering = true;
            renderedKey = '';
            tipEl.textContent = getTip('loading', '正在生成海报...');
            downloadEl.hidden = true;

            try {
                var cacheUrl = await getPosterCache(data);
                if (cacheUrl) {
                    try {
                        await drawCachedPoster(canvas, cacheUrl);
                    } catch (cacheDrawError) {
                        // 缓存图预览失败时，回退到即时重绘
                        await renderPoster(canvas, data, tipEl, downloadEl);
                        renderedKey = key;
                        return;
                    }

                    var serverUrl = buildServerDownloadUrl(data);
                    if (serverUrl) {
                        setDownloadAsServer(downloadEl, serverUrl, createFileName(data.title));
                    }
                    tipEl.textContent = getTip('cached', '已加载缓存海报');
                } else {
                    await renderPoster(canvas, data, tipEl, downloadEl);
                }
                renderedKey = key;
            } catch (err) {
                tipEl.textContent = getTip('error', '海报生成失败，请稍后重试');
            } finally {
                isRendering = false;
            }
        }

        trigger.addEventListener('click', openModal);
        closeBtn.addEventListener('click', closeModal);

        downloadEl.addEventListener('click', function (event) {
            var mode = downloadEl.dataset.mode || '';
            if (mode === 'server') {
                // 走后端附件流下载，不拦截
                return;
            }

            var href = downloadEl.getAttribute('href') || '';
            if (!href || href === '#') {
                event.preventDefault();
                return;
            }

            event.preventDefault();
            var a = document.createElement('a');
            a.href = href;
            a.download = downloadEl.dataset.fileName || 'poster.png';
            document.body.appendChild(a);
            a.click();
            a.remove();
        });

        modal.addEventListener('click', function (event) {
            if (event.target === modal) {
                closeModal();
            }
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && !modal.hidden) {
                closeModal();
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
