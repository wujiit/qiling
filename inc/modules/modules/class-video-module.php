<?php
/**
 * Video Module - 视频展示
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Modules\Modules;

use Developer_Starter\Modules\Module_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Video_Module extends Module_Base {

    public function __construct() {
        $this->category = 'homepage';
        $this->icon = 'dashicons-video-alt3';
        $this->description = __( '展示视频内容', 'developer-starter' );
    }

    public function get_id() {
        return 'video';
    }

    public function get_name() {
        return __( '视频展示', 'developer-starter' );
    }

    public function get_fields() {
        return array(
            array( 'id' => 'video_title', 'type' => 'text', 'label' => __( '标题', 'developer-starter' ), 'default' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '视频展示', 'Video Showcase' ) : __( '视频展示', 'developer-starter' ) ),
            array(
                'id'      => 'video_title_size',
                'type'    => 'text',
                'label'   => __( '标题字体大小', 'developer-starter' ),
                'default' => '2rem',
                'desc'    => __( '如 2rem 或 32px', 'developer-starter' ),
            ),
            array( 'id' => 'video_title_color', 'type' => 'color', 'label' => __( '标题颜色', 'developer-starter' ) ),
            
            array( 'id' => 'video_subtitle', 'type' => 'text', 'label' => __( '副标题', 'developer-starter' ) ),
            array(
                'id'      => 'video_subtitle_size',
                'type'    => 'text',
                'label'   => __( '副标题字体大小', 'developer-starter' ),
                'default' => '1rem',
                'desc'    => __( '如 1rem 或 16px', 'developer-starter' ),
            ),
            array( 'id' => 'video_subtitle_color', 'type' => 'color', 'label' => __( '副标题颜色', 'developer-starter' ) ),
            
            array( 'id' => 'video_bg_color', 'type' => 'color', 'label' => __( '背景颜色 (支持渐变)', 'developer-starter' ) ),
            
            array(
                'id' => 'module_padding_top',
                'label' => __( '上边距 (如 60px)', 'developer-starter' ),
                'type' => 'text',
                'default' => '80px',
            ),
            array(
                'id' => 'module_padding_bottom',
                'label' => __( '下边距 (如 60px)', 'developer-starter' ),
                'type' => 'text',
                'default' => '80px',
            ),
            
            array( 'id' => 'video_url', 'type' => 'text', 'label' => __( '视频链接 (MP4或B站链接)', 'developer-starter' ) ),
            array( 'id' => 'video_width', 'type' => 'text', 'label' => __( '视频宽度', 'developer-starter' ), 'default' => '100%' ),
            array( 'id' => 'video_height', 'type' => 'text', 'label' => __( '视频高度', 'developer-starter' ), 'default' => '500px' ),
            array( 'id' => 'video_poster', 'type' => 'image', 'label' => __( '封面图片', 'developer-starter' ) ),
        );
    }

    public function render( $data = array() ) {
        $title = isset( $data['video_title'] ) && $data['video_title'] !== ''
            ? $data['video_title']
            : ( function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '视频展示', 'Video Showcase' ) : __( '视频展示', 'developer-starter' ) );
        $title_size = isset( $data['video_title_size'] ) && $data['video_title_size'] !== '' ? $data['video_title_size'] : '2rem';
        
        $subtitle = isset( $data['video_subtitle'] ) ? $data['video_subtitle'] : '';
        $subtitle_size = isset( $data['video_subtitle_size'] ) && $data['video_subtitle_size'] !== '' ? $data['video_subtitle_size'] : '1rem';
        
        $bg_color = isset( $data['video_bg_color'] ) && ! empty( $data['video_bg_color'] ) ? $data['video_bg_color'] : '';
        $title_color = isset( $data['video_title_color'] ) && ! empty( $data['video_title_color'] ) ? $data['video_title_color'] : '';
        $subtitle_color = isset( $data['video_subtitle_color'] ) && ! empty( $data['video_subtitle_color'] ) ? $data['video_subtitle_color'] : '';
        
        $pt = isset( $data['module_padding_top'] ) && $data['module_padding_top'] !== '' ? $data['module_padding_top'] : '80px';
        $pb = isset( $data['module_padding_bottom'] ) && $data['module_padding_bottom'] !== '' ? $data['module_padding_bottom'] : '80px';
        
        $video_url = isset( $data['video_url'] ) ? trim( $data['video_url'] ) : '';
        $video_width = isset( $data['video_width'] ) && ! empty( $data['video_width'] ) ? $data['video_width'] : '100%';
        $video_height = isset( $data['video_height'] ) && ! empty( $data['video_height'] ) ? $data['video_height'] : '500px';
        $video_poster = isset( $data['video_poster'] ) ? $data['video_poster'] : '';
        
        if ( empty( $video_url ) ) {
            return;
        }
        
        // Section Styles
        $section_style = "padding-top: {$pt}; padding-bottom: {$pb};";
        if ( ! empty( $bg_color ) ) {
            $section_style .= strpos( $bg_color, 'gradient' ) !== false ? "background: {$bg_color};" : "background-color: {$bg_color};";
        }
        
        // Typography Styles
        $title_style = "font-size: {$title_size};";
        if ( $title_color ) $title_style .= "color: {$title_color};";
        
        $subtitle_style = "font-size: {$subtitle_size};";
        if ( $subtitle_color ) $subtitle_style .= "color: {$subtitle_color};";
        
        // Video Container Style
        $container_style = "max-width: {$video_width};";
        
        // 检测视频类型
        $is_bilibili = $this->is_bilibili_url( $video_url );
        $bvid = '';
        if ( $is_bilibili ) {
            $bvid = $this->extract_bvid( $video_url );
        }
        ?>
        <section class="module module-video" style="<?php echo esc_attr( $section_style ); ?>">
            <div class="container">
                <?php if ( $title || $subtitle ) : ?>
                <div class="section-header text-center">
                    <?php if ( $title ) : ?>
                        <h2 class="section-title" style="<?php echo esc_attr( $title_style ); ?>"><?php echo esc_html( $title ); ?></h2>
                    <?php endif; ?>
                    <?php if ( $subtitle ) : ?>
                        <p class="section-subtitle" style="<?php echo esc_attr( $subtitle_style ); ?>"><?php echo esc_html( $subtitle ); ?></p>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                
                <div class="video-container" style="<?php echo esc_attr( $container_style ); ?>">
                    <div class="video-aspect-ratio" style="height:<?php echo esc_attr( $video_height ); ?>;padding-bottom:0;">
                        <?php if ( $is_bilibili && $bvid ) : ?>
                            <?php $embed_url = 'https://player.bilibili.com/player.html?bvid=' . rawurlencode( $bvid ) . '&page=1&high_quality=1&danmaku=0&autoplay=0'; ?>
                            <div class="ds-lazy-embed ds-lazy-embed-video" data-src="<?php echo esc_url( $embed_url ); ?>" data-autoplay="1">
                                <button type="button" class="ds-lazy-embed-trigger" aria-label="<?php esc_attr_e( '播放视频', 'developer-starter' ); ?>">
                                    <?php if ( $video_poster ) : ?>
                                        <img src="<?php echo esc_url( $video_poster ); ?>" alt="<?php echo esc_attr( $title ); ?>" class="ds-lazy-embed-poster" loading="lazy" />
                                    <?php endif; ?>
                                    <span class="ds-lazy-embed-play" aria-hidden="true">▶</span>
                                </button>
                            </div>
                        <?php else : ?>
                            <!-- 普通视频播放器 -->
                            <video 
                                controls 
                                preload="metadata"
                                <?php if ( $video_poster ) : ?>poster="<?php echo esc_url( $video_poster ); ?>"<?php endif; ?>
                            >
                                <source src="<?php echo esc_url( $video_url ); ?>" type="video/mp4">
                                <?php esc_html_e( '您的浏览器不支持视频播放', 'developer-starter' ); ?>
                            </video>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>
        <?php
    }
    
    /**
     * 检测是否为B站链接
     */
    private function is_bilibili_url( $url ) {
        return strpos( $url, 'bilibili.com' ) !== false || strpos( $url, 'b23.tv' ) !== false;
    }
    
    /**
     * 从B站链接提取BV号
     */
    private function extract_bvid( $url ) {
        // 匹配 BV 号格式
        if ( preg_match( '/BV([a-zA-Z0-9]+)/', $url, $matches ) ) {
            return 'BV' . $matches[1];
        }
        return '';
    }
}
