<?php
/**
 * Gallery Module - 画廊/相册（增强版）
 * 
 * 支持分类筛选、增强Lightbox导航、键盘操作
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
 * 画廊相册模块类
 * 
 * CSS前缀: gallery-（保持原有）+ ql-gallery-（新增样式）
 */
class Gallery_Module extends Module_Base {

    /**
     * 构造函数 - 设置模块基本信息
     */
    public function __construct() {
        $this->category    = 'general';
        $this->icon        = 'dashicons-format-gallery';
        $this->description = __( '图片画廊/相册展示', 'developer-starter' );
    }

    /**
     * 获取模块唯一标识
     *
     * @return string 模块ID
     */
    public function get_id() {
        return 'gallery';
    }

    /**
     * 获取模块显示名称
     *
     * @return string 模块名称
     */
    public function get_name() {
        return __( '画廊相册', 'developer-starter' );
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
                'id'      => 'gallery_title',
                'type'    => 'text',
                'label'   => __( '标题', 'developer-starter' ),
                'default' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '图片展示', 'Gallery' ) : __( '图片展示', 'developer-starter' ),
            ),
            array(
                'id'      => 'gallery_title_size',
                'type'    => 'text',
                'label'   => __( '标题字体大小', 'developer-starter' ),
                'default' => '',
                'desc'    => __( '如 2rem 或 36px，留空使用默认', 'developer-starter' ),
            ),
            array(
                'id'    => 'gallery_title_color',
                'type'  => 'color',
                'label' => __( '标题颜色', 'developer-starter' ),
            ),
            array(
                'id'      => 'gallery_subtitle',
                'type'    => 'text',
                'label'   => __( '副标题', 'developer-starter' ),
                'default' => '',
            ),
            array(
                'id'      => 'gallery_subtitle_size',
                'type'    => 'text',
                'label'   => __( '副标题字体大小', 'developer-starter' ),
                'default' => '',
            ),
            array(
                'id'    => 'gallery_subtitle_color',
                'type'  => 'color',
                'label' => __( '副标题颜色', 'developer-starter' ),
            ),

            // ========================================
            // 布局设置
            // ========================================
            array(
                'id'      => 'gallery_columns',
                'type'    => 'select',
                'label'   => __( '每行列数', 'developer-starter' ),
                'options' => array(
                    '2' => __( '2列', 'developer-starter' ),
                    '3' => __( '3列', 'developer-starter' ),
                    '4' => __( '4列', 'developer-starter' ),
                    '5' => __( '5列', 'developer-starter' ),
                    '6' => __( '6列', 'developer-starter' ), // 新增
                ),
                'default' => '4',
            ),
            array(
                'id'      => 'gallery_style',
                'type'    => 'select',
                'label'   => __( '展示样式', 'developer-starter' ),
                'options' => array(
                    'grid'      => __( '网格布局', 'developer-starter' ),
                    'masonry'   => __( '瀑布流', 'developer-starter' ),
                    'fullwidth' => __( '全宽展示', 'developer-starter' ), // 新增
                ),
                'default' => 'grid',
            ),
            array(
                'id'      => 'gallery_gap',
                'type'    => 'number',
                'label'   => __( '图片间距(px)', 'developer-starter' ),
                'default' => '15',
            ),

            // ========================================
            // 分类筛选（新增）
            // ========================================
            array(
                'id'      => 'enable_filter',
                'type'    => 'select',
                'label'   => __( '启用分类筛选', 'developer-starter' ),
                'options' => array(
                    'yes' => __( '启用', 'developer-starter' ),
                    'no'  => __( '禁用', 'developer-starter' ),
                ),
                'default' => 'no',
            ),
            array(
                'id'    => 'filter_categories',
                'type'  => 'text',
                'label' => __( '分类列表', 'developer-starter' ),
                'desc'  => __( '用逗号分隔，如：酒店外观,客房环境,餐厅美食,休闲设施', 'developer-starter' ),
            ),

            // ========================================
            // Lightbox 设置
            // ========================================
            array(
                'id'      => 'gallery_lightbox',
                'type'    => 'checkbox',
                'label'   => __( '点击放大', 'developer-starter' ),
                'default' => '1',
            ),
            array(
                'id'      => 'show_counter',
                'type'    => 'select',
                'label'   => __( '显示图片数量', 'developer-starter' ),
                'options' => array(
                    'yes' => __( '显示', 'developer-starter' ),
                    'no'  => __( '隐藏', 'developer-starter' ),
                ),
                'default' => 'yes',
                'desc'    => __( 'Lightbox中显示"第X张/共Y张"', 'developer-starter' ),
            ),

            // ========================================
            // 图片列表 (Repeater)
            // ========================================
            array(
                'id'     => 'gallery_images',
                'type'   => 'repeater',
                'label'  => __( '图片列表', 'developer-starter' ),
                'desc'   => __( '添加需要展示的图片', 'developer-starter' ),
                'fields' => array(
                    array(
                        'id'    => 'image',
                        'type'  => 'text',
                        'label' => __( '图片URL', 'developer-starter' ),
                    ),
                    array(
                        'id'    => 'title',
                        'type'  => 'text',
                        'label' => __( '标题', 'developer-starter' ),
                    ),
                    array(
                        'id'    => 'desc',
                        'type'  => 'text',
                        'label' => __( '描述', 'developer-starter' ),
                    ),
                    array(
                        'id'    => 'category',
                        'type'  => 'text',
                        'label' => __( '所属分类', 'developer-starter' ),
                        'desc'  => __( '需与上方分类列表中的名称一致', 'developer-starter' ),
                    ),
                ),
            ),

            // ========================================
            // 背景设置
            // ========================================
            array(
                'id'    => 'module_bg_color',
                'type'  => 'color',
                'label' => __( '背景颜色', 'developer-starter' ),
                'desc'  => __( '支持CSS颜色值或渐变代码', 'developer-starter' ),
            ),
            array(
                'id'      => 'module_padding_top',
                'type'    => 'text',
                'label'   => __( '上边距', 'developer-starter' ),
                'default' => '60px',
            ),
            array(
                'id'      => 'module_padding_bottom',
                'type'    => 'text',
                'label'   => __( '下边距', 'developer-starter' ),
                'default' => '60px',
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
        // ========================================
        // 获取配置数据
        // ========================================
        $title       = isset( $data['gallery_title'] ) && $data['gallery_title'] !== '' 
                       ? $data['gallery_title'] 
                       : ( function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '图片展示', 'Gallery' ) : __( '图片展示', 'developer-starter' ) );
        $subtitle    = isset( $data['gallery_subtitle'] ) ? $data['gallery_subtitle'] : '';
        $title_color = isset( $data['gallery_title_color'] ) ? $data['gallery_title_color'] : '';
        $title_size  = isset( $data['gallery_title_size'] ) ? $data['gallery_title_size'] : '';
        $subtitle_color = isset( $data['gallery_subtitle_color'] ) ? $data['gallery_subtitle_color'] : '';
        $subtitle_size  = isset( $data['gallery_subtitle_size'] ) ? $data['gallery_subtitle_size'] : '';
        
        $columns  = isset( $data['gallery_columns'] ) ? intval( $data['gallery_columns'] ) : 4;
        $style    = isset( $data['gallery_style'] ) ? $data['gallery_style'] : 'grid';
        $gap      = isset( $data['gallery_gap'] ) && $data['gallery_gap'] !== '' ? intval( $data['gallery_gap'] ) : 15;
        $lightbox = isset( $data['gallery_lightbox'] ) ? $data['gallery_lightbox'] : '1';
        $images   = isset( $data['gallery_images'] ) ? $data['gallery_images'] : array();
        
        // 分类筛选
        $enable_filter = isset( $data['enable_filter'] ) ? $data['enable_filter'] : 'no';
        $filter_cats   = isset( $data['filter_categories'] ) ? $data['filter_categories'] : '';
        $categories    = array();
        if ( $enable_filter === 'yes' && $filter_cats ) {
            $categories = array_filter( array_map( 'trim', explode( ',', $filter_cats ) ) );
        }
        
        // 图片数量显示
        $show_counter = isset( $data['show_counter'] ) ? $data['show_counter'] : 'yes';
        
        // 背景设置
        $bg_color = isset( $data['module_bg_color'] ) ? $data['module_bg_color'] : '';
        $pt = isset( $data['module_padding_top'] ) && $data['module_padding_top'] !== '' 
              ? $data['module_padding_top'] 
              : '60px';
        $pb = isset( $data['module_padding_bottom'] ) && $data['module_padding_bottom'] !== '' 
              ? $data['module_padding_bottom'] 
              : '60px';
        
        // 动画
        $enable_anim = isset( $data['enable_staggered_animation'] ) ? $data['enable_staggered_animation'] : 'yes';

        // ========================================
        // 默认示例数据
        // ========================================
        if ( empty( $images ) ) {
            $images = array(
                array(
                    'image'    => '',
                    'title'    => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '产品展示', 'Product Showcase' ) : __( '产品展示', 'developer-starter' ),
                    'desc'     => '',
                    'category' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '产品', 'Products' ) : __( '产品', 'developer-starter' ),
                ),
                array(
                    'image'    => '',
                    'title'    => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '办公环境', 'Workspace' ) : __( '办公环境', 'developer-starter' ),
                    'desc'     => '',
                    'category' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '环境', 'Spaces' ) : __( '环境', 'developer-starter' ),
                ),
                array(
                    'image'    => '',
                    'title'    => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '团队活动', 'Team Moments' ) : __( '团队活动', 'developer-starter' ),
                    'desc'     => '',
                    'category' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '团队', 'Team' ) : __( '团队', 'developer-starter' ),
                ),
                array(
                    'image'    => '',
                    'title'    => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '荣誉资质', 'Recognition' ) : __( '荣誉资质', 'developer-starter' ),
                    'desc'     => '',
                    'category' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '荣誉', 'Awards' ) : __( '荣誉', 'developer-starter' ),
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

        $title_style = '';
        if ( $title_size ) $title_style .= "font-size: {$title_size};";
        if ( $title_color ) $title_style .= "color: {$title_color};";

        $subtitle_style = '';
        if ( $subtitle_size ) $subtitle_style .= "font-size: {$subtitle_size};";
        if ( $subtitle_color ) $subtitle_style .= "color: {$subtitle_color};";

        $gallery_id = 'ql-gallery-' . uniqid();
        $grid_style = "grid-template-columns: repeat({$columns}, 1fr); gap: {$gap}px;";

        // 全宽样式调整
        if ( $style === 'fullwidth' ) {
            $grid_style = "gap: {$gap}px;";
        }

        // 占位图颜色
        $placeholder_colors = array(
            'linear-gradient(135deg, var(--color-primary) 0%, var(--qiling-color-764ba2) 100%)',
            'linear-gradient(135deg, var(--color-accent) 0%, var(--color-error) 100%)',
            'linear-gradient(135deg, var(--color-primary-light) 0%, var(--color-info) 100%)',
            'linear-gradient(135deg, var(--color-success) 0%, var(--color-info) 100%)',
            'linear-gradient(135deg, var(--color-error) 0%, var(--color-warning) 100%)',
            'linear-gradient(135deg, var(--color-violet-600) 0%, var(--color-accent) 100%)',
        );
        ?>
        
        <section class="module module-gallery" style="<?php echo esc_attr( $section_style ); ?>">
            <div class="container module-gallery-container">
                <!-- 标题区域 -->
                <?php if ( $title ) : ?>
                    <div class="section-header text-center">
                        <h2 class="section-title"<?php echo $title_style ? ' style="' . esc_attr( $title_style ) . '"' : ''; ?>>
                            <?php echo esc_html( $title ); ?>
                        </h2>
                        <?php if ( $subtitle ) : ?>
                            <p class="section-subtitle"<?php echo $subtitle_style ? ' style="' . esc_attr( $subtitle_style ) . '"' : ''; ?>>
                                <?php echo esc_html( $subtitle ); ?>
                            </p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <!-- 分类筛选Tab（新增） -->
                <?php if ( $enable_filter === 'yes' && ! empty( $categories ) ) : ?>
                    <div class="ql-gallery-filters">
                        <button type="button" class="ql-gallery-filter-btn active" data-filter="all">
                            <?php esc_html_e( '全部', 'developer-starter' ); ?>
                        </button>
                        <?php foreach ( $categories as $cat ) : ?>
                            <button type="button" class="ql-gallery-filter-btn" data-filter="<?php echo esc_attr( $cat ); ?>">
                                <?php echo esc_html( $cat ); ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <!-- 图片网格 -->
                <?php if ( ! empty( $images ) ) : ?>
                    <div id="<?php echo esc_attr( $gallery_id ); ?>" 
                         class="gallery-grid gallery-<?php echo esc_attr( $style ); ?> ql-gallery-cols-<?php echo esc_attr( $columns ); ?>" 
                         style="<?php echo esc_attr( $grid_style ); ?>"
                         data-total="<?php echo count( $images ); ?>">
                        
                        <?php foreach ( $images as $index => $item ) :
                            $image     = isset( $item['image'] ) ? $item['image'] : '';
                            $img_title = isset( $item['title'] ) ? $item['title'] : '';
                            $desc      = isset( $item['desc'] ) ? $item['desc'] : '';
                            $category  = isset( $item['category'] ) ? $item['category'] : '';

                            $placeholder_bg = $placeholder_colors[ $index % count( $placeholder_colors ) ];
                            $item_style = $style === 'grid' ? 'aspect-ratio: 1;' : '';
                            $item_class = 'gallery-item';
                            if ( $lightbox === '1' ) $item_class .= ' is-lightbox';

                            $anim_attr = '';
                            if ( $enable_anim === 'yes' ) {
                                $anim_attr = $this->get_staggered_animation_attr( $index );
                            }
                        ?>
                            <div class="<?php echo esc_attr( $item_class ); ?>" 
                                 style="<?php echo esc_attr( $item_style ); ?>" 
                                 data-category="<?php echo esc_attr( $category ); ?>"
                                 data-index="<?php echo esc_attr( $index ); ?>"
                                 <?php echo $lightbox === '1' ? 'data-lightbox="' . esc_attr( $image ) . '"' : ''; ?>
                                 <?php echo $anim_attr; ?>>
                                
                                <?php if ( $image ) : ?>
                                    <img src="<?php echo esc_url( $image ); ?>" 
                                         alt="<?php echo esc_attr( $img_title ); ?>" 
                                         class="gallery-image" 
                                         loading="lazy" />
                                <?php else : ?>
                                    <div class="gallery-placeholder" style="background: <?php echo esc_attr( $placeholder_bg ); ?>;">
                                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="var(--qiling-color-rgba-255-255-255-05)" stroke-width="1.5">
                                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                                            <circle cx="8.5" cy="8.5" r="1.5"/>
                                            <polyline points="21 15 16 10 5 21"/>
                                        </svg>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- 悬浮信息 -->
                                <?php if ( $img_title || $desc ) : ?>
                                    <div class="gallery-info-overlay">
                                        <?php if ( $img_title ) : ?>
                                            <h4 class="gallery-info-title"><?php echo esc_html( $img_title ); ?></h4>
                                        <?php endif; ?>
                                        <?php if ( $desc ) : ?>
                                            <p class="gallery-info-desc"><?php echo esc_html( $desc ); ?></p>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <!-- Lightbox Modal（增强版） -->
        <?php if ( $lightbox === '1' ) : ?>
        <div id="<?php echo esc_attr( $gallery_id ); ?>-lightbox" class="gallery-lightbox-modal ql-gallery-lightbox" tabindex="-1">
            <img src="" alt="" class="gallery-lightbox-image" />
            
            <!-- 左右导航按钮（新增） -->
            <button type="button" class="ql-gallery-nav ql-gallery-prev" aria-label="<?php esc_attr_e( '上一张', 'developer-starter' ); ?>">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="15 18 9 12 15 6"></polyline>
                </svg>
            </button>
            <button type="button" class="ql-gallery-nav ql-gallery-next" aria-label="<?php esc_attr_e( '下一张', 'developer-starter' ); ?>">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="9 18 15 12 9 6"></polyline>
                </svg>
            </button>
            
            <!-- 图片计数器（新增） -->
            <?php if ( $show_counter === 'yes' ) : ?>
                <div class="ql-gallery-counter">
                    <span class="ql-gallery-current">1</span> / <span class="ql-gallery-total"><?php echo count( $images ); ?></span>
                </div>
            <?php endif; ?>
            
            <button type="button" class="gallery-lightbox-close">&times;</button>
        </div>

        <script>
        (function() {
            var galleryId = '<?php echo esc_js( $gallery_id ); ?>';
            var gallery = document.getElementById(galleryId);
            var lightbox = document.getElementById(galleryId + '-lightbox');
            if (!gallery || !lightbox) return;
            var galleryRoot = gallery.closest('.module-gallery');
            
            var items = gallery.querySelectorAll('[data-lightbox]');
            var currentIndex = 0;
            var totalItems = items.length;
            var currentCounter = lightbox.querySelector('.ql-gallery-current');
            var bodyOverflow = '';
            
            // 收集所有图片数据
            var imageData = [];
            items.forEach(function(item, i) {
                imageData.push({
                    src: item.getAttribute('data-lightbox'),
                    item: item
                });
            });
            
            // 显示指定图片
            function showImage(index) {
                if (index < 0) index = totalItems - 1;
                if (index >= totalItems) index = 0;
                currentIndex = index;
                
                var src = imageData[index].src;
                if (src) {
                    lightbox.querySelector('img').src = src;
                    if (currentCounter) {
                        currentCounter.textContent = (index + 1);
                    }
                }
            }
            
            // 打开Lightbox
            items.forEach(function(item, i) {
                item.addEventListener('click', function() {
                    var src = this.getAttribute('data-lightbox');
                    if (src) {
                        currentIndex = imageData.findIndex(function(image) { return image.item === item; });
                        showImage(currentIndex);
                        bodyOverflow = document.body.style.overflow;
                        lightbox.classList.add('is-open');
                        document.body.style.overflow = 'hidden';
                        lightbox.focus({ preventScroll: true });
                    }
                });
            });
            
            // 关闭Lightbox
            var closeBtn = lightbox.querySelector('.gallery-lightbox-close');
            var closeLightbox = function() {
                if (!lightbox.classList.contains('is-open')) return;
                lightbox.classList.remove('is-open');
                document.body.style.overflow = bodyOverflow;
            };
            
            closeBtn.addEventListener('click', closeLightbox);
            lightbox.addEventListener('click', function(e) {
                if (e.target === lightbox) {
                    closeLightbox();
                }
            });
            
            // 导航按钮
            var prevBtn = lightbox.querySelector('.ql-gallery-prev');
            var nextBtn = lightbox.querySelector('.ql-gallery-next');
            
            if (prevBtn) {
                prevBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    showImage(currentIndex - 1);
                });
            }
            
            if (nextBtn) {
                nextBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    showImage(currentIndex + 1);
                });
            }
            
            // 键盘支持（新增）
            function handleKeydown(e) {
                switch(e.key) {
                    case 'ArrowLeft':
                        showImage(currentIndex - 1);
                        break;
                    case 'ArrowRight':
                        showImage(currentIndex + 1);
                        break;
                    case 'Escape':
                        closeLightbox();
                        break;
                }
            }
            lightbox.addEventListener('keydown', handleKeydown);
            
            // 分类筛选（新增）
            var filterBtns = galleryRoot ? galleryRoot.querySelectorAll('.ql-gallery-filter-btn') : [];
            if (filterBtns.length > 0) {
                filterBtns.forEach(function(btn) {
                    btn.addEventListener('click', function() {
                        var filter = this.getAttribute('data-filter');
                        
                        // 更新按钮状态
                        filterBtns.forEach(function(b) { b.classList.remove('active'); });
                        this.classList.add('active');
                        
                        // 筛选图片
                        items.forEach(function(item) {
                            var cat = item.getAttribute('data-category');
                            if (filter === 'all' || cat === filter) {
                                item.style.display = '';
                            } else {
                                item.style.display = 'none';
                            }
                        });
                        
                        // 更新图片数据用于导航
                        imageData = [];
                        items.forEach(function(item) {
                            if (item.style.display !== 'none') {
                                imageData.push({
                                    src: item.getAttribute('data-lightbox'),
                                    item: item
                                });
                            }
                        });
                        totalItems = imageData.length;
                        if (lightbox.querySelector('.ql-gallery-total')) {
                            lightbox.querySelector('.ql-gallery-total').textContent = totalItems;
                        }
                    });
                });
            }
        })();
        </script>
        <?php endif; ?>
        <?php
    }
}
