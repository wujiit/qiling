/**
 * 启灵主题文章增强脚本
 *
 * 负责文章列表与详情页的前端增强交互（如视频封面等）。
 */

document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    // ===== Video Cover Hover Play =====
    // 视频封面悬停自动播放功能
    (function initVideoCoverHover() {
        var videoCovers = document.querySelectorAll('.post-video-cover');

        if (videoCovers.length === 0) return;

        // 检测是否为移动设备
        var isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);

        var coverObserver = null;
        if ('IntersectionObserver' in window) {
            coverObserver = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (!entry.isIntersecting) return;
                    var targetCover = entry.target;
                    var targetVideo = targetCover.querySelector('.video-cover-player');
                    if (!targetVideo) return;
                    var targetHasPoster = !!(targetVideo.getAttribute('poster') || targetCover.querySelector('.video-poster'));
                    if (targetVideo.dataset.dsMetaLoaded === '1') {
                        coverObserver.unobserve(targetCover);
                        return;
                    }
                    targetVideo.dataset.dsMetaLoaded = '1';
                    targetVideo.preload = targetHasPoster ? 'metadata' : 'auto';
                    targetVideo.load();
                    coverObserver.unobserve(targetCover);
                });
            }, { rootMargin: '180px 0px' });
        }

        videoCovers.forEach(function (cover) {
            var video = cover.querySelector('.video-cover-player');
            if (!video) return;
            var hasPosterImage = !!(video.getAttribute('poster') || cover.querySelector('.video-poster'));
            var previewTime = hasPosterImage ? 0 : 0.001;

            // 无海报图时尽早准备一点首帧数据，避免列表卡片白屏。
            video.preload = hasPosterImage ? 'none' : 'auto';

            var ensureVideoMetaLoaded = function () {
                if (video.dataset.dsMetaLoaded === '1') return;
                video.dataset.dsMetaLoaded = '1';
                video.preload = hasPosterImage ? 'metadata' : 'auto';
                video.load();
            };

            var restorePreviewFrame = function () {
                if (previewTime <= 0) {
                    try {
                        video.currentTime = 0;
                    } catch (err) {}
                    return;
                }

                ensureVideoMetaLoaded();

                var seekToPreview = function () {
                    try {
                        if (video.currentTime !== previewTime) {
                            video.currentTime = previewTime;
                        }
                    } catch (err) {}
                };

                if (video.readyState >= 1) {
                    seekToPreview();
                } else {
                    video.addEventListener('loadedmetadata', seekToPreview, { once: true });
                }
            };

            if (coverObserver) {
                coverObserver.observe(cover);
            }

            if (previewTime > 0) {
                video.addEventListener('loadedmetadata', function () {
                    if (!cover.classList.contains('is-playing')) {
                        restorePreviewFrame();
                    }
                });
            }

            if (isMobile) {
                // 移动端：点击播放/暂停切换
                var isPlaying = false;

                // 阻止overlay链接在播放时跳转
                cover.addEventListener('click', function (e) {
                    // 如果点击的是播放按钮区域
                    if (e.target.closest('.video-play-overlay') || e.target.closest('.video-cover-overlay-link')) {
                        // 第一次点击播放视频
                        if (!isPlaying) {
                            e.preventDefault();
                            e.stopPropagation();

                            ensureVideoMetaLoaded();
                            video.play().then(function () {
                                isPlaying = true;
                                cover.classList.add('is-playing');
                            }).catch(function (err) {
                                console.warn('Video play failed:', err);
                            });
                        }
                        // 第二次点击跳转到文章（让默认行为执行）
                    }
                });

                // 视频结束时重置状态
                video.addEventListener('ended', function () {
                    isPlaying = false;
                    cover.classList.remove('is-playing');
                    restorePreviewFrame();
                });

            } else {
                // 桌面端：鼠标悬停播放
                cover.addEventListener('mouseenter', function () {
                    // 播放视频
                    ensureVideoMetaLoaded();
                    video.play().catch(function (err) {
                        // 自动播放可能被浏览器阻止，忽略错误
                        console.warn('Video autoplay prevented:', err);
                    });
                });

                cover.addEventListener('mouseleave', function () {
                    // 暂停并重置视频
                    video.pause();
                    restorePreviewFrame();
                });
            }

            if (previewTime > 0 && !coverObserver) {
                restorePreviewFrame();
            }

            // 视频加载错误处理
            video.addEventListener('error', function () {
                // 如果视频加载失败，隐藏视频元素，只显示封面图
                video.style.display = 'none';
                cover.classList.remove('post-video-cover');
            });
        });
    })();
});
