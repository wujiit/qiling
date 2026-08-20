<?php
/**
 * Software Carousel Module - 软件轮播模块
 *
 * 展示启灵软件库数据的轮播模块，支持分类筛选和自定义按钮
 *
 * @package Developer_Starter
 * @since 1.0.0
 */

namespace Developer_Starter\Modules\Modules;

use Developer_Starter\Modules\Module_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Software_Carousel_Module extends Module_Base {

    public function __construct() {
        $this->category = 'content';
        $this->icon = 'dashicons-layout';
        $this->description = __( '展示启灵软件库数据的轮播模块', 'developer-starter' );
    }

    public function get_id() {
        return 'software_carousel';
    }

    public function get_name() {
        return __( '软件轮播', 'developer-starter' );
    }

    public function get_fields() {
        return array(
            array( 'id' => 'software_carousel_title', 'type' => 'text', 'label' => __( '标题', 'developer-starter' ) ),
            array( 'id' => 'software_carousel_subtitle', 'type' => 'text', 'label' => __( '副标题', 'developer-starter' ) ),
            
            // 样式设置
            array( 
                'id' => 'software_carousel_bg_type', 
                'type' => 'select', 
                'label' => __( '背景类型', 'developer-starter' ), 
                'options' => array(
                    'color' => __( '纯色背景', 'developer-starter' ),
                    'image' => __( '图片背景', 'developer-starter' ),
                ),
                'default' => 'color',
            ),
            array( 
                'id' => 'software_carousel_bg_color', 
                'type' => 'color', 
                'label' => '背景颜色', 
                'default' => 'var(--color-neutral-0)',
                'dependency' => array( 'software_carousel_bg_type', '==', 'color' )
            ),
            array( 
                'id' => 'software_carousel_bg_image', 
                'type' => 'image', 
                'label' => '背景图片',
                'dependency' => array( 'software_carousel_bg_type', '==', 'image' )
            ),
            array( 
                'id' => 'software_carousel_bg_overlay', 
                'type' => 'color', 
                'label' => __( '图片遮罩颜色', 'developer-starter' ),
                'desc' => __( '设置带透明度的颜色以增加文字可读性', 'developer-starter' ),
                'default' => 'var(--qiling-color-rgba-0-0-0-05)',
                'dependency' => array( 'software_carousel_bg_type', '==', 'image' )
            ),

            // 排版设置
            array( 'id' => 'software_carousel_title_color', 'type' => 'color', 'label' => __( '标题颜色', 'developer-starter' ), 'default' => 'var(--color-neutral-800)' ),
            array( 'id' => 'software_carousel_title_size', 'type' => 'text', 'label' => __( '标题大小', 'developer-starter' ), 'default' => '1.5rem' ),
            array( 'id' => 'software_carousel_subtitle_color', 'type' => 'color', 'label' => __( '副标题颜色', 'developer-starter' ), 'default' => 'var(--color-text-muted)' ),
            array( 'id' => 'software_carousel_subtitle_size', 'type' => 'text', 'label' => __( '副标题大小', 'developer-starter' ), 'default' => '0.95rem' ),

            // 布局与显示
            array( 'id' => 'software_carousel_categories', 'type' => 'text', 'label' => __( '软件分类ID (逗号分隔)', 'developer-starter' ) ),
            array( 'id' => 'software_carousel_count', 'type' => 'number', 'label' => __( '显示数量', 'developer-starter' ), 'default' => '8' ),
            array( 'id' => 'software_carousel_speed', 'type' => 'number', 'label' => __( '滚动速度 (秒)', 'developer-starter' ), 'default' => '30' ),
            array( 'id' => 'software_carousel_card_bg', 'type' => 'color', 'label' => __( '卡片背景色', 'developer-starter' ), 'default' => 'var(--color-neutral-0)' ),
            array(
                'id'          => 'software_carousel_badge_bg',
                'type'        => 'color',
                'label'       => __( '标签/徽章背景颜色', 'developer-starter' ),
                'description' => __( '控制版本标签背景，留空时跟随页面预设风格或全局徽章颜色。', 'developer-starter' ),
                'default'     => '',
            ),
            array( 'id' => 'software_carousel_icon_size', 'type' => 'text', 'label' => __( '图标大小', 'developer-starter' ), 'default' => '64px' ),
            
            array( 'id' => 'software_carousel_show_btn', 'type' => 'select', 'label' => __( '显示按钮', 'developer-starter' ), 'options' => array( '1' => __( '是', 'developer-starter' ), '0' => __( '否', 'developer-starter' ) ), 'default' => '0' ),
            array( 'id' => 'software_carousel_btn_text', 'type' => 'text', 'label' => __( '按钮文字', 'developer-starter' ), 'default' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '查看更多', 'View More' ) : __( '查看更多', 'developer-starter' ) ),
            array( 'id' => 'software_carousel_btn_link', 'type' => 'text', 'label' => __( '按钮链接', 'developer-starter' ) ),
            array(
                'id'          => 'software_carousel_btn_bg_color',
                'type'        => 'color',
                'label'       => __( '按钮背景颜色', 'developer-starter' ),
                'description' => __( '留空时跟随全局设计里的按钮样式', 'developer-starter' ),
            ),
            array(
                'id'          => 'software_carousel_btn_text_color',
                'type'        => 'color',
                'label'       => __( '按钮文字颜色', 'developer-starter' ),
                'description' => __( '留空时跟随全局设计里的按钮样式', 'developer-starter' ),
            ),
            $this->get_button_border_color_field( 'software_carousel_btn_border_color' ),
            array(
                'id'          => 'software_carousel_btn_hover_bg_color',
                'type'        => 'color',
                'label'       => __( '按钮悬停背景颜色', 'developer-starter' ),
                'description' => __( '留空时跟随全局设计里的按钮悬停样式', 'developer-starter' ),
            ),
            array(
                'id'          => 'software_carousel_btn_hover_text_color',
                'type'        => 'color',
                'label'       => __( '按钮悬停文字颜色', 'developer-starter' ),
                'description' => __( '留空时跟随全局设计里的按钮悬停样式', 'developer-starter' ),
            ),
            $this->get_button_border_color_field( 'software_carousel_btn_hover_border_color', __( '按钮悬停边框颜色', 'developer-starter' ), __( '留空时跟随按钮悬停背景颜色。', 'developer-starter' ) ),
        );
    }

    public function render( $data = array() ) {
        if ( ! function_exists( 'developer_starter_qiapp_is_available' ) ) {
            $helper_file = defined( 'DEVELOPER_STARTER_INC' )
                ? DEVELOPER_STARTER_INC . '/core/helpers/helpers-qiapp-theme.php'
                : get_template_directory() . '/inc/core/helpers/helpers-qiapp-theme.php';
            if ( is_string( $helper_file ) && file_exists( $helper_file ) ) {
                require_once $helper_file;
            }
        }

        // 检查启灵软件库插件是否激活
        if ( ! function_exists( 'developer_starter_qiapp_is_available' ) || ! developer_starter_qiapp_is_available() ) {
            $this->render_plugin_notice();
            return;
        }

        // 基础配置
        $title = isset( $data['software_carousel_title'] ) ? $data['software_carousel_title'] : '';
        $subtitle = isset( $data['software_carousel_subtitle'] ) ? $data['software_carousel_subtitle'] : '';
        
        // 背景配置
        $bg_type = isset( $data['software_carousel_bg_type'] ) && in_array( $data['software_carousel_bg_type'], array( 'color', 'image' ), true )
            ? $data['software_carousel_bg_type']
            : 'color';
        $bg_color = $this->sanitize_css_color_value(
            isset( $data['software_carousel_bg_color'] ) ? $data['software_carousel_bg_color'] : '',
            'var(--color-neutral-0)'
        );
        $bg_image = isset( $data['software_carousel_bg_image'] )
            ? $this->sanitize_css_url_value( $data['software_carousel_bg_image'] )
            : '';
        $bg_overlay = $this->sanitize_css_color_value(
            isset( $data['software_carousel_bg_overlay'] ) ? $data['software_carousel_bg_overlay'] : '',
            'var(--qiling-color-rgba-0-0-0-05)'
        );
        
        // 排版配置
        $title_color = $this->sanitize_css_color_value(
            isset( $data['software_carousel_title_color'] ) ? $data['software_carousel_title_color'] : '',
            'var(--color-neutral-800)'
        );
        $title_size = $this->sanitize_css_size_value(
            isset( $data['software_carousel_title_size'] ) ? $data['software_carousel_title_size'] : '',
            '1.5rem'
        );
        $subtitle_color = $this->sanitize_css_color_value(
            isset( $data['software_carousel_subtitle_color'] ) ? $data['software_carousel_subtitle_color'] : '',
            'var(--color-text-muted)'
        );
        $subtitle_size = $this->sanitize_css_size_value(
            isset( $data['software_carousel_subtitle_size'] ) ? $data['software_carousel_subtitle_size'] : '',
            '0.95rem'
        );
        
        // 数据配置
        $categories = isset( $data['software_carousel_categories'] ) ? $data['software_carousel_categories'] : '';
        $count = isset( $data['software_carousel_count'] ) && $data['software_carousel_count'] !== '' ? intval( $data['software_carousel_count'] ) : 8;
        
        // 轮播配置
        $scroll_speed = $this->sanitize_integer_range(
            isset( $data['software_carousel_speed'] ) ? $data['software_carousel_speed'] : '',
            30,
            5,
            300
        );
        
        // 样式配置
        $card_bg = $this->sanitize_css_color_value(
            isset( $data['software_carousel_card_bg'] ) ? $data['software_carousel_card_bg'] : '',
            'var(--color-neutral-0)'
        );
        $badge_bg = $this->sanitize_css_color_value(
            isset( $data['software_carousel_badge_bg'] ) ? $data['software_carousel_badge_bg'] : '',
            ''
        );
        $icon_size = $this->sanitize_css_size_value(
            isset( $data['software_carousel_icon_size'] ) ? $data['software_carousel_icon_size'] : '',
            '64px'
        );
        
        // 按钮配置
        $show_btn = isset( $data['software_carousel_show_btn'] ) && $data['software_carousel_show_btn'] === '1';
        $btn_text = isset( $data['software_carousel_btn_text'] ) && ! empty( $data['software_carousel_btn_text'] )
            ? $data['software_carousel_btn_text']
            : ( function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '查看更多', 'View More' ) : __( '查看更多', 'developer-starter' ) );
        $btn_link = ! empty( $data['software_carousel_btn_link'] ) ? $data['software_carousel_btn_link'] : developer_starter_qiapp_get_archive_link();
        $btn_bg_color = $this->sanitize_css_color_value(
            isset( $data['software_carousel_btn_bg_color'] ) ? $data['software_carousel_btn_bg_color'] : '',
            ''
        );
        $btn_text_color = $this->sanitize_css_color_value(
            isset( $data['software_carousel_btn_text_color'] ) ? $data['software_carousel_btn_text_color'] : '',
            ''
        );
        $btn_border_color = $this->sanitize_css_color_value(
            isset( $data['software_carousel_btn_border_color'] ) ? $data['software_carousel_btn_border_color'] : '',
            ''
        );
        $btn_hover_bg_color = $this->sanitize_css_color_value(
            isset( $data['software_carousel_btn_hover_bg_color'] ) ? $data['software_carousel_btn_hover_bg_color'] : '',
            ''
        );
        $btn_hover_text_color = $this->sanitize_css_color_value(
            isset( $data['software_carousel_btn_hover_text_color'] ) ? $data['software_carousel_btn_hover_text_color'] : '',
            ''
        );
        $btn_hover_border_color = $this->sanitize_css_color_value(
            isset( $data['software_carousel_btn_hover_border_color'] ) ? $data['software_carousel_btn_hover_border_color'] : '',
            ''
        );

        // 获取软件数据
        $software_items = $this->get_software_items( $categories, $count );
        
        if ( empty( $software_items ) ) {
            $this->render_empty_notice();
            return;
        }

        // 构建 CSS 变量
        $css_vars = array();
        
        // 背景处理
        if ( $bg_type === 'image' && ! empty( $bg_image ) ) {
            $css_vars['--sc-bg-image'] = 'url("' . $bg_image . '")';
            $css_vars['--sc-bg-overlay'] = $bg_overlay;
            // 图片背景通常需要白色文字
            if ( in_array( $title_color, array( 'var(--color-neutral-800)', '#' . '1e293b' ), true ) ) {
                $title_color = 'var(--color-neutral-0)';
            }
            if ( in_array( $subtitle_color, array( 'var(--color-text-muted)', '#' . '64748b' ), true ) ) {
                $subtitle_color = 'var(--qiling-color-rgba-255-255-255-08)';
            }
        } else {
            $css_vars['--sc-bg-color'] = $bg_color;
        }
        
        $css_vars['--sc-title-color'] = $title_color;
        $css_vars['--sc-title-size'] = $title_size;
        $css_vars['--sc-subtitle-color'] = $subtitle_color;
        $css_vars['--sc-subtitle-size'] = $subtitle_size;
        $css_vars['--sc-card-bg'] = $card_bg;
        $css_vars['--sc-icon-size'] = $icon_size;
        $css_vars['--sc-scroll-speed'] = "{$scroll_speed}s";
        if ( '' !== $badge_bg ) {
            $css_vars['--qiling-component-badge-bg'] = $badge_bg;
        }
        if ( '' !== $btn_bg_color ) {
            $css_vars['--sc-btn-bg'] = $btn_bg_color;
            $css_vars['--sc-btn-border'] = $btn_bg_color;
        }
        if ( '' !== $btn_text_color ) {
            $css_vars['--sc-btn-text'] = $btn_text_color;
        }
        if ( '' !== $btn_border_color ) {
            $css_vars['--sc-btn-border'] = $btn_border_color;
        }
        if ( '' !== $btn_hover_bg_color ) {
            $css_vars['--sc-btn-hover-bg'] = $btn_hover_bg_color;
            $css_vars['--sc-btn-hover-border'] = $btn_hover_bg_color;
        }
        if ( '' !== $btn_hover_text_color ) {
            $css_vars['--sc-btn-hover-text'] = $btn_hover_text_color;
        }
        if ( '' !== $btn_hover_border_color ) {
            $css_vars['--sc-btn-hover-border'] = $btn_hover_border_color;
        }

        $style_attr = $this->build_safe_css_var_style( $css_vars );
        
        // 容器类名
        $container_classes = array( 'module', 'module-software-carousel', 'section-padding' );
        if ( $bg_type === 'image' && ! empty( $bg_image ) ) $container_classes[] = 'has-bg-image';
        
        $unique_id = 'software-carousel-' . uniqid();
        ?>
        <section class="<?php echo esc_attr( implode( ' ', $container_classes ) ); ?>" id="<?php echo esc_attr( $unique_id ); ?>"<?php echo '' !== $style_attr ? ' style="' . esc_attr( $style_attr ) . '"' : ''; ?>>
            <div class="module-bg-overlay"></div>
            <div class="container">
                <?php if ( $title || $show_btn ) : ?>
                    <div class="sc-header">
                        <div class="sc-header-left">
                            <?php if ( $title ) : ?>
                                <h2 class="section-title"><?php echo esc_html( $title ); ?></h2>
                            <?php endif; ?>
                            <?php if ( $subtitle ) : ?>
                                <p class="section-subtitle"><?php echo esc_html( $subtitle ); ?></p>
                            <?php endif; ?>
                        </div>
                        <?php if ( $show_btn && $btn_link ) : ?>
                            <a href="<?php echo esc_url( $btn_link ); ?>" class="sc-more-btn">
                                <?php echo esc_html( $btn_text ); ?>
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
                <!-- 自动滚动轮播 -->
                <div class="sc-scroll-wrapper">
                    <div class="sc-scroll-track">
                        <?php 
                        // 复制两次以实现无缝滚动
                        for ( $loop = 0; $loop < 2; $loop++ ) :
                            foreach ( $software_items as $item ) : 
                                $icon = isset( $item['icon'] ) ? $item['icon'] : '';
                                $name = isset( $item['name'] ) ? $item['name'] : '';
                                $version = isset( $item['version'] ) ? $item['version'] : '';
                                $update_date = isset( $item['update_date'] ) ? $item['update_date'] : '';
                                $link = isset( $item['link'] ) ? $item['link'] : '';
                        ?>
                            <a href="<?php echo esc_url( $link ); ?>" class="sc-card">
                                <?php if ( $icon ) : ?>
                                    <img src="<?php echo esc_url( $icon ); ?>" alt="<?php echo esc_attr( $name ); ?>" class="sc-icon" />
                                <?php else : ?>
                                    <div class="sc-icon-placeholder">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="var(--color-neutral-400)"><path d="M4 4h16v16H4V4zm2 2v12h12V6H6z"/></svg>
                                    </div>
                                <?php endif; ?>
                                <span class="sc-name"><?php echo esc_html( $name ); ?></span>
                                <?php if ( $version ) : ?>
                                    <span class="sc-version"><?php echo esc_html( $version ); ?></span>
                                <?php endif; ?>
                                <span class="sc-date"><?php echo esc_html( $update_date ); ?></span>
                            </a>
                        <?php 
                            endforeach;
                        endfor;
                        ?>
                    </div>
                </div>
            </div>
        </section>
        <?php
    }

    /**
     * 清洗 CSS 颜色值（仅允许常见安全格式）
     */
    private function sanitize_css_color_value( $value, $default ) {
        $value = is_string( $value ) ? trim( wp_strip_all_tags( $value ) ) : '';
        if ( '' === $value ) {
            return $default;
        }

        $hex_color = sanitize_hex_color( $value );
        if ( $hex_color ) {
            return $hex_color;
        }

        if ( preg_match( '/^(rgba?|hsla?)\(\s*[0-9\.\s,%]+\s*\)$/i', $value ) ) {
            return $value;
        }

        if ( preg_match( '/^var\(--[a-z0-9_-]+\)$/i', $value ) ) {
            return $value;
        }

        return $default;
    }

    /**
     * 清洗 CSS 尺寸值（如 16px / 1.2rem / 80%）
     */
    private function sanitize_css_size_value( $value, $default ) {
        $value = is_string( $value ) ? trim( wp_strip_all_tags( $value ) ) : '';
        if ( '' === $value ) {
            return $default;
        }

        if ( preg_match( '/^\d+(?:\.\d+)?(?:px|rem|em|vh|vw|%)$/i', $value ) ) {
            return $value;
        }

        return $default;
    }

    /**
     * 清洗 CSS URL 值（仅允许 http/https，禁止分号等可断句字符）。
     */
    private function sanitize_css_url_value( $value ) {
        $value = is_string( $value ) ? trim( wp_strip_all_tags( $value ) ) : '';
        if ( '' === $value ) {
            return '';
        }

        $value = esc_url_raw( $value, array( 'http', 'https' ) );
        if ( '' === $value ) {
            return '';
        }

        if ( ! preg_match( '#^https?://[^\\s"\'<>;]+$#i', $value ) ) {
            return '';
        }

        return $value;
    }

    /**
     * 构建安全的 CSS 自定义变量 style 字符串。
     */
    private function build_safe_css_var_style( $variables ) {
        $declarations = array();

        foreach ( (array) $variables as $property => $raw_value ) {
            $property = trim( (string) $property );
            if ( '' === $property || ! preg_match( '/^(--sc-[a-z0-9-]+|--qiling-component-badge-bg)$/', $property ) ) {
                continue;
            }

            if ( ! is_scalar( $raw_value ) ) {
                continue;
            }

            $value = trim( (string) $raw_value );
            if ( '' === $value || preg_match( '/[;<>{}]/', $value ) ) {
                continue;
            }

            $declarations[] = "{$property}: {$value}";
        }

        return implode( '; ', $declarations );
    }

    /**
     * 限制整数字段范围，避免异常输入影响渲染
     */
    private function sanitize_integer_range( $value, $default, $min, $max ) {
        $number = is_numeric( $value ) ? (int) $value : (int) $default;
        return max( $min, min( $max, $number ) );
    }

    /**
     * 获取软件数据
     *
     * @param string $categories 分类ID（逗号分隔）
     * @param int    $count      获取数量
     * @return array
     */
    private function get_software_items( $categories, $count ) {
        $items = array();
        $software_post_type = function_exists( 'developer_starter_qiapp_get_post_type' ) ? developer_starter_qiapp_get_post_type() : 'qiapp_software';
        if ( ! post_type_exists( $software_post_type ) ) {
            return $items;
        }
        $post_ids = function_exists( 'developer_starter_qiapp_get_post_ids' )
            ? developer_starter_qiapp_get_post_ids(
                array(
                    'term_ids' => $categories,
                    'limit'    => $count,
                    'orderby'  => 'updated',
                )
            )
            : array();

        if ( empty( $post_ids ) ) {
            return $items;
        }

        $args = array(
            'post_type'      => $software_post_type,
            'posts_per_page' => count( $post_ids ),
            'post_status'    => 'publish',
            'post__in'       => $post_ids,
            'orderby'        => 'post__in',
            'ignore_sticky_posts'  => true,
            'no_found_rows'        => true,
            'update_post_meta_cache' => false,
            'update_post_term_cache' => false,
        );

        // 执行查询（接入主题查询缓存；用户交互/个性化作用域自动绕过）
        if ( function_exists( 'developer_starter_run_cached_query' ) ) {
            $query = \developer_starter_run_cached_query(
                $args,
                'module_software_carousel',
                array(
                    'needs_pagination' => false,
                )
            );
        } else {
            $query = new \WP_Query( $args );
        }

        $preloaded_entries = function_exists( 'developer_starter_qiapp_preload_entries' )
            ? developer_starter_qiapp_preload_entries( $post_ids )
            : array();

        if ( $query->have_posts() ) {
            while ( $query->have_posts() ) {
                $query->the_post();
                $post_id = get_the_ID();

                $entry = function_exists( 'developer_starter_qiapp_get_entry_data' )
                    ? developer_starter_qiapp_get_entry_data( $post_id, $preloaded_entries )
                    : null;

                if ( ! $entry ) {
                    continue;
                }

                $items[] = array(
                    'icon'        => $entry['icon'],
                    'name'        => $entry['title'],
                    'version'     => $entry['version_label'],
                    'update_date' => $entry['update_date'],
                    'link'        => $entry['permalink'],
                );
            }
            wp_reset_postdata();
        }
        
        return $items;
    }

    /**
     * 渲染插件未安装提示
     */
    private function render_plugin_notice() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        ?>
        <div class="software-carousel-notice" style="
            background: linear-gradient(135deg, var(--qiling-color-fef3c7), var(--qiling-color-fde68a));
            border: 1px solid var(--color-warning);
            border-radius: 12px;
            padding: var(--qiling-space-30);
            text-align: center;
            margin: var(--qiling-space-20) 0;
        ">
            <span style="font-size: var(--qiling-text-rem-2p5); display: block; margin-bottom: var(--qiling-space-12);">⚠️</span>
            <h3 style="margin: 0 0 var(--qiling-space-8); color: var(--qiling-color-92400e);"><?php esc_html_e( '请先安装启灵软件库插件', 'developer-starter' ); ?></h3>
            <p style="margin: 0; color: var(--qiling-color-a16207);"><?php esc_html_e( '软件轮播模块需要启灵软件库插件（qilingapp）的支持，请先安装并激活该插件。', 'developer-starter' ); ?></p>
        </div>
        <?php
    }

    /**
     * 渲染无数据提示
     */
    private function render_empty_notice() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        ?>
        <div class="software-carousel-notice" style="
            background: var(--color-neutral-50);
            border: 1px dashed var(--color-neutral-300);
            border-radius: 12px;
            padding: var(--qiling-space-30);
            text-align: center;
            margin: var(--qiling-space-20) 0;
        ">
            <span style="font-size: var(--qiling-text-rem-2p5); display: block; margin-bottom: var(--qiling-space-12);">📦</span>
            <h3 style="margin: 0 0 var(--qiling-space-8); color: var(--color-neutral-600);"><?php esc_html_e( '暂无软件数据', 'developer-starter' ); ?></h3>
            <p style="margin: 0; color: var(--color-text-muted);"><?php esc_html_e( '请先在启灵软件库插件中添加软件数据，或选择包含软件数据的分类。', 'developer-starter' ); ?></p>
        </div>
        <?php
    }
}
