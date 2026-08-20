<?php
/**
 * Image Comparison Module - 图片对比滑块
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Modules\Modules;

use Developer_Starter\Modules\Module_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Image_Comparison_Module extends Module_Base {

    public function __construct() {
        $this->category = 'media';
        $this->icon = 'dashicons-format-gallery';
        $this->description = __( '两张图片左右拖动对比', 'developer-starter' );
    }

    public function get_id() {
        return 'image_comparison';
    }

    public function get_name() {
        return __( '图片对比滑块', 'developer-starter' );
    }

    public function get_fields() {
        return array(
            array(
                'id'      => 'heading_basic',
                'type'    => 'heading',
                'label'   => __( '基础设置', 'developer-starter' ),
            ),
            array(
                'id'      => 'title',
                'type'    => 'text',
                'label'   => __( '标题', 'developer-starter' ),
                'default' => '',
            ),
            array(
                'id'      => 'subtitle',
                'type'    => 'text',
                'label'   => __( '副标题', 'developer-starter' ),
                'default' => '',
            ),
            array(
                'id'      => 'heading_images',
                'type'    => 'heading',
                'label'   => __( '图片设置', 'developer-starter' ),
            ),
            array(
                'id'      => 'image_before',
                'type'    => 'image',
                'label'   => __( 'Before 图片 (左)', 'developer-starter' ),
                'default' => '',
                'desc'    => __( '建议两张图片尺寸完全一致', 'developer-starter' ),
            ),
            array(
                'id'      => 'label_before',
                'type'    => 'text',
                'label'   => __( 'Before 标签', 'developer-starter' ),
                'default' => 'Before',
            ),
            array(
                'id'      => 'image_after',
                'type'    => 'image',
                'label'   => __( 'After 图片 (右)', 'developer-starter' ),
                'default' => '',
                'desc'    => __( '建议两张图片尺寸完全一致', 'developer-starter' ),
            ),
            array(
                'id'      => 'label_after',
                'type'    => 'text',
                'label'   => __( 'After 标签', 'developer-starter' ),
                'default' => 'After',
            ),
            array(
                'id'          => 'comparison_badge_bg',
                'type'        => 'color',
                'label'       => __( '标签/徽章背景颜色', 'developer-starter' ),
                'default'     => '',
                'description' => __( '控制 Before/After 标签背景，留空时跟随页面预设风格或全局徽章颜色。', 'developer-starter' ),
            ),
            array(
                'id'      => 'heading_style',
                'type'    => 'heading',
                'label'   => __( '样式设置', 'developer-starter' ),
            ),
            array(
                'id'      => 'slider_color',
                'type'    => 'color',
                'label'   => __( '滑块颜色', 'developer-starter' ),
                'default' => 'var(--color-neutral-0)',
            ),
            array(
                'id'      => 'height',
                'type'    => 'number',
                'label'   => __( '高度 (px)', 'developer-starter' ),
                'default' => '500',
                'desc'    => __( 'PC端显示高度，移动端会自动适应', 'developer-starter' ),
            ),
            array(
                'id'      => 'initial_offset',
                'type'    => 'range',
                'label'   => __( '初始位置 (%)', 'developer-starter' ),
                'default' => '50',
                'min'     => '0',
                'max'     => '100',
            ),
        );
    }

    public function render( $data = array() ) {
        if ( empty( $data['image_before'] ) || empty( $data['image_after'] ) ) {
            if ( current_user_can( 'edit_posts' ) ) {
                echo '<div class="alert alert-warning">' . __( '请在后台设置 Before 和 After 图片', 'developer-starter' ) . '</div>';
            }
            return;
        }

        $title = isset( $data['title'] ) ? $data['title'] : '';
        $subtitle = isset( $data['subtitle'] ) ? $data['subtitle'] : '';
        $before_img = $data['image_before'];
        $after_img = $data['image_after'];
        $before_label = isset( $data['label_before'] ) ? $data['label_before'] : 'Before';
        $after_label = isset( $data['label_after'] ) ? $data['label_after'] : 'After';
        $badge_bg = isset( $data['comparison_badge_bg'] ) ? trim( wp_strip_all_tags( (string) $data['comparison_badge_bg'] ) ) : '';
        $badge_bg = str_replace( array( ';', '{', '}' ), '', $badge_bg );
        $slider_color = isset( $data['slider_color'] ) ? $data['slider_color'] : 'var(--color-neutral-0)';
        $height = isset( $data['height'] ) ? absint( $data['height'] ) : 500;
        $offset = isset( $data['initial_offset'] ) ? absint( $data['initial_offset'] ) : 50;
        
        $unique_id = 'ds-compare-' . uniqid();
        
        ?>
        <div class="module module-image-comparison section-padding"<?php echo '' !== $badge_bg ? ' style="' . esc_attr( '--qiling-component-badge-bg: ' . $badge_bg . ';' ) . '"' : ''; ?>>
            <div class="container">
                <?php if ( $title ) : ?>
                    <div class="section-header text-center">
                        <h2 class="section-title"><?php echo esc_html( $title ); ?></h2>
                        <?php if ( $subtitle ) : ?>
                            <p class="section-subtitle"><?php echo esc_html( $subtitle ); ?></p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
                <div id="<?php echo esc_attr( $unique_id ); ?>" class="ds-compare-container" style="--ds-compare-height: <?php echo esc_attr( $height ); ?>px; --ds-compare-slider-color: <?php echo esc_attr( $slider_color ); ?>;">
                    <!-- After Image (Background) -->
                    <img src="<?php echo esc_url( $after_img ); ?>" class="ds-compare-img" alt="After">
                    <?php if ( $after_label ) : ?>
                        <span class="ds-compare-label label-after"><?php echo esc_html( $after_label ); ?></span>
                    <?php endif; ?>
                    
                    <!-- Before Image (Overlay) -->
                    <div class="ds-compare-overlay">
                        <img src="<?php echo esc_url( $before_img ); ?>" class="ds-compare-img" alt="Before">
                        <?php if ( $before_label ) : ?>
                            <span class="ds-compare-label label-before"><?php echo esc_html( $before_label ); ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Handle -->
                    <div class="ds-compare-handle"></div>
                </div>
            </div>
        </div>
        <script>
        (function() {
            var container = document.getElementById('<?php echo esc_js( $unique_id ); ?>');
            if (!container) return;

            var overlay = container.querySelector('.ds-compare-overlay');
            var handle = container.querySelector('.ds-compare-handle');
            var activePointer = null;

            function updateSlider(x) {
                var rect = container.getBoundingClientRect();
                var position = ((x - rect.left) / rect.width) * 100;
                position = Math.max(0, Math.min(100, position));
                overlay.style.width = position + '%';
                handle.style.left = position + '%';
            }

            handle.style.touchAction = 'none';
            handle.addEventListener('pointerdown', function(e) {
                activePointer = e.pointerId;
                handle.setPointerCapture(e.pointerId);
                updateSlider(e.clientX);
                e.preventDefault();
            });
            handle.addEventListener('pointermove', function(e) {
                if (e.pointerId === activePointer) updateSlider(e.clientX);
            });
            function stopDragging(e) {
                if (e.pointerId === activePointer) activePointer = null;
            }
            handle.addEventListener('pointerup', stopDragging);
            handle.addEventListener('pointercancel', stopDragging);
            handle.addEventListener('lostpointercapture', stopDragging);

            container.addEventListener('click', function(e) {
                if (e.target === handle || handle.contains(e.target)) return;
                updateSlider(e.clientX);
            });

            overlay.style.width = '<?php echo esc_js( $offset ); ?>%';
            handle.style.left = '<?php echo esc_js( $offset ); ?>%';
        })();
        </script>
        <?php
    }
}
