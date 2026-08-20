<?php
/**
 * Double Column Carousel Module - 双栏轮播
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Modules\Modules;

use Developer_Starter\Modules\Module_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Double_Column_Carousel_Module extends Module_Base {

    public function __construct() {
        $this->category = 'homepage';
        $this->icon = 'dashicons-slides';
        $this->description = __( '左侧轮播图+右侧广告图', 'developer-starter' );
    }

    public function get_id() {
        return 'double_column_carousel';
    }

    public function get_name() {
        return __( '双栏轮播', 'developer-starter' );
    }

    public function get_fields() {
        return array(
            array(
                'id'      => 'dcc_layout',
                'type'    => 'select',
                'label'   => __( '右侧布局', 'developer-starter' ),
                'options' => array(
                    '2' => __( '2张图片（上下排列）', 'developer-starter' ),
                    '3' => __( '3张图片（上1下2）', 'developer-starter' ),
                ),
                'default' => '2',
            ),
            array(
                'id'      => 'dcc_height',
                'type'    => 'select',
                'label'   => __( '模块高度', 'developer-starter' ),
                'options' => array(
                    '400' => '400px',
                    '450' => '450px',
                    '500' => __( '500px（推荐）', 'developer-starter' ),
                    '550' => '550px',
                    '600' => '600px',
                ),
                'default' => '500',
            ),
            array(
                'id'      => 'dcc_gap',
                'type'    => 'select',
                'label'   => __( '间距', 'developer-starter' ),
                'options' => array(
                    '8'  => '8px',
                    '12' => '12px',
                    '16' => __( '16px（推荐）', 'developer-starter' ),
                    '20' => '20px',
                ),
                'default' => '16',
            ),
            array(
                'id'         => 'dcc_slides',
                'type'       => 'repeater',
                'label'      => __( '左侧轮播图', 'developer-starter' ),
                'add_button' => __( '添加轮播图', 'developer-starter' ),
                'fields'     => array(
                    array(
                        'id'    => 'image',
                        'type'  => 'image',
                        'label' => __( '图片', 'developer-starter' ),
                    ),
                    array(
                        'id'    => 'url',
                        'type'  => 'text',
                        'label' => __( '链接地址', 'developer-starter' ),
                    ),
                    array(
                        'id'    => 'title',
                        'type'  => 'text',
                        'label' => __( '标题（可选）', 'developer-starter' ),
                    ),
                ),
            ),
            array(
                'id'    => 'dcc_right_1_image',
                'type'  => 'image',
                'label' => __( '右侧图1（上方）', 'developer-starter' ),
            ),
            array(
                'id'    => 'dcc_right_1_url',
                'type'  => 'text',
                'label' => __( '右侧图1链接', 'developer-starter' ),
            ),
            array(
                'id'    => 'dcc_right_2_image',
                'type'  => 'image',
                'label' => __( '右侧图2（下方/左下）', 'developer-starter' ),
            ),
            array(
                'id'    => 'dcc_right_2_url',
                'type'  => 'text',
                'label' => __( '右侧图2链接', 'developer-starter' ),
            ),
            array(
                'id'    => 'dcc_right_3_image',
                'type'  => 'image',
                'label' => __( '右侧图3（右下，仅3张布局）', 'developer-starter' ),
            ),
            array(
                'id'    => 'dcc_right_3_url',
                'type'  => 'text',
                'label' => __( '右侧图3链接', 'developer-starter' ),
            ),
        );
    }

    public function render( $data = array() ) {
        $layout = isset( $data['dcc_layout'] ) ? $data['dcc_layout'] : '2';
        $height = isset( $data['dcc_height'] ) ? intval( $data['dcc_height'] ) : 500;
        $gap = isset( $data['dcc_gap'] ) ? intval( $data['dcc_gap'] ) : 16;
        $slides = isset( $data['dcc_slides'] ) ? $data['dcc_slides'] : array();
        
        // 右侧图片
        $right_1_image = isset( $data['dcc_right_1_image'] ) ? $data['dcc_right_1_image'] : '';
        $right_1_url = isset( $data['dcc_right_1_url'] ) ? $data['dcc_right_1_url'] : '';
        $right_2_image = isset( $data['dcc_right_2_image'] ) ? $data['dcc_right_2_image'] : '';
        $right_2_url = isset( $data['dcc_right_2_url'] ) ? $data['dcc_right_2_url'] : '';
        $right_3_image = isset( $data['dcc_right_3_image'] ) ? $data['dcc_right_3_image'] : '';
        $right_3_url = isset( $data['dcc_right_3_url'] ) ? $data['dcc_right_3_url'] : '';
        
        // 计算右侧图片高度
        if ( $layout === '2' ) {
            // 2张图片：上下各一半（减去中间间距）
            $right_top_height = ( $height - $gap ) / 2;
            $right_bottom_height = $right_top_height;
        } else {
            // 3张图片：上方占40%，下方两张各占60%的一半
            $right_top_height = ( $height - $gap ) * 0.4;
            $right_bottom_height = ( $height - $gap ) * 0.6;
        }
        
        $module_id = 'dcc-' . uniqid();
        ?>
        <section class="module module-double-column-carousel" id="<?php echo esc_attr( $module_id ); ?>">
            <div class="container">
                <div class="dcc-wrapper" style="display: flex; gap: <?php echo $gap; ?>px; height: <?php echo $height; ?>px;">
                    <!-- 左侧轮播 -->
                    <div class="dcc-left" style="flex: 2; min-width: 0; height: 100%; border-radius: 12px; overflow: hidden;">
                        <?php if ( ! empty( $slides ) && count( $slides ) > 1 ) : ?>
                            <div class="swiper dcc-swiper" style="width: 100%; height: 100%;">
                                <div class="swiper-wrapper">
                                    <?php foreach ( $slides as $slide ) : ?>
                                        <div class="swiper-slide">
                                            <?php if ( ! empty( $slide['url'] ) ) : ?>
                                                <a href="<?php echo esc_url( $slide['url'] ); ?>" class="dcc-slide-link" style="display: block; width: 100%; height: 100%;">
                                            <?php endif; ?>
                                                <div class="dcc-slide" style="width: 100%; height: 100%; background-image: url('<?php echo esc_url( $slide['image'] ); ?>'); background-size: cover; background-position: center;">
                                                    <?php if ( ! empty( $slide['title'] ) ) : ?>
                                                        <div class="dcc-slide-title" style="position: absolute; bottom: 0; left: 0; right: 0; padding: var(--qiling-space-20); background: linear-gradient(transparent, rgba(var(--qiling-rgb-0-0-0), 0.7)); color: var(--color-neutral-0); font-size: var(--qiling-text-rem-1p25); font-weight: 600;">
                                                            <?php echo esc_html( $slide['title'] ); ?>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            <?php if ( ! empty( $slide['url'] ) ) : ?>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="swiper-pagination"></div>
                            </div>
                        <?php elseif ( ! empty( $slides ) ) : 
                            $slide = $slides[0];
                        ?>
                            <?php if ( ! empty( $slide['url'] ) ) : ?>
                                <a href="<?php echo esc_url( $slide['url'] ); ?>" style="display: block; width: 100%; height: 100%;">
                            <?php endif; ?>
                                <div class="dcc-single-slide" style="width: 100%; height: 100%; background-image: url('<?php echo esc_url( $slide['image'] ); ?>'); background-size: cover; background-position: center; position: relative;">
                                    <?php if ( ! empty( $slide['title'] ) ) : ?>
                                        <div class="dcc-slide-title" style="position: absolute; bottom: 0; left: 0; right: 0; padding: var(--qiling-space-20); background: linear-gradient(transparent, rgba(var(--qiling-rgb-0-0-0), 0.7)); color: var(--color-neutral-0); font-size: var(--qiling-text-rem-1p25); font-weight: 600;">
                                            <?php echo esc_html( $slide['title'] ); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php if ( ! empty( $slide['url'] ) ) : ?>
                                </a>
                            <?php endif; ?>
                        <?php else : ?>
                            <div style="width: 100%; height: 100%; background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-dark) 100%); display: flex; align-items: center; justify-content: center; color: var(--qiling-color-rgba-255-255-255-07);">
                                <?php esc_html_e( '请添加轮播图片', 'developer-starter' ); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- 右侧广告图 -->
                    <div class="dcc-right" style="flex: 1; min-width: 0; display: flex; flex-direction: column; gap: <?php echo $gap; ?>px;">
                        <!-- 右侧图1（上方）-->
                        <div class="dcc-right-top" style="height: <?php echo $right_top_height; ?>px; border-radius: 12px; overflow: hidden;">
                            <?php if ( $right_1_image ) : ?>
                                <?php if ( $right_1_url ) : ?>
                                    <a href="<?php echo esc_url( $right_1_url ); ?>" style="display: block; width: 100%; height: 100%;">
                                <?php endif; ?>
                                    <div style="width: 100%; height: 100%; background-image: url('<?php echo esc_url( $right_1_image ); ?>'); background-size: cover; background-position: center; transition: transform 0.3s;"></div>
                                <?php if ( $right_1_url ) : ?>
                                    </a>
                                <?php endif; ?>
                            <?php else : ?>
                                <div style="width: 100%; height: 100%; background: var(--color-gray-200); display: flex; align-items: center; justify-content: center; color: var(--color-gray-400);"><?php esc_html_e( '广告位1', 'developer-starter' ); ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- 右侧下方 -->
                        <?php if ( $layout === '2' ) : ?>
                            <!-- 2张布局：下方单张图 -->
                            <div class="dcc-right-bottom" style="height: <?php echo $right_bottom_height; ?>px; border-radius: 12px; overflow: hidden;">
                                <?php if ( $right_2_image ) : ?>
                                    <?php if ( $right_2_url ) : ?>
                                        <a href="<?php echo esc_url( $right_2_url ); ?>" style="display: block; width: 100%; height: 100%;">
                                    <?php endif; ?>
                                        <div style="width: 100%; height: 100%; background-image: url('<?php echo esc_url( $right_2_image ); ?>'); background-size: cover; background-position: center; transition: transform 0.3s;"></div>
                                    <?php if ( $right_2_url ) : ?>
                                        </a>
                                    <?php endif; ?>
                                <?php else : ?>
                                    <div style="width: 100%; height: 100%; background: var(--color-gray-200); display: flex; align-items: center; justify-content: center; color: var(--color-gray-400);"><?php esc_html_e( '广告位2', 'developer-starter' ); ?></div>
                                <?php endif; ?>
                            </div>
                        <?php else : ?>
                            <!-- 3张布局：下方两张图 -->
                            <div class="dcc-right-bottom-row" style="height: <?php echo $right_bottom_height; ?>px; display: flex; gap: <?php echo $gap; ?>px;">
                                <div class="dcc-right-bottom-left" style="flex: 1; border-radius: 12px; overflow: hidden;">
                                    <?php if ( $right_2_image ) : ?>
                                        <?php if ( $right_2_url ) : ?>
                                            <a href="<?php echo esc_url( $right_2_url ); ?>" style="display: block; width: 100%; height: 100%;">
                                        <?php endif; ?>
                                            <div style="width: 100%; height: 100%; background-image: url('<?php echo esc_url( $right_2_image ); ?>'); background-size: cover; background-position: center; transition: transform 0.3s;"></div>
                                        <?php if ( $right_2_url ) : ?>
                                            </a>
                                        <?php endif; ?>
                                    <?php else : ?>
                                        <div style="width: 100%; height: 100%; background: var(--color-gray-200); display: flex; align-items: center; justify-content: center; color: var(--color-gray-400);"><?php esc_html_e( '广告位2', 'developer-starter' ); ?></div>
                                    <?php endif; ?>
                                </div>
                                <div class="dcc-right-bottom-right" style="flex: 1; border-radius: 12px; overflow: hidden;">
                                    <?php if ( $right_3_image ) : ?>
                                        <?php if ( $right_3_url ) : ?>
                                            <a href="<?php echo esc_url( $right_3_url ); ?>" style="display: block; width: 100%; height: 100%;">
                                        <?php endif; ?>
                                            <div style="width: 100%; height: 100%; background-image: url('<?php echo esc_url( $right_3_image ); ?>'); background-size: cover; background-position: center; transition: transform 0.3s;"></div>
                                        <?php if ( $right_3_url ) : ?>
                                            </a>
                                        <?php endif; ?>
                                    <?php else : ?>
                                        <div style="width: 100%; height: 100%; background: var(--color-gray-200); display: flex; align-items: center; justify-content: center; color: var(--color-gray-400);"><?php esc_html_e( '广告位3', 'developer-starter' ); ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>
<script>
            (function() {
            function boot() {
                var root = document.getElementById('<?php echo esc_js( $module_id ); ?>');
                if (!root || root.dataset.doubleColumnCarouselInitialized) return;
                root.dataset.doubleColumnCarouselInitialized = 'true';
                var dccSwiper = root.querySelector('.dcc-swiper');
                if (dccSwiper && typeof Swiper !== 'undefined') {
                    new Swiper(dccSwiper, {
                        loop: true,
                        autoplay: {
                            delay: 4000,
                            disableOnInteraction: false,
                        },
                        pagination: {
                            el: root.querySelector('.swiper-pagination'),
                            clickable: true,
                        },
                        effect: 'fade',
                        fadeEffect: {
                            crossFade: true
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
        <?php
    }
}
