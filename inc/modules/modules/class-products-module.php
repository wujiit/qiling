<?php
/**
 * Products Module - 产品中心模块
 *
 * @package Developer_Starter
 * @since 1.0.0
 */

namespace Developer_Starter\Modules\Modules;

use Developer_Starter\Modules\Module_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Products_Module extends Module_Base {

    public function __construct() {
        $this->category = 'content';
        $this->icon = 'dashicons-products';
        $this->description = __( '手动添加产品，支持网格/轮播模式，点击弹出文章详情 (B2B模式)', 'developer-starter' );
    }

    public function get_id() {
        return 'products';
    }

    public function get_name() {
        return __( '产品列表 (手动版)', 'developer-starter' );
    }

    public function get_fields() {
        return array(
            array( 'id' => 'products_title', 'type' => 'text', 'label' => __( '模块标题', 'developer-starter' ), 'default' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '产品中心', 'Product Catalog' ) : __( '产品中心', 'developer-starter' ) ),
            array( 'id' => 'products_subtitle', 'type' => 'text', 'label' => __( '模块副标题', 'developer-starter' ), 'default' => 'PRODUCT CENTER' ),
            
            // 手动产品列表
            array(
                'id'         => 'items',
                'type'       => 'repeater',
                'label'      => __( '产品列表', 'developer-starter' ),
                'add_button' => __( '+ 添加产品', 'developer-starter' ),
                'fields'     => array(
                    array(
                        'id'    => 'image',
                        'type'  => 'image',
                        'label' => __( '产品图片', 'developer-starter' ),
                    ),
                    array(
                        'id'    => 'title',
                        'type'  => 'text',
                        'label' => __( '产品型号/名称', 'developer-starter' ),
                    ),
                    array(
                        'id'    => 'desc',
                        'type'  => 'text',
                        'label' => __( '简短描述 (显示在卡片)', 'developer-starter' ),
                    ),
                    array(
                        'id'    => 'specs',
                        'type'  => 'textarea',
                        'label' => __( '详细参数 (显示在弹窗左侧，每行一条)', 'developer-starter' ),
                        'desc'  => __( '例如：<br>尺寸：100x100mm<br>材质：铝合金', 'developer-starter' ),
                    ),
                    array(
                        'id'    => 'post_id',
                        'type'  => 'text',
                        'label' => __( '关联文章ID (详情来源)', 'developer-starter' ),
                        'desc'  => __( '填写产品介绍文章的ID', 'developer-starter' ),
                    ),
                    array(
                        'id'    => 'btn_text',
                        'type'  => 'text',
                        'label' => __( '卡片按钮文字 (留空则不显示)', 'developer-starter' ),
                        'default' => 'View Details',
                    ),
                ),
            ),

            // 布局设置
            array(
                'id'      => 'columns',
                'type'    => 'select',
                'label'   => __( '显示模式 (超过此数量自动变轮播)', 'developer-starter' ),
                'options' => array(
                    '3' => __( '3个/行', 'developer-starter' ),
                    '4' => __( '4个/行', 'developer-starter' ),
                ),
                'default' => '4',
            ),
            
            // 弹窗设置
            array(
                'id'      => 'modal_inquire_text',
                'type'    => 'text',
                'label'   => __( '弹窗咨询按钮文案', 'developer-starter' ),
                'default' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '立即咨询', 'Inquire Now' ) : __( '立即咨询', 'developer-starter' ),
            ),
            array(
                'id'      => 'modal_inquire_url',
                'type'    => 'text',
                'label'   => __( '弹窗咨询按钮链接', 'developer-starter' ),
                'default' => '/contact',
            ),
            array(
                'id'    => 'modal_inquire_btn_bg_color',
                'type'  => 'color',
                'label' => __( '弹窗咨询按钮背景颜色', 'developer-starter' ),
                'desc'  => __( '留空时跟随全局设计里的按钮样式', 'developer-starter' ),
            ),
            array(
                'id'    => 'modal_inquire_btn_text_color',
                'type'  => 'color',
                'label' => __( '弹窗咨询按钮文字颜色', 'developer-starter' ),
                'desc'  => __( '留空时跟随全局设计里的按钮样式', 'developer-starter' ),
            ),
            $this->get_button_border_color_field( 'modal_inquire_btn_border_color', __( '弹窗咨询按钮边框颜色', 'developer-starter' ) ),
            array(
                'id'    => 'modal_inquire_btn_hover_bg_color',
                'type'  => 'color',
                'label' => __( '弹窗咨询按钮悬停背景颜色', 'developer-starter' ),
                'desc'  => __( '留空时跟随全局设计里的按钮悬停样式', 'developer-starter' ),
            ),
            array(
                'id'    => 'modal_inquire_btn_hover_text_color',
                'type'  => 'color',
                'label' => __( '弹窗咨询按钮悬停文字颜色', 'developer-starter' ),
                'desc'  => __( '留空时跟随全局设计里的按钮悬停样式', 'developer-starter' ),
            ),
            $this->get_button_border_color_field( 'modal_inquire_btn_hover_border_color', __( '弹窗咨询按钮悬停边框颜色', 'developer-starter' ), __( '留空时跟随弹窗咨询按钮悬停背景颜色。', 'developer-starter' ) ),
        );
    }

    public function render( $data = array() ) {
        $title = isset( $data['products_title'] ) ? $data['products_title'] : '';
        $subtitle = isset( $data['products_subtitle'] ) ? $data['products_subtitle'] : '';
        $items = isset( $data['items'] ) && is_array( $data['items'] ) ? $data['items'] : array();
        $cols = isset( $data['columns'] ) ? intval( $data['columns'] ) : 4;
        $clean_css_value = static function( $value ) {
            $value = trim( wp_strip_all_tags( (string) $value ) );
            return str_replace( array( ';', '{', '}' ), '', $value );
        };
        
        $inquire_text = isset( $data['modal_inquire_text'] ) ? $data['modal_inquire_text'] : ( function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '立即咨询', 'Inquire Now' ) : __( '立即咨询', 'developer-starter' ) );
        $inquire_url = isset( $data['modal_inquire_url'] ) ? $data['modal_inquire_url'] : '/contact';
        $inquire_btn_bg_color = isset( $data['modal_inquire_btn_bg_color'] ) ? $clean_css_value( $data['modal_inquire_btn_bg_color'] ) : '';
        $inquire_btn_text_color = isset( $data['modal_inquire_btn_text_color'] ) ? $clean_css_value( $data['modal_inquire_btn_text_color'] ) : '';
        $inquire_btn_border_color = isset( $data['modal_inquire_btn_border_color'] ) ? $clean_css_value( $data['modal_inquire_btn_border_color'] ) : '';
        $inquire_btn_hover_bg_color = isset( $data['modal_inquire_btn_hover_bg_color'] ) ? $clean_css_value( $data['modal_inquire_btn_hover_bg_color'] ) : '';
        $inquire_btn_hover_text_color = isset( $data['modal_inquire_btn_hover_text_color'] ) ? $clean_css_value( $data['modal_inquire_btn_hover_text_color'] ) : '';
        $inquire_btn_hover_border_color = isset( $data['modal_inquire_btn_hover_border_color'] ) ? $clean_css_value( $data['modal_inquire_btn_hover_border_color'] ) : '';
        
        $module_id = 'products-' . uniqid();
        $source_post_id = $this->resolve_source_post_id();
        $allowed_product_post_ids = class_exists( '\Developer_Starter\Core\AJAX_Product_Loader' )
            ? \Developer_Starter\Core\AJAX_Product_Loader::extract_allowed_post_ids_from_products_data( $data )
            : array();
        $product_module_key = class_exists( '\Developer_Starter\Core\AJAX_Product_Loader' )
            ? \Developer_Starter\Core\AJAX_Product_Loader::build_module_key( $source_post_id, $allowed_product_post_ids )
            : '';
        $section_style = '--products-inquire-btn-text:#ffffff;--products-inquire-btn-hover-text:#ffffff;';

        if ( '' !== $inquire_btn_bg_color ) {
            $section_style .= '--products-inquire-btn-bg:' . $inquire_btn_bg_color . ';';
            $section_style .= '--products-inquire-btn-border:' . $inquire_btn_bg_color . ';';
        }

        if ( '' !== $inquire_btn_text_color ) {
            $section_style .= '--products-inquire-btn-text:' . $inquire_btn_text_color . ';';
        }

        if ( '' !== $inquire_btn_border_color ) {
            $section_style .= '--products-inquire-btn-border:' . $inquire_btn_border_color . ';';
        }

        if ( '' !== $inquire_btn_hover_bg_color ) {
            $section_style .= '--products-inquire-btn-hover-bg:' . $inquire_btn_hover_bg_color . ';';
            $section_style .= '--products-inquire-btn-hover-border:' . $inquire_btn_hover_bg_color . ';';
        }

        if ( '' !== $inquire_btn_hover_text_color ) {
            $section_style .= '--products-inquire-btn-hover-text:' . $inquire_btn_hover_text_color . ';';
        }

        if ( '' !== $inquire_btn_hover_border_color ) {
            $section_style .= '--products-inquire-btn-hover-border:' . $inquire_btn_hover_border_color . ';';
        }
        
        // 判断是否需要轮播
        $count = count( $items );
        $is_carousel = $count > $cols;
        ?>
        <section class="module module-products-manual section-padding" id="<?php echo esc_attr( $module_id ); ?>"<?php echo '' !== $section_style ? ' style="' . esc_attr( $section_style ) . '"' : ''; ?>>
            <div class="container">
                <!-- 头部 -->
                <?php if ( $title || $subtitle ) : ?>
                    <div class="section-header text-center" style="margin-bottom: var(--qiling-space-50);">
                        <?php if ( $subtitle ) : ?>
                    <span class="sub-title" style="display:block; font-size:var(--qiling-text-rem-0p875); color:var(--color-primary); text-transform:uppercase; letter-spacing:var(--qiling-space-1); margin-bottom:var(--qiling-space-10);">
                                <?php echo esc_html( $subtitle ); ?>
                            </span>
                        <?php endif; ?>
                        <?php if ( $title ) : ?>
                            <h2 class="section-title" style="margin:0; font-size:var(--qiling-text-rem-2); font-weight:700;">
                                <?php echo esc_html( $title ); ?>
                            </h2>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <!-- Content Area -->
                <?php if ( ! empty( $items ) ) : ?>
                    
                    <?php if ( $is_carousel ) : ?>
                        <!-- Carousel Mode -->
                        <div class="pm-carousel-wrapper">
                            <div class="swiper pm-swiper">
                                <div class="swiper-wrapper">
                                    <?php foreach ( $items as $item ) : ?>
                                        <div class="swiper-slide">
                                            <?php $this->render_card( $item ); ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="swiper-pagination"></div>
                            </div>
                        </div>
                    <?php else : ?>
                        <!-- Grid Mode -->
                        <div class="pm-grid cols-<?php echo esc_attr( $cols ); ?>">
                            <?php foreach ( $items as $item ) : ?>
                                <?php $this->render_card( $item ); ?>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                <?php else : ?>
                    <div class="text-center" style="padding: var(--qiling-space-40); color:var(--qiling-color-999999); background:var(--color-neutral-50); border-radius:8px;">
                        <?php esc_html_e( '请在后台添加产品列表', 'developer-starter' ); ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- 弹窗 Portal 会移动到 body，但保留产品模块自己的样式作用域与实例变量。 -->
            <div id="<?php echo esc_attr( $module_id ); ?>-portal" class="qiling-products-modal-portal qiling-module-scope qiling-module-scope-products" data-qiling-module-scope="products"<?php echo '' !== $section_style ? ' style="' . esc_attr( $section_style ) . '"' : ''; ?>>
            <div id="<?php echo esc_attr( $module_id ); ?>-modal" class="pm-modal-mask" hidden aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="<?php echo esc_attr( $module_id ); ?>-modal-title">
                <div class="pm-modal-box">
                    <button type="button" class="pm-close" aria-label="<?php esc_attr_e( '关闭产品详情', 'developer-starter' ); ?>">&times;</button>
                    
                    <!-- Modal Container Flex Wrapper -->
                    <div class="pm-modal-layout">
                        
                        <!-- Left Sidebar: Static Info -->
                        <div class="pm-modal-sidebar custom-scrollbar">
                            <div class="pm-ms-thumb">
                                <img src="" class="pm-mh-img">
                            </div>
                            <div class="pm-ms-info">
                                <h3 id="<?php echo esc_attr( $module_id ); ?>-modal-title" class="pm-mh-title"><?php esc_html_e( '产品名称', 'developer-starter' ); ?></h3>
                                <div class="pm-mh-desc"><?php esc_html_e( '产品描述', 'developer-starter' ); ?></div>
                                
                                <!-- New Args List -->
                                <ul class="pm-mh-specs"></ul>

                                <div class="pm-mh-actions">
                                    <a href="<?php echo esc_url( $inquire_url ); ?>" class="pm-inquire-btn"><?php echo esc_html( $inquire_text ); ?></a>
                                </div>
                            </div>
                        </div>

                        <!-- Right Content: AJAX Info -->
                        <div class="pm-modal-main custom-scrollbar">
                            <div class="pm-loader">
                                <div class="pm-spinner"></div>
                                <p><?php esc_html_e( '正在加载详情...', 'developer-starter' ); ?></p>
                            </div>
                            <div class="pm-content-area article-content"></div>
                        </div>

                    </div>
                </div>
            </div>
            </div>
        </section>



        <script>
        (function () {
            var moduleId = <?php echo wp_json_encode( $module_id ); ?>;
            var section = document.getElementById(moduleId);
            if (!section || section.getAttribute('data-products-initialized') === '1') {
                return;
            }
            section.setAttribute('data-products-initialized', '1');

            var productAjaxNonce = <?php echo wp_json_encode( wp_create_nonce( 'ds_product_nonce' ) ); ?>;
            var ajaxUrl = <?php echo wp_json_encode( admin_url( 'admin-ajax.php' ) ); ?>;
            var productSourceId = <?php echo wp_json_encode( (string) $source_post_id ); ?>;
            var productModuleKey = <?php echo wp_json_encode( $product_module_key ); ?>;
            var noContentText = <?php echo wp_json_encode( __( '暂无详细内容', 'developer-starter' ) ); ?>;
            var errorPrefix = <?php echo wp_json_encode( __( '错误：', 'developer-starter' ) ); ?>;
            var genericError = <?php echo wp_json_encode( __( '加载失败，请稍后重试', 'developer-starter' ) ); ?>;

            <?php if ( $is_carousel ) : ?>
            if (typeof window.Swiper !== 'undefined') {
                var swiperEl = section.querySelector('.pm-swiper');
                var paginationEl = section.querySelector('.swiper-pagination');
                if (swiperEl) {
                    new window.Swiper(swiperEl, {
                        slidesPerView: <?php echo $cols; ?>,
                        spaceBetween: 30,
                        pagination: { el: paginationEl, clickable: true },
                        breakpoints: {
                            320: { slidesPerView: 1, spaceBetween: 20 },
                            768: { slidesPerView: 2, spaceBetween: 20 },
                            1024: { slidesPerView: <?php echo $cols; ?>, spaceBetween: 30 }
                        }
                    });
                }
            }
            <?php endif; ?>

            var portal = document.getElementById(moduleId + '-portal');
            var modal = portal ? portal.querySelector('#' + moduleId + '-modal') : null;
            if (!portal || !modal) {
                return;
            }

            if (portal.parentNode !== document.body) {
                document.body.appendChild(portal);
            }

            var loader = modal.querySelector('.pm-loader');
            var content = modal.querySelector('.pm-content-area');
            var specsList = modal.querySelector('.pm-mh-specs');
            var titleEl = modal.querySelector('.pm-mh-title');
            var descEl = modal.querySelector('.pm-mh-desc');
            var imgEl = modal.querySelector('.pm-mh-img');
            var closeBtn = modal.querySelector('.pm-close');
            var activeRequest = 0;
            var previousBodyOverflow = '';
            var bodyScrollLocked = false;
            var removalObserver = null;

            function setBodyScrollLocked(locked) {
                if (locked && !bodyScrollLocked) {
                    previousBodyOverflow = document.body.style.overflow;
                    document.body.style.overflow = 'hidden';
                    bodyScrollLocked = true;
                    return;
                }
                if (!locked && bodyScrollLocked) {
                    document.body.style.overflow = previousBodyOverflow;
                    bodyScrollLocked = false;
                }
            }

            function setLoaderVisible(visible) {
                if (!loader) {
                    return;
                }
                loader.style.display = visible ? '' : 'none';
            }

            function renderSpecs(specs) {
                if (!specsList) {
                    return;
                }

                specsList.innerHTML = '';
                var lines = String(specs || '')
                    .split('|||')
                    .map(function (line) {
                        return line.trim();
                    })
                    .filter(function (line) {
                        return line !== '';
                    });

                if (!lines.length) {
                    specsList.style.display = 'none';
                    return;
                }

                lines.forEach(function (line) {
                    var item = document.createElement('li');
                    item.textContent = line;
                    specsList.appendChild(item);
                });
                specsList.style.display = '';
            }

            function closeModal() {
                activeRequest += 1;
                modal.classList.remove('open');
                modal.hidden = true;
                modal.setAttribute('aria-hidden', 'true');
                setBodyScrollLocked(false);
            }

            function handleSectionClick(event) {
                var card = event.target.closest('.pm-card');
                if (!card || !section.contains(card) || !section.isConnected || !portal.isConnected) {
                    return;
                }

                event.preventDefault();

                var pid = card.getAttribute('data-post-id') || '';
                var title = card.getAttribute('data-title') || '';
                var img = card.getAttribute('data-image') || '';
                var desc = card.getAttribute('data-desc') || '';
                var specs = card.getAttribute('data-specs') || '';

                if (titleEl) {
                    titleEl.textContent = title;
                }
                if (descEl) {
                    descEl.textContent = desc;
                }
                if (imgEl) {
                    imgEl.setAttribute('src', img);
                }

                renderSpecs(specs);

                if (content) {
                    content.innerHTML = '';
                }
                setLoaderVisible(true);
                modal.hidden = false;
                modal.setAttribute('aria-hidden', 'false');
                modal.classList.add('open');
                setBodyScrollLocked(true);

                if (!pid) {
                    setLoaderVisible(false);
                    if (content) {
                        content.innerHTML = '<p class="no-content">' + noContentText + '</p>';
                    }
                    return;
                }

                var requestId = ++activeRequest;
                loadProductContent(pid).then(function (result) {
                    if (requestId !== activeRequest || modal.hidden || !section.isConnected || !portal.isConnected) {
                        return;
                    }
                    setLoaderVisible(false);
                    if (!content) {
                        return;
                    }

                    if (result && result.success && result.data && typeof result.data.html === 'string') {
                        content.innerHTML = result.data.html;
                        return;
                    }

                    var message = genericError;
                    if (result && result.data && result.data.message) {
                        message = result.data.message;
                    }
                    content.innerHTML = '<p class="error">' + errorPrefix + message + '</p>';
                }).catch(function () {
                    if (requestId !== activeRequest || modal.hidden || !section.isConnected || !portal.isConnected) {
                        return;
                    }
                    setLoaderVisible(false);
                    if (content) {
                        content.innerHTML = '<p class="error">' + errorPrefix + genericError + '</p>';
                    }
                });
            }

            function handleModalClick(event) {
                if (event.target === modal) {
                    closeModal();
                }
            }

            function handleDocumentKeydown(event) {
                if (!modal.hidden && (event.key === 'Escape' || event.keyCode === 27)) {
                    closeModal();
                }
            }

            function cleanupModule() {
                closeModal();
                section.removeEventListener('click', handleSectionClick);
                modal.removeEventListener('click', handleModalClick);
                if (closeBtn) {
                    closeBtn.removeEventListener('click', closeModal);
                }
                document.removeEventListener('keydown', handleDocumentKeydown);
                if (removalObserver) {
                    removalObserver.disconnect();
                    removalObserver = null;
                }
                if (portal.parentNode) {
                    portal.parentNode.removeChild(portal);
                }
            }

            function parseJsonSafely(text) {
                if (!text) {
                    return null;
                }

                try {
                    return JSON.parse(text);
                } catch (error) {
                    return null;
                }
            }

            function loadProductContent(postId) {
                var payload = new URLSearchParams();
                payload.set('action', 'ds_fetch_product_content');
                payload.set('post_id', String(postId));
                payload.set('source_id', String(productSourceId || ''));
                payload.set('module_key', productModuleKey || '');
                payload.set('nonce', productAjaxNonce);

                return fetch(ajaxUrl, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
                    },
                    body: payload.toString()
                }).then(function (response) {
                    return response.text();
                }).then(function (text) {
                    return parseJsonSafely(text);
                });
            }

            section.addEventListener('click', handleSectionClick);

            if (closeBtn) {
                closeBtn.addEventListener('click', closeModal);
            }

            modal.addEventListener('click', handleModalClick);
            document.addEventListener('keydown', handleDocumentKeydown);

            removalObserver = new MutationObserver(function () {
                if (!section.isConnected) {
                    cleanupModule();
                }
            });
            removalObserver.observe(document.body, { childList: true, subtree: true });
        })();
        </script>
        <?php
    }

    /**
     * Resolve the page that provided this module configuration.
     *
     * @return int
     */
    private function resolve_source_post_id() {
        $source_post_id = function_exists( 'get_queried_object_id' ) ? absint( get_queried_object_id() ) : 0;
        if ( $source_post_id <= 0 ) {
            $source_post_id = absint( get_the_ID() );
        }

        return $source_post_id;
    }

    private function render_card( $item ) {
        $img = isset( $item['image'] ) ? $item['image'] : '';
        $name = isset( $item['title'] ) ? $item['title'] : '';
        $desc = isset( $item['desc'] ) ? $item['desc'] : '';
        $specs = isset( $item['specs'] ) ? $item['specs'] : '';
        $pid = isset( $item['post_id'] ) ? $item['post_id'] : '';
        $btn = isset( $item['btn_text'] ) ? $item['btn_text'] : '';
        
        // 确保统一换行符后再替换为分隔符
        $specs_attr = str_replace( array("\r\n", "\r", "\n"), "|||", $specs );
        
        $bg_style = $img ? 'background-image: url(' . esc_url($img) . ');' : 'background-color: var(--color-neutral-200);';
        ?>
        <div class="pm-card" 
             data-post-id="<?php echo esc_attr( $pid ); ?>"
             data-title="<?php echo esc_attr( $name ); ?>"
             data-image="<?php echo esc_url( $img ); ?>"
             data-desc="<?php echo esc_attr( $desc ); ?>"
             data-specs="<?php echo esc_attr( $specs_attr ); ?>">
            
            <div class="pm-thumb">
                <div class="pm-bg" style="<?php echo $bg_style; ?>"></div>
                <?php if ( $btn ) : ?>
                    <div class="pm-overlay">
                        <span class="pm-btn"><?php echo esc_html( $btn ); ?></span>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="pm-info">
                <h3 class="pm-name"><?php echo esc_html( $name ); ?></h3>
                <?php if( $desc ) : ?>
                    <p class="pm-desc"><?php echo esc_html( $desc ); ?></p>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }
}
