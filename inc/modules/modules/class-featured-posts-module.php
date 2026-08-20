<?php
/**
 * Featured Posts Module - 博客置顶推荐模块
 *
 * 支持轮播滚动文章展示，用于博客顶部运营引导
 *
 * @package Developer_Starter
 * @since 1.0.0
 */

namespace Developer_Starter\Modules\Modules;

use Developer_Starter\Modules\Module_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Featured_Posts_Module extends Module_Base {

    public function __construct() {
        $this->category = 'content';
        $this->icon = 'dashicons-star-filled';
        $this->description = __( '博客置顶推荐，支持轮播和文章列表展示', 'developer-starter' );
    }

    public function get_id() {
        return 'featured_posts';
    }

    public function get_name() {
        return __( '博客置顶推荐', 'developer-starter' );
    }

    public function get_fields() {
        return array(
            // === 基础配置 ===
            array(
                'id'      => 'fp_title',
                'type'    => 'text',
                'label'   => __( '模块标题 (可选)', 'developer-starter' ),
            ),
            array(
                'id'      => 'fp_bg_color',
                'type'    => 'text',
                'label'   => __( '背景颜色', 'developer-starter' ),
                'default' => '',
            ),
            array(
                'id'      => 'fp_title_color',
                'type'    => 'color',
                'label'   => __( '文章标题颜色', 'developer-starter' ),
                'default' => '',
            ),
            array(
                'id'      => 'fp_padding_top',
                'type'    => 'select',
                'label'   => __( '顶部间距', 'developer-starter' ),
                'options' => array(
                    'default' => __( '默认 (80px)', 'developer-starter' ),
                    'none'    => __( '无 (0px)', 'developer-starter' ),
                    'small'   => __( '小 (40px)', 'developer-starter' ),
                    'large'   => __( '大 (120px)', 'developer-starter' ),
                ),
                'default' => 'default',
            ),
            array(
                'id'      => 'fp_padding_bottom',
                'type'    => 'select',
                'label'   => __( '底部间距', 'developer-starter' ),
                'options' => array(
                    'default' => __( '默认 (80px)', 'developer-starter' ),
                    'none'    => __( '无 (0px)', 'developer-starter' ),
                    'small'   => __( '小 (40px)', 'developer-starter' ),
                    'large'   => __( '大 (120px)', 'developer-starter' ),
                ),
                'default' => 'default',
            ),
            
            // === 布局配置 ===
            array(
                'id'      => 'fp_layout',
                'type'    => 'select',
                'label'   => __( '布局模式', 'developer-starter' ),
                'options' => array(
                    'full' => __( '全宽轮播', 'developer-starter' ),
                    'dual' => __( '左侧轮播 + 右侧列表', 'developer-starter' ),
                    'magazine' => __( '杂志头条流（推荐）', 'developer-starter' ),
                ),
                'default' => 'full',
            ),
            array(
                'id'      => 'fp_slider_ratio',
                'type'    => 'text',
                'label'   => __( '轮播区域宽度占比 (双栏/杂志模式, 0-100)', 'developer-starter' ),
                'default' => '65',
            ),
            array(
                'id'      => 'fp_slider_height',
                'type'    => 'text',
                'label'   => __( '轮播区域高度', 'developer-starter' ),
                'default' => '320px',
            ),
            
            // === 轮播设置 ===
            array(
                'id'      => 'fp_autoplay',
                'type'    => 'select',
                'label'   => __( '自动播放', 'developer-starter' ),
                'options' => array(
                    'yes' => __( '是', 'developer-starter' ),
                    'no'  => __( '否', 'developer-starter' ),
                ),
                'default' => 'no',
            ),
            array(
                'id'      => 'fp_interval',
                'type'    => 'number',
                'label'   => __( '轮播间隔 (毫秒)', 'developer-starter' ),
                'default' => '5000',
            ),
            array(
                'id'      => 'fp_effect',
                'type'    => 'select',
                'label'   => __( '切换效果', 'developer-starter' ),
                'options' => array(
                    'slide' => __( '滑动', 'developer-starter' ),
                    'fade'  => __( '淡入淡出', 'developer-starter' ),
                ),
                'default' => 'slide',
            ),
            array(
                'id'      => 'fp_show_arrows',
                'type'    => 'select',
                'label'   => __( '显示箭头', 'developer-starter' ),
                'options' => array(
                    'yes' => __( '是', 'developer-starter' ),
                    'no'  => __( '否', 'developer-starter' ),
                ),
                'default' => 'yes',
            ),
            array(
                'id'      => 'fp_show_dots',
                'type'    => 'select',
                'label'   => __( '显示导航点', 'developer-starter' ),
                'options' => array(
                    'yes' => __( '是', 'developer-starter' ),
                    'no'  => __( '否', 'developer-starter' ),
                ),
                'default' => 'yes',
            ),
            
            // === 轮播数据源 ===
            array(
                'id'      => 'fp_slider_source',
                'type'    => 'select',
                'label'   => __( '轮播内容来源', 'developer-starter' ),
                'options' => array(
                    'latest'   => __( '最新发布', 'developer-starter' ),
                    'popular'  => __( '热门浏览 (需插件支持)', 'developer-starter' ),
                    'comment'  => __( '热评文章', 'developer-starter' ),
                    'random'   => __( '随机推荐', 'developer-starter' ),
                    'category' => __( '指定分类', 'developer-starter' ),
                    'tag'      => __( '指定标签', 'developer-starter' ),
                    'manual'   => __( '手动指定ID', 'developer-starter' ),
                ),
                'default' => 'latest',
            ),
            array(
                'id'      => 'fp_slider_category',
                'type'    => 'text',
                'label'   => __( '轮播分类ID (多个用逗号分隔)', 'developer-starter' ),
                'desc'    => __( '仅在来源为"指定分类"时有效', 'developer-starter' ),
            ),
            array(
                'id'      => 'fp_slider_ids',
                'type'    => 'text',
                'label'   => __( '轮播文章ID (多个用逗号分隔)', 'developer-starter' ),
                'desc'    => __( '仅在来源为"手动指定ID"时有效', 'developer-starter' ),
            ),
            array(
                'id'      => 'fp_slider_tag',
                'type'    => 'text',
                'label'   => __( '轮播标签ID/Slug (多个用逗号分隔)', 'developer-starter' ),
                'desc'    => __( '仅在来源为"指定标签"时有效', 'developer-starter' ),
            ),
            array(
                'id'      => 'fp_slider_count',
                'type'    => 'number',
                'label'   => __( '轮播文章数量', 'developer-starter' ),
                'default' => '5',
            ),
            
            // === 列表数据源 (双栏模式) ===
            array(
                'id'      => 'fp_list_source',
                'type'    => 'select',
                'label'   => __( '右侧列表来源', 'developer-starter' ),
                'options' => array(
                    'latest'   => __( '最新发布', 'developer-starter' ),
                    'popular'  => __( '热门浏览', 'developer-starter' ),
                    'comment'  => __( '热评文章', 'developer-starter' ),
                    'random'   => __( '随机推荐', 'developer-starter' ),
                    'category' => __( '指定分类', 'developer-starter' ),
                    'tag'      => __( '指定标签', 'developer-starter' ),
                    'manual'   => __( '手动指定ID', 'developer-starter' ),
                ),
                'default' => 'latest',
            ),
            array(
                'id'      => 'fp_list_category',
                'type'    => 'text',
                'label'   => __( '列表分类ID', 'developer-starter' ),
            ),
            array(
                'id'      => 'fp_list_ids',
                'type'    => 'text',
                'label'   => __( '列表文章ID', 'developer-starter' ),
            ),
            array(
                'id'      => 'fp_list_tag',
                'type'    => 'text',
                'label'   => __( '列表标签ID/Slug', 'developer-starter' ),
            ),
            array(
                'id'      => 'fp_list_count',
                'type'    => 'number',
                'label'   => __( '列表文章数量', 'developer-starter' ),
                'default' => '4',
            ),
            array(
                'id'      => 'fp_deduplicate',
                'type'    => 'select',
                'label'   => __( '列表去重（排除已在轮播出现文章）', 'developer-starter' ),
                'options' => array(
                    'yes' => __( '是', 'developer-starter' ),
                    'no'  => __( '否', 'developer-starter' ),
                ),
                'default' => 'yes',
            ),
            
            // === 显示选项 ===
            array(
                'id'      => 'fp_badge_type',
                'type'    => 'select',
                'label'   => __( '文章角标类型', 'developer-starter' ),
                'options' => array(
                    'none'      => __( '不显示', 'developer-starter' ),
                    'recommend' => __( '推荐', 'developer-starter' ),
                    'hot'       => __( '热门', 'developer-starter' ),
                    'featured'  => __( '精选', 'developer-starter' ),
                    'top'       => __( '置顶', 'developer-starter' ),
                    'custom'    => __( '自定义文字', 'developer-starter' ),
                ),
                'default' => 'none',
            ),
            array(
                'id'      => 'fp_badge_text',
                'type'    => 'text',
                'label'   => __( '自定义角标文字', 'developer-starter' ),
                'desc'    => __( '仅当角标类型为"自定义文字"时有效', 'developer-starter' ),
            ),
            array(
                'id'      => 'fp_badge_color',
                'type'    => 'color',
                'label'   => __( '角标背景颜色', 'developer-starter' ),
                'default' => '',
                'desc'    => __( '留空时跟随全局徽章颜色。', 'developer-starter' ),
            ),
            array(
                'id'      => 'fp_badge_position',
                'type'    => 'select',
                'label'   => __( '角标位置', 'developer-starter' ),
                'options' => array(
                    'left'  => __( '左上角', 'developer-starter' ),
                    'right' => __( '右上角', 'developer-starter' ),
                ),
                'default' => 'left',
            ),
            array(
                'id'      => 'fp_show_category',
                'type'    => 'select',
                'label'   => __( '显示分类', 'developer-starter' ),
                'options' => array( 'yes' => __( '是', 'developer-starter' ), 'no' => __( '否', 'developer-starter' ) ),
                'default' => 'yes',
            ),
            array(
                'id'      => 'fp_show_author',
                'type'    => 'select',
                'label'   => __( '显示作者', 'developer-starter' ),
                'options' => array( 'yes' => __( '是', 'developer-starter' ), 'no' => __( '否', 'developer-starter' ) ),
                'default' => 'yes',
            ),
            array(
                'id'      => 'fp_show_date',
                'type'    => 'select',
                'label'   => __( '显示日期', 'developer-starter' ),
                'options' => array( 'yes' => __( '是', 'developer-starter' ), 'no' => __( '否', 'developer-starter' ) ),
                'default' => 'yes',
            ),
            array(
                'id'      => 'fp_show_excerpt',
                'type'    => 'select',
                'label'   => __( '显示摘要', 'developer-starter' ),
                'options' => array( 'yes' => __( '是', 'developer-starter' ), 'no' => __( '否', 'developer-starter' ) ),
                'default' => 'yes',
            ),
            array(
                'id'      => 'fp_show_views',
                'type'    => 'select',
                'label'   => __( '显示浏览量', 'developer-starter' ),
                'options' => array( 'yes' => __( '是', 'developer-starter' ), 'no' => __( '否', 'developer-starter' ) ),
                'default' => 'no',
            ),
            array(
                'id'      => 'fp_show_reading_time',
                'type'    => 'select',
                'label'   => __( '显示预计阅读时长', 'developer-starter' ),
                'options' => array( 'yes' => __( '是', 'developer-starter' ), 'no' => __( '否', 'developer-starter' ) ),
                'default' => 'no',
            ),
        );
    }

    public function render( $data = array() ) {
        // 基础配置
        $title = isset( $data['fp_title'] ) ? $data['fp_title'] : '';
        $bg_color = isset( $data['fp_bg_color'] ) ? $data['fp_bg_color'] : '';
        $title_color = isset( $data['fp_title_color'] ) ? $data['fp_title_color'] : '';
        
        // 布局配置
        $layout = isset( $data['fp_layout'] ) ? $data['fp_layout'] : 'full'; // full | dual | magazine
        $slider_ratio = isset( $data['fp_slider_ratio'] ) ? $data['fp_slider_ratio'] : '65'; // 轮播区域占比
        $is_split_layout = in_array( $layout, array( 'dual', 'magazine' ), true );
        
        // 轮播配置
        $autoplay = isset( $data['fp_autoplay'] ) && $data['fp_autoplay'] === 'yes';
        $interval = isset( $data['fp_interval'] ) && $data['fp_interval'] !== '' ? intval( $data['fp_interval'] ) : 5000;
        $effect = isset( $data['fp_effect'] ) ? $data['fp_effect'] : 'slide'; // slide | fade
        $show_arrows = isset( $data['fp_show_arrows'] ) && $data['fp_show_arrows'] === 'yes';
        $show_dots = isset( $data['fp_show_dots'] ) && $data['fp_show_dots'] === 'yes';
        $slider_height = isset( $data['fp_slider_height'] ) && $data['fp_slider_height'] !== '' ? $data['fp_slider_height'] : '320px';
        
        // 数据来源 - 轮播
        $slider_source = isset( $data['fp_slider_source'] ) ? $data['fp_slider_source'] : 'latest';
        $slider_ids = isset( $data['fp_slider_ids'] ) ? $data['fp_slider_ids'] : '';
        $slider_category = isset( $data['fp_slider_category'] ) ? $data['fp_slider_category'] : '';
        $slider_tag = isset( $data['fp_slider_tag'] ) ? $data['fp_slider_tag'] : '';
        $slider_count = isset( $data['fp_slider_count'] ) && $data['fp_slider_count'] !== '' ? intval( $data['fp_slider_count'] ) : 5;
        
        // 数据来源 - 右侧列表
        $list_source = isset( $data['fp_list_source'] ) ? $data['fp_list_source'] : 'latest';
        $list_ids = isset( $data['fp_list_ids'] ) ? $data['fp_list_ids'] : '';
        $list_category = isset( $data['fp_list_category'] ) ? $data['fp_list_category'] : '';
        $list_tag = isset( $data['fp_list_tag'] ) ? $data['fp_list_tag'] : '';
        $list_count = isset( $data['fp_list_count'] ) && $data['fp_list_count'] !== '' ? intval( $data['fp_list_count'] ) : 4;
        $deduplicate = ! isset( $data['fp_deduplicate'] ) || $data['fp_deduplicate'] === 'yes';
        
        // 角标配置
        $badge_type = isset( $data['fp_badge_type'] ) ? $data['fp_badge_type'] : 'none'; // none | recommend | hot | featured | top | custom
        $badge_text = isset( $data['fp_badge_text'] ) ? $data['fp_badge_text'] : '';
        $badge_position = isset( $data['fp_badge_position'] ) ? $data['fp_badge_position'] : 'left'; // left | right
        $badge_color = isset( $data['fp_badge_color'] ) ? $data['fp_badge_color'] : '';
        
        // 显示控制
        $show_category = isset( $data['fp_show_category'] ) && $data['fp_show_category'] === 'yes';
        $show_author = isset( $data['fp_show_author'] ) && $data['fp_show_author'] === 'yes';
        $show_date = isset( $data['fp_show_date'] ) && $data['fp_show_date'] === 'yes';
        $show_excerpt = isset( $data['fp_show_excerpt'] ) && $data['fp_show_excerpt'] === 'yes';
        $show_views = isset( $data['fp_show_views'] ) && $data['fp_show_views'] === 'yes';
        $show_reading_time = isset( $data['fp_show_reading_time'] ) && $data['fp_show_reading_time'] === 'yes';
        
        // 获取轮播文章
        $slider_posts = $this->get_posts( $slider_source, $slider_ids, $slider_category, $slider_tag, $slider_count );
        
        // 获取列表文章（双栏/杂志布局时）
        $list_posts = array();
        if ( $is_split_layout ) {
            $exclude_ids = array();
            if ( $deduplicate && ! empty( $slider_posts ) ) {
                $exclude_ids = wp_list_pluck( $slider_posts, 'ID' );
            }
            $list_posts = $this->get_posts( $list_source, $list_ids, $list_category, $list_tag, $list_count, $exclude_ids );
        }
        
        $module_id = 'featured-posts-' . uniqid();
        
        // 背景样式
        $section_style = '';
        if ( ! empty( $bg_color ) ) {
            if ( strpos( $bg_color, 'gradient' ) !== false ) {
                $section_style = 'background: ' . $bg_color . ';';
            } else {
                $section_style = 'background-color: ' . $bg_color . ';';
            }
        }
        
        // 间距控制
        $padding_top = isset( $data['fp_padding_top'] ) ? $data['fp_padding_top'] : 'default';
        $padding_bottom = isset( $data['fp_padding_bottom'] ) ? $data['fp_padding_bottom'] : 'default';
        
        $padding_styles = array(
            'none' => '0',
            'small' => '40px',
            'large' => '120px',
            'default' => ''
        );
        
        $pt_style = isset($padding_styles[$padding_top]) ? $padding_styles[$padding_top] : '';
        $pb_style = isset($padding_styles[$padding_bottom]) ? $padding_styles[$padding_bottom] : '';
        
        if ( $pt_style !== '' ) {
            $section_style .= ' padding-top: ' . $pt_style . ';';
        }
        if ( $pb_style !== '' ) {
            $section_style .= ' padding-bottom: ' . $pb_style . ';';
        }

        $post_title_style_attr = '';
        if ( '' !== $title_color ) {
            $post_title_style_attr = sprintf(
                ' style="%s"',
                esc_attr( 'color: ' . $title_color . ';' )
            );
        }
        
        // 角标文字
        $badge_labels = array(
            'recommend' => __( '推荐', 'developer-starter' ),
            'hot' => __( '热门', 'developer-starter' ),
            'featured' => __( '精选', 'developer-starter' ),
            'top' => __( '置顶', 'developer-starter' ),
            'custom' => $badge_text,
        );
        $badge_label = isset( $badge_labels[ $badge_type ] ) ? $badge_labels[ $badge_type ] : '';
        
        ?>
        <section class="module module-featured-posts section-padding" id="<?php echo esc_attr( $module_id ); ?>" <?php echo $section_style ? 'style="' . esc_attr( $section_style ) . '"' : ''; ?>>
            <div class="container">
                <?php if ( $title ) : ?>
                    <div class="section-header" style="margin-bottom: var(--qiling-space-24);">
                        <h2 class="section-title" style="font-size: var(--qiling-text-rem-1p5); font-weight: 700; margin: 0;"><?php echo esc_html( $title ); ?></h2>
                    </div>
                <?php endif; ?>
                
                <div class="fp-wrapper fp-layout-<?php echo esc_attr( $layout ); ?>" style="--qiling-fp-slider-height: <?php echo esc_attr( $slider_height ); ?>;">
                    <!-- 轮播区域 -->
                    <div class="fp-slider-wrapper" style="<?php echo $is_split_layout ? 'width: ' . esc_attr( $slider_ratio ) . '%;' : ''; ?>">
                        <?php if ( ! empty( $slider_posts ) ) : ?>
                        <div class="fp-slider" data-autoplay="<?php echo $autoplay ? 'true' : 'false'; ?>" data-interval="<?php echo esc_attr( $interval ); ?>" data-effect="<?php echo esc_attr( $effect ); ?>" style="height: <?php echo esc_attr( $slider_height ); ?>;">
                            <?php foreach ( $slider_posts as $index => $post ) : 
                                $image = $this->get_post_image( $post->ID );
                                $categories = get_the_category( $post->ID );
                                $cat = ! empty( $categories ) ? $categories[0] : null;
                            ?>
                                <div class="fp-slide <?php echo $index === 0 ? 'active' : ''; ?>" data-index="<?php echo $index; ?>">
                                    <a href="<?php echo esc_url( get_permalink( $post->ID ) ); ?>" class="fp-slide-link">
                                        <div class="fp-slide-image" style="background-image: url('<?php echo esc_url( $image ); ?>');"></div>
                                        <div class="fp-slide-overlay"></div>
                                        <div class="fp-slide-content">
                                            <?php if ( $badge_type !== 'none' && $badge_label ) : ?>
                                                <span class="fp-badge fp-badge-<?php echo esc_attr( $badge_position ); ?>" <?php echo $badge_color ? 'style="background:' . esc_attr( $badge_color ) . ';"' : ''; ?>>
                                                    <?php echo esc_html( $badge_label ); ?>
                                                </span>
                                            <?php endif; ?>
                                            
                                            <div class="fp-slide-meta">
                                                <?php if ( $show_category && $cat ) : ?>
                                                    <span class="fp-category"><?php echo esc_html( $cat->name ); ?></span>
                                                <?php endif; ?>
                                                <?php if ( $show_date ) : ?>
                                                    <span class="fp-date"><?php echo get_the_date( '', $post->ID ); ?></span>
                                                <?php endif; ?>
                                                <?php if ( $show_views ) : ?>
                                                    <span class="fp-views"><?php echo esc_html( $this->get_post_views_text( $post->ID ) ); ?></span>
                                                <?php endif; ?>
                                                <?php if ( $show_reading_time ) : ?>
                                                    <span class="fp-reading-time"><?php echo esc_html( $this->get_post_reading_time_text( $post->ID ) ); ?></span>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <h3 class="fp-slide-title"<?php echo $post_title_style_attr; ?>><?php echo esc_html( $post->post_title ); ?></h3>
                                            
                                            <?php if ( $show_excerpt ) : ?>
                                                <p class="fp-slide-excerpt"><?php echo wp_trim_words( get_the_excerpt( $post->ID ), 20 ); ?></p>
                                            <?php endif; ?>
                                            
                                            <?php if ( $show_author ) : ?>
                                                <div class="fp-author">
                                                    <img src="<?php echo esc_url( get_avatar_url( $post->post_author, array( 'size' => 32 ) ) ); ?>" alt="" class="fp-author-avatar">
                                                    <span class="fp-author-name"><?php echo esc_html( get_the_author_meta( 'display_name', $post->post_author ) ); ?></span>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                            
                            <?php if ( $show_arrows && count( $slider_posts ) > 1 ) : ?>
                                <button class="fp-arrow fp-arrow-prev" aria-label="<?php esc_attr_e( '上一张', 'developer-starter' ); ?>">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"></polyline></svg>
                                </button>
                                <button class="fp-arrow fp-arrow-next" aria-label="<?php esc_attr_e( '下一张', 'developer-starter' ); ?>">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"></polyline></svg>
                                </button>
                            <?php endif; ?>
                            
                            <?php if ( $show_dots && count( $slider_posts ) > 1 ) : ?>
                                <div class="fp-dots">
                                    <?php for ( $i = 0; $i < count( $slider_posts ); $i++ ) : ?>
                                        <button class="fp-dot <?php echo $i === 0 ? 'active' : ''; ?>" data-index="<?php echo $i; ?>"></button>
                                    <?php endfor; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <?php else : ?>
                        <!-- 空轮播提示 -->
                        <div class="fp-empty" style="height: <?php echo esc_attr( $slider_height ); ?>; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, var(--color-primary) 0%, var(--qiling-color-764ba2) 100%); border-radius: 16px; color: var(--color-neutral-0);">
                            <div style="text-align: center;">
                                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="opacity: 0.8; margin-bottom: var(--qiling-space-12);"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="9" y1="21" x2="9" y2="9"/></svg>
                                <p style="margin: 0; font-size: var(--qiling-text-rem-0p875); opacity: 0.9;"><?php esc_html_e( '暂无轮播文章，请在后台配置数据来源', 'developer-starter' ); ?></p>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ( $is_split_layout && ! empty( $list_posts ) ) : ?>
                    <!-- 右侧文章列表 -->
                    <div class="fp-list-wrapper" style="width: <?php echo 100 - intval( $slider_ratio ); ?>%;">
                        <div class="fp-list">
                            <?php foreach ( $list_posts as $index => $post ) : 
                                $image = $this->get_post_image( $post->ID );
                                $categories = get_the_category( $post->ID );
                                $cat = ! empty( $categories ) ? $categories[0] : null;
                            ?>
                                <a href="<?php echo esc_url( get_permalink( $post->ID ) ); ?>" class="fp-list-item <?php echo $index === 0 ? 'fp-list-item-featured' : ''; ?>">
                                    <div class="fp-list-image" style="background-image: url('<?php echo esc_url( $image ); ?>');"></div>
                                    <div class="fp-list-overlay"></div>
                                    <div class="fp-list-content">
                                        <?php if ( $show_category && $cat && $index === 0 ) : ?>
                                            <span class="fp-list-category"><?php echo esc_html( $cat->name ); ?></span>
                                        <?php endif; ?>
                                        <h4 class="fp-list-title"<?php echo $post_title_style_attr; ?>><?php echo esc_html( $post->post_title ); ?></h4>
                                        <?php if ( ( $show_date || $show_views || $show_reading_time ) && $index === 0 ) : ?>
                                            <span class="fp-list-date">
                                                <?php if ( $show_date ) : ?>
                                                    <?php echo esc_html( get_the_date( '', $post->ID ) ); ?>
                                                <?php endif; ?>
                                                <?php if ( $show_views ) : ?>
                                                    · <?php echo esc_html( $this->get_post_views_text( $post->ID ) ); ?>
                                                <?php endif; ?>
                                                <?php if ( $show_reading_time ) : ?>
                                                    · <?php echo esc_html( $this->get_post_reading_time_text( $post->ID ) ); ?>
                                                <?php endif; ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            

            
            <script>
            (function(){
                var module = document.getElementById('<?php echo esc_js( $module_id ); ?>');
                if (!module) return;
                
                var slider = module.querySelector('.fp-slider');
                if (!slider) return;
                
                var slides = slider.querySelectorAll('.fp-slide');
                var dots = slider.querySelectorAll('.fp-dot');
                var prevBtn = slider.querySelector('.fp-arrow-prev');
                var nextBtn = slider.querySelector('.fp-arrow-next');
                
                if (slides.length <= 1) return;
                
                var currentIndex = 0;
                var autoplayEnabled = slider.dataset.autoplay === 'true';
                var interval = parseInt(slider.dataset.interval) || 5000;
                var autoplayTimer = null;
                
                function showSlide(index) {
                    if (index < 0) index = slides.length - 1;
                    if (index >= slides.length) index = 0;
                    
                    slides.forEach(function(slide, i) {
                        slide.classList.toggle('active', i === index);
                    });
                    
                    dots.forEach(function(dot, i) {
                        dot.classList.toggle('active', i === index);
                    });
                    
                    currentIndex = index;
                }
                
                function nextSlide() {
                    showSlide(currentIndex + 1);
                }
                
                function prevSlide() {
                    showSlide(currentIndex - 1);
                }
                
                function startAutoplay() {
                    if (autoplayEnabled && !autoplayTimer) {
                        autoplayTimer = setInterval(function() {
                            if (!document.body.contains(slider)) {
                                stopAutoplay();
                                return;
                            }
                            nextSlide();
                        }, interval);
                    }
                }
                
                function stopAutoplay() {
                    if (autoplayTimer) {
                        clearInterval(autoplayTimer);
                        autoplayTimer = null;
                    }
                }
                
                // 绑定事件
                if (prevBtn) {
                    prevBtn.addEventListener('click', function(e) {
                        e.preventDefault();
                        stopAutoplay();
                        prevSlide();
                        startAutoplay();
                    });
                }
                
                if (nextBtn) {
                    nextBtn.addEventListener('click', function(e) {
                        e.preventDefault();
                        stopAutoplay();
                        nextSlide();
                        startAutoplay();
                    });
                }
                
                dots.forEach(function(dot) {
                    dot.addEventListener('click', function(e) {
                        e.preventDefault();
                        stopAutoplay();
                        showSlide(parseInt(this.dataset.index));
                        startAutoplay();
                    });
                });
                
                // 鼠标悬停暂停
                slider.addEventListener('mouseenter', stopAutoplay);
                slider.addEventListener('mouseleave', startAutoplay);
                
                // 触摸滑动支持
                var touchStartX = 0;
                var touchEndX = 0;
                
                slider.addEventListener('touchstart', function(e) {
                    touchStartX = e.changedTouches[0].screenX;
                    stopAutoplay();
                }, { passive: true });
                
                slider.addEventListener('touchend', function(e) {
                    touchEndX = e.changedTouches[0].screenX;
                    var diff = touchStartX - touchEndX;
                    if (Math.abs(diff) > 50) {
                        if (diff > 0) {
                            nextSlide();
                        } else {
                            prevSlide();
                        }
                    }
                    startAutoplay();
                }, { passive: true });
                
                // 启动自动播放
                startAutoplay();
            })();
            </script>
        </section>
        <?php
    }
    
    /**
     * 获取文章列表
     */
    private function get_posts( $source, $ids = '', $category = '', $tag = '', $count = 5, $exclude_ids = array() ) {
        $args = array(
            'post_type'      => 'post',
            'posts_per_page' => $count,
            'post_status'    => 'publish',
            'ignore_sticky_posts' => true,
        );

        if ( ! empty( $exclude_ids ) && is_array( $exclude_ids ) ) {
            $args['post__not_in'] = array_map( 'intval', $exclude_ids );
        }
        
        switch ( $source ) {
            case 'manual':
                if ( ! empty( $ids ) ) {
                    $post_ids = array_map( 'intval', array_filter( explode( ',', $ids ) ) );
                    if ( ! empty( $post_ids ) ) {
                        $args['post__in'] = $post_ids;
                        $args['orderby'] = 'post__in';
                    } else {
                        // 如果没有有效的ID，回退到最新文章
                        $args['orderby'] = 'date';
                        $args['order'] = 'DESC';
                    }
                } else {
                    // 如果没有提供ID，回退到最新文章
                    $args['orderby'] = 'date';
                    $args['order'] = 'DESC';
                }
                break;
                
            case 'random':
                $args['orderby'] = 'rand';
                break;
                
            case 'popular':
                $args['meta_key'] = 'ds_post_views_count';
                $args['orderby'] = 'meta_value_num';
                $args['order'] = 'DESC';
                break;
                
            case 'comment':
                $args['orderby'] = 'comment_count';
                $args['order'] = 'DESC';
                break;
                
            case 'category':
                if ( ! empty( $category ) ) {
                    $cat_ids = array_map( 'intval', array_filter( explode( ',', $category ) ) );
                    if ( ! empty( $cat_ids ) ) {
                        $args['category__in'] = $cat_ids;
                    }
                }
                $args['orderby'] = 'date';
                $args['order'] = 'DESC';
                break;

            case 'tag':
                if ( ! empty( $tag ) ) {
                    $tag_list = array_map( 'trim', explode( ',', $tag ) );
                    $tag_ids = array();
                    $tag_slugs = array();
                    foreach ( $tag_list as $tag_item ) {
                        if ( $tag_item === '' ) {
                            continue;
                        }
                        if ( ctype_digit( $tag_item ) ) {
                            $tag_ids[] = (int) $tag_item;
                        } else {
                            $tag_slugs[] = sanitize_title( $tag_item );
                        }
                    }
                    if ( ! empty( $tag_ids ) ) {
                        $args['tag__in'] = $tag_ids;
                    } elseif ( ! empty( $tag_slugs ) ) {
                        $args['tag_slug__in'] = $tag_slugs;
                    }
                }
                $args['orderby'] = 'date';
                $args['order'] = 'DESC';
                break;
                
            case 'latest':
            default:
                $args['orderby'] = 'date';
                $args['order'] = 'DESC';
                break;
        }
        
        $query = \developer_starter_run_cached_query(
            $args,
            'module_featured_posts',
            array(
                'needs_pagination' => false,
            )
        );
        return $query->posts;
    }

    /**
     * 获取浏览量文案
     */
    private function get_post_views_text( $post_id ) {
        $views = 0;
        if ( class_exists( '\Developer_Starter\Core\Post_Enhancer' ) ) {
            $views = \Developer_Starter\Core\Post_Enhancer::get_post_views( $post_id );
        }
        return sprintf( __( '%s 次浏览', 'developer-starter' ), number_format_i18n( (int) $views ) );
    }

    /**
     * 获取预计阅读时长文案
     */
    private function get_post_reading_time_text( $post_id ) {
        $minutes = 1;
        if ( class_exists( '\Developer_Starter\Core\Post_Enhancer' ) ) {
            $minutes = \Developer_Starter\Core\Post_Enhancer::get_reading_time( $post_id );
        }
        return sprintf( __( '%d 分钟阅读', 'developer-starter' ), max( 1, (int) $minutes ) );
    }
    
    /**
     * 获取文章图片
     */
    private function get_post_image( $post_id ) {
        // 优先使用主题的缩略图优化函数
        if ( function_exists( 'developer_starter_get_thumbnail_url' ) ) {
            $image = developer_starter_get_thumbnail_url( $post_id, 'medium' );
            if ( $image ) {
                return $image;
            }
        }
        
        // 回退到特色图片
        if ( function_exists( 'developer_starter_get_featured_image_url' ) ) {
            $image = developer_starter_get_featured_image_url( $post_id, 'large' );
        } else {
            $image = get_the_post_thumbnail_url( $post_id, 'large' );
        }
        
        // 如果没有特色图片，尝试获取文章中的第一张图片
        if ( ! $image ) {
            $post = get_post( $post_id );
            if ( $post && preg_match( '/<img[^>]+src=["\']([^"\']+)["\']/', $post->post_content, $matches ) ) {
                $image = $matches[1];
            }
        }
        
        // 如果还是没有图片，使用占位图
        if ( ! $image ) {
            $placeholder_text = esc_html__( 'No Image', 'developer-starter' );
            $svg = sprintf(
                '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 800 400" fill="var(--color-neutral-200)"><rect width="800" height="400"/><text x="50%%" y="50%%" text-anchor="middle" dy=".3em" fill="var(--color-neutral-400)" font-family="sans-serif" font-size="24">%s</text></svg>',
                $placeholder_text
            );
            $image = 'data:image/svg+xml,' . rawurlencode( $svg );
        }
        
        return $image;
    }
}
