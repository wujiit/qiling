<?php
/**
 * Testimonials Module - 客户评价（增强版）
 * 
 * 支持多种布局样式、评价来源、日期、认证标识和评分统计
 *
 * @package Developer_Starter
 * @since 1.0.3
 */

namespace Developer_Starter\Modules\Modules;

use Developer_Starter\Modules\Module_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 客户评价模块类
 * 
 * CSS前缀: testimonial-（保持原有）+ ql-testimonial-（新增样式）
 */
class Testimonials_Module extends Module_Base {

    /**
     * 构造函数 - 设置模块基本信息
     */
    public function __construct() {
        $this->category    = 'homepage';
        $this->icon        = 'dashicons-format-quote';
        $this->description = __( '展示客户评价和推荐', 'developer-starter' );
    }

    /**
     * 获取模块唯一标识
     *
     * @return string 模块ID
     */
    public function get_id() {
        return 'testimonials';
    }

    /**
     * 获取模块显示名称
     *
     * @return string 模块名称
     */
    public function get_name() {
        return __( '客户评价', 'developer-starter' );
    }

    /**
     * 获取模块配置字段
     *
     * @return array 字段配置数组
     */
    public function get_fields() {
        return array(
            // ========================================
            // 标题设置
            // ========================================
            array(
                'id'      => 'testimonials_title',
                'type'    => 'text',
                'label'   => __( '标题', 'developer-starter' ),
                'default' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '客户评价', 'Client Reviews' ) : __( '客户评价', 'developer-starter' ),
            ),
            array(
                'id'      => 'testimonials_title_size',
                'type'    => 'text',
                'label'   => __( '标题字体大小', 'developer-starter' ),
                'default' => '2rem',
                'desc'    => __( '如 2rem 或 32px', 'developer-starter' ),
            ),
            array(
                'id'    => 'testimonials_title_color',
                'type'  => 'color',
                'label' => __( '标题颜色', 'developer-starter' ),
            ),
            array(
                'id'      => 'testimonials_subtitle',
                'type'    => 'text',
                'label'   => __( '副标题', 'developer-starter' ),
                'default' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '听听客户怎么说', 'See what clients are saying.' ) : __( '听听客户怎么说', 'developer-starter' ),
            ),
            array(
                'id'      => 'testimonials_subtitle_size',
                'type'    => 'text',
                'label'   => __( '副标题字体大小', 'developer-starter' ),
                'default' => '1rem',
            ),
            array(
                'id'    => 'testimonials_subtitle_color',
                'type'  => 'color',
                'label' => __( '副标题颜色', 'developer-starter' ),
            ),

            // ========================================
            // 布局设置（新增）
            // ========================================
            array(
                'id'      => 'testimonials_layout',
                'type'    => 'select',
                'label'   => __( '布局样式', 'developer-starter' ),
                'options' => array(
                    'grid'     => __( '网格布局', 'developer-starter' ),
                    'carousel' => __( '轮播滑动', 'developer-starter' ),
                    'list'     => __( '列表布局', 'developer-starter' ),
                    'large'    => __( '大卡片', 'developer-starter' ),
                ),
                'default' => 'grid',
            ),
            array(
                'id'      => 'testimonials_columns',
                'type'    => 'select',
                'label'   => __( '列数（网格布局）', 'developer-starter' ),
                'options' => array(
                    '2' => __( '2列', 'developer-starter' ),
                    '3' => __( '3列', 'developer-starter' ),
                    '4' => __( '4列', 'developer-starter' ),
                ),
                'default' => '3',
            ),
            array(
                'id'      => 'testimonials_badge_bg',
                'type'    => 'color',
                'label'   => __( '标签/徽章背景颜色', 'developer-starter' ),
                'default' => '',
                'desc'    => __( '控制评价来源与认证徽章，留空时保留来源默认色并跟随全局徽章颜色。', 'developer-starter' ),
            ),

            // ========================================
            // 评分统计（新增）
            // ========================================
            array(
                'id'      => 'show_rating_summary',
                'type'    => 'select',
                'label'   => __( '显示评分统计', 'developer-starter' ),
                'options' => array(
                    'yes' => __( '显示', 'developer-starter' ),
                    'no'  => __( '隐藏', 'developer-starter' ),
                ),
                'default' => 'no',
            ),
            array(
                'id'      => 'total_reviews',
                'type'    => 'text',
                'label'   => __( '总评价数', 'developer-starter' ),
                'desc'    => __( '如：1280+', 'developer-starter' ),
            ),
            array(
                'id'      => 'average_rating',
                'type'    => 'text',
                'label'   => __( '平均评分', 'developer-starter' ),
                'desc'    => __( '如：4.9', 'developer-starter' ),
            ),

            // ========================================
            // 评价列表 (Repeater)
            // ========================================
            array(
                'id'     => 'testimonials_items',
                'type'   => 'repeater',
                'label'  => __( '评价列表', 'developer-starter' ),
                'fields' => array(
                    array(
                        'id'    => 'avatar',
                        'type'  => 'image',
                        'label' => __( '头像', 'developer-starter' ),
                    ),
                    array(
                        'id'    => 'name',
                        'type'  => 'text',
                        'label' => __( '姓名', 'developer-starter' ),
                    ),
                    array(
                        'id'    => 'position',
                        'type'  => 'text',
                        'label' => __( '职位/身份', 'developer-starter' ),
                    ),
                    array(
                        'id'    => 'content',
                        'type'  => 'textarea',
                        'label' => __( '评价内容', 'developer-starter' ),
                    ),
                    array(
                        'id'      => 'rating',
                        'type'    => 'number',
                        'label'   => __( '评分 (1-5)', 'developer-starter' ),
                        'default' => '5',
                    ),
                    // 新增字段
                    array(
                        'id'      => 'source',
                        'type'    => 'select',
                        'label'   => __( '评价来源', 'developer-starter' ),
                        'options' => array(
                            ''           => __( '无', 'developer-starter' ),
                            'dianping'   => __( '大众点评', 'developer-starter' ),
                            'ctrip'      => __( '携程', 'developer-starter' ),
                            'meituan'    => __( '美团', 'developer-starter' ),
                            'fliggy'     => __( '飞猪', 'developer-starter' ),
                            'booking'    => __( 'Booking', 'developer-starter' ),
                            'tripadvisor'=> __( 'TripAdvisor', 'developer-starter' ),
                            'google'     => __( 'Google', 'developer-starter' ),
                            'weibo'      => __( '微博', 'developer-starter' ),
                            'xiaohongshu'=> __( '小红书', 'developer-starter' ),
                        ),
                    ),
                    array(
                        'id'    => 'date',
                        'type'  => 'text',
                        'label' => __( '评价日期', 'developer-starter' ),
                        'desc'  => __( '如：2024-01-15', 'developer-starter' ),
                    ),
                    array(
                        'id'      => 'verified',
                        'type'    => 'select',
                        'label'   => __( '认证标识', 'developer-starter' ),
                        'options' => array(
                            ''         => __( '无', 'developer-starter' ),
                            'verified' => __( '已验证用户', 'developer-starter' ),
                            'vip'      => __( 'VIP会员', 'developer-starter' ),
                            'guest'    => __( '已入住', 'developer-starter' ),
                        ),
                    ),
                    array(
                        'id'      => 'card_bg',
                        'type'    => 'color',
                        'label'   => __( '卡片背景色', 'developer-starter' ),
                        'default' => 'var(--color-neutral-0)',
                    ),
                    array(
                        'id'    => 'name_color',
                        'type'  => 'color',
                        'label' => __( '姓名颜色', 'developer-starter' ),
                    ),
                    array(
                        'id'    => 'content_color',
                        'type'  => 'color',
                        'label' => __( '内容颜色', 'developer-starter' ),
                    ),
                ),
            ),

            // ========================================
            // 背景设置
            // ========================================
            array(
                'id'    => 'testimonials_bg_color',
                'type'  => 'color',
                'label' => __( '背景颜色 (支持渐变)', 'developer-starter' ),
            ),
            array(
                'id'      => 'module_padding_top',
                'type'    => 'text',
                'label'   => __( '上边距', 'developer-starter' ),
                'default' => '80px',
            ),
            array(
                'id'      => 'module_padding_bottom',
                'type'    => 'text',
                'label'   => __( '下边距', 'developer-starter' ),
                'default' => '80px',
            ),

            // ========================================
            // 动画设置
            // ========================================
            array(
                'id'      => 'enable_staggered_animation',
                'type'    => 'select',
                'label'   => __( '开启逐个显示动画', 'developer-starter' ),
                'options' => array(
                    'yes' => __( '开启', 'developer-starter' ),
                    'no'  => __( '关闭', 'developer-starter' ),
                ),
                'default' => 'yes',
            ),
        );
    }

    /**
     * 渲染模块前端HTML
     *
     * @param array $data 模块配置数据
     */
    public function render( $data = array() ) {
        $clean_css_value = static function( $value ) {
            $value = trim( wp_strip_all_tags( (string) $value ) );
            return str_replace( array( ';', '{', '}' ), '', $value );
        };

        // ========================================
        // 获取配置数据
        // ========================================
        $title       = isset( $data['testimonials_title'] ) && $data['testimonials_title'] !== '' 
                       ? $data['testimonials_title'] 
                       : ( function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '客户评价', 'Client Reviews' ) : __( '客户评价', 'developer-starter' ) );
        $title_size  = isset( $data['testimonials_title_size'] ) && $data['testimonials_title_size'] !== '' 
                       ? $data['testimonials_title_size'] 
                       : '2rem';
        $title_color = isset( $data['testimonials_title_color'] ) ? $data['testimonials_title_color'] : '';
        
        $subtitle       = isset( $data['testimonials_subtitle'] ) ? $data['testimonials_subtitle'] : '';
        $subtitle_size  = isset( $data['testimonials_subtitle_size'] ) && $data['testimonials_subtitle_size'] !== '' 
                          ? $data['testimonials_subtitle_size'] 
                          : '1rem';
        $subtitle_color = isset( $data['testimonials_subtitle_color'] ) ? $data['testimonials_subtitle_color'] : '';
        
        // 布局
        $layout  = isset( $data['testimonials_layout'] ) ? $data['testimonials_layout'] : 'grid';
        $columns = isset( $data['testimonials_columns'] ) ? intval( $data['testimonials_columns'] ) : 3;
        $badge_bg = isset( $data['testimonials_badge_bg'] ) ? $clean_css_value( $data['testimonials_badge_bg'] ) : '';
        
        // 评分统计
        $show_summary   = isset( $data['show_rating_summary'] ) ? $data['show_rating_summary'] : 'no';
        $total_reviews  = isset( $data['total_reviews'] ) ? $data['total_reviews'] : '';
        $average_rating = isset( $data['average_rating'] ) ? $data['average_rating'] : '';
        
        $items = isset( $data['testimonials_items'] ) ? $data['testimonials_items'] : array();
        
        // 背景设置
        $bg_color = isset( $data['testimonials_bg_color'] ) ? $data['testimonials_bg_color'] : '';
        $pt       = isset( $data['module_padding_top'] ) && $data['module_padding_top'] !== '' 
                    ? $data['module_padding_top'] 
                    : '80px';
        $pb       = isset( $data['module_padding_bottom'] ) && $data['module_padding_bottom'] !== '' 
                    ? $data['module_padding_bottom'] 
                    : '80px';
        
        // 动画
        $enable_anim = isset( $data['enable_staggered_animation'] ) ? $data['enable_staggered_animation'] : 'yes';

        // ========================================
        // 默认示例数据
        // ========================================
        if ( empty( $items ) ) {
            $items = array(
                array(
                    'avatar'        => '',
                    'name'          => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '张先生', 'Michael Z.' ) : __( '张先生', 'developer-starter' ),
                    'position'      => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( 'CEO · 某科技公司', 'CEO · Technology Company' ) : __( 'CEO · 某科技公司', 'developer-starter' ),
                    'content'       => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '非常专业的团队，项目交付准时，质量超出预期。推荐给所有需要高品质服务的企业！', 'A very professional team. Delivery was on time and the quality exceeded expectations.' ) : __( '非常专业的团队，项目交付准时，质量超出预期。推荐给所有需要高品质服务的企业！', 'developer-starter' ),
                    'rating'        => '5',
                    'source'        => 'google',
                    'date'          => '2024-01-15',
                    'verified'      => 'verified',
                    'card_bg'       => 'var(--color-neutral-0)',
                    'name_color'    => '',
                    'content_color' => '',
                ),
                array(
                    'avatar'        => '',
                    'name'          => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '李女士', 'Sophia L.' ) : __( '李女士', 'developer-starter' ),
                    'position'      => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '市场总监', 'Marketing Director' ) : __( '市场总监', 'developer-starter' ),
                    'content'       => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '合作非常愉快，沟通顺畅，设计方案很有创意，完美达成了我们的需求目标。', 'The collaboration was smooth, communication was clear, and the final direction matched our goals perfectly.' ) : __( '合作非常愉快，沟通顺畅，设计方案很有创意，完美达成了我们的需求目标。', 'developer-starter' ),
                    'rating'        => '5',
                    'source'        => 'dianping',
                    'date'          => '2024-01-10',
                    'verified'      => 'guest',
                    'card_bg'       => 'var(--color-neutral-0)',
                    'name_color'    => '',
                    'content_color' => '',
                ),
                array(
                    'avatar'        => '',
                    'name'          => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '王总', 'David W.' ) : __( '王总', 'developer-starter' ),
                    'position'      => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '创始人', 'Founder' ) : __( '创始人', 'developer-starter' ),
                    'content'       => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '从需求分析到最终交付，每个环节都很用心。技术实力强，值得长期合作！', 'From discovery to delivery, every step felt thoughtful and well executed. A strong long-term partner.' ) : __( '从需求分析到最终交付，每个环节都很用心。技术实力强，值得长期合作！', 'developer-starter' ),
                    'rating'        => '5',
                    'source'        => 'ctrip',
                    'date'          => '2024-01-05',
                    'verified'      => 'vip',
                    'card_bg'       => 'var(--color-neutral-0)',
                    'name_color'    => '',
                    'content_color' => '',
                ),
            );
        }

        // ========================================
        // 构建样式
        // ========================================
        $section_style = "padding-top: {$pt}; padding-bottom: {$pb};";
        if ( $bg_color ) {
            $section_style .= strpos( $bg_color, 'gradient' ) !== false 
                              ? "background: {$bg_color};" 
                              : "background-color: {$bg_color};";
        }
        if ( $badge_bg ) {
            $section_style .= "--qiling-component-badge-bg: {$badge_bg};";
            $section_style .= "--ql-testimonial-source-bg: {$badge_bg};--ql-testimonial-source-text: var(--qiling-component-badge-text);";
        }

        $title_style = "font-size: {$title_size};";
        if ( $title_color ) {
            $title_style .= "color: {$title_color};";
        }

        $subtitle_style = "font-size: {$subtitle_size};";
        if ( $subtitle_color ) {
            $subtitle_style .= "color: {$subtitle_color};";
        }

        // 来源平台标签
        $source_labels = array(
            'dianping'    => __( '大众点评', 'developer-starter' ),
            'ctrip'       => __( '携程', 'developer-starter' ),
            'meituan'     => __( '美团', 'developer-starter' ),
            'fliggy'      => __( '飞猪', 'developer-starter' ),
            'booking'     => 'Booking',
            'tripadvisor' => 'TripAdvisor',
            'google'      => 'Google',
            'weibo'       => __( '微博', 'developer-starter' ),
            'xiaohongshu' => __( '小红书', 'developer-starter' ),
        );

        // 认证标识标签
        $verified_labels = array(
            'verified' => __( '已验证', 'developer-starter' ),
            'vip'      => __( 'VIP会员', 'developer-starter' ),
            'guest'    => __( '已入住', 'developer-starter' ),
        );

        // 网格类名
        $grid_class = 'testimonials-grid';
        if ( $layout === 'grid' ) {
            $grid_class .= ' grid-cols-' . $columns;
        }

        // 唯一ID用于轮播
        $carousel_id = 'ql-testimonial-carousel-' . uniqid();
        ?>
        
        <section class="module module-testimonials ql-testimonial-layout-<?php echo esc_attr( $layout ); ?>" style="<?php echo esc_attr( $section_style ); ?>">
            <div class="container">
                <!-- 标题区域 -->
                <?php if ( $title || $subtitle ) : ?>
                    <div class="section-header text-center">
                        <?php if ( $title ) : ?>
                            <h2 class="section-title" style="<?php echo esc_attr( $title_style ); ?>">
                                <?php echo esc_html( $title ); ?>
                            </h2>
                        <?php endif; ?>
                        <?php if ( $subtitle ) : ?>
                            <p class="section-subtitle" style="<?php echo esc_attr( $subtitle_style ); ?>">
                                <?php echo esc_html( $subtitle ); ?>
                            </p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <!-- 评分统计（新增） -->
                <?php if ( $show_summary === 'yes' && ( $average_rating || $total_reviews ) ) : ?>
                    <div class="ql-testimonial-summary">
                        <?php if ( $average_rating ) : ?>
                            <div class="ql-testimonial-summary-score">
                                <span class="ql-testimonial-score-num"><?php echo esc_html( $average_rating ); ?></span>
                                <div class="ql-testimonial-score-stars">
                                    <?php 
                                    $avg = floatval( $average_rating );
                                    for ( $i = 1; $i <= 5; $i++ ) :
                                        if ( $i <= floor( $avg ) ) :
                                            echo '<span class="star filled">★</span>';
                                        elseif ( $i - 0.5 <= $avg ) :
                                            echo '<span class="star half">★</span>';
                                        else :
                                            echo '<span class="star">★</span>';
                                        endif;
                                    endfor;
                                    ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        <?php if ( $total_reviews ) : ?>
                            <div class="ql-testimonial-summary-count">
                                <?php 
                                /* translators: %s: number of reviews */
                                printf( esc_html__( '基于 %s 条评价', 'developer-starter' ), '<strong>' . esc_html( $total_reviews ) . '</strong>' ); 
                                ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <!-- 评价列表 -->
                <?php if ( ! empty( $items ) ) : ?>
                    <?php if ( $layout === 'carousel' ) : ?>
                        <!-- 轮播布局 -->
                        <div class="ql-testimonial-carousel" id="<?php echo esc_attr( $carousel_id ); ?>">
                            <div class="ql-testimonial-carousel-track">
                                <?php foreach ( $items as $index => $item ) : 
                                    $this->ql_testimonial_render_card( $item, $index, $enable_anim, $source_labels, $verified_labels );
                                endforeach; ?>
                            </div>
                            <button type="button" class="ql-testimonial-carousel-btn ql-testimonial-prev" aria-label="<?php esc_attr_e( '上一个', 'developer-starter' ); ?>">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="15 18 9 12 15 6"></polyline>
                                </svg>
                            </button>
                            <button type="button" class="ql-testimonial-carousel-btn ql-testimonial-next" aria-label="<?php esc_attr_e( '下一个', 'developer-starter' ); ?>">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="9 18 15 12 9 6"></polyline>
                                </svg>
                            </button>
                            <div class="ql-testimonial-carousel-dots"></div>
                        </div>
                        
                        <script>
                        (function() {
                            var container = document.getElementById('<?php echo esc_js( $carousel_id ); ?>');
                            if (!container) return;
                            
                            var track = container.querySelector('.ql-testimonial-carousel-track');
                            var cards = track.querySelectorAll('.testimonial-card');
                            var prevBtn = container.querySelector('.ql-testimonial-prev');
                            var nextBtn = container.querySelector('.ql-testimonial-next');
                            var dotsContainer = container.querySelector('.ql-testimonial-carousel-dots');
                            var currentIndex = 0;
                            var totalCards = cards.length;
                            
                            // 响应式每屏显示数量
                            function getVisibleCount() {
                                if (window.innerWidth < 768) return 1;
                                if (window.innerWidth < 1024) return 2;
                                return 3;
                            }
                            
                            // 创建指示点
                            function createDots() {
                                dotsContainer.innerHTML = '';
                                var visibleCount = getVisibleCount();
                                var dotsCount = Math.ceil(totalCards / visibleCount);
                                for (var i = 0; i < dotsCount; i++) {
                                    var dot = document.createElement('span');
                                    dot.className = 'ql-testimonial-dot' + (i === 0 ? ' active' : '');
                                    dot.setAttribute('data-index', i);
                                    dotsContainer.appendChild(dot);
                                }
                            }
                            
                            // 更新轮播位置
                            function updateCarousel() {
                                var visibleCount = getVisibleCount();
                                var cardWidth = 100 / visibleCount;
                                var offset = currentIndex * cardWidth;
                                track.style.transform = 'translateX(-' + offset + '%)';
                                
                                // 更新指示点
                                var dots = dotsContainer.querySelectorAll('.ql-testimonial-dot');
                                var activeDotIndex = Math.floor(currentIndex / visibleCount);
                                dots.forEach(function(dot, i) {
                                    dot.classList.toggle('active', i === activeDotIndex);
                                });
                            }
                            
                            // 事件绑定
                            prevBtn.addEventListener('click', function() {
                                var visibleCount = getVisibleCount();
                                currentIndex = Math.max(0, currentIndex - visibleCount);
                                updateCarousel();
                            });
                            
                            nextBtn.addEventListener('click', function() {
                                var visibleCount = getVisibleCount();
                                var maxIndex = totalCards - visibleCount;
                                currentIndex = Math.min(maxIndex, currentIndex + visibleCount);
                                updateCarousel();
                            });
                            
                            dotsContainer.addEventListener('click', function(e) {
                                if (e.target.classList.contains('ql-testimonial-dot')) {
                                    var visibleCount = getVisibleCount();
                                    currentIndex = parseInt(e.target.getAttribute('data-index')) * visibleCount;
                                    updateCarousel();
                                }
                            });
                            
                            // 初始化
                            createDots();
                            var handleResize = function() {
                                if (!container.isConnected) {
                                    window.removeEventListener('resize', handleResize);
                                    return;
                                }
                                createDots();
                                currentIndex = 0;
                                updateCarousel();
                            };
                            window.addEventListener('resize', handleResize);
                        })();
                        </script>
                    <?php else : ?>
                        <!-- 网格/列表/大卡片布局 -->
                        <div class="<?php echo esc_attr( $grid_class ); ?> ql-testimonial-<?php echo esc_attr( $layout ); ?>">
                            <?php foreach ( $items as $index => $item ) : 
                                $this->ql_testimonial_render_card( $item, $index, $enable_anim, $source_labels, $verified_labels );
                            endforeach; ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </section>
        <?php
    }

    /**
     * 渲染单个评价卡片
     *
     * @param array  $item           评价数据
     * @param int    $index          索引
     * @param string $enable_anim    是否启用动画
     * @param array  $source_labels  来源标签
     * @param array  $verified_labels 认证标签
     */
    private function ql_testimonial_render_card( $item, $index, $enable_anim, $source_labels, $verified_labels ) {
        $avatar        = isset( $item['avatar'] ) ? $item['avatar'] : '';
        $name          = isset( $item['name'] ) ? $item['name'] : '';
        $name_color    = isset( $item['name_color'] ) && ! empty( $item['name_color'] ) ? $item['name_color'] : '';
        $position      = isset( $item['position'] ) ? $item['position'] : '';
        $content       = isset( $item['content'] ) ? $item['content'] : '';
        $content_color = isset( $item['content_color'] ) && ! empty( $item['content_color'] ) ? $item['content_color'] : '';
        $rating        = isset( $item['rating'] ) ? intval( $item['rating'] ) : 5;
        $card_bg       = isset( $item['card_bg'] ) && ! empty( $item['card_bg'] ) ? $item['card_bg'] : 'var(--color-neutral-0)';
        
        // 新增字段
        $source   = isset( $item['source'] ) ? $item['source'] : '';
        $date     = isset( $item['date'] ) ? $item['date'] : '';
        $verified = isset( $item['verified'] ) ? $item['verified'] : '';

        $card_style = strpos( $card_bg, 'gradient' ) !== false 
                      ? "background: {$card_bg};" 
                      : "background-color: {$card_bg};";
        $name_style    = $name_color ? "color: {$name_color};" : '';
        $content_style = $content_color ? "color: {$content_color};" : '';

        // 动画属性
        $anim_attr = '';
        if ( $enable_anim === 'yes' ) {
            $anim_attr = $this->get_staggered_animation_attr( $index );
        }
        ?>
        <div class="testimonial-card" style="<?php echo esc_attr( $card_style ); ?>" <?php echo $anim_attr; ?>>
            <div class="testimonial-quote-icon">"</div>
            
            <div class="testimonial-content" style="<?php echo esc_attr( $content_style ); ?>">
                <?php echo esc_html( $content ); ?>
            </div>
            
            <?php if ( $rating > 0 ) : ?>
                <div class="testimonial-rating">
                    <?php for ( $i = 1; $i <= 5; $i++ ) : ?>
                        <span class="star <?php echo $i <= $rating ? 'filled' : ''; ?>">★</span>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>

            <!-- 来源和日期（新增） -->
            <?php if ( $source || $date ) : ?>
                <div class="ql-testimonial-meta">
                    <?php if ( $source && isset( $source_labels[ $source ] ) ) : ?>
                        <span class="ql-testimonial-source ql-source-<?php echo esc_attr( $source ); ?>">
                            <?php echo esc_html( $source_labels[ $source ] ); ?>
                        </span>
                    <?php endif; ?>
                    <?php if ( $date ) : ?>
                        <span class="ql-testimonial-date"><?php echo esc_html( $date ); ?></span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <div class="testimonial-author">
                <div class="testimonial-avatar <?php echo ! $avatar ? 'default-avatar-' . ( $index % 5 ) : ''; ?>">
                    <?php if ( $avatar ) : ?>
                        <img src="<?php echo esc_url( $avatar ); ?>" alt="<?php echo esc_attr( $name ); ?>" />
                    <?php else : ?>
                        <span><?php echo esc_html( mb_substr( $name, 0, 1 ) ); ?></span>
                    <?php endif; ?>
                </div>
                
                <div class="testimonial-info">
                    <h4 class="testimonial-name" style="<?php echo esc_attr( $name_style ); ?>">
                        <?php echo esc_html( $name ); ?>
                        <?php // 认证标识（新增） ?>
                        <?php if ( $verified && isset( $verified_labels[ $verified ] ) ) : ?>
                            <span class="ql-testimonial-badge ql-badge-<?php echo esc_attr( $verified ); ?>">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                </svg>
                                <?php echo esc_html( $verified_labels[ $verified ] ); ?>
                            </span>
                        <?php endif; ?>
                    </h4>
                    <?php if ( $position ) : ?>
                        <p class="testimonial-position"><?php echo esc_html( $position ); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php
    }
}
