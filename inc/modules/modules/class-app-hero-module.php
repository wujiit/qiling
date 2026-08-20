<?php
/**
 * App Hero Module - APP推广首屏
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Modules\Modules;

use Developer_Starter\Modules\Module_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class App_Hero_Module extends Module_Base {

    public function __construct() {
        // 设置模块所属分类
        $this->category = 'header'; // 或者 'hero'
        $this->icon = 'dashicons-smartphone';
        $this->description = __( 'APP推广首屏展示，支持图片/视频切换、多下载按钮及二维码卡片', 'developer-starter' );
    }

    public function get_id() {
        return 'app_hero';
    }

    public function get_name() {
        return __( 'APP推广首屏', 'developer-starter' );
    }

    public function get_fields() {
        return array(
            // --- 内容设置 ---
            array(
                'id' => 'hero_title',
                'label' => __( '主标题', 'developer-starter' ),
                'type' => 'text',
                'default' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '立即下载我们的APP', 'Download Our App Today' ) : __( '立即下载我们的APP', 'developer-starter' ),
            ),
            array(
                'id' => 'hero_desc',
                'label' => __( '描述文本', 'developer-starter' ),
                'type' => 'textarea',
                'default' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '随时随地捕捉灵感，手机也能高效创作。支持 iOS 和 Android 系统，数据多端同步。', 'Create from anywhere with a mobile workflow built for iOS and Android, with seamless sync across devices.' ) : __( '随时随地捕捉灵感，手机也能高效创作。支持 iOS 和 Android 系统，数据多端同步。', 'developer-starter' ),
            ),
            
            // --- 下载按钮组 ---
            array(
                'id' => 'hero_buttons',
                'label' => __( '下载按钮配置', 'developer-starter' ),
                'type' => 'repeater',
                'fields' => array(
                    array( 
                        'id' => 'btn_type', 
                        'label' => __( '按钮类型', 'developer-starter' ), 
                        'type' => 'select',
                        'options' => array(
                            'apple' => 'Apple (App Store)',
                            'android' => 'Android (Google Play)',
                            'windows' => 'Windows',
                            'mac' => 'Mac OS',
                            'custom' => __( '自定义', 'developer-starter' ),
                        ),
                        'default' => 'apple'
                    ),
                    array( 'id' => 'btn_text', 'label' => __( '主平标题 (如 App Store)', 'developer-starter' ), 'type' => 'text' ),
                    array( 'id' => 'btn_subtext', 'label' => __( '副标题 (如 Download on)', 'developer-starter' ), 'type' => 'text' ),
                    array( 'id' => 'btn_icon', 'label' => __( '图标 (如 icon-apple)', 'developer-starter' ), 'type' => 'text', 'desc' => __( '支持 icon-xxx 或图片URL，留空则根据类型自动显示默认图标', 'developer-starter' ) ),
                    array( 'id' => 'btn_link', 'label' => __( '下载链接', 'developer-starter' ), 'type' => 'text', 'default' => '#' ),
                    array( 
                        'id' => 'btn_style', 
                        'label' => __( '样式风格', 'developer-starter' ), 
                        'type' => 'select', 
                        'options' => array(
                            'light' => __( '白色背景(深色字)', 'developer-starter' ),
                            'dark' => __( '深色背景(白色字)', 'developer-starter' ),
                            'outline' => __( '描边风格', 'developer-starter' ),
                        ),
                        'default' => 'light'
                    ),
                    $this->get_button_border_color_field( 'btn_border_color', __( '按钮边框颜色', 'developer-starter' ) ),
                    $this->get_button_border_color_field( 'btn_hover_border_color', __( '按钮悬停边框颜色', 'developer-starter' ), __( '留空时跟随按钮边框颜色。', 'developer-starter' ) ),
                ),
            ),

            // --- 底部二维码卡片 ---
            array(
                'id' => 'show_qr_card',
                'label' => __( '显示底部二维码/标语栏', 'developer-starter' ),
                'type' => 'select', // Changed from switch to select for better clarity
                'options' => array(
                    '1' => __( '显示', 'developer-starter' ),
                    '0' => __( '隐藏', 'developer-starter' ),
                ),
                'default' => '1',
                'desc' => __( '选择是否显示底部的二维码或标语卡片', 'developer-starter' ),
            ),
            array(
                'id' => 'qr_icon',
                'label' => __( '左侧图标', 'developer-starter' ),
                'type' => 'text',
                'default' => 'icon-shouji',
                'desc' => __( '支持 icon-xxx 或图片URL', 'developer-starter' ),
                'dependency' => array( 'show_qr_card', '==', '1' ),
            ),
            array(
                'id' => 'qr_sub', // New field
                'label' => __( '左侧小标题 (如 手机扫一扫)', 'developer-starter' ),
                'type' => 'text',
                'default' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '手机扫一扫', 'Scan with Your Phone' ) : __( '手机扫一扫', 'developer-starter' ),
                'dependency' => array( 'show_qr_card', '==', '1' ),
            ),
            array(
                'id' => 'qr_title',
                'label' => __( '左侧主标题', 'developer-starter' ),
                'type' => 'text',
                'default' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '微信小程序', 'Mobile Quick Access' ) : __( '微信小程序', 'developer-starter' ),
                'dependency' => array( 'show_qr_card', '==', '1' ),
            ),
            array(
                'id' => 'qr_desc',
                'label' => __( '左侧描述', 'developer-starter' ),
                'type' => 'text',
                'default' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '免下载，扫码直接使用', 'No install needed. Scan to launch instantly.' ) : __( '免下载，扫码直接使用', 'developer-starter' ),
                'dependency' => array( 'show_qr_card', '==', '1' ),
            ),
            array(
                'id' => 'qr_image',
                'label' => __( '右侧二维码图片', 'developer-starter' ),
                'type' => 'image',
                'dependency' => array( 'show_qr_card', '==', '1' ),
            ),

            // --- 右侧媒体设置 ---
            array(
                'id' => 'media_type',
                'label' => __( '右侧展示类型', 'developer-starter' ),
                'type' => 'select',
                'options' => array(
                    'image' => __( '静态图片', 'developer-starter' ),
                    'video' => __( '视频播放', 'developer-starter' ),
                ),
                'default' => 'image',
            ),
            array(
                'id' => 'hero_image',
                'label' => __( '上传图片', 'developer-starter' ),
                'type' => 'image',
                'desc' => __( '建议尺寸: 600x800px 透明背景PNG效果最佳', 'developer-starter' ),
                'dependency' => array( 'media_type', '==', 'image' ),
            ),
            array(
                'id' => 'hero_video',
                'label' => __( '视频地址 (MP4)', 'developer-starter' ),
                'type' => 'text',
                'dependency' => array( 'media_type', '==', 'video' ),
            ),
            
            // --- 悬浮装饰元素 (新建) ---
            array(
                'id' => 'floating_elements',
                'label' => __( '悬浮装饰卡片', 'developer-starter' ),
                'type' => 'repeater',
                'fields' => array(
                    array( 'id' => 'float_icon', 'label' => __( '图标(支持 icon-xxx 或图片URL)', 'developer-starter' ), 'type' => 'text', 'desc' => __( '支持 icon-xxx 或图片URL', 'developer-starter' ) ),
                    array( 'id' => 'float_title', 'label' => __( '标题', 'developer-starter' ), 'type' => 'text', 'default' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '功能亮点', 'Feature Highlight' ) : __( '功能亮点', 'developer-starter' ) ),
                    array( 'id' => 'float_desc', 'label' => __( '副标题', 'developer-starter' ), 'type' => 'text', 'default' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '多功能', 'All-in-One' ) : __( '多功能', 'developer-starter' ) ),
                    array( 
                        'id' => 'float_pos', 
                        'label' => __( '位置', 'developer-starter' ), 
                        'type' => 'select',
                        'options' => array(
                            'top-left' => __( '左上 (Top-Left)', 'developer-starter' ),
                            'top-right' => __( '右上 (Top-Right)', 'developer-starter' ),
                            'bottom-left' => __( '左下 (Bottom-Left)', 'developer-starter' ),
                            'bottom-right' => __( '右下 (Bottom-Right)', 'developer-starter' ),
                            'center-left' => __( '左中 (Center-Left)', 'developer-starter' ),
                            'center-right' => __( '右中 (Center-Right)', 'developer-starter' ),
                        ),
                        'default' => 'top-left'
                    ),
                ),
                'dependency' => array( 'media_type', '==', 'image' ), // Only show for image mode initially to avoid clutter
            ),

            // --- 背景设置 ---
            array(
                'id' => 'bg_type',
                'label' => __( '背景类型', 'developer-starter' ),
                'type' => 'select',
                'options' => array(
                    'color' => __( '纯色/渐变', 'developer-starter' ),
                    'image' => __( '图片背景', 'developer-starter' ),
                ),
                'default' => 'color',
            ),
            array(
                'id' => 'bg_color',
                'label' => __( '背景颜色', 'developer-starter' ),
                'type' => 'color',
                'default' => 'linear-gradient(135deg, var(--color-primary) 0%, var(--qiling-color-4f46e5) 100%)',
                'dependency' => array( 'bg_type', '==', 'color' ),
            ),
            array(
                'id' => 'bg_image',
                'label' => __( '背景图片', 'developer-starter' ),
                'type' => 'image',
                'dependency' => array( 'bg_type', '==', 'image' ),
            ),
            array(
                'id' => 'bg_overlay',
                'label' => __( '遮罩浓度 (0-1)', 'developer-starter' ),
                'type' => 'text',
                'default' => '0',
                'dependency' => array( 'bg_type', '==', 'image' ),
            ),
            
            // --- 间距 ---
            array(
                'id' => 'padding_top',
                'label' => __( '上内边距', 'developer-starter' ),
                'type' => 'text',
                'default' => '100px',
            ),
            array(
                'id' => 'padding_bottom',
                'label' => __( '下内边距', 'developer-starter' ),
                'type' => 'text',
                'default' => '100px',
            ),
        );
    }

    public function get_demo_data() {
        return array(
            'hero_title' => __( '立即下载玫瑰克隆APP', 'developer-starter' ),
            'hero_desc' => __( '随时随地捕捉灵感，手机也能高效创作。支持 iOS 和 Android 系统，数据多端同步。', 'developer-starter' ),
            'hero_buttons' => array(
                array(
                    'btn_type' => 'apple',
                    'btn_text' => 'App Store',
                    'btn_subtext' => 'Download on the',
                    'btn_link' => '#',
                    'btn_style' => 'light'
                ),
                array(
                    'btn_type' => 'android',
                    'btn_text' => 'Android',
                    'btn_subtext' => 'GET IT ON',
                    'btn_link' => '#',
                    'btn_style' => 'dark'
                ),
            ),
            'show_qr_card' => true,
            'qr_title' => __( '微信小程序', 'developer-starter' ),
            'qr_desc' => __( '免下载，扫码直接使用', 'developer-starter' ),
            'media_type' => 'image',
            'bg_type' => 'color',
            'bg_color' => 'linear-gradient(135deg, var(--color-primary) 0%, var(--qiling-color-4f46e5) 100%)',
        );
    }

    /**
     * 渲染图标 - 支持 SVG代码、Symbol类名 和 图片URL
     * @param string $icon 图标值 (SVG代码、icon-xxx 或 图片URL)
     * @return string HTML输出
     */
    private function render_icon( $icon ) {
        if ( empty( $icon ) ) {
            return '';
        }
        
        $icon = trim( $icon );
        
        // 1. 完整 SVG 代码
        if ( strpos( $icon, '<svg' ) !== false ) {
            if ( function_exists( 'developer_starter_sanitize_svg' ) ) {
                return developer_starter_sanitize_svg( $icon );
            }
            return $icon;
        }
        
        // 2. Symbol/JS 方式: icon-xxx
        if ( strpos( $icon, 'icon-' ) !== false ) {
            return '<svg class="icon" aria-hidden="true"><use xlink:href="#' . esc_attr( $icon ) . '"></use></svg>';
        }
        
        // 3. 图片URL方式
        if ( filter_var( $icon, FILTER_VALIDATE_URL ) || strpos( $icon, '/' ) !== false ) {
            return '<img src="' . esc_url( $icon ) . '" alt="">';
        }
        
        // 4. Emoji 或其他
        return '<span>' . esc_html( $icon ) . '</span>';
    }

    public function render( $data = array() ) {
        $clean_css_color_value = static function ( $value ) {
            $value = is_string( $value ) ? trim( wp_strip_all_tags( $value ) ) : '';
            if ( '' === $value || preg_match( '/[;<>{}]/', $value ) ) {
                return '';
            }

            $hex_color = sanitize_hex_color( $value );
            if ( $hex_color ) {
                return $hex_color;
            }

            if ( preg_match( '/^(rgba?|hsla?)\(\s*[0-9\.\s,%]+\s*\)$/i', $value ) ) {
                return $value;
            }

            if ( preg_match( '/^(?:rgba?|hsla?)\(\s*var\(--[a-z0-9_-]+\)(?:\s*,\s*[0-9\.\s%]+)*\s*\)$/i', $value ) ) {
                return $value;
            }

            if ( preg_match( '/^var\(--[a-z0-9_-]+\)$/i', $value ) ) {
                return $value;
            }

            return '';
        };

        // 数据提取
        $title = ! empty( $data['hero_title'] ) ? $data['hero_title'] : '';
        $desc = ! empty( $data['hero_desc'] ) ? $data['hero_desc'] : '';
        $buttons = ! empty( $data['hero_buttons'] ) ? $data['hero_buttons'] : array();
        
        $show_qr = ! empty( $data['show_qr_card'] ) ? $data['show_qr_card'] : false;
        $qr_title = ! empty( $data['qr_title'] ) ? $data['qr_title'] : '';
        $qr_desc = ! empty( $data['qr_desc'] ) ? $data['qr_desc'] : '';
        $qr_icon = ! empty( $data['qr_icon'] ) ? $data['qr_icon'] : '';
        $qr_image = ! empty( $data['qr_image'] ) ? $data['qr_image'] : '';

        $media_type = ! empty( $data['media_type'] ) ? $data['media_type'] : 'image';
        $hero_image = ! empty( $data['hero_image'] ) ? $data['hero_image'] : '';
        $video_url = ! empty( $data['hero_video'] ) ? $data['hero_video'] : '';
        // $video_poster removed

        // 背景样式
        $bg_style = '';
        $bg_type = ! empty( $data['bg_type'] ) ? $data['bg_type'] : 'color';
        if ( $bg_type === 'color' ) {
            $bg_color = ! empty( $data['bg_color'] ) ? $data['bg_color'] : 'var(--color-primary)';
            $bg_style = strpos($bg_color, 'gradient') !== false ? "background: $bg_color;" : "background-color: $bg_color;";
        } else {
            $bg_image = ! empty( $data['bg_image'] ) ? $data['bg_image'] : '';
            if ( $bg_image ) {
                $bg_style = "background-image: url('$bg_image'); background-size: cover; background-position: center;";
            }
        }
        
        $pt = ! empty( $data['padding_top'] ) ? $data['padding_top'] : '100px';
        $pb = ! empty( $data['padding_bottom'] ) ? $data['padding_bottom'] : '100px';
        $container_style = "padding-top: $pt; padding-bottom: $pb;";

        ?>
        <div class="module-app-hero" style="<?php echo esc_attr( $bg_style ); ?>">
            <?php if ( $bg_type === 'image' && ! empty( $data['bg_overlay'] ) ) : ?>
                <div class="hero-overlay" style="opacity: <?php echo esc_attr( $data['bg_overlay'] ); ?>"></div>
            <?php endif; ?>

            <div class="container" style="<?php echo esc_attr( $container_style ); ?>">
                <div class="app-hero-grid">
                    
                    <!-- 左侧内容区 -->
                    <div class="app-hero-content" data-aos="fade-right">
                        <?php if ( $title ) : ?>
                            <h1 class="hero-title"><?php echo wp_kses_post( $title ); ?></h1>
                        <?php endif; ?>
                        
                        <?php if ( $desc ) : ?>
                            <p class="hero-desc"><?php echo wp_kses_post( nl2br( $desc ) ); ?></p>
                        <?php endif; ?>

                        <!-- 下载按钮 -->
                        <?php if ( ! empty( $buttons ) ) : ?>
                            <div class="hero-buttons">
                                <?php foreach ( $buttons as $btn ) : 
                                    $b_type = isset($btn['btn_type']) ? $btn['btn_type'] : 'custom';
                                    $b_text = isset($btn['btn_text']) ? $btn['btn_text'] : '';
                                    $b_sub = isset($btn['btn_subtext']) ? $btn['btn_subtext'] : '';
                                    $b_link = isset($btn['btn_link']) ? $btn['btn_link'] : '#';
                                    $b_style = isset($btn['btn_style']) ? $btn['btn_style'] : 'light';
                                    $b_icon = isset($btn['btn_icon']) ? $btn['btn_icon'] : '';
                                    $b_border = isset($btn['btn_border_color']) ? $clean_css_color_value( $btn['btn_border_color'] ) : '';
                                    $b_hover_border = isset($btn['btn_hover_border_color']) ? $clean_css_color_value( $btn['btn_hover_border_color'] ) : '';
                                    $button_style_vars = '';
                                    if ( $b_border !== '' ) {
                                        $button_style_vars .= '--app-btn-border:' . $b_border . ';';
                                    }
                                    if ( $b_hover_border !== '' ) {
                                        $button_style_vars .= '--app-btn-hover-border:' . $b_hover_border . ';';
                                    }
                                    
                                    // 自动图标处理
                                    if ( empty( $b_icon ) ) {
                                        if ( $b_type === 'apple' ) $b_icon = 'icon-apple';
                                        if ( $b_type === 'android' ) $b_icon = 'icon-android';
                                        if ( $b_type === 'windows' ) $b_icon = 'icon-windows';
                                    }
                                ?>
                                    <a href="<?php echo esc_url( $b_link ); ?>" class="app-btn style-<?php echo esc_attr( $b_style ); ?>" style="<?php echo esc_attr( $button_style_vars ); ?>">
                                        <?php if ( $b_icon ) : ?>
                                            <span class="app-btn-icon"><?php echo $this->render_icon( $b_icon ); ?></span>
                                        <?php endif; ?>
                                        <div class="app-btn-text">
                                            <?php if ( $b_sub ) : ?>
                                                <span class="btn-sub"><?php echo wp_kses_post( $b_sub ); ?></span>
                                            <?php endif; ?>
                                            <span class="btn-main"><?php echo wp_kses_post( $b_text ); ?></span>
                                        </div>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <!-- 次级卡片 (二维码) -->
                        <?php if ( $show_qr == '1' ) : ?>
                            <div class="hero-qr-card">
                                <div class="qr-card-info">
                                    <?php if ( $qr_icon ) : ?>
                                        <div class="qr-icon-wrap">
                                            <?php echo $this->render_icon( $qr_icon ); ?>
                                        </div>
                                    <?php endif; ?>
                                    <div class="qr-text-wrap">
                                        <?php 
                                        $qr_sub_text = isset($data['qr_sub']) ? $data['qr_sub'] : ( function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '手机扫一扫', 'Scan with your phone' ) : __( '手机扫一扫', 'developer-starter' ) );
                                        if ( $qr_sub_text ) : 
                                        ?>
                                            <span class="qr-sub"><?php echo esc_html( $qr_sub_text ); ?></span>
                                        <?php endif; ?>
                                        <?php if ( $qr_title ) : ?>
                                            <h4 class="qr-title"><?php echo wp_kses_post( $qr_title ); ?></h4>
                                        <?php endif; ?>
                                        <?php if ( $qr_desc ) : ?>
                                            <p class="qr-desc"><?php echo wp_kses_post( $qr_desc ); ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php if ( $qr_image ) : ?>
                                    <div class="qr-code-img">
                                        <img src="<?php echo esc_url( $qr_image ); ?>" alt="QR Code">
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- 右侧媒体区 -->
                    <div class="app-hero-media" data-aos="fade-left" data-aos-delay="200">
                        <div class="media-wrapper">
                            <!-- 装饰光晕 -->
                            <div class="media-glow"></div>
                            
                            <?php if ( $media_type === 'video' && $video_url ) : ?>
                                <div class="device-mockup video-mode">
                                    <video 
                                        src="<?php echo esc_url( $video_url ); ?>" 
                                        autoplay muted loop playsinline
                                        class="hero-video"
                                    ></video>
                                    <!-- 简单的手机外壳装饰 (CSS实现) -->
                                    <div class="mockup-frame"></div>
                                </div>
                            <?php elseif ( $media_type === 'image' && $hero_image ) : ?>
                                <div class="device-mockup image-mode">
                                    <img src="<?php echo esc_url( $hero_image ); ?>" alt="App Screenshot" class="hero-img">
                                    
                                    <!-- 悬浮装饰元素 -->
                                    <?php 
                                    $float_items = isset( $data['floating_elements'] ) ? $data['floating_elements'] : array();
                                    if ( ! empty( $float_items ) ) : 
                                        foreach ( $float_items as $f_item ) :
                                            $f_icon = isset($f_item['float_icon']) ? $f_item['float_icon'] : '';
                                            $f_title = isset($f_item['float_title']) ? $f_item['float_title'] : '';
                                            $f_desc = isset($f_item['float_desc']) ? $f_item['float_desc'] : '';
                                            $f_pos = isset($f_item['float_pos']) ? $f_item['float_pos'] : 'top-left';
                                    ?>
                                        <div class="floating-badge pos-<?php echo esc_attr( $f_pos ); ?>">
                                            <?php if ( $f_icon ) : ?>
                                                <div class="float-icon">
                                                    <?php echo $this->render_icon( $f_icon ); ?>
                                                </div>
                                            <?php endif; ?>
                                            <div class="float-text">
                                                <div class="float-title"><?php echo wp_kses_post( $f_title ); ?></div>
                                                <div class="float-desc"><?php echo wp_kses_post( $f_desc ); ?></div>
                                            </div>
                                        </div>
                                    <?php 
                                        endforeach;
                                    endif; 
                                    ?>
                                </div>
                            <?php else : ?>
                                <div class="media-placeholder">
                                    <span><?php esc_html_e( '请配置图片或视频', 'developer-starter' ); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        <?php
    }
}
