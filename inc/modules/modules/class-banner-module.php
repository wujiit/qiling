<?php
/**
 * Banner Module - 首屏Banner
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Modules\Modules;

use Developer_Starter\Modules\Module_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Banner_Module extends Module_Base {

    public function __construct() {
        $this->category = 'homepage';
        $this->icon = 'dashicons-format-image';
        $this->description = __( '首屏Banner模块', 'developer-starter' );
    }

    public function get_id() {
        return 'banner';
    }

    public function get_name() {
        return __( '首屏Banner', 'developer-starter' );
    }

    public function get_fields() {
        return array(
            array(
                'id' => 'banner_layout',
                'label' => __( '布局风格', 'developer-starter' ),
                'type' => 'select',
                'options' => array(
                    'slider' => __( '全屏轮播 (Slider)', 'developer-starter' ),
                    'image_text' => __( '图文左右 (Image + Text)', 'developer-starter' ),
                ),
                'default' => 'slider',
            ),
            array(
                'id' => 'banner_height',
                'label' => __( '模块高度', 'developer-starter' ),
                'type' => 'select',
                'options' => array(
                    'full' => __( '全屏 (100vh)', 'developer-starter' ),
                    'large' => __( '高 (80vh)', 'developer-starter' ),
                    'medium' => __( '中 (60vh)', 'developer-starter' ),
                    'small' => __( '矮 (50vh)', 'developer-starter' ),
                ),
                'default' => 'full',
            ),
            array(
                'id' => 'banner_image_position',
                'label' => __( '图片/视频位置 (仅图文布局)', 'developer-starter' ),
                'type' => 'select',
                'options' => array(
                    'right' => __( '右侧', 'developer-starter' ),
                    'left' => __( '左侧', 'developer-starter' ),
                ),
                'default' => 'right',
            ),
            array(
                'id' => 'banner_bg_color',
                'label' => __( '背景颜色 (可选)', 'developer-starter' ),
                'type' => 'color',
                'desc' => __( '留空则使用默认渐变色', 'developer-starter' ),
            ),
            array(
                'id' => 'banner_slides',
                'label' => __( '幻灯片内容', 'developer-starter' ),
                'type' => 'repeater',
                'fields' => array(
                    array( 'id' => 'media_type', 'label' => __( '媒体类型', 'developer-starter' ), 'type' => 'select', 'options' => array( 'image' => __( '图片', 'developer-starter' ), 'video' => __( '视频', 'developer-starter' ) ), 'default' => 'image' ),
                    array( 'id' => 'image', 'label' => __( '图片', 'developer-starter' ), 'type' => 'image', 'dependency' => array( 'media_type', '==', 'image' ) ),
                    array( 'id' => 'video_url', 'label' => __( '视频URL (.mp4)', 'developer-starter' ), 'type' => 'text', 'dependency' => array( 'media_type', '==', 'video' ) ),
                    array( 'id' => 'title', 'label' => __( '标题', 'developer-starter' ), 'type' => 'text' ),
                    array( 'id' => 'title_align', 'label' => __( '标题位置（仅PC）', 'developer-starter' ), 'type' => 'select', 'options' => array( 'default' => __( '跟随布局', 'developer-starter' ), 'left' => __( '居左', 'developer-starter' ), 'center' => __( '居中', 'developer-starter' ), 'right' => __( '居右', 'developer-starter' ) ), 'default' => 'default' ),
                    array( 'id' => 'subtitle', 'label' => __( '副标题', 'developer-starter' ), 'type' => 'textarea' ),
                    array( 'id' => 'btn_text', 'label' => __( '按钮文字', 'developer-starter' ), 'type' => 'text' ),
                    array( 'id' => 'btn_url', 'label' => __( '按钮链接', 'developer-starter' ), 'type' => 'text' ),
                    array( 'id' => 'btn_bg_color', 'label' => __( '按钮背景颜色', 'developer-starter' ), 'type' => 'color', 'default' => '' ),
                    array( 'id' => 'btn_text_color', 'label' => __( '按钮文字颜色', 'developer-starter' ), 'type' => 'color', 'default' => '' ),
                    $this->get_button_border_color_field( 'btn_border_color' ),
                ),
            ),
            
            array( 'id' => 'heading_stats', 'type' => 'heading', 'label' => __( '数据展示条 (Stats Bar)', 'developer-starter' ) ),
            array(
                'id' => 'show_stats_bar',
                'label' => __( '显示数据条', 'developer-starter' ),
                'type' => 'select',
                'options' => array( '0' => __( '关闭', 'developer-starter' ), '1' => __( '开启', 'developer-starter' ) ),
                'default' => '0',
            ),
            array(
                'id' => 'stats_bar_bg_color',
                'label' => __( '数据条背景颜色', 'developer-starter' ),
                'type' => 'color',
                'default' => '',
                'desc' => __( '留空则跟随当前模块视觉风格；可填写 HEX、rgba 或渐变等 CSS 背景值。', 'developer-starter' ),
                'dependency' => array( 'show_stats_bar', '==', '1' ),
            ),
            array(
                'id' => 'stats_data',
                'label' => __( '数据项配置', 'developer-starter' ),
                'type' => 'repeater',
                'dependency' => array( 'show_stats_bar', '==', '1' ),
                'fields' => array(
                    array( 'id' => 'icon', 'label' => __( '图标类名', 'developer-starter' ), 'type' => 'text', 'desc' => __( '支持 Emoji 或 Symbol类名', 'developer-starter' ) ),
                    array( 'id' => 'number', 'label' => __( '数值', 'developer-starter' ), 'type' => 'text', 'default' => '10k+' ),
                    array( 'id' => 'label', 'label' => __( '描述文本', 'developer-starter' ), 'type' => 'text', 'default' => __( '活跃用户', 'developer-starter' ) ),
                    array( 'id' => 'color', 'label' => __( '图标/数值颜色', 'developer-starter' ), 'type' => 'color', 'default' => '' ),
                    array( 'id' => 'label_color', 'label' => __( '描述文本颜色', 'developer-starter' ), 'type' => 'color', 'default' => '' ),
                ),
            ),
            
            // === 底部波浪效果 ===
            array(
                'id' => 'banner_wave_enable',
                'label' => __( '【波浪】启用底部波浪', 'developer-starter' ),
                'type' => 'select',
                'options' => array( '0' => __( '关闭', 'developer-starter' ), '1' => __( '开启', 'developer-starter' ) ),
                'default' => '0',
            ),
            array(
                'id' => 'banner_wave_style',
                'label' => __( '波浪样式', 'developer-starter' ),
                'type' => 'select',
                'options' => array( 
                    'single' => __( '单波浪', 'developer-starter' ), 
                    'double' => __( '双波浪', 'developer-starter' ), 
                    'triple' => __( '三波浪', 'developer-starter' ),
                    'curve' => __( '曲线', 'developer-starter' ),
                ),
                'default' => 'single',
                'dependency' => array( 'banner_wave_enable', '==', '1' ),
            ),
            array(
                'id' => 'banner_wave_color',
                'label' => __( '波浪颜色', 'developer-starter' ),
                'type' => 'color',
                'default' => '',
                'desc' => __( '⚠️ 重要：必须与下方模块背景色一致，否则会出现横线', 'developer-starter' ),
                'dependency' => array( 'banner_wave_enable', '==', '1' ),
            ),
            array(
                'id' => 'banner_wave_height',
                'label' => __( '波浪高度', 'developer-starter' ),
                'type' => 'text',
                'default' => '80px',
                'desc' => __( '如 60px、80px、100px', 'developer-starter' ),
                'dependency' => array( 'banner_wave_enable', '==', '1' ),
            ),
            array(
                'id' => 'banner_wave_flip',
                'label' => __( '翻转波浪', 'developer-starter' ),
                'type' => 'select',
                'options' => array( '0' => __( '否', 'developer-starter' ), '1' => __( '是', 'developer-starter' ) ),
                'default' => '0',
                'dependency' => array( 'banner_wave_enable', '==', '1' ),
            ),
        );
    }

    public function render( $data = array() ) {
        $layout = isset( $data['banner_layout'] ) ? $data['banner_layout'] : 'slider';
        $height = isset( $data['banner_height'] ) ? $data['banner_height'] : 'full';
        $slides = isset( $data['banner_slides'] ) ? $data['banner_slides'] : array();
        $image_position = isset( $data['banner_image_position'] ) ? $data['banner_image_position'] : 'right';
        $bg_color = isset( $data['banner_bg_color'] ) ? trim( $data['banner_bg_color'] ) : '';
        
        if ( empty( $slides ) ) {
            $slides = array(
                array(
                    'image'    => '',
                    'title'    => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '专业企业解决方案', 'Professional solutions for modern business' ) : __( '专业企业解决方案', 'developer-starter' ),
                    'subtitle' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '助力企业数字化转型，提供一站式服务', 'Support digital growth with an end-to-end service approach.' ) : __( '助力企业数字化转型，提供一站式服务', 'developer-starter' ),
                    'btn_text' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '了解更多', 'Learn More' ) : __( '了解更多', 'developer-starter' ),
                    'btn_url'  => '#',
                ),
            );
        }
        
        $height_class = 'banner-height-' . $height;
        
        if ( $layout === 'image_text' ) {
            $this->render_image_text_layout( $slides, $image_position, $height_class, $bg_color, $data );
        } else {
            $this->render_slider_layout( $slides, $height_class, $bg_color, $data );
        }
    }

    /**
     * 获取单张幻灯片的标题对齐方式。
     *
     * @param array<string,mixed> $slide 幻灯片数据。
     * @param string              $default_align 默认对齐。
     * @return string
     */
    private function get_slide_title_align( $slide, $default_align = 'center' ) {
        $default_align = in_array( $default_align, array( 'left', 'center', 'right' ), true ) ? $default_align : 'center';
        if ( ! is_array( $slide ) || empty( $slide['title_align'] ) || 'default' === (string) $slide['title_align'] ) {
            return $default_align;
        }

        $title_align = sanitize_key( (string) $slide['title_align'] );
        return in_array( $title_align, array( 'left', 'center', 'right' ), true ) ? $title_align : $default_align;
    }
    
    private function render_slider_layout( $slides, $height_class, $bg_color = '', $data = array() ) {
        // 根据高度类计算实际高度值
        $height_map = array(
            'banner-height-full'   => '100vh',
            'banner-height-large'  => '80vh',
            'banner-height-medium' => '60vh',
            'banner-height-small'  => '50vh',
        );
        $height_value = isset( $height_map[ $height_class ] ) ? $height_map[ $height_class ] : '100vh';
        $has_swiper = count( $slides ) > 1;

        $module_id = 'banner-' . uniqid();
        ?>
        <section class="module module-banner banner-slider <?php echo esc_attr( $height_class ); ?>" id="<?php echo esc_attr( $module_id ); ?>" style="height: <?php echo esc_attr( $height_value ); ?>; min-height: <?php echo esc_attr( $height_value ); ?>; position: relative; overflow: hidden;">
            <?php if ( $has_swiper ) : ?>
                <div class="swiper banner-swiper" style="width: 100%; height: 100%;">
                    <div class="swiper-wrapper">
                        <?php foreach ( $slides as $slide ) : 
                            $media_type = isset( $slide['media_type'] ) ? $slide['media_type'] : 'image';
                            $has_media = ( $media_type === 'video' && ! empty( $slide['video_url'] ) ) || ! empty( $slide['image'] );
                            $title_align = $this->get_slide_title_align( $slide, 'center' );
                        ?>
                            <div class="swiper-slide" style="position: relative; display: flex; align-items: center; justify-content: center;">
                                <?php $this->render_slide_background( $slide, $media_type, $bg_color ); ?>
                                <div class="banner-overlay" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(var(--qiling-rgb-0-0-0), 0.4); z-index: 1;"></div>
                                <div class="container" style="position: relative; z-index: 2;">
                                    <div class="banner-content" style="text-align: center; color: var(--color-neutral-0); max-width: var(--qiling-measure-800); margin: 0 auto;">
                                        <?php if ( ! empty( $slide['title'] ) ) : ?>
                                            <h1 class="banner-title" style="font-size: var(--qiling-text-rem-3p5); font-weight: 700; line-height: 1.2; margin-bottom: var(--qiling-space-20); text-align: <?php echo esc_attr( $title_align ); ?>; text-shadow: 0 2px 10px rgba(var(--qiling-rgb-0-0-0), 0.3);"><?php echo wp_kses_post( $slide['title'] ); ?></h1>
                                        <?php endif; ?>
                                        <?php if ( ! empty( $slide['subtitle'] ) ) : ?>
                                            <p class="banner-subtitle" style="font-size: var(--qiling-text-rem-1p25); opacity: 0.9; margin-bottom: var(--qiling-space-35); line-height: 1.6;"><?php echo wp_kses_post( $slide['subtitle'] ); ?></p>
                                        <?php endif; ?>
                                        <?php if ( ! empty( $slide['btn_text'] ) ) : ?>
                                            <div class="banner-buttons">
                                                <?php
                                                $btn_bg_color = isset( $slide['btn_bg_color'] ) && ! empty( $slide['btn_bg_color'] ) ? $slide['btn_bg_color'] : 'var(--qiling-module-button-bg, var(--qiling-component-button-bg, var(--color-primary)))';
                                                $btn_text_color = isset( $slide['btn_text_color'] ) && ! empty( $slide['btn_text_color'] ) ? $slide['btn_text_color'] : 'var(--qiling-module-button-text, var(--qiling-component-button-text, #ffffff))';
                                                $btn_border_color = isset( $slide['btn_border_color'] ) && ! empty( $slide['btn_border_color'] ) ? $slide['btn_border_color'] : $btn_bg_color;
                                                ?>
                                                <a href="<?php echo esc_url( $slide['btn_url'] ?: '#' ); ?>" class="banner-cta-btn" style="background: <?php echo esc_attr( $btn_bg_color ); ?>; color: <?php echo esc_attr( $btn_text_color ); ?>; border-color: <?php echo esc_attr( $btn_border_color ); ?>;">
                                                    <?php echo esc_html( $slide['btn_text'] ); ?>
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="swiper-pagination"></div>
                    <div class="swiper-button-prev"></div>
                    <div class="swiper-button-next"></div>
                </div>
            <?php else : 
                $slide = $slides[0];
                $media_type = isset( $slide['media_type'] ) ? $slide['media_type'] : 'image';
                $title_align = $this->get_slide_title_align( $slide, 'center' );
            ?>
                <div class="banner-single" style="width: 100%; height: 100%; position: relative; display: flex; align-items: center; justify-content: center;">
                    <?php $this->render_slide_background( $slide, $media_type, $bg_color ); ?>
                    <div class="banner-overlay" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(var(--qiling-rgb-0-0-0), 0.4); z-index: 1;"></div>
                    <div class="container" style="position: relative; z-index: 2;">
                        <div class="banner-content" style="text-align: center; color: var(--color-neutral-0); max-width: var(--qiling-measure-800); margin: 0 auto;">
                            <?php if ( ! empty( $slide['title'] ) ) : ?>
                                <h1 class="banner-title" style="font-size: var(--qiling-text-rem-3p5); font-weight: 700; line-height: 1.2; margin-bottom: var(--qiling-space-20); text-align: <?php echo esc_attr( $title_align ); ?>; text-shadow: 0 2px 10px rgba(var(--qiling-rgb-0-0-0), 0.3);"><?php echo wp_kses_post( $slide['title'] ); ?></h1>
                            <?php endif; ?>
                            <?php if ( ! empty( $slide['subtitle'] ) ) : ?>
                                <p class="banner-subtitle" style="font-size: var(--qiling-text-rem-1p25); opacity: 0.9; margin-bottom: var(--qiling-space-35); line-height: 1.6;"><?php echo wp_kses_post( $slide['subtitle'] ); ?></p>
                            <?php endif; ?>
                            <?php if ( ! empty( $slide['btn_text'] ) ) : ?>
                                <div class="banner-buttons">
                                    <?php
                                     $btn_bg_color = isset( $slide['btn_bg_color'] ) && ! empty( $slide['btn_bg_color'] ) ? $slide['btn_bg_color'] : 'var(--qiling-module-button-bg, var(--qiling-component-button-bg, var(--color-primary)))';
                                     $btn_text_color = isset( $slide['btn_text_color'] ) && ! empty( $slide['btn_text_color'] ) ? $slide['btn_text_color'] : 'var(--qiling-module-button-text, var(--qiling-component-button-text, #ffffff))';
                                    $btn_border_color = isset( $slide['btn_border_color'] ) && ! empty( $slide['btn_border_color'] ) ? $slide['btn_border_color'] : $btn_bg_color;
                                    ?>
                                    <a href="<?php echo esc_url( $slide['btn_url'] ?: '#' ); ?>" class="banner-cta-btn" style="background: <?php echo esc_attr( $btn_bg_color ); ?>; color: <?php echo esc_attr( $btn_text_color ); ?>; border-color: <?php echo esc_attr( $btn_border_color ); ?>;">
                                        <?php echo esc_html( $slide['btn_text'] ); ?>
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php $this->render_stats_bar( $data, 'slider', $module_id ); ?>
            
            <?php $this->render_wave( $data ); ?>
            
        </section>
        <script>
            (function() {
            function boot() {
                var root = document.getElementById('<?php echo esc_js( $module_id ); ?>');
                if (!root || root.dataset.bannerInitialized) return;
                root.dataset.bannerInitialized = 'true';
                // 视频自动播放
                var bannerVideos = root.querySelectorAll('.banner-bg-video');
                bannerVideos.forEach(function(video) {
                    video.muted = true;
                    video.play().catch(function() {});
                });

                <?php if ( $has_swiper ) : ?>
                var bannerSwiperEl = root.querySelector('.banner-swiper');

                function initBannerSwiper() {
                    if (!bannerSwiperEl) {
                        return true;
                    }
                    if (bannerSwiperEl.classList.contains('swiper-initialized')) {
                        return true;
                    }
                    if (typeof Swiper === 'undefined') {
                        return false;
                    }

                    new Swiper(bannerSwiperEl, {
                        loop: true,
                        autoplay: {
                            delay: 5000,
                            disableOnInteraction: false,
                        },
                        effect: 'fade',
                        fadeEffect: { crossFade: true },
                        pagination: {
                            el: root.querySelector('.swiper-pagination'),
                            clickable: true,
                        },
                        navigation: {
                            nextEl: root.querySelector('.swiper-button-next'),
                            prevEl: root.querySelector('.swiper-button-prev'),
                        },
                    });
                    return true;
                }

                if (!initBannerSwiper()) {
                    var retryCount = 0;
                    var retryTimer = setInterval(function() {
                        if (!root.isConnected) {
                            clearInterval(retryTimer);
                            return;
                        }
                        retryCount++;
                        if (initBannerSwiper() || retryCount >= 100) {
                            clearInterval(retryTimer);
                        }
                    }, 100);
                }
                <?php endif; ?>
            }
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', boot, { once: true });
            } else {
                boot();
            }
            })();
        </script>
        <?php
    }
    
    /**
     * 渲染幻灯片背景（图片或视频）
     * 
     * @param array  $slide 幻灯片数据
     * @param string $media_type 媒体类型
     * @param string $bg_color 自定义背景色
     */
    private function render_slide_background( $slide, $media_type, $bg_color = '' ) {
        if ( $media_type === 'video' && ! empty( $slide['video_url'] ) ) {
            ?>
            <video class="banner-bg-video" autoplay muted loop playsinline style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover; z-index: 0;">
                <source src="<?php echo esc_url( $slide['video_url'] ); ?>" type="video/mp4">
            </video>
            <?php
        } elseif ( ! empty( $slide['image'] ) ) {
            ?>
            <div class="banner-bg-image" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background-image: url('<?php echo esc_url( $slide['image'] ); ?>'); background-size: cover; background-position: center; z-index: 0;"></div>
            <?php
        } else {
            // 使用自定义背景色，如果未设置则使用主题主色调
            $bg_style = ! empty( $bg_color ) ? $bg_color : 'var(--qiling-module-bg, linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-dark) 100%))';
            // 如果是纯色（以#开头），转换为渐变色
            if ( ! empty( $bg_color ) && strpos( $bg_color, '#' ) === 0 && strpos( $bg_color, 'gradient' ) === false ) {
                $bg_style = "linear-gradient(135deg, {$bg_color} 0%, {$bg_color} 100%)";
            }
            ?>
            <div class="banner-bg-default" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: <?php echo esc_attr( $bg_style ); ?>; z-index: 0;"></div>
            <?php
        }
    }
    
    private function render_image_text_layout( $slides, $image_position, $height_class, $bg_color = '', $data = array() ) {
        $slide = is_array( $slides ) && ! empty( $slides ) ? $slides[0] : array();
        $title = isset( $slide['title'] ) ? $slide['title'] : ( function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '专业企业解决方案', 'Professional solutions for modern business' ) : __( '专业企业解决方案', 'developer-starter' ) );
        $subtitle = isset( $slide['subtitle'] ) ? $slide['subtitle'] : ( function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '助力企业数字化转型', 'Support digital growth with a polished online presence.' ) : __( '助力企业数字化转型', 'developer-starter' ) );
        $btn_text = isset( $slide['btn_text'] ) ? $slide['btn_text'] : ( function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '了解更多', 'Learn More' ) : __( '了解更多', 'developer-starter' ) );
        $btn_url = isset( $slide['btn_url'] ) ? $slide['btn_url'] : '#';
        $image = isset( $slide['image'] ) ? $slide['image'] : '';
        $media_type = isset( $slide['media_type'] ) ? $slide['media_type'] : 'image';
        $video_url = isset( $slide['video_url'] ) ? $slide['video_url'] : '';
        $btn_bg_color = isset( $slide['btn_bg_color'] ) && ! empty( $slide['btn_bg_color'] ) ? $slide['btn_bg_color'] : 'var(--qiling-module-button-bg, var(--color-neutral-0))';
        $btn_text_color = isset( $slide['btn_text_color'] ) && ! empty( $slide['btn_text_color'] ) ? $slide['btn_text_color'] : 'var(--qiling-component-button-secondary-text, var(--color-primary))';
        $title_align = $this->get_slide_title_align( $slide, 'left' );
        
        // 判断是否有媒体内容
        $has_media = ( $media_type === 'video' && ! empty( $video_url ) ) || ! empty( $image );
        
        // 根据高度类计算实际高度值
        $height_map = array(
            'banner-height-full'   => '100vh',
            'banner-height-large'  => '80vh',
            'banner-height-medium' => '60vh',
            'banner-height-small'  => '50vh',
        );
        $height_value = isset( $height_map[ $height_class ] ) ? $height_map[ $height_class ] : '100vh';
        $flex_direction = $image_position === 'left' ? 'row-reverse' : 'row';
        $module_id = 'banner-it-' . uniqid();
        
        // 背景色处理：自定义色优先，否则使用主题主色调
        $bg_style = ! empty( $bg_color ) ? $bg_color : 'var(--qiling-module-bg, linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-dark) 100%))';
        // 如果是纯色（以#开头），转换为渐变色
        if ( ! empty( $bg_color ) && strpos( $bg_color, '#' ) === 0 && strpos( $bg_color, 'gradient' ) === false ) {
            $bg_style = "linear-gradient(135deg, {$bg_color} 0%, {$bg_color} 100%)";
        }
        ?>
        <section class="module module-banner banner-image-text <?php echo esc_attr( $height_class ); ?> <?php echo ! empty( $bg_color ) ? 'qds-local-bg-style' : ''; ?>" id="<?php echo esc_attr( $module_id ); ?>" style="background: <?php echo esc_attr( $bg_style ); ?>; min-height: <?php echo esc_attr( $height_value ); ?>; display: flex; align-items: center;">
            <div class="container" style="width: 100%;">
                <div class="banner-flex" style="display: flex; align-items: center; gap: var(--qiling-space-60); flex-direction: <?php echo $flex_direction; ?>; padding: calc(var(--qiling-space-60) + var(--qiling-space-5)) 0;">
                    <div class="banner-text" style="flex: 1; min-width: 0;">
                        <h1 class="banner-title" style="color: var(--qiling-module-title, var(--color-neutral-0)); font-size: var(--qiling-text-rem-3); font-weight: 700; line-height: 1.2; margin-bottom: var(--qiling-space-20); text-align: <?php echo esc_attr( $title_align ); ?>;">
                            <?php echo wp_kses_post( $title ); ?>
                        </h1>
                        <p class="banner-subtitle" style="color: var(--qiling-module-text, rgba(var(--qiling-rgb-255-255-255), 0.85)); font-size: var(--qiling-text-rem-1p25); margin-bottom: var(--qiling-space-30); line-height: 1.6;">
                            <?php echo wp_kses_post( $subtitle ); ?>
                        </p>
                        <?php if ( $btn_text ) : ?>
                            <a href="<?php echo esc_url( $btn_url ); ?>" class="banner-cta-btn" style="background: <?php echo esc_attr( $btn_bg_color ); ?>; color: <?php echo esc_attr( $btn_text_color ); ?>;">
                                <?php echo esc_html( $btn_text ); ?>
                            </a>
                        <?php endif; ?>
                    </div>
                    <div class="banner-media" style="flex: 1; min-width: 0; display: flex; flex-direction: column; gap: var(--qiling-space-20);">
                        <?php if ( $media_type === 'video' && ! empty( $video_url ) ) : ?>
                            <video class="banner-it-video" autoplay muted loop playsinline style="width: 100%; max-width: min(100%, var(--qiling-measure-800)); border-radius: var(--qiling-space-12); box-shadow: 0 var(--qiling-space-20) var(--qiling-space-40) rgba(var(--qiling-rgb-0-0-0), 0.2);">
                                <source src="<?php echo esc_url( $video_url ); ?>" type="video/mp4">
                            </video>
                        <?php elseif ( ! empty( $image ) ) : ?>
                            <img src="<?php echo esc_url( $image ); ?>" alt="<?php echo esc_attr( $title ); ?>" style="max-width: min(100%, var(--qiling-measure-800)); height: auto; border-radius: var(--qiling-space-12); box-shadow: 0 var(--qiling-space-20) var(--qiling-space-40) rgba(var(--qiling-rgb-0-0-0), 0.2);" />
                        <?php else : ?>
                            <div style="aspect-ratio: 4/3; background: rgba(var(--qiling-rgb-255-255-255), 0.1); border-radius: var(--qiling-space-12); display: flex; align-items: center; justify-content: center; color: rgba(var(--qiling-rgb-255-255-255), 0.5);">
                                <?php esc_html_e( '请上传图片或视频', 'developer-starter' ); ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php $this->render_stats_bar( $data, 'image_text', $module_id ); ?>
                    </div>
                </div>
            </div>
            
            <?php $this->render_wave( $data ); ?>
            
        </section>

        <script>
            (function() {
            function boot() {
                var root = document.getElementById('<?php echo esc_js( $module_id ); ?>');
                if (!root || root.dataset.bannerInitialized) return;
                root.dataset.bannerInitialized = 'true';
                var itVideo = root.querySelector('.banner-it-video');
                if (itVideo) {
                    itVideo.muted = true;
                    itVideo.play().catch(function() {});
                }
            }
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', boot, { once: true });
            } else {
                boot();
            }
            })();
        </script>
        <?php
    }
    
    /**
     * Whether the stats bar should be shown.
     *
     * @param mixed $value Raw option value.
     * @return bool
     */
    private function is_stats_bar_enabled( $value ) {
        if ( is_bool( $value ) ) {
            return $value;
        }
        if ( ! is_scalar( $value ) ) {
            return false;
        }

        $value = strtolower( trim( (string) $value ) );
        return in_array( $value, array( '1', 'yes', 'true', 'on' ), true );
    }

    /**
     * Normalize stats bar items from current and legacy payload shapes.
     *
     * @param array<string,mixed> $data Module data.
     * @return array<int,array<string,string>>
     */
    private function get_stats_bar_items( $data ) {
        foreach ( array( 'stats_data', 'stats_items', 'items' ) as $stats_key ) {
            if ( isset( $data[ $stats_key ] ) && is_array( $data[ $stats_key ] ) && ! empty( $data[ $stats_key ] ) ) {
                $normalized = $this->normalize_stats_bar_items( $data[ $stats_key ] );
                if ( ! empty( $normalized ) ) {
                    return $normalized;
                }
            }
        }

        return array();
    }

    /**
     * @param array<mixed,mixed> $stats Raw stats items.
     * @return array<int,array<string,string>>
     */
    private function normalize_stats_bar_items( $stats ) {
        if ( empty( $stats ) || ! is_array( $stats ) ) {
            return array();
        }

        $normalized = array();
        foreach ( $stats as $stat ) {
            if ( ! is_array( $stat ) ) {
                continue;
            }

            $icon = $this->get_stats_bar_value( $stat, array( 'icon', 'stat_icon' ) );
            $number = $this->get_stats_bar_value( $stat, array( 'number', 'stat_number', 'stat_value', 'value' ) );
            $label = $this->get_stats_bar_value( $stat, array( 'label', 'stat_label', 'text' ) );
            $color = $this->get_stats_bar_value( $stat, array( 'color', 'stat_color' ) );
            $label_color = $this->get_stats_bar_value( $stat, array( 'label_color', 'description_color', 'desc_color', 'text_color' ) );

            if ( '' === trim( $icon ) && '' === trim( $number ) && '' === trim( $label ) ) {
                continue;
            }

            $normalized[] = array(
                'icon'        => $icon,
                'number'      => $number,
                'label'       => $label,
                'color'       => $color,
                'label_color' => $label_color,
            );
        }

        return $normalized;
    }

    /**
     * @param array<string,mixed> $item Stats item.
     * @param array<int,string>   $keys Candidate keys.
     * @return string
     */
    private function get_stats_bar_value( $item, $keys ) {
        foreach ( $keys as $key ) {
            if ( array_key_exists( $key, $item ) && is_scalar( $item[ $key ] ) && '' !== (string) $item[ $key ] ) {
                return (string) $item[ $key ];
            }
        }

        return '';
    }

    /**
     * 渲染数据展示条
     */
    private function render_stats_bar( $data, $layout, $module_id ) {
        // 检查开关
        if ( ! isset( $data['show_stats_bar'] ) || ! $this->is_stats_bar_enabled( $data['show_stats_bar'] ) ) {
            return;
        }

        $stats = $this->get_stats_bar_items( is_array( $data ) ? $data : array() );
        if ( empty( $stats ) ) {
            return;
        }
        
        // 布局特定样式
        $container_style = '';
        $item_style = '';
        $stats_bar_bg_color = isset( $data['stats_bar_bg_color'] ) && is_scalar( $data['stats_bar_bg_color'] )
            ? trim( (string) $data['stats_bar_bg_color'] )
            : '';
        if ( '' !== $stats_bar_bg_color && function_exists( 'developer_starter_sanitize_page_visual_style_css_value' ) ) {
            $stats_bar_bg_color = developer_starter_sanitize_page_visual_style_css_value( $stats_bar_bg_color );
        }
        
        if ( $layout === 'slider' ) {
            // 轮播图模式：绝对定位在底部，磨砂黑/白风格
            $stats_bar_bg = '' !== $stats_bar_bg_color ? $stats_bar_bg_color : 'var(--qiling-module-card-bg, rgba(var(--qiling-rgb-255-255-255), 0.15))';
            $container_style = '
                position: absolute;
                bottom: var(--qiling-space-40);
                left: 50%;
                transform: translateX(-50%);
                width: 90%;
                max-width: var(--qiling-measure-1000);
                background: ' . $stats_bar_bg . ';
                backdrop-filter: blur(12px);
                -webkit-backdrop-filter: blur(12px);
                border: 1px solid rgba(var(--qiling-rgb-255-255-255), 0.2);
                border-radius: var(--qiling-space-16);
                padding: var(--qiling-space-24) var(--qiling-space-40);
                display: flex;
                justify-content: space-around;
                align-items: center;
                z-index: 10;
                box-shadow: 0 var(--qiling-space-10) var(--qiling-space-30) rgba(var(--qiling-rgb-0-0-0), 0.1);
            ';
            $item_color_default = 'var(--qiling-module-button-text, var(--qiling-module-text, var(--color-neutral-0)))';
        } else {
            // 图文模式：位于图片下方，更紧凑的卡片或透明条
            $stats_bar_bg = '' !== $stats_bar_bg_color ? $stats_bar_bg_color : 'var(--qiling-module-card-bg, rgba(var(--qiling-rgb-255-255-255), 0.1))';
            $container_style = '
                background: ' . $stats_bar_bg . ';
                backdrop-filter: blur(10px);
                border-radius: var(--qiling-space-12);
                padding: var(--qiling-space-16) var(--qiling-space-24);
                display: flex;
                justify-content: space-around;
                align-items: center;
                gap: var(--qiling-space-15);
                margin-top: var(--qiling-space-10);
            ';
            $item_color_default = 'var(--qiling-module-button-text, var(--qiling-module-text, var(--color-neutral-0)))';
        }
        ?>
        <div class="banner-stats-bar" style="<?php echo esc_attr( $container_style ); ?>">
            <?php foreach ( $stats as $index => $stat ) : 
                $icon = isset( $stat['icon'] ) ? $stat['icon'] : '';
                $number = isset( $stat['number'] ) ? $stat['number'] : '';
                $label = isset( $stat['label'] ) ? $stat['label'] : '';
                $color = isset( $stat['color'] ) && ! empty( $stat['color'] ) ? $stat['color'] : $item_color_default;
                $label_color = isset( $stat['label_color'] ) && ! empty( $stat['label_color'] ) ? $stat['label_color'] : 'var(--qiling-module-muted, ' . $color . ')';
            ?>
                <div class="stat-item" style="display: flex; align-items: center; gap: var(--qiling-space-12); flex: 1; justify-content: center;">
                    <?php if ( $icon ) : ?>
                        <div class="stat-icon" style="
                            font-size: var(--qiling-text-rem-1p5); 
                            color: <?php echo esc_attr( $color ); ?>;
                            background: rgba(var(--qiling-rgb-255-255-255), 0.1);
                            width: 42px;
                            height: 42px;
                            border-radius: 50%;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                        ">
                        <?php 
                        echo developer_starter_get_icon_html( $icon );
                        ?>
                        </div>
                    <?php endif; ?>
                    <div class="stat-text" style="color: <?php echo esc_attr( $color ); ?>; text-align: left;">
                        <div class="stat-number" style="font-size: var(--qiling-text-rem-1p5); font-weight: 700; line-height: 1.1;"><?php echo esc_html( $number ); ?></div>
                        <div class="stat-label" style="font-size: var(--qiling-text-rem-0p85); opacity: 0.8; color: <?php echo esc_attr( $label_color ); ?>;"><?php echo esc_html( $label ); ?></div>
                    </div>
                </div>
                <?php if ( $index < count( $stats ) - 1 ) : ?>
                    <div class="stat-divider" style="width: 1px; height: 30px; background: rgba(var(--qiling-rgb-255-255-255), 0.2);"></div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
        

        <?php
    }
    
    /**
     * 渲染底部波浪效果
     */
    private function render_wave( $data ) {
        // 检查开关
        if ( ! isset( $data['banner_wave_enable'] ) || $data['banner_wave_enable'] !== '1' ) {
            return;
        }
        
        $style = isset( $data['banner_wave_style'] ) ? $data['banner_wave_style'] : 'single';
        $color = isset( $data['banner_wave_color'] ) && $data['banner_wave_color'] ? $data['banner_wave_color'] : 'var(--color-neutral-0)';
        $height = isset( $data['banner_wave_height'] ) && $data['banner_wave_height'] ? $data['banner_wave_height'] : '80px';
        $flip = isset( $data['banner_wave_flip'] ) && $data['banner_wave_flip'] === '1';
        
        // 根据样式选择不同的 SVG 路径 - 波浪从顶部开始绘制确保完全覆盖
        $svg_paths = array(
            'single' => '<rect fill="' . esc_attr( $color ) . '" x="0" y="60" width="1440" height="40"/><path fill="' . esc_attr( $color ) . '" d="M0,64 C320,96 640,32 960,64 C1280,96 1440,48 1440,48 L1440,100 L0,100 Z"/>',
            'double' => '<rect fill="' . esc_attr( $color ) . '" x="0" y="70" width="1440" height="30"/><path fill="' . esc_attr( $color ) . '" fill-opacity="0.5" d="M0,80 C240,40 480,80 720,60 C960,40 1200,80 1440,60 L1440,100 L0,100 Z"/><path fill="' . esc_attr( $color ) . '" d="M0,64 C360,96 720,32 1080,64 C1260,80 1440,48 1440,48 L1440,100 L0,100 Z"/>',
            'triple' => '<rect fill="' . esc_attr( $color ) . '" x="0" y="75" width="1440" height="25"/><path fill="' . esc_attr( $color ) . '" fill-opacity="0.3" d="M0,90 C180,60 360,90 540,70 C720,50 900,90 1080,70 C1260,50 1440,80 1440,80 L1440,100 L0,100 Z"/><path fill="' . esc_attr( $color ) . '" fill-opacity="0.6" d="M0,75 C240,50 480,80 720,55 C960,30 1200,70 1440,50 L1440,100 L0,100 Z"/><path fill="' . esc_attr( $color ) . '" d="M0,60 C360,90 720,30 1080,60 C1260,75 1440,40 1440,40 L1440,100 L0,100 Z"/>',
            'curve' => '<path fill="' . esc_attr( $color ) . '" d="M0,100 C480,0 960,0 1440,100 L1440,100 L0,100 Z"/>',
        );
        
        $svg_path = isset( $svg_paths[ $style ] ) ? $svg_paths[ $style ] : $svg_paths['single'];
        $flip_style = $flip ? 'transform: scaleX(-1);' : '';
        ?>
        <div class="banner-wave" style="position: absolute; bottom: 0; left: 0; width: 100%; overflow: hidden; line-height: 0; z-index: 5; <?php echo $flip_style; ?>">
            <svg viewBox="0 0 1440 100" preserveAspectRatio="none" style="display: block; width: 100%; height: <?php echo esc_attr( $height ); ?>;">
                <?php echo $svg_path; ?>
            </svg>
        </div>
        <?php
    }
}
