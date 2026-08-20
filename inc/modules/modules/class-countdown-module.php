<?php
/**
 * Countdown Module - 产品上线倒计时
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Modules\Modules;

use Developer_Starter\Modules\Module_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Countdown_Module extends Module_Base {

    public function __construct() {
        $this->category = 'homepage';
        $this->icon = 'dashicons-clock';
        $this->description = __( '产品上线倒计时展示', 'developer-starter' );
    }

    public function get_id() {
        return 'countdown';
    }

    public function get_name() {
        return __( '产品倒计时', 'developer-starter' );
    }

    public function get_fields() {
        return array(
            // === 文本内容 ===
            array(
                'id'      => 'countdown_title',
                'type'    => 'text',
                'label'   => __( '主标题', 'developer-starter' ),
                'default' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '新品即将上线', 'New Release Coming Soon' ) : __( '新品即将上线', 'developer-starter' ),
            ),
            array(
                'id'      => 'countdown_title_size',
                'type'    => 'text',
                'label'   => __( '主标题字体大小', 'developer-starter' ),
                'default' => '2.8rem',
                'desc'    => __( '如 2.8rem 或 48px', 'developer-starter' ),
            ),
            array(
                'id'      => 'countdown_subtitle',
                'type'    => 'text',
                'label'   => __( '副标题', 'developer-starter' ),
                'default' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '敬请期待', 'Stay Tuned' ) : __( '敬请期待', 'developer-starter' ),
            ),
            array(
                'id'      => 'countdown_subtitle_size',
                'type'    => 'text',
                'label'   => __( '副标题字体大小', 'developer-starter' ),
                'default' => '0.9rem',
                'desc'    => __( '如 0.9rem 或 14px', 'developer-starter' ),
            ),
            array(
                'id'      => 'countdown_desc',
                'type'    => 'textarea',
                'label'   => __( '描述文本', 'developer-starter' ),
                'default' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '我们正在精心打造一款革命性的产品，即将与您见面！', 'We are crafting a bold new product experience and cannot wait to share it with you.' ) : __( '我们正在精心打造一款革命性的产品，即将与您见面！', 'developer-starter' ),
            ),
            
            // === 样式设置 ===
            array(
                'id'      => 'countdown_bg_color',
                'type'    => 'text', // support gradient
                'label'   => __( '背景颜色(支持渐变)', 'developer-starter' ),
                'default' => 'linear-gradient(135deg, var(--color-primary) 0%, var(--qiling-color-764ba2) 100%)',
            ),
            array(
                'id'      => 'countdown_title_color',
                'type'    => 'color',
                'label'   => __( '标题颜色', 'developer-starter' ),
                'default' => 'var(--color-primary)',
            ),
            array(
                'id'      => 'countdown_subtitle_color',
                'type'    => 'color',
                'label'   => __( '副标题颜色', 'developer-starter' ),
                'default' => 'var(--qiling-color-rgba-255-255-255-08)',
            ),
            array(
                'id'      => 'countdown_desc_color',
                'type'    => 'color',
                'label'   => __( '描述文字颜色', 'developer-starter' ),
                'default' => 'var(--qiling-color-rgba-255-255-255-07)',
            ),
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
            
            // === 媒体与时间 ===
            array(
                'id'      => 'countdown_image',
                'type'    => 'image',
                'label'   => __( '左侧产品图（可选）', 'developer-starter' ),
            ),
            array(
                'id'      => 'countdown_date',
                'type'    => 'text', // In a real scenario, a date picker would be better, but text works for YYYY-MM-DD
                'label'   => __( '目标日期 (格式: YYYY-MM-DD HH:MM:SS)', 'developer-starter' ),
                'desc'    => __( '如果留空，默认显示30天后', 'developer-starter' ),
            ),
            
            // === 倒计时样式 ===
            array(
                'id'      => 'countdown_timer_bg',
                'type'    => 'text',
                'label'   => __( '倒计时方块背景', 'developer-starter' ),
                'default' => 'var(--qiling-color-rgba-255-255-255-015)',
            ),
            array(
                'id'      => 'countdown_timer_color',
                'type'    => 'color',
                'label'   => __( '倒计时文字颜色', 'developer-starter' ),
                'default' => 'var(--color-neutral-0)',
            ),
            
            // === 按钮设置 ===
            array(
                'id'      => 'countdown_btn_text',
                'type'    => 'text',
                'label'   => __( '按钮文字', 'developer-starter' ),
                'default' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '立即预约', 'Reserve Now' ) : __( '立即预约', 'developer-starter' ),
            ),
            array(
                'id'      => 'countdown_btn_link',
                'type'    => 'text',
                'label'   => __( '按钮链接', 'developer-starter' ),
                'default' => '#',
            ),
            array(
                'id'      => 'countdown_btn_bg',
                'type'    => 'color',
                'label'   => __( '按钮背景色', 'developer-starter' ),
                'default' => 'var(--color-neutral-0)',
            ),
            array(
                'id'      => 'countdown_btn_text_color',
                'type'    => 'color',
                'label'   => __( '按钮文字颜色', 'developer-starter' ),
                'default' => '#ffffff',
            ),
            $this->get_button_border_color_field( 'countdown_btn_border_color' ),
        );
    }

    public function render( $data = array() ) {
        $title = isset( $data['countdown_title'] ) ? $data['countdown_title'] : '';
        $title_size = isset( $data['countdown_title_size'] ) && $data['countdown_title_size'] !== '' ? $data['countdown_title_size'] : '2.8rem';
        
        $subtitle = isset( $data['countdown_subtitle'] ) ? $data['countdown_subtitle'] : '';
        $subtitle_size = isset( $data['countdown_subtitle_size'] ) && $data['countdown_subtitle_size'] !== '' ? $data['countdown_subtitle_size'] : '0.9rem';
        
        $description = isset( $data['countdown_desc'] ) ? $data['countdown_desc'] : '';
        
        $bg_color = isset( $data['countdown_bg_color'] ) && ! empty( $data['countdown_bg_color'] ) ? $data['countdown_bg_color'] : 'linear-gradient(135deg, var(--color-primary) 0%, var(--qiling-color-764ba2) 100%)';
        $title_color = isset( $data['countdown_title_color'] ) && ! empty( $data['countdown_title_color'] ) ? $data['countdown_title_color'] : 'var(--color-neutral-0)';
        $subtitle_color = isset( $data['countdown_subtitle_color'] ) && ! empty( $data['countdown_subtitle_color'] ) ? $data['countdown_subtitle_color'] : 'var(--qiling-color-rgba-255-255-255-08)';
        $desc_color = isset( $data['countdown_desc_color'] ) && ! empty( $data['countdown_desc_color'] ) ? $data['countdown_desc_color'] : 'var(--qiling-color-rgba-255-255-255-07)';
        
        $pt = isset( $data['module_padding_top'] ) && $data['module_padding_top'] !== '' ? $data['module_padding_top'] : '80px';
        $pb = isset( $data['module_padding_bottom'] ) && $data['module_padding_bottom'] !== '' ? $data['module_padding_bottom'] : '80px';
        
        $product_image = isset( $data['countdown_image'] ) ? $data['countdown_image'] : '';
        $target_date = isset( $data['countdown_date'] ) && ! empty( $data['countdown_date'] ) ? $data['countdown_date'] : '';
        $timer_bg = isset( $data['countdown_timer_bg'] ) && ! empty( $data['countdown_timer_bg'] ) ? $data['countdown_timer_bg'] : 'var(--qiling-color-rgba-255-255-255-015)';
        $timer_color = isset( $data['countdown_timer_color'] ) && ! empty( $data['countdown_timer_color'] ) ? $data['countdown_timer_color'] : 'var(--color-neutral-0)';
        $btn_text = isset( $data['countdown_btn_text'] ) ? $data['countdown_btn_text'] : ( function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '立即预约', 'Reserve Now' ) : __( '立即预约', 'developer-starter' ) );
        $btn_link = isset( $data['countdown_btn_link'] ) ? $data['countdown_btn_link'] : '#';
        $btn_bg = isset( $data['countdown_btn_bg'] ) && ! empty( $data['countdown_btn_bg'] ) ? $data['countdown_btn_bg'] : 'var(--color-primary)';
        $btn_text_color = isset( $data['countdown_btn_text_color'] ) && ! empty( $data['countdown_btn_text_color'] ) ? $data['countdown_btn_text_color'] : '#ffffff';
        $btn_border_color = isset( $data['countdown_btn_border_color'] ) && ! empty( $data['countdown_btn_border_color'] ) ? $data['countdown_btn_border_color'] : $btn_bg;
        
        // 计算目标时间戳
        if ( ! empty( $target_date ) ) {
            $target_timestamp = strtotime( $target_date );
        } else {
            $target_timestamp = time() + ( 30 * 24 * 60 * 60 );
        }
        
        // Dynamic Styles
        $section_style = "padding-top: {$pt}; padding-bottom: {$pb};";
        $section_style .= strpos( $bg_color, 'gradient' ) !== false ? "background: {$bg_color};" : "background-color: {$bg_color};";
        
        $title_style = "font-size: {$title_size}; color: {$title_color};";
        $subtitle_style = "font-size: {$subtitle_size}; color: {$subtitle_color};";
        $desc_style = "color: {$desc_color};";
        
        $timer_item_style = "background: {$timer_bg}; color: {$timer_color}; border-color: var(--qiling-color-rgba-255-255-255-01);";
        $btn_style = "background: {$btn_bg}; color: {$btn_text_color}; border-color: {$btn_border_color};";
        
        $unique_id = 'countdown-' . uniqid();
        $wrapper_class = $product_image ? 'has-image' : 'no-image';
        ?>
        <section class="module module-countdown" id="<?php echo esc_attr( $unique_id ); ?>" style="<?php echo esc_attr( $section_style ); ?>">
            <div class="countdown-bg-decoration">
                <div class="decoration-circle circle-1"></div>
                <div class="decoration-circle circle-2"></div>
            </div>
            
            <div class="container countdown-container">
                <div class="countdown-wrapper <?php echo esc_attr( $wrapper_class ); ?>">
                    
                    <?php if ( $product_image ) : ?>
                    <div class="countdown-image-col">
                        <div class="countdown-image-wrapper">
                            <img src="<?php echo esc_url( $product_image ); ?>" alt="<?php echo esc_attr( $title ); ?>" />
                            <div class="image-shine"></div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="countdown-content-col">
                        <?php if ( $subtitle ) : ?>
                            <div class="countdown-label">
                                <span style="<?php echo esc_attr( $subtitle_style ); ?>">
                                    🎉 <?php echo esc_html( $subtitle ); ?>
                                </span>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ( $title ) : ?>
                            <h2 class="countdown-title" style="<?php echo esc_attr( $title_style ); ?>"><?php echo esc_html( $title ); ?></h2>
                        <?php endif; ?>
                        
                        <?php if ( $description ) : ?>
                        <p class="countdown-desc" style="<?php echo esc_attr( $desc_style ); ?>"><?php echo esc_html( $description ); ?></p>
                        <?php endif; ?>
                        
                        <div class="countdown-timer" data-date="<?php echo esc_attr( $target_timestamp * 1000 ); ?>">
                            <div class="countdown-item" style="<?php echo esc_attr( $timer_item_style ); ?>">
                                <div class="countdown-value" data-type="days">00</div>
                                <div class="countdown-unit"><?php esc_html_e( '天', 'developer-starter' ); ?></div>
                            </div>
                            <div class="countdown-item" style="<?php echo esc_attr( $timer_item_style ); ?>">
                                <div class="countdown-value" data-type="hours">00</div>
                                <div class="countdown-unit"><?php esc_html_e( '时', 'developer-starter' ); ?></div>
                            </div>
                            <div class="countdown-item" style="<?php echo esc_attr( $timer_item_style ); ?>">
                                <div class="countdown-value" data-type="minutes">00</div>
                                <div class="countdown-unit"><?php esc_html_e( '分', 'developer-starter' ); ?></div>
                            </div>
                            <div class="countdown-item" style="<?php echo esc_attr( $timer_item_style ); ?>">
                                <div class="countdown-value" data-type="seconds">00</div>
                                <div class="countdown-unit"><?php esc_html_e( '秒', 'developer-starter' ); ?></div>
                            </div>
                        </div>
                        
                        <?php if ( $btn_text && $btn_link ) : ?>
                        <a href="<?php echo esc_url( $btn_link ); ?>" class="countdown-btn" style="<?php echo esc_attr( $btn_style ); ?>">
                            <?php echo esc_html( $btn_text ); ?>
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>
        
        <script>
        (function() {
            var container = document.getElementById('<?php echo esc_js( $unique_id ); ?>');
            if (!container) return;
            
            var timerContainer = container.querySelector('.countdown-timer');
            var targetTime = parseInt(timerContainer.getAttribute('data-date'));
            
            function updateCountdown() {
                var now = new Date().getTime();
                var distance = targetTime - now;
                
                if (distance < 0) {
                    distance = 0;
                }
                
                var days = Math.floor(distance / (1000 * 60 * 60 * 24));
                var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                var seconds = Math.floor((distance % (1000 * 60)) / 1000);
                
                var daysEl = container.querySelector('[data-type="days"]');
                var hoursEl = container.querySelector('[data-type="hours"]');
                var minutesEl = container.querySelector('[data-type="minutes"]');
                var secondsEl = container.querySelector('[data-type="seconds"]');
                
                if (daysEl) daysEl.textContent = String(days).padStart(2, '0');
                if (hoursEl) hoursEl.textContent = String(hours).padStart(2, '0');
                if (minutesEl) minutesEl.textContent = String(minutes).padStart(2, '0');
                if (secondsEl) secondsEl.textContent = String(seconds).padStart(2, '0');
            }
            
            updateCountdown();
            var countdownTimer = setInterval(function() {
                if (!document.body.contains(container)) {
                    clearInterval(countdownTimer);
                    return;
                }
                updateCountdown();
            }, 1000);
        })();
        </script>
        <?php
    }
}
