/**
 * 启灵主题页脚动画特效脚本
 * 
 * 支持的特效类型:
 * - particles: 粒子飘动
 * - lines: 线条网络
 * - waves: 波浪效果
 * - stars: 星空闪烁
 * - bubbles: 气泡上升
 * - snow: 雪花飘落
 * - aurora: 极光效果
 * - fireflies: 萤火虫
 */

(function() {
    'use strict';

    var canvas = document.getElementById('footer-effect-canvas');
    if (!canvas) return;

    var ctx = canvas.getContext('2d');
    var effectType = window.footerEffectType || 'particles';
    var particles = [];
    var animationId = null;

    /**
     * 调整画布大小
     */
    function resize() {
        canvas.width = canvas.parentElement.offsetWidth;
        canvas.height = canvas.parentElement.offsetHeight;
        init(); // 重新初始化粒子
    }

    /**
     * 初始化粒子/对象
     */
    function init() {
        particles = [];
        var count;

        switch (effectType) {
            case 'stars':
                count = 80;
                break;
            case 'particles':
            case 'bubbles':
            case 'fireflies':
                count = 50;
                break;
            case 'snow':
                count = 60;
                break;
            case 'lines':
                count = 30;
                break;
            default:
                count = 40;
        }

        for (var i = 0; i < count; i++) {
            particles.push(createParticle());
        }
    }

    /**
     * 创建粒子对象
     */
    function createParticle() {
        var particle = {
            x: Math.random() * canvas.width,
            y: Math.random() * canvas.height,
            size: Math.random() * 3 + 1,
            speedX: (Math.random() - 0.5) * 0.5,
            speedY: (Math.random() - 0.5) * 0.5,
            opacity: Math.random() * 0.5 + 0.2,
            phase: Math.random() * Math.PI * 2
        };

        // 特定类型的特殊属性
        if (effectType === 'bubbles') {
            particle.speedY = -Math.random() * 1 - 0.5; // 向上
            particle.size = Math.random() * 8 + 4;
            particle.wobble = Math.random() * 0.02;
        } else if (effectType === 'snow') {
            particle.speedY = Math.random() * 1 + 0.5; // 向下
            particle.speedX = (Math.random() - 0.5) * 0.3;
            particle.size = Math.random() * 4 + 2;
        } else if (effectType === 'fireflies') {
            particle.glowPhase = Math.random() * Math.PI * 2;
            particle.glowSpeed = Math.random() * 0.05 + 0.02;
        }

        return particle;
    }

    /**
     * 绘制粒子效果
     */
    function drawParticles() {
        particles.forEach(function(p) {
            ctx.beginPath();
            ctx.arc(p.x, p.y, p.size, 0, Math.PI * 2);
            ctx.fillStyle = 'rgba(255,255,255,' + p.opacity + ')';
            ctx.fill();

            p.x += p.speedX;
            p.y += p.speedY;

            // 边界检测
            if (p.x < 0) p.x = canvas.width;
            if (p.x > canvas.width) p.x = 0;
            if (p.y < 0) p.y = canvas.height;
            if (p.y > canvas.height) p.y = 0;
        });
    }

    /**
     * 绘制线条网络效果
     */
    function drawLines() {
        ctx.strokeStyle = 'rgba(255,255,255,0.1)';
        ctx.lineWidth = 1;

        particles.forEach(function(p, i) {
            particles.forEach(function(p2, j) {
                if (i < j) {
                    var dx = p.x - p2.x;
                    var dy = p.y - p2.y;
                    var dist = Math.sqrt(dx * dx + dy * dy);
                    if (dist < 120) {
                        ctx.beginPath();
                        ctx.moveTo(p.x, p.y);
                        ctx.lineTo(p2.x, p2.y);
                        ctx.globalAlpha = 1 - dist / 120;
                        ctx.stroke();
                        ctx.globalAlpha = 1;
                    }
                }
            });

            ctx.beginPath();
            ctx.arc(p.x, p.y, 2, 0, Math.PI * 2);
            ctx.fillStyle = 'rgba(255,255,255,0.5)';
            ctx.fill();

            p.x += p.speedX;
            p.y += p.speedY;

            if (p.x < 0 || p.x > canvas.width) p.speedX *= -1;
            if (p.y < 0 || p.y > canvas.height) p.speedY *= -1;
        });
    }

    /**
     * 绘制波浪效果
     */
    function drawWaves() {
        var time = Date.now() * 0.001;

        for (var w = 0; w < 3; w++) {
            ctx.beginPath();
            ctx.moveTo(0, canvas.height);

            for (var x = 0; x <= canvas.width; x += 10) {
                var y = canvas.height - 30 - w * 20 + Math.sin(x * 0.01 + time + w) * 15;
                ctx.lineTo(x, y);
            }

            ctx.lineTo(canvas.width, canvas.height);
            ctx.closePath();
            ctx.fillStyle = 'rgba(255,255,255,' + (0.03 + w * 0.02) + ')';
            ctx.fill();
        }
    }

    /**
     * 绘制星空闪烁效果
     */
    function drawStars() {
        var time = Date.now() * 0.002;

        particles.forEach(function(p) {
            var twinkle = Math.sin(time + p.phase) * 0.5 + 0.5;
            ctx.beginPath();
            ctx.arc(p.x, p.y, p.size * twinkle, 0, Math.PI * 2);
            ctx.fillStyle = 'rgba(255,255,255,' + (p.opacity * twinkle) + ')';
            ctx.fill();
        });
    }

    /**
     * 绘制气泡上升效果
     */
    function drawBubbles() {
        var time = Date.now() * 0.001;

        particles.forEach(function(p) {
            // 左右摇摆
            var wobbleX = Math.sin(time * 2 + p.phase) * 20 * p.wobble;

            ctx.beginPath();
            ctx.arc(p.x + wobbleX, p.y, p.size, 0, Math.PI * 2);
            
            // 气泡边缘
            ctx.strokeStyle = 'rgba(255,255,255,' + (p.opacity * 0.6) + ')';
            ctx.lineWidth = 1;
            ctx.stroke();
            
            // 气泡高光
            ctx.beginPath();
            ctx.arc(p.x + wobbleX - p.size * 0.3, p.y - p.size * 0.3, p.size * 0.2, 0, Math.PI * 2);
            ctx.fillStyle = 'rgba(255,255,255,' + (p.opacity * 0.8) + ')';
            ctx.fill();

            p.y += p.speedY;
            p.x += p.speedX;

            // 超出顶部后重置到底部
            if (p.y < -p.size * 2) {
                p.y = canvas.height + p.size * 2;
                p.x = Math.random() * canvas.width;
            }
        });
    }

    /**
     * 绘制雪花飘落效果
     */
    function drawSnow() {
        var time = Date.now() * 0.001;

        particles.forEach(function(p) {
            // 横向摇摆
            var swayX = Math.sin(time + p.phase) * 30;

            ctx.beginPath();
            ctx.arc(p.x + swayX, p.y, p.size, 0, Math.PI * 2);
            ctx.fillStyle = 'rgba(255,255,255,' + p.opacity + ')';
            ctx.fill();

            p.y += p.speedY;
            p.x += p.speedX;

            // 超出底部后重置到顶部
            if (p.y > canvas.height + p.size) {
                p.y = -p.size;
                p.x = Math.random() * canvas.width;
            }
        });
    }

    /**
     * 绘制极光效果
     */
    function drawAurora() {
        var time = Date.now() * 0.0005;
        var gradient;

        for (var i = 0; i < 3; i++) {
            ctx.beginPath();
            ctx.moveTo(0, canvas.height);

            for (var x = 0; x <= canvas.width; x += 5) {
                var y = canvas.height * 0.3 + 
                        Math.sin(x * 0.005 + time * (i + 1)) * 50 +
                        Math.sin(x * 0.01 + time * 0.5) * 30;
                ctx.lineTo(x, y + i * 40);
            }

            ctx.lineTo(canvas.width, canvas.height);
            ctx.closePath();

            // 渐变色
            gradient = ctx.createLinearGradient(0, 0, canvas.width, 0);
            if (i === 0) {
                gradient.addColorStop(0, 'rgba(100, 200, 255, 0.1)');
                gradient.addColorStop(0.5, 'rgba(150, 100, 255, 0.15)');
                gradient.addColorStop(1, 'rgba(100, 255, 150, 0.1)');
            } else if (i === 1) {
                gradient.addColorStop(0, 'rgba(150, 100, 255, 0.08)');
                gradient.addColorStop(0.5, 'rgba(100, 255, 200, 0.1)');
                gradient.addColorStop(1, 'rgba(200, 100, 255, 0.08)');
            } else {
                gradient.addColorStop(0, 'rgba(100, 255, 200, 0.05)');
                gradient.addColorStop(0.5, 'rgba(100, 150, 255, 0.08)');
                gradient.addColorStop(1, 'rgba(150, 255, 100, 0.05)');
            }

            ctx.fillStyle = gradient;
            ctx.fill();
        }
    }

    /**
     * 绘制萤火虫效果
     */
    function drawFireflies() {
        var time = Date.now() * 0.001;

        particles.forEach(function(p) {
            // 更新发光相位
            p.glowPhase += p.glowSpeed;
            var glow = (Math.sin(p.glowPhase) + 1) / 2; // 0-1

            // 绘制发光效果
            var gradient = ctx.createRadialGradient(p.x, p.y, 0, p.x, p.y, p.size * 4);
            gradient.addColorStop(0, 'rgba(255, 255, 150, ' + (glow * 0.8) + ')');
            gradient.addColorStop(0.3, 'rgba(255, 230, 100, ' + (glow * 0.4) + ')');
            gradient.addColorStop(1, 'rgba(255, 200, 50, 0)');

            ctx.beginPath();
            ctx.arc(p.x, p.y, p.size * 4, 0, Math.PI * 2);
            ctx.fillStyle = gradient;
            ctx.fill();

            // 萤火虫核心
            ctx.beginPath();
            ctx.arc(p.x, p.y, p.size * 0.5, 0, Math.PI * 2);
            ctx.fillStyle = 'rgba(255, 255, 200, ' + glow + ')';
            ctx.fill();

            // 随机移动
            p.x += Math.sin(time + p.phase) * 0.5;
            p.y += Math.cos(time * 0.7 + p.phase) * 0.3;

            // 边界检测
            if (p.x < 0) p.x = canvas.width;
            if (p.x > canvas.width) p.x = 0;
            if (p.y < 0) p.y = canvas.height;
            if (p.y > canvas.height) p.y = 0;
        });
    }

    /**
     * 主绘制函数
     */
    function draw() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);

        switch (effectType) {
            case 'particles':
                drawParticles();
                break;
            case 'lines':
                drawLines();
                break;
            case 'waves':
                drawWaves();
                break;
            case 'stars':
                drawStars();
                break;
            case 'bubbles':
                drawBubbles();
                break;
            case 'snow':
                drawSnow();
                break;
            case 'aurora':
                drawAurora();
                break;
            case 'fireflies':
                drawFireflies();
                break;
            default:
                drawParticles();
        }

        animationId = requestAnimationFrame(draw);
    }

    // 初始化
    resize();
    window.addEventListener('resize', resize);
    draw();

    // 可见性API - 页面不可见时暂停动画
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            if (animationId) {
                cancelAnimationFrame(animationId);
                animationId = null;
            }
        } else {
            if (!animationId) {
                draw();
            }
        }
    });
})();
