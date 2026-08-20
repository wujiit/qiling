<?php
/**
 * Tabbed Carousel Module - Tab切换轮播
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Modules\Modules;

use Developer_Starter\Modules\Module_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Tabbed_Carousel_Module extends Module_Base {

    public function __construct() {
        $this->category = 'general';
        $this->icon = 'dashicons-images-alt2';
        $this->description = __( '支持Tab切换的3D轮播展示模块', 'developer-starter' );
    }

    public function get_id() {
        return 'tabbed_carousel';
    }

    public function get_name() {
        return __( 'Tab切换轮播', 'developer-starter' );
    }

    public function get_fields() {
        return array(
            // --- 头部内容 ---
            array( 'id' => 'tc_title', 'type' => 'text', 'label' => __( '模块标题', 'developer-starter' ), 'default' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '启灵主题 · 多维特色功能展示', 'Qiling Theme · Feature Highlights' ) : __( '启灵主题 · 多维特色功能展示', 'developer-starter' ), 'desc' => __( '支持HTML标签，如 <strong>加粗</strong>', 'developer-starter' ) ),
            array( 'id' => 'tc_subtitle', 'type' => 'text', 'label' => __( '模块副标题', 'developer-starter' ) ),

            // --- 轮播内容项 ---
            array(
                'id' => 'tc_items',
                'label' => __( '轮播项', 'developer-starter' ),
                'type' => 'repeater',
                'description' => __( '每项对应一个Tab和一张轮播图片', 'developer-starter' ),
                'fields' => array(
                    array( 'id' => 'tab_title', 'label' => __( 'Tab标题', 'developer-starter' ), 'type' => 'text', 'default' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '功能名称', 'Feature Name' ) : __( '功能名称', 'developer-starter' ) ),
                    array( 'id' => 'tab_icon', 'label' => __( 'Tab图标 (可选)', 'developer-starter' ), 'type' => 'text', 'desc' => __( '支持 Emoji (如 🔥) 或 Symbol类名 (如 icon-home)', 'developer-starter' ) ),
                    array( 'id' => 'image', 'label' => __( '轮播图片', 'developer-starter' ), 'type' => 'image' ),
                    array( 'id' => 'desc', 'label' => __( '图片描述/标题 (可选)', 'developer-starter' ), 'type' => 'text' ),
                ),
            ),

            // --- 样式设置 (Tab) ---
            array(
                'id' => 'tc_tab_color',
                'label' => __( 'Tab文字颜色 (默认)', 'developer-starter' ),
                'type' => 'color',
                'default' => 'var(--color-neutral-400)',
            ),
            array(
                'id' => 'tc_tab_active_color',
                'label' => __( 'Tab文字颜色 (激活)', 'developer-starter' ),
                'type' => 'color',
                'default' => 'var(--color-neutral-800)',
            ),
            array(
                'id' => 'tc_tab_bar_color',
                'label' => __( 'Tab下划线颜色', 'developer-starter' ),
                'type' => 'color',
                'default' => 'var(--color-warning)',
            ),

            // --- 样式设置 (轮播) ---
            array(
                'id' => 'tc_effect',
                'label' => __( '轮播效果', 'developer-starter' ),
                'type' => 'select',
                'options' => array(
                    'coverflow' => __( '3D Coverflow (封面流)', 'developer-starter' ),
                    'slide' => __( 'Slide (平滑滑动)', 'developer-starter' ),
                    'fade' => __( 'Fade (渐隐渐显)', 'developer-starter' ),
                ),
                'default' => 'coverflow',
            ),
            array(
                'id' => 'tc_autoplay',
                'label' => __( '自动轮播', 'developer-starter' ),
                'type' => 'select',
                'options' => array(
                    '1' => __( '开启', 'developer-starter' ),
                    '' => __( '关闭', 'developer-starter' ),
                ),
                'default' => '1',
            ),
            array(
                'id' => 'tc_autoplay_delay',
                'label' => __( '自动轮播间隔 (毫秒)', 'developer-starter' ),
                'type' => 'number',
                'default' => '3000',
                'dependency' => array( 'tc_autoplay', '==', '1' ),
            ),
            array(
                'id' => 'tc_image_width',
                'label' => __( '主图宽度 (如 800px)', 'developer-starter' ),
                'type' => 'text',
                'default' => '800px',
                'desc' => __( '设置中间主图的显示宽度', 'developer-starter' ),
            ),
            array(
                'id' => 'tc_image_shadow',
                'label' => __( '图片阴影', 'developer-starter' ),
                'type' => 'select',
                'options' => array(
                    'none' => __( '无阴影', 'developer-starter' ),
                    'soft' => __( '柔和阴影', 'developer-starter' ),
                    'strong' => __( '强阴影', 'developer-starter' ),
                ),
                'default' => 'soft',
            ),
            array(
                'id' => 'tc_slide_bg',
                'label' => __( '图片背景色 (可选)', 'developer-starter' ),
                'type' => 'color',
                'desc' => __( '如果图片是透明PNG，可设置卡片背景色', 'developer-starter' ),
                'default' => 'var(--color-neutral-0)',
            ),

            // --- 模块背景 ---
            array(
                'id' => 'module_bg_type',
                'label' => __( '背景类型', 'developer-starter' ),
                'type' => 'select',
                'options' => array(
                    'color' => __( '纯色/渐变', 'developer-starter' ),
                    'image' => __( '图片', 'developer-starter' ),
                ),
                'default' => 'color',
            ),
            array(
                'id' => 'module_bg_color',
                'label' => __( '背景颜色', 'developer-starter' ),
                'type' => 'color',
                'default' => 'var(--color-neutral-0)',
                'dependency' => array( 'module_bg_type', '==', 'color' ),
            ),
            array(
                'id' => 'module_bg_image',
                'label' => __( '背景图片', 'developer-starter' ),
                'type' => 'image',
                'dependency' => array( 'module_bg_type', '==', 'image' ),
            ),

            // --- 间距 ---
            array( 'id' => 'pt', 'label' => __( '上边距', 'developer-starter' ), 'type' => 'text', 'default' => '80px' ),
            array( 'id' => 'pb', 'label' => __( '下边距', 'developer-starter' ), 'type' => 'text', 'default' => '80px' ),
        );
    }

    public function render( $data = array() ) {
        // 基础数据
        $title = isset( $data['tc_title'] ) ? $data['tc_title'] : ( function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '启灵主题 · 多维特色功能展示', 'Qiling Theme · Feature Highlights' ) : __( '启灵主题 · 多维特色功能展示', 'developer-starter' ) );
        $subtitle = isset( $data['tc_subtitle'] ) ? $data['tc_subtitle'] : '';
        $items = isset( $data['tc_items'] ) ? $data['tc_items'] : array();

        if ( empty( $items ) ) {
            // 默认演示数据
            $items = array(
                array( 'tab_title' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '极速开发', 'Fast Setup' ) : __( '极速开发', 'developer-starter' ), 'tab_icon' => '⚡', 'image' => '', 'desc' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '基于模块化开发，快速搭建现代化网站', 'Launch polished pages quickly with modular building blocks.' ) : __( '基于模块化开发，快速搭建现代化网站', 'developer-starter' ) ),
                array( 'tab_title' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '响应式设计', 'Responsive Design' ) : __( '响应式设计', 'developer-starter' ), 'tab_icon' => '📱', 'image' => '', 'desc' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '完美适配桌面、平板和移动设备', 'Designed to feel consistent across desktop, tablet, and mobile.' ) : __( '完美适配桌面、平板和移动设备', 'developer-starter' ) ),
                array( 'tab_title' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '高度定制', 'Flexible Control' ) : __( '高度定制', 'developer-starter' ), 'tab_icon' => '🎨', 'image' => '', 'desc' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '灵活的后台配置，满足各种个性化需求', 'Adjust layouts, content, and styles with flexible admin options.' ) : __( '灵活的后台配置，满足各种个性化需求', 'developer-starter' ) ),
                array( 'tab_title' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( 'SEO友好', 'SEO Friendly' ) : __( 'SEO友好', 'developer-starter' ), 'tab_icon' => '🔍', 'image' => '', 'desc' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '内置SEO优化机制，提升搜索引擎排名', 'Built with search-friendly structure and content presentation.' ) : __( '内置SEO优化机制，提升搜索引擎排名', 'developer-starter' ) ),
            );
        }

        // 样式配置
        $tab_color = isset( $data['tc_tab_color'] ) ? $data['tc_tab_color'] : 'var(--color-neutral-400)';
        $tab_active_color = isset( $data['tc_tab_active_color'] ) ? $data['tc_tab_active_color'] : 'var(--color-neutral-800)';
        $tab_bar_color = isset( $data['tc_tab_bar_color'] ) ? $data['tc_tab_bar_color'] : 'var(--color-warning)';
        
        $effect = isset( $data['tc_effect'] ) ? $data['tc_effect'] : 'coverflow';
        $autoplay = isset( $data['tc_autoplay'] ) && $data['tc_autoplay'] === '1';
        $autoplay_delay = isset( $data['tc_autoplay_delay'] ) ? intval( $data['tc_autoplay_delay'] ) : 3000;
        
        $img_width = isset( $data['tc_image_width'] ) ? $data['tc_image_width'] : '800px';
        $img_shadow = isset( $data['tc_image_shadow'] ) ? $data['tc_image_shadow'] : 'soft';
        $slide_bg = isset( $data['tc_slide_bg'] ) ? $data['tc_slide_bg'] : 'var(--color-neutral-0)';

        // 背景
        $bg_type = isset( $data['module_bg_type'] ) ? $data['module_bg_type'] : 'color';
        $bg_color = isset( $data['module_bg_color'] ) ? $data['module_bg_color'] : 'var(--color-neutral-0)';
        $bg_image = isset( $data['module_bg_image'] ) ? $data['module_bg_image'] : '';
        $pt = isset( $data['pt'] ) ? $data['pt'] : '80px';
        $pb = isset( $data['pb'] ) ? $data['pb'] : '80px';

        $section_style = "padding-top: {$pt}; padding-bottom: {$pb};";
        if ( $bg_type === 'image' && $bg_image ) {
            $section_style .= "background-image: url('" . esc_url( $bg_image ) . "'); background-size: cover; background-position: center;";
        } else {
            $section_style .= strpos( $bg_color, 'gradient' ) !== false ? "background: {$bg_color};" : "background-color: {$bg_color};";
        }

        // CSS变量
        $css_vars = array();
        $css_vars[] = "--tc-tab-color: {$tab_color}";
        $css_vars[] = "--tc-tab-active: {$tab_active_color}";
        $css_vars[] = "--tc-bar-color: {$tab_bar_color}";
        $css_vars[] = "--tc-img-width: {$img_width}";
        $css_vars[] = "--tc-slide-bg: {$slide_bg}";

        $shadow_val = 'none';
        if ( $img_shadow === 'soft' ) $shadow_val = '0 10px 30px -10px var(--qiling-color-rgba-0-0-0-01)';
        if ( $img_shadow === 'strong' ) $shadow_val = '0 20px 50px -12px var(--qiling-color-rgba-0-0-0-025)';
        $css_vars[] = "--tc-shadow: {$shadow_val}";

        $module_id = 'tc-' . uniqid();
        ?>
        <section class="module module-tabbed-carousel" id="<?php echo esc_attr( $module_id ); ?>" style="<?php echo esc_attr( $section_style ); ?> <?php echo implode('; ', $css_vars); ?>">
            <div class="container">
                <!-- 头部 -->
                <div class="section-header text-center" style="margin-bottom: var(--qiling-space-50);">
                    <?php if ( $title ) : ?>
                        <h2 class="section-title"><?php echo wp_kses_post( $title ); ?></h2>
                    <?php endif; ?>
                    <?php if ( $subtitle ) : ?>
                        <p class="section-subtitle"><?php echo wp_kses_post( $subtitle ); ?></p>
                    <?php endif; ?>
                </div>

                <!-- Tab 导航 -->
                <div class="tc-tabs-wrapper">
                    <div class="tc-tabs">
                        <?php foreach ( $items as $index => $item ) : 
                            $tab_icon = isset( $item['tab_icon'] ) ? trim( $item['tab_icon'] ) : '';
                        ?>
                            <div class="tc-tab-item <?php echo $index === 0 ? 'active' : ''; ?>" data-index="<?php echo $index; ?>">
                                <?php if ( ! empty( $tab_icon ) ) : ?>
                                    <!-- 智能判断图标类型 -->
                                    <!-- 智能判断图标类型 -->
                                    <?php echo developer_starter_get_icon_html( $tab_icon ); ?>
                                <?php endif; ?>
                                <span><?php echo esc_html( $item['tab_title'] ); ?></span>
                            </div>
                        <?php endforeach; ?>
                        <div class="tc-tab-bar"></div>
                    </div>
                </div>

                <!-- Swiper 轮播 -->
                <div class="tc-carousel-wrapper">
                    <div class="swiper tc-swiper">
                        <div class="swiper-wrapper">
                            <?php foreach ( $items as $item ) : ?>
                                <div class="swiper-slide">
                                    <div class="tc-slide-content">
                                        <?php if ( ! empty( $item['image'] ) ) : ?>
                                            <img src="<?php echo esc_url( $item['image'] ); ?>" alt="<?php echo esc_attr( $item['tab_title'] ); ?>">
                                        <?php else : ?>
                                            <div class="tc-placeholder">
                                                <span>🖼️</span>
                                                <p><?php esc_html_e( '请上传图片', 'developer-starter' ); ?></p>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <?php if ( ! empty( $item['desc'] ) ) : ?>
                                            <div class="tc-slide-desc">
                                                <?php echo wp_kses_post( $item['desc'] ); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="swiper-pagination"></div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Script & Style -->
        <script>
        (function() {
        function boot() {
            var moduleId = '<?php echo esc_js( $module_id ); ?>';
            var container = document.getElementById(moduleId);
            if (!container || container.dataset.tabbedCarouselInitialized) return;
            container.dataset.tabbedCarouselInitialized = 'true';

            var tabs = container.querySelectorAll('.tc-tab-item');
            var tabBar = container.querySelector('.tc-tab-bar');
            var swiperEl = container.querySelector('.tc-swiper');

            var swiper = null;

            function bindTabEvents() {
                tabs.forEach(function(tab, index) {
                    tab.addEventListener('click', function() {
                        if (swiper) {
                            swiper.slideTo(index);
                        }
                    });
                });
            }

            function initSwiper() {
                if (!swiperEl) {
                    return true;
                }
                if (swiperEl.classList.contains('swiper-initialized')) {
                    swiper = swiperEl.swiper || null;
                    return true;
                }
                if (typeof Swiper === 'undefined') {
                    return false;
                }

                // 初始化 Swiper
                swiper = new Swiper(swiperEl, {
                    effect: '<?php echo esc_js( $effect ); ?>',
                    grabCursor: true,
                    centeredSlides: true,
                    slidesPerView: 'auto',
                    initialSlide: 0,
                    speed: 600,
                    <?php if ( $autoplay ) : ?>
                    autoplay: {
                        delay: <?php echo $autoplay_delay; ?>,
                        disableOnInteraction: false,
                        pauseOnMouseEnter: true,
                    },
                    <?php endif; ?>
                    coverflowEffect: {
                        rotate: 0,
                        stretch: 0,
                        depth: 100,
                        modifier: 2,
                        slideShadows: false, // 关闭自带阴影，用CSS控制更好看
                    },
                    pagination: {
                        el: container.querySelector('.swiper-pagination'),
                        clickable: true,
                    },
                    on: {
                        slideChange: function () {
                            updateActiveTab(this.activeIndex);
                        }
                    }
                });

                return true;
            }

            // 更新 Tab 状态和下划线位置
            function updateActiveTab(index) {
                // 移除所有 active
                tabs.forEach(function(t) { t.classList.remove('active'); });
                
                // 激活当前
                var activeTab = tabs[index];
                if (activeTab) {
                    activeTab.classList.add('active');
                    moveTabBar(activeTab);
                    
                    // 移动端：保持 Tab 在可视区域中间
                    var wrapper = container.querySelector('.tc-tabs');
                    var scrollLeft = activeTab.offsetLeft - (wrapper.clientWidth / 2) + (activeTab.clientWidth / 2);
                    wrapper.scrollTo({ left: scrollLeft, behavior: 'smooth' });
                }
            }

            // 移动下划线
            function moveTabBar(tab) {
                if (!tabBar || !tab) return;
                tabBar.style.width = tab.clientWidth + 'px';
                tabBar.style.transform = 'translateX(' + tab.offsetLeft + 'px)';
            }

            // 初始化下划线位置
            setTimeout(function() {
                if (!container.isConnected) return;
                updateActiveTab(0);
            }, 100);

            bindTabEvents();

            if (!initSwiper()) {
                if (window.console && typeof console.warn === 'function') {
                    console.warn('Swiper 未加载，Tab切换轮播进入重试模式');
                }
                var retryCount = 0;
                var retryTimer = setInterval(function() {
                    if (!container.isConnected) {
                        clearInterval(retryTimer);
                        return;
                    }
                    retryCount++;
                    if (initSwiper() || retryCount >= 100) {
                        clearInterval(retryTimer);
                    }
                }, 100);
            }

            // 窗口调整时重新计算
            var handleResize = function() {
                if (!container.isConnected) {
                    window.removeEventListener('resize', handleResize);
                    return;
                }
                var active = container.querySelector('.tc-tab-item.active');
                if (active) moveTabBar(active);
            };
            window.addEventListener('resize', handleResize);
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
