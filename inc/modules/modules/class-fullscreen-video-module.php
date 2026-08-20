<?php
/**
 * Full Screen Video Module - 全屏沉浸式视频首屏
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Modules\Modules;

use Developer_Starter\Modules\Module_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Fullscreen_Video_Module extends Module_Base {

    public function __construct() {
        $this->category = 'hero';
        $this->icon = 'dashicons-video-alt3';
        $this->description = __( '全屏沉浸式视频背景首屏，支持光晕标题和底部互动导航', 'developer-starter' );
    }

    public function get_id() {
        return 'fullscreen_video';
    }

    public function get_name() {
        return __( '全屏视频首屏', 'developer-starter' );
    }

    public function get_fields() {
        return array(
            // --- 视频/背景设置 ---
            array( 'id' => 'fsv_video_url', 'type' => 'text', 'label' => __( '背景视频URL (.mp4)', 'developer-starter' ), 'desc' => __( '建议使用CDN链接或相对路径，视频将自动循环播放', 'developer-starter' ) ),
            array( 
                'id' => 'fsv_overlay_opacity', 
                'type' => 'text', 
                'label' => __( '遮罩浓度 (0-1)', 'developer-starter' ), 
                'default' => '0.3',
                'desc' => __( '黑色遮罩层的透明度，用于提升文字可读性', 'developer-starter' )
            ),

            // --- 标题内容 ---
            array( 'id' => 'fsv_title', 'type' => 'text', 'label' => __( '主标题', 'developer-starter' ), 'default' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '启灵主题 · <strong>超乎想象</strong>', 'Qiling Theme <strong>beyond expectations</strong>' ) : __( '启灵主题 · <strong>超乎想象</strong>', 'developer-starter' ), 'desc' => __( '支持HTML标签，如 <strong>加粗</strong>, <span>文字</span>', 'developer-starter' ) ),
            array( 'id' => 'fsv_subtitle', 'type' => 'text', 'label' => __( '副标题/描述', 'developer-starter' ), 'default' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '极速开发 · 优雅设计 · 极致体验', 'Fast delivery, refined design, and a polished experience.' ) : __( '极速开发 · 优雅设计 · 极致体验', 'developer-starter' ) ),
            
            // --- 标题特效 ---
            array( 
                'id' => 'fsv_glow_color', 
                'type' => 'color', 
                'label' => __( '光晕颜色', 'developer-starter' ), 
                'default' => 'var(--color-neutral-0)',
                'desc' => __( '标题呼吸光晕的颜色', 'developer-starter' )
            ),
            array( 
                'id' => 'fsv_glow_intensity', 
                'type' => 'select', 
                'label' => __( '光晕强度', 'developer-starter' ), 
                'options' => array(
                    'low' => __( '微光', 'developer-starter' ),
                    'medium' => __( '正常', 'developer-starter' ),
                    'high' => __( '强光', 'developer-starter' ),
                ),
                'default' => 'medium'
            ),

            // --- 底部导航 ---
            array(
                'id' => 'fsv_nav_items',
                'label' => __( '底部导航按钮', 'developer-starter' ),
                'type' => 'repeater',
                'fields' => array(
                    array( 'id' => 'item_label', 'label' => __( '按钮文字', 'developer-starter' ), 'type' => 'text', 'default' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '快速入门', 'Quick Start' ) : __( '快速入门', 'developer-starter' ) ),
                    array( 'id' => 'item_icon', 'label' => __( '按钮图标', 'developer-starter' ), 'type' => 'text', 'default' => '🚀', 'desc' => __( '支持 Emoji (如 🔥) 或图标类名 (如 icon-home)', 'developer-starter' ) ),
                    array( 
                        'id' => 'item_type', 
                        'label' => __( '交互类型', 'developer-starter' ), 
                        'type' => 'select',
                        'options' => array(
                            'link' => __( '直接跳转链接', 'developer-starter' ),
                            'qr' => __( '悬停显示二维码', 'developer-starter' ),
                        ),
                        'default' => 'link'
                    ),
                    array( 'id' => 'item_link', 'label' => __( '跳转链接', 'developer-starter' ), 'type' => 'text', 'dependency' => array( 'item_type', '==', 'link' ) ),
                    array( 'id' => 'item_qr', 'label' => __( '二维码图片', 'developer-starter' ), 'type' => 'image', 'dependency' => array( 'item_type', '==', 'qr' ) ),
                    array( 'id' => 'item_qr_desc', 'label' => __( '二维码描述', 'developer-starter' ), 'type' => 'text', 'default' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '扫码查看', 'Scan to view' ) : __( '扫码查看', 'developer-starter' ), 'dependency' => array( 'item_type', '==', 'qr' ) ),
                ),
            ),

            // --- 控制选项 ---
            array( 
                'id' => 'fsv_show_controls', 
                'type' => 'select', 
                'label' => __( '显示右上角控制按钮', 'developer-starter' ), 
                'options' => array( '1' => __( '是', 'developer-starter' ), '0' => __( '否', 'developer-starter' ) ), 
                'default' => '1' 
            ),
        );
    }

    public function render( $data = array() ) {
        // 提取数据
        $video_url = isset( $data['fsv_video_url'] ) ? $data['fsv_video_url'] : '';
        $overlay_opacity = isset( $data['fsv_overlay_opacity'] ) ? $data['fsv_overlay_opacity'] : '0.3';
        
        $title = isset( $data['fsv_title'] ) ? $data['fsv_title'] : '';
        $subtitle = isset( $data['fsv_subtitle'] ) ? $data['fsv_subtitle'] : '';
        
        $glow_color = isset( $data['fsv_glow_color'] ) ? $data['fsv_glow_color'] : 'var(--color-neutral-0)';
        $glow_intensity = isset( $data['fsv_glow_intensity'] ) ? $data['fsv_glow_intensity'] : 'medium';
        
        $nav_items = isset( $data['fsv_nav_items'] ) ? $data['fsv_nav_items'] : array();
        $show_controls = isset( $data['fsv_show_controls'] ) && $data['fsv_show_controls'] === '1';

        // 生成唯一ID
        $module_id = 'fsv-' . uniqid();
        
        // CSS 变量
        $css_vars = array();
        $css_vars[] = "--fsv-glow-color: {$glow_color}";
        $css_vars[] = "--fsv-overlay: {$overlay_opacity}";
        
        // 光晕强度映射
        $blur_radius = '10px';
        if ( $glow_intensity === 'low' ) $blur_radius = '5px';
        if ( $glow_intensity === 'high' ) $blur_radius = '20px';
        $css_vars[] = "--fsv-blur: {$blur_radius}";

        ?>
        <section class="module module-fullscreen-video" id="<?php echo esc_attr( $module_id ); ?>" style="<?php echo implode('; ', $css_vars); ?>">
            
            <!-- 视频背景 -->
            <div class="fsv-bg">
                <div class="fsv-overlay"></div>
                <?php if ( $video_url ) : ?>
                    <video 
                        class="fsv-video" 
                        src="<?php echo esc_url( $video_url ); ?>" 
                        autoplay muted loop playsinline
                    ></video>
                <?php else : ?>
                    <div class="fsv-placeholder">
                        <span><?php esc_html_e( '请设置背景视频URL', 'developer-starter' ); ?></span>
                    </div>
                <?php endif; ?>
            </div>

            <!-- 右上角控制区 -->
            <?php if ( $show_controls && $video_url ) : ?>
                <div class="fsv-controls" style="z-index: 200;">
                    <button class="fsv-ctrl-btn" data-action="play-pause" title="<?php esc_attr_e( '播放/暂停', 'developer-starter' ); ?>">
                        <!-- Play Icon -->
                        <svg class="icon-play" width="20" height="20" viewBox="0 0 24 24" fill="currentColor" style="display:none;"><path d="M8 5v14l11-7z"/></svg>
                        <!-- Pause Icon -->
                        <svg class="icon-pause" width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/></svg>
                    </button>
                    <button class="fsv-ctrl-btn" data-action="mute-unmute" title="<?php esc_attr_e( '静音/取消静音', 'developer-starter' ); ?>">
                        <!-- Mute Icon -->
                        <svg class="icon-mute" width="20" height="20" viewBox="0 0 24 24" fill="currentColor" style="display:none;"><path d="M16.5 12c0-1.77-1.02-3.29-2.5-4.03v2.21l2.45 2.45c.03-.2.05-.41.05-.63zm2.5 0c0 .94-.2 1.82-.54 2.64l1.51 1.51C20.63 14.91 21 13.5 21 12c0-4.28-2.99-7.86-7-8.77v2.06c2.89.86 5 3.54 5 6.71zM4.27 3L3 4.27 7.73 9H3v6h4l5 5v-6.73l4.25 4.25c-.67.52-1.42.93-2.25 1.18v2.06c1.38-.31 2.63-.95 3.69-1.81L19.73 21 21 19.73 4.27 3zM12 4L9.91 6.09 12 8.18V4z"/></svg>
                        <!-- Volume Icon -->
                        <svg class="icon-volume" width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M3 9v6h4l5 5V4L7 9H3zm13.5 3c0-1.77-1.02-3.29-2.5-4.03v8.05c1.48-.73 2.5-2.25 2.5-4.02zM14 3.23v2.06c2.89.86 5 3.54 5 6.71s-2.11 5.85-5 6.71v2.06c4.01-.91 7-4.49 7-8.77s-2.99-7.86-7-8.77z"/></svg>
                    </button>
                </div>
            <?php endif; ?>

            <!-- 主内容字幕 -->
            <div class="fsv-content">
                <?php if ( $subtitle ) : ?>
                    <div class="fsv-subtitle tracking-in-expand"><?php echo wp_kses_post( $subtitle ); ?></div>
                <?php endif; ?>
                
                <?php if ( $title ) : ?>
                    <h1 class="fsv-title glow-animation"><?php echo wp_kses_post( $title ); ?></h1>
                <?php endif; ?>
            </div>

            <!-- 底部导航 -->
            <?php if ( ! empty( $nav_items ) ) : ?>
                <div class="fsv-bottom-nav">
                    <?php foreach ( $nav_items as $item ) : 
                        $type = isset( $item['item_type'] ) ? $item['item_type'] : 'link';
                        $label = isset( $item['item_label'] ) ? $item['item_label'] : '';
                        $icon = isset( $item['item_icon'] ) ? trim( $item['item_icon'] ) : '';
                        $link = isset( $item['item_link'] ) ? $item['item_link'] : 'javascript:;';
                        $qr_img = isset( $item['item_qr'] ) ? $item['item_qr'] : '';
                        $qr_desc = isset( $item['item_qr_desc'] ) ? $item['item_qr_desc'] : '';
                    ?>
                        <div class="fsv-nav-item <?php echo $type === 'qr' ? 'has-qr' : ''; ?>">
                            <a href="<?php echo $type === 'link' ? esc_url( $link ) : 'javascript:;'; ?>" class="fsv-nav-link">
                                <?php if ( ! empty( $icon ) ) : ?>
                                    <?php echo developer_starter_get_icon_html( $icon ); ?>
                                <?php endif; ?>
                                <span><?php echo esc_html( $label ); ?></span>
                            </a>
                            
                            <?php if ( $type === 'qr' && $qr_img ) : ?>
                                <div class="fsv-qr-card">
                                    <div class="fsv-qr-box">
                                        <img src="<?php echo esc_url( $qr_img ); ?>" alt="QR Code">
                                    </div>
                                    <?php if ( $qr_desc ) : ?>
                                        <div class="fsv-qr-text"><?php echo esc_html( $qr_desc ); ?></div>
                                    <?php endif; ?>
                                    <div class="fsv-qr-arrow"></div>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

        </section>

        <?php if ( $video_url ) : ?>
        <script>
        (function() {
        function boot() {
            var container = document.getElementById('<?php echo esc_js( $module_id ); ?>');
            if (!container || container.dataset.fullscreenVideoInitialized) return;
            container.dataset.fullscreenVideoInitialized = 'true';

            var video = container.querySelector('.fsv-video');
            var btnPlay = container.querySelector('[data-action="play-pause"] i');
            var btnMute = container.querySelector('[data-action="mute-unmute"] i');

            if (video) {
                var btnPlay = container.querySelector('[data-action="play-pause"]');
                var btnMute = container.querySelector('[data-action="mute-unmute"]');
                
                // 播放/暂停
                btnPlay?.addEventListener('click', function() {
                    var iconPlay = this.querySelector('.icon-play');
                    var iconPause = this.querySelector('.icon-pause');
                    
                    if (video.paused) {
                        video.play();
                        iconPlay.style.display = 'none';
                        iconPause.style.display = 'block';
                    } else {
                        video.pause();
                        iconPlay.style.display = 'block';
                        iconPause.style.display = 'none';
                    }
                });

                // 静音/取消静音
                btnMute?.addEventListener('click', function() {
                    var iconMute = this.querySelector('.icon-mute');
                    var iconVolume = this.querySelector('.icon-volume');
                    
                    video.muted = !video.muted;
                    if (video.muted) {
                        iconMute.style.display = 'block';
                        iconVolume.style.display = 'none';
                    } else {
                        iconMute.style.display = 'none';
                        iconVolume.style.display = 'block';
                    }
                });
            }
        }
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', boot, { once: true });
        } else {
            boot();
        }
        })();
        </script>
        <?php endif;
    }
}
