<?php
/**
 * Main Category Content Display Module - 主分类内容展示
 * 
 * @package Developer_Starter
 */

namespace Developer_Starter\Modules\Modules;

use Developer_Starter\Modules\Module_Base;
use WP_Query;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Main_Category_Content_Module extends Module_Base {

    public function __construct() {
        $this->category = 'content';
        $this->icon = 'dashicons-grid-view';
        $this->description = __( '展示指定分类的内容，包含子分类导航和“查看更多”按钮', 'developer-starter' );
    }

    public function get_id() {
        return 'qiling_main_category_content';
    }

    public function get_name() {
        return __( '主分类内容展示', 'developer-starter' );
    }

    public function get_fields() {
        // 获取所有分类用于选择
        $categories = get_categories( array( 'hide_empty' => false ) );
        $cat_options = array( '' => __( '请选择影视分类', 'developer-starter' ) );
        foreach ( $categories as $cat ) {
            $cat_options[ $cat->term_id ] = $cat->name;
        }

        return array(
            // --- 核心内容设置 ---
            array(
                'id' => 'category_header_info',
                'label' => __( '分类信息设置', 'developer-starter' ),
                'type' => 'info',
                'description' => __( '尚未选择影视分类时，请在下方选择该模块要展示的分类。模块会自动获取所选分类下的子分类链接。', 'developer-starter' )
            ),
            array( 
                'id' => 'target_category', 
                'label' => __( '选择主分类', 'developer-starter' ), 
                'type' => 'select', 
                'options' => $cat_options,
                'default' => '' 
            ),
            array( 
                'id' => 'category_icon', 
                'label' => __( '分类图标', 'developer-starter' ), 
                'type' => 'image', 
                'description' => __( '显示在分类名称左侧的图标', 'developer-starter' ) 
            ),
            array( 
                'id' => 'custom_title', 
                'label' => __( '自定义标题', 'developer-starter' ), 
                'type' => 'text', 
                'description' => __( '留空则默认显示分类名称', 'developer-starter' ),
                'default' => '' 
            ),
            
            // --- 右侧按钮设置 ---
            array(
                'id' => 'more_btn_text',
                'label' => __( '“查看更多”按钮文字', 'developer-starter' ),
                'type' => 'text',
                'default' => __( '查看更多', 'developer-starter' ),
                'description' => __( '显示在最右侧的按钮文字', 'developer-starter' )
            ),
            $this->get_button_border_color_field( 'more_btn_border_color', __( '“查看更多”按钮边框颜色', 'developer-starter' ) ),
            $this->get_button_border_color_field( 'more_btn_hover_border_color', __( '“查看更多”按钮悬停边框颜色', 'developer-starter' ), __( '留空时跟随全局按钮悬停背景色。', 'developer-starter' ) ),
            
            // --- 显示设置 ---
            array(
                'id' => 'enable_artplayer_data',
                'label' => __( '启用启灵播放器视频数据', 'developer-starter' ),
                'type' => 'select',
                'options' => array( 'yes' => __( '启用', 'developer-starter' ), 'no' => __( '禁用', 'developer-starter' ) ),
                'default' => 'no',
                'description' => __( '启用后将读取播放器封面、高清类型、评分，并显示播放按钮图标。', 'developer-starter' )
            ),
            array(
                'id' => 'show_episode_count',
                'label' => __( '显示视频集数', 'developer-starter' ),
                'type' => 'select',
                'options' => array( 'yes' => __( '显示', 'developer-starter' ), 'no' => __( '隐藏', 'developer-starter' ) ),
                'default' => 'no',
                'description' => __( '启用后将在封面左下角显示“更新至第X集”', 'developer-starter' )
            ),
            array(
                'id' => 'show_subcategories',
                'label' => __( '显示子分类链接', 'developer-starter' ),
                'type' => 'select',
                'options' => array( 'yes' => __( '显示', 'developer-starter' ), 'no' => __( '隐藏', 'developer-starter' ) ),
                'default' => 'yes',
                'description' => __( '自动读取当前选中分类的直接子分类显示在标题右侧', 'developer-starter' )
            ),
            array( 
                'id' => 'post_count', 
                'label' => __( '显示文章数量', 'developer-starter' ), 
                'type' => 'number', 
                'default' => '10' 
            ),
            array( 
                'id' => 'columns', 
                'label' => __( '显示列数', 'developer-starter' ), 
                'type' => 'select', 
                'options' => array( 
                    '4' => __( '4列', 'developer-starter' ),
                    '5' => __( '5列', 'developer-starter' ),
                    '6' => __( '6列', 'developer-starter' )
                ), 
                'default' => '5' 
            ),
            array( 
                'id' => 'image_aspect_ratio', 
                'label' => __( '封面比例', 'developer-starter' ), 
                'type' => 'select', 
                'options' => array( 
                    '16:9' => __( '16:9 (宽屏)', 'developer-starter' ), 
                    '4:3' => __( '4:3 (标准)', 'developer-starter' ), 
                    '3:4' => __( '3:4 (竖屏)', 'developer-starter' ), 
                    '1:1' => __( '1:1 (正方形)', 'developer-starter' ), 
                    '2:3' => __( '2:3 (电影海报)', 'developer-starter' )
                ), 
                'default' => '2:3' 
            ),

            // --- 样式设置 ---
            array(
                'id' => 'bg_color',
                'label' => __( '背景颜色', 'developer-starter' ),
                'type' => 'color',
                'default' => '',
            ),
            array(
                'id' => 'padding_top',
                'label' => __( '上边距', 'developer-starter' ),
                'type' => 'text',
                'default' => '40px',
            ),
            array(
                'id' => 'padding_bottom',
                'label' => __( '下边距', 'developer-starter' ),
                'type' => 'text',
                'default' => '40px',
            ),
        );
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

        $cat_id   = isset( $data['target_category'] ) ? absint( $data['target_category'] ) : 0;
        $category = $cat_id > 0 ? get_term( $cat_id, 'category' ) : null;
        
        if ( ! $cat_id || is_wp_error( $category ) || ! $category instanceof \WP_Term ) {
            if ( $this->is_builder_preview() ) {
                echo '<div class="qmcc-config-notice" role="status"><strong>' . esc_html__( '尚未选择影视分类', 'developer-starter' ) . '</strong><span>' . esc_html__( '请在模块设置中选择该模块要展示的分类。', 'developer-starter' ) . '</span></div>';
            }
            return;
        }

        // 准备数据
        $icon = isset( $data['category_icon'] ) ? $data['category_icon'] : '';
        $title = ! empty( $data['custom_title'] ) ? $data['custom_title'] : $category->name;
        $more_text = ! empty( $data['more_btn_text'] ) ? $data['more_btn_text'] : __( '查看更多', 'developer-starter' );
        $show_sub = isset( $data['show_subcategories'] ) ? $data['show_subcategories'] : 'yes';
        $post_count = isset( $data['post_count'] ) ? intval( $data['post_count'] ) : 10;
        $columns = isset( $data['columns'] ) ? intval( $data['columns'] ) : 5;
        $ratio = isset( $data['image_aspect_ratio'] ) ? $data['image_aspect_ratio'] : '2:3';
        $ratio = isset( $data['image_aspect_ratio'] ) ? $data['image_aspect_ratio'] : '2:3';
        $enable_artplayer = isset( $data['enable_artplayer_data'] ) && $data['enable_artplayer_data'] === 'yes';
        $show_episode_count = isset( $data['show_episode_count'] ) && $data['show_episode_count'] === 'yes';
        
        // 样式
        $bg_color = isset( $data['bg_color'] ) ? $data['bg_color'] : '';
        $pt = isset( $data['padding_top'] ) ? $data['padding_top'] : '40px';
        $pb = isset( $data['padding_bottom'] ) ? $data['padding_bottom'] : '40px';
        
        $style = "padding-top: {$pt}; padding-bottom: {$pb};";
        if ( $bg_color ) {
            $style .= strpos( $bg_color, 'gradient' ) !== false ? "background: {$bg_color};" : "background-color: {$bg_color};";
        }
        $more_btn_border_color = isset( $data['more_btn_border_color'] ) ? $clean_css_color_value( $data['more_btn_border_color'] ) : '';
        $more_btn_hover_border_color = isset( $data['more_btn_hover_border_color'] ) ? $clean_css_color_value( $data['more_btn_hover_border_color'] ) : '';
        if ( '' !== $more_btn_border_color ) {
            $style .= '--qmcc-more-btn-border:' . $more_btn_border_color . ';';
        }
        if ( '' !== $more_btn_hover_border_color ) {
            $style .= '--qmcc-more-btn-hover-border:' . $more_btn_hover_border_color . ';';
        }

        // 获取子分类
        $sub_cats = array();
        if ( $show_sub === 'yes' ) {
            $sub_cats = get_categories( array(
                'parent' => $cat_id,
                'hide_empty' => false, // 即使子分类没文章也显示，为了布局好看
                'number' => 8 // 限制显示数量防止溢出
            ) );
        }

        // 查询文章
        $args = array(
            'cat' => $cat_id,
            'posts_per_page' => $post_count,
            'post_status' => 'publish',
            'ignore_sticky_posts' => true
        );
        $query = \developer_starter_run_cached_query(
            $args,
            'module_main_category_content',
            array(
                'needs_pagination' => false,
            )
        );

        // 模块ID
        $module_uid = 'qmcc-' . $cat_id . '-' . uniqid();

        ?>
        <section class="module qiling-main-cat-module" id="<?php echo esc_attr( $module_uid ); ?>" style="<?php echo esc_attr( $style ); ?>">
            <div class="container">
                
                <!-- 模块头部 -->
                <div class="qmcc-header">
                    <div class="qmcc-header-left">
                        <?php if ( $icon ) : ?>
                            <img src="<?php echo esc_url( $icon ); ?>" class="qmcc-icon" alt="<?php echo esc_attr( $title ); ?>">
                        <?php endif; ?>
                        
                        <h2 class="qmcc-title">
                            <a href="<?php echo esc_url( get_category_link( $cat_id ) ); ?>">
                                <?php echo esc_html( $title ); ?>
                            </a>
                        </h2>

                        <?php if ( ! empty( $sub_cats ) ) : ?>
                            <div class="qmcc-subcats">
                                <?php foreach ( $sub_cats as $sub ) : ?>
                                    <a href="<?php echo esc_url( get_category_link( $sub->term_id ) ); ?>" class="qmcc-subcat-link">
                                        <?php echo esc_html( $sub->name ); ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="qmcc-header-right">
                        <a href="<?php echo esc_url( get_category_link( $cat_id ) ); ?>" class="qmcc-more-btn">
                            <?php echo esc_html( $more_text ); ?>
                        </a>
                    </div>
                </div>

                <!-- 文章网格 -->
                <?php if ( $query->have_posts() ) : ?>
                    <div class="qmcc-grid grid-cols-<?php echo esc_attr( $columns ); ?> ratio-<?php echo esc_attr( str_replace( ':', '-', $ratio ) ); ?>">
                        <?php while ( $query->have_posts() ) : $query->the_post(); 
                            $post_id = get_the_ID();
                            $thumb_url = $this->get_post_thumbnail( $post_id, $enable_artplayer );
                            
                            // 获取启灵播放器元数据 (仅当启用时)
                            $video_meta = null;
                            if ( $enable_artplayer && class_exists( 'ArtPlayer_Video_Frontend' ) ) {
                                // 优先使用预取数据
                                if ( isset( $prefetched_videos[ $post_id ] ) ) {
                                    $video_meta = $prefetched_videos[ $post_id ];
                                } else {
                                    $video_meta = \ArtPlayer_Video_Frontend::get_instance()->get_video_meta_public( $post_id );
        }
    }

                        ?>
                            <article class="qmcc-item">
                                <a href="<?php echo esc_url( get_permalink() ); ?>" class="qmcc-thumb-link" title="<?php the_title_attribute(); ?>">
                                    <div class="qmcc-thumb-wrapper">
                                        <?php if ( $thumb_url ) : ?>
                                            <img src="<?php echo esc_url( $thumb_url ); ?>" alt="<?php the_title_attribute(); ?>" loading="lazy">
                                        <?php endif; ?>
                                        
                                        <?php if ( $enable_artplayer ) : ?>
                                            <!-- 播放按钮 -->
                                            <span class="qmcc-play-btn">
                                                <svg viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>
                                            </span>

                                            <!-- 清晰度/评分 标记 -->
                                            <div class="qmcc-thumb-badges">
                                                <?php 
                                                // 高清类型
                                                $quality = $video_meta ? $video_meta->video_quality : '';
                                                if ( $quality ) echo '<span class="qmcc-badge-hd">' . esc_html( $quality ) . '</span>';
                                                
                                                // 评分
                                                $rating = ($video_meta && $video_meta->rating > 0) ? $video_meta->rating : '';
                                                if ( $rating ) echo '<span class="qmcc-badge-rating">' . esc_html( $rating ) . '</span>';
                                                ?>
                                            </div>

                                            <?php 
                                            // 视频集数 (左下角)
                                            if ( $show_episode_count ) {
                                                $episode_count = $this->get_video_episode_count( $post_id, isset($video_meta) ? $video_meta : null );
                                                if ( $episode_count > 0 ) {
                                                    echo '<span class="qmcc-badge-episode">';
                                                    printf( __( '更新至第<span class="qmcc-badge-count">%s</span>集', 'developer-starter' ), esc_html( $episode_count ) );
                                                    echo '</span>';
                                                }
                                            }
                                            ?>
                                        <?php endif; ?>
                                    </div>
                                    <div class="qmcc-info">
                                        <h3 class="qmcc-post-title"><?php echo esc_html( get_the_title() ); ?></h3>
                                        <div class="qmcc-meta">
                                            <?php 
                                            // 显示第一个子分类，或者标签
                                            $cats = get_the_category();
                                            if ( ! empty( $cats ) ) {
                                                echo '<span class="qmcc-meta-cat">' . esc_html( $cats[0]->name ) . '</span>';
                                            }
                                            ?>
                                            <span class="qmcc-meta-date"><?php echo get_the_date( 'm-d' ); ?></span>
                                        </div>
                                    </div>
                                </a>
                            </article>
                        <?php endwhile; wp_reset_postdata(); ?>
                    </div>
                <?php else : ?>
                    <div class="qmcc-empty"><?php esc_html_e( '暂无内容', 'developer-starter' ); ?></div>
                <?php endif; ?>

            </div>

            <!-- 模块特定样式 -->
</section>
        <?php
    }

    /**
     * Only expose configuration guidance inside an authorized builder preview.
     *
     * @return bool
     */
    private function is_builder_preview() {
        if ( class_exists( '\Developer_Starter\Core\Frontend_Builder' ) && \Developer_Starter\Core\Frontend_Builder::is_builder_mode() ) {
            return true;
        }

        if ( ! wp_doing_ajax() || ! current_user_can( 'manage_options' ) ) {
            return false;
        }

        $action = isset( $_REQUEST['action'] ) ? sanitize_key( wp_unslash( (string) $_REQUEST['action'] ) ) : '';
        return in_array( $action, array( 'qiling_frontend_builder_render_module_preview', 'qiling_frontend_builder_render_preview' ), true );
    }

    /**
     * 获取视频集数
     * 
     * @param int $post_id 文章ID
     * @param object|null $video_meta 可选的ArtPlayer元数据
     * @return int 集数
     */
    private function get_video_episode_count( $post_id, $video_meta = null ) {
        $count = 0;
        $content = get_post_field( 'post_content', $post_id );

        // 1. 尝试从 Gutenberg 块解析
        if ( function_exists( 'parse_blocks' ) && has_block( 'wpartplayer/artplayer', $content ) ) {
            $blocks = parse_blocks( $content );
            foreach ( $blocks as $block ) {
                if ( $block['blockName'] === 'wpartplayer/artplayer' ) {
                    if ( isset( $block['attrs']['url'] ) && ! empty( $block['attrs']['url'] ) ) {
                        // URL 以逗号分隔
                        $urls = explode( ',', $block['attrs']['url'] );
                        $count = count( array_filter( $urls ) ); // 过滤空值
                    }
                    if ( $count > 0 ) return $count; // 找到即返回
                }
            }
        }

        // 2. 尝试从 Shortcode 解析 (正则)
        // 匹配 [artplayer ... url="..."]
        if ( preg_match( '/\[artplayer[^\]]*url=["\']([^"\']+)["\'][^\]]*\]/', $content, $matches ) ) {
            if ( isset( $matches[1] ) ) {
                $urls = explode( ',', $matches[1] );
                $count = count( array_filter( $urls ) );
            }
            if ( $count > 0 ) return $count;
        }

        // 3. 尝试从 ArtPlayer 元数据读取 (如果数据结构支持直接存储URL)
        // 注意：根据之前的代码分析，ArtPlayer_Video_Meta 并没有把 urls 存在 artplayer_video_meta 表中，
        // 而是主要依赖 shortcode 或者 blocks 在内容中。
        // 但是 artplayer_video_downloads 是分开存的，这里不计入播放集数。
        // 不过，有些版本可能把 url 存在 video_meta->video_url 中作为 fallback?
        if ( $video_meta && ! empty( $video_meta->video_url ) ) {
            $urls = explode( ',', $video_meta->video_url );
            $count = count( array_filter( $urls ) );
        }

        return $count;
    }

    /**
     * 获取文章缩略图（支持4种模式 + 启灵播放器封面）
     * 1. 启灵播放器设置的封面
     * 2. 自定义特色图URL
     * 3. WP默认特色图
     * 4. 文章第一张图片
     * 5. 默认占位图
     */
    private function get_post_thumbnail( $post_id, $check_artplayer = false ) {
        // 0. 尝试获取启灵播放器封面 (仅当启用检测时)
        if ( $check_artplayer && class_exists( 'ArtPlayer_Video_Frontend' ) ) {
            $video_meta = \ArtPlayer_Video_Frontend::get_instance()->get_video_meta_public( $post_id );
            if ( $video_meta && ! empty( $video_meta->cover_image ) ) {
                return $video_meta->cover_image;
            }
        }

        if ( function_exists( 'developer_starter_qiapp_get_screenshot_url' ) ) {
            $software_screenshot = developer_starter_qiapp_get_screenshot_url( $post_id );
            if ( $software_screenshot ) {
                return $software_screenshot;
            }
        }

        // 1. 检查自定义特色图URL
        $custom_url = get_post_meta( $post_id, '_developer_starter_featured_image_url', true );
        if ( ! empty( $custom_url ) ) {
            if ( function_exists( 'developer_starter_get_custom_featured_image_url' ) ) {
                return developer_starter_get_custom_featured_image_url( $post_id );
            }

            return $custom_url;
        }

        // 2. WP默认特色图
        if ( function_exists( 'developer_starter_get_thumbnail_url' ) ) {
            $thumbnail_url = developer_starter_get_thumbnail_url( $post_id, 'medium_large' );
            if ( ! empty( $thumbnail_url ) ) {
                return $thumbnail_url;
            }
        }

        if ( function_exists( 'developer_starter_get_featured_image_url' ) ) {
            $thumbnail_url = developer_starter_get_featured_image_url( $post_id, 'medium_large' );
            if ( ! empty( $thumbnail_url ) ) {
                return $thumbnail_url;
            }
        }

        if ( has_post_thumbnail( $post_id ) ) {
            return get_the_post_thumbnail_url( $post_id, 'medium_large' );
        }

        // 3. 获取文章第一张图片
        if ( function_exists( 'developer_starter_get_first_image' ) ) {
            $first_image = developer_starter_get_first_image( $post_id );
            if ( ! empty( $first_image ) ) {
                return $first_image;
            }
        }

        // 4. 默认占位图
        if ( function_exists( 'developer_starter_get_option' ) ) {
            return developer_starter_get_option( 'default_thumbnail', '' );
        }

        return '';
    }
}
