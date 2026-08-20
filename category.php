<?php
/**
 * 分类归档页面模板
 * 
 * 支持自定义布局、背景色、图标等设置
 * 设置可在 WordPress 后台 → 文章 → 分类目录 → 编辑分类 中配置
 *
 * @package Developer_Starter
 */

// 获取当前分类信息
$category = get_queried_object();
$cat_id = $category->term_id;

// 获取分类设置（从分类元数据获取，每个分类可独立配置）
$settings = Developer_Starter\Core\Category_Manager::get_category_settings( $cat_id );

$blog_visual_manager_available = class_exists( '\Developer_Starter\Core\Blog_Visual_Manager' );
$resolved_archive_settings = $blog_visual_manager_available
    ? \Developer_Starter\Core\Blog_Visual_Manager::resolve_category_archive_settings( $cat_id, $settings )
    : array(
        'preset'                  => 'default',
        'layout'                  => 'card',
        'columns'                 => 3,
        'posts_per_page'          => 0,
        'thumb_height'            => 200,
        'excerpt_length'          => 40,
        'hide_thumb'              => false,
        'hide_excerpt'            => false,
        'hide_date'               => false,
        'hide_category'           => false,
        'hide_author'             => false,
        'hide_breadcrumb'         => false,
        'has_custom_layout'       => false,
        'has_custom_columns'      => false,
        'has_custom_posts_per_page' => false,
        'has_custom_thumb_height' => false,
        'has_custom_excerpt_length' => false,
    );
$active_blog_preset = isset( $resolved_archive_settings['preset'] ) ? (string) $resolved_archive_settings['preset'] : 'default';

// 获取后台全局设置（仅用于视频封面等全局设置）
$theme_options = developer_starter_get_options_cache();

// 布局设置 - 未单独设置时跟随博客风格默认节奏
$layout = ! empty( $resolved_archive_settings['layout'] ) ? (string) $resolved_archive_settings['layout'] : 'card';
$video_category_enabled = ! empty( $settings['video_category_enabled'] );
$video_plugin_active = class_exists( 'ArtPlayer_Video_Frontend' );
$video_list_enabled = $video_category_enabled && $video_plugin_active;
$video_frontend = $video_list_enabled ? ArtPlayer_Video_Frontend::get_instance() : null;

// 列数设置 - 优先分类自定义，否则使用预设默认节奏
$has_custom_columns = ! empty( $resolved_archive_settings['has_custom_columns'] );
$columns = isset( $resolved_archive_settings['columns'] ) ? intval( $resolved_archive_settings['columns'] ) : 3;

// 缩略图高度 - 优先分类自定义，否则使用预设默认节奏
$has_custom_thumb_height = ! empty( $resolved_archive_settings['has_custom_thumb_height'] );
$thumb_height = isset( $resolved_archive_settings['thumb_height'] ) ? intval( $resolved_archive_settings['thumb_height'] ) : 200;

// 摘要字数 - 优先分类自定义，否则使用预设默认节奏
$excerpt_length = isset( $resolved_archive_settings['excerpt_length'] ) ? intval( $resolved_archive_settings['excerpt_length'] ) : 40;

// 隐藏选项 - 优先分类自定义，否则使用当前风格默认值
$hide_thumb = ! empty( $resolved_archive_settings['hide_thumb'] );
$hide_excerpt = ! empty( $resolved_archive_settings['hide_excerpt'] );
$hide_date = ! empty( $resolved_archive_settings['hide_date'] );
$hide_category_tag = ! empty( $resolved_archive_settings['hide_category'] );
$hide_author = ! empty( $resolved_archive_settings['hide_author'] );
$hide_breadcrumb = ! empty( $resolved_archive_settings['hide_breadcrumb'] );
$theme_option_enabled = static function ( $key, $default = true ) use ( $theme_options ) {
    if ( is_array( $theme_options ) && array_key_exists( $key, $theme_options ) ) {
        return '1' === (string) $theme_options[ $key ];
    }

    return (bool) $default;
};

$category_header_enabled = $theme_option_enabled( 'category_header_enable', true );
if ( '1' === get_term_meta( $cat_id, 'ds_category_hide_header', true ) ) {
    $category_header_enabled = false;
}

$category_breadcrumb_enabled = $category_header_enabled
    && $theme_option_enabled( 'category_breadcrumb_enable', true )
    && ! $hide_breadcrumb;
$category_icon_enabled = $theme_option_enabled( 'category_show_icon', true );
$category_description_enabled = $theme_option_enabled( 'category_show_description', true );

$category_count_enabled = $theme_option_enabled( 'category_show_count', true );
if ( '1' === get_term_meta( $cat_id, 'ds_category_hide_count', true ) ) {
    $category_count_enabled = false;
}

$category_count_label = isset( $theme_options['category_count_label'] ) ? trim( wp_strip_all_tags( (string) $theme_options['category_count_label'] ) ) : '';
if ( '' === $category_count_label ) {
    $category_count_label = __( '%s 篇文章', 'developer-starter' );
}

// 视频封面设置（全局设置）
$video_cover_enable = ! empty( $theme_options['video_cover_enable'] );
$video_badge_enable = ! empty( $theme_options['video_badge_enable'] );

// 背景颜色设置
$bg_color = $settings['bg_color'];
$header_padding = ! empty( $settings['header_padding'] ) ? $settings['header_padding'] : '';
$breadcrumb_color = ! empty( $settings['breadcrumb_color'] ) ? $settings['breadcrumb_color'] : '';
$icon = $settings['icon'];
$has_custom_bg_color = ! empty( $bg_color );
$force_category_header_background = $has_custom_bg_color || 'default' === $active_blog_preset;
$sanitize_css_var_value = static function ( $value, $fallback = '' ) {
    $value = trim( (string) $value );
    $value = preg_replace( '/[;\r\n<>]+/', '', $value );
    if ( ! is_string( $value ) || $value === '' ) {
        return (string) $fallback;
    }
    return $value;
};

// 背景颜色处理
if ( $force_category_header_background && empty( $bg_color ) ) {
    $primary_color = developer_starter_get_option( 'primary_color', '#2563eb' );
    if ( class_exists( '\Developer_Starter\Core\Design_Tokens' ) ) {
        $design_tokens = \Developer_Starter\Core\Design_Tokens::get_current_tokens();
        if ( ! empty( $design_tokens['primary'] ) ) {
            $primary_color = (string) $design_tokens['primary'];
        }
    }
    $category_header_bg = 'linear-gradient(135deg, ' . $primary_color . ' 0%, ' . developer_starter_darken_color( $primary_color, 20 ) . ' 100%)';
} elseif ( $force_category_header_background ) {
    $category_header_bg = 'linear-gradient(135deg, ' . $bg_color . ' 0%, ' . developer_starter_darken_color( $bg_color, 20 ) . ' 100%)';
} else {
    $category_header_bg = '';
}
$category_header_bg = '' !== $category_header_bg
    ? $sanitize_css_var_value( $category_header_bg, 'linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%)' )
    : '';
$category_header_padding_value = ! empty( $header_padding ) ? $sanitize_css_var_value( $header_padding ) : '';

// 高级分类筛选设置
$adv_filter_enabled = ! empty( $settings['adv_filter_enabled'] );
$category_sort_options = function_exists( 'developer_starter_get_category_sort_options' )
    ? developer_starter_get_category_sort_options( $theme_options )
    : array(
        'latest'   => __( '最新', 'developer-starter' ),
        'random'   => __( '随机', 'developer-starter' ),
        'hot'      => __( '热门', 'developer-starter' ),
        'like'     => __( '点赞', 'developer-starter' ),
        'favorite' => __( '收藏', 'developer-starter' ),
    );
$category_default_sort = function_exists( 'developer_starter_get_category_default_sort' )
    ? developer_starter_get_category_default_sort( $theme_options, $category_sort_options )
    : 'latest';
$current_sort = isset( $_GET['sort'] ) ? sanitize_text_field( wp_unslash( $_GET['sort'] ) ) : $category_default_sort;
$current_sort = function_exists( 'developer_starter_normalize_category_sort' )
    ? developer_starter_normalize_category_sort( $current_sort, $theme_options )
    : $current_sort;
$category_sort_row_enabled = $theme_option_enabled( 'category_show_sort_row', true ) && ! empty( $category_sort_options );

// 根据布局确定容器和文章项的CSS类
$layout_class_map = array(
    'card'     => array( 'container' => 'posts-card', 'item' => 'post-item-card' ),
    'list'     => array( 'container' => 'posts-list', 'item' => 'post-item-list' ),
    'grid'     => array( 'container' => 'posts-grid', 'item' => 'post-item-grid' ),
    'magazine' => array( 'container' => 'posts-magazine', 'item' => 'post-item-card' ),
    'video'    => array( 'container' => 'posts-video', 'item' => 'post-item-video' ),
);
$active_layout = $layout;
$layout_classes = isset( $layout_class_map[ $active_layout ] ) ? $layout_class_map[ $active_layout ] : $layout_class_map['card'];

// 加载分类页面专属CSS
$assets_version = ! empty( $theme_options['assets_version'] ) ? $theme_options['assets_version'] : DEVELOPER_STARTER_VERSION;
wp_enqueue_style( 'developer-starter-category', get_template_directory_uri() . '/assets/css/category.css', array(), $assets_version );

// 高级分类筛选设置
$adv_levels = array();
$adv_custom_levels = ! empty( $settings['adv_custom_levels'] ) ? $settings['adv_custom_levels'] : '';
if ( $adv_custom_levels ) {
    $decoded_levels = null;
    $trimmed_custom = ltrim( $adv_custom_levels );
    if ( strpos( $trimmed_custom, '[' ) === 0 || strpos( $trimmed_custom, '{' ) === 0 ) {
        $decoded_levels = json_decode( $adv_custom_levels, true );
    }
    if ( is_array( $decoded_levels ) ) {
        foreach ( $decoded_levels as $index => $level ) {
            $label = isset( $level['label'] ) ? trim( (string) $level['label'] ) : '';
            $options = isset( $level['options'] ) && is_array( $level['options'] ) ? array_filter( array_map( 'trim', $level['options'] ) ) : array();
            if ( $label && ! empty( $options ) ) {
                $adv_levels[] = array(
                    'key' => 'level_' . $index,
                    'label' => $label,
                    'options' => $options,
                );
            }
        }
    } else {
        $lines = array_filter( array_map( 'trim', preg_split( "/\r\n|\n|\r/", $adv_custom_levels ) ) );
        $level_index = 0;
        foreach ( $lines as $line ) {
            $parts = explode( ':', $line, 2 );
            $label = isset( $parts[0] ) ? trim( $parts[0] ) : '';
            $opts_text = isset( $parts[1] ) ? trim( $parts[1] ) : '';
            if ( $label && $opts_text ) {
                $options = array_filter( array_map( 'trim', explode( ',', $opts_text ) ) );
                if ( ! empty( $options ) ) {
                    $adv_levels[] = array(
                        'key' => 'level_' . $level_index,
                        'label' => $label,
                        'options' => $options,
                    );
                    $level_index++;
                }
            }
        }
    }
}
if ( empty( $adv_levels ) ) {
    $adv_major_cats = ! empty( $settings['adv_major_cats'] ) ? array_filter( array_map( 'trim', explode( ',', $settings['adv_major_cats'] ) ) ) : array();
    $adv_minor_cats = ! empty( $settings['adv_minor_cats'] ) ? array_filter( array_map( 'trim', explode( ',', $settings['adv_minor_cats'] ) ) ) : array();
    if ( ! empty( $adv_major_cats ) ) {
        $adv_levels[] = array(
            'key' => 'level_0',
            'label' => __( '大分类', 'developer-starter' ),
            'options' => $adv_major_cats,
        );
    }
    if ( ! empty( $adv_minor_cats ) ) {
        $adv_levels[] = array(
            'key' => 'level_1',
            'label' => __( '小分类', 'developer-starter' ),
            'options' => $adv_minor_cats,
        );
    }
}
$category_filter_bar_enabled = $adv_filter_enabled && ( ! empty( $adv_levels ) || $category_sort_row_enabled );
// 如果启用了高级筛选，加载筛选样式
if ( $category_filter_bar_enabled ) {
    wp_enqueue_style( 'developer-starter-adv-filter', get_template_directory_uri() . '/assets/css/advanced-filter.css', array(), $assets_version );
}

$archive_loading_mode = isset( $theme_options['archive_loading_mode'] ) ? sanitize_key( (string) $theme_options['archive_loading_mode'] ) : 'regular';
if ( ! in_array( $archive_loading_mode, array( 'regular', 'infinite' ), true ) ) {
    $archive_loading_mode = 'regular';
}
$archive_infinite_enabled = 'infinite' === $archive_loading_mode;
if ( $archive_infinite_enabled ) {
    wp_enqueue_style( 'developer-starter-infinite-scroll', get_template_directory_uri() . '/assets/css/infinite-scroll.css', array(), $assets_version );
    wp_enqueue_script( 'developer-starter-infinite-scroll', get_template_directory_uri() . '/assets/js/infinite-scroll.js', array(), $assets_version, true );
    wp_localize_script(
        'developer-starter-infinite-scroll',
        'qilingInfiniteScrollI18n',
        array(
            'loadMore' => __( '加载更多', 'developer-starter' ),
            'loading'  => __( '正在加载...', 'developer-starter' ),
            'done'     => __( '已经到底了', 'developer-starter' ),
            'error'    => __( '加载失败，请稍后再试', 'developer-starter' ),
            'retry'    => __( '重试加载', 'developer-starter' ),
        )
    );
}

$thumb_fit = function_exists( 'developer_starter_get_thumbnail_display_mode' ) ? developer_starter_get_thumbnail_display_mode() : 'cover';
$thumb_fit = in_array( $thumb_fit, array( 'cover', 'contain', 'fill', 'none', 'scale-down' ), true ) ? $thumb_fit : 'cover';

$category_dynamic_vars = array(
    '--qiling-category-columns'      => max( 1, (int) $columns ),
    '--qiling-category-thumb-height' => max( 0, (int) $thumb_height ) . 'px',
    '--qiling-category-thumb-fit'    => $thumb_fit,
);
if ( '' !== $category_header_bg ) {
    $category_dynamic_vars['--qiling-category-header-bg'] = $category_header_bg;
}
if ( '' !== $category_header_padding_value ) {
    $category_dynamic_vars['--qiling-category-header-padding'] = $category_header_padding_value;
}
if ( ! empty( $breadcrumb_color ) ) {
    $category_dynamic_vars['--qiling-category-breadcrumb-color'] = $sanitize_css_var_value( $breadcrumb_color );
}

$category_dynamic_css = "body.category {\n";
foreach ( $category_dynamic_vars as $css_var => $css_value ) {
    $category_dynamic_css .= '    ' . $css_var . ': ' . $sanitize_css_var_value( $css_value ) . ";\n";
}
$category_dynamic_css .= "}\n";
wp_add_inline_style( 'developer-starter-category', $category_dynamic_css );

$category_body_classes = array(
    'qiling-category-layout-' . sanitize_html_class( $active_layout ),
);
if ( 'card' === $layout || 'grid' === $layout ) {
    $category_body_classes[] = 'qiling-category-dynamic-grid';
}
if ( $thumb_height > 0 ) {
    $category_body_classes[] = 'qiling-category-dynamic-thumb';
}
if ( '' !== $category_header_bg ) {
    $category_body_classes[] = 'qiling-category-has-header-bg';
}
if ( '' !== $category_header_padding_value ) {
    $category_body_classes[] = 'qiling-category-has-header-padding';
}
if ( ! empty( $breadcrumb_color ) ) {
    $category_body_classes[] = 'qiling-category-has-breadcrumb-color';
}
if ( $archive_infinite_enabled ) {
    $category_body_classes[] = 'qiling-archive-infinite-scroll';
}
add_filter(
    'body_class',
    static function ( $classes ) use ( $category_body_classes ) {
        return array_merge( $classes, $category_body_classes );
    }
);

$category_current_page = max( 1, (int) get_query_var( 'paged' ) );
$category_max_pages    = ( isset( $GLOBALS['wp_query'] ) && $GLOBALS['wp_query'] instanceof WP_Query ) ? max( 1, (int) $GLOBALS['wp_query']->max_num_pages ) : 1;
$category_next_url     = ( $archive_infinite_enabled && $category_current_page < $category_max_pages ) ? get_pagenum_link( $category_current_page + 1 ) : '';
$category_infinite_attrs = '';
if ( $archive_infinite_enabled ) {
    $category_infinite_attrs = sprintf(
        ' data-qiling-infinite-scroll="1" data-context="category" data-item-container=".%1$s" data-pagination=".ds-pagination" data-current-page="%2$d" data-max-pages="%3$d" data-next-url="%4$s" data-category-id="%5$d" data-adv-filter="%6$s" data-ajax-url="%7$s" data-nonce="%8$s"',
        esc_attr( $layout_classes['container'] ),
        (int) $category_current_page,
        (int) $category_max_pages,
        esc_url( $category_next_url ),
        (int) $cat_id,
        $category_filter_bar_enabled ? '1' : '0',
        esc_url( admin_url( 'admin-ajax.php' ) ),
        esc_attr( wp_create_nonce( 'ds_adv_filter_nonce' ) )
    );
}

get_header();
?>

<!-- 分类页面头部 -->
<?php if ( $category_header_enabled ) : ?>
<div class="category-header">
    <div class="container">
        <!-- 面包屑导航 -->
        <?php if ( $category_breadcrumb_enabled && apply_filters( 'qiling_show_breadcrumb', true, 'category' ) ) : ?>
        <nav class="category-breadcrumb" aria-label="<?php esc_attr_e( '面包屑导航', 'developer-starter' ); ?>">
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( '首页', 'developer-starter' ); ?></a>
            <span class="breadcrumb-separator">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="9 18 15 12 9 6"></polyline>
                </svg>
            </span>
            <span class="breadcrumb-current">
                <?php if ( $category_icon_enabled && ! empty( $icon ) ) : ?>
                    <span class="category-icon">
                        <?php if ( filter_var( $icon, FILTER_VALIDATE_URL ) ) : ?>
                            <img src="<?php echo esc_url( $icon ); ?>" alt="" loading="eager" decoding="async" />
                        <?php else : ?>
                            <?php echo esc_html( $icon ); ?>
                        <?php endif; ?>
                    </span>
                <?php endif; ?>
                <?php single_cat_title(); ?>
            </span>
        </nav>
        <?php endif; ?>
        
        <!-- 分类标题 -->
        <h1 class="category-title">
            <?php if ( $category_icon_enabled && ! empty( $icon ) ) : ?>
                <span class="category-icon-large">
                    <?php if ( filter_var( $icon, FILTER_VALIDATE_URL ) ) : ?>
                        <img src="<?php echo esc_url( $icon ); ?>" alt="" loading="eager" decoding="async" />
                    <?php else : ?>
                        <?php echo esc_html( $icon ); ?>
                    <?php endif; ?>
                </span>
            <?php endif; ?>
            <?php single_cat_title(); ?>
        </h1>
        
        <?php if ( $category_description_enabled && category_description() ) : ?>
            <p class="category-description"><?php echo category_description(); ?></p>
        <?php endif; ?>
        
        <!-- 文章统计 -->
        <?php if ( $category_count_enabled ) : ?>
        <div class="category-meta">
            <span class="category-count">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                    <line x1="16" y1="13" x2="8" y2="13"></line>
                    <line x1="16" y1="17" x2="8" y2="17"></line>
                    <polyline points="10 9 9 9 8 9"></polyline>
                </svg>
                <?php echo esc_html( false !== strpos( $category_count_label, '%s' ) ? str_replace( '%s', number_format_i18n( (int) $category->count ), $category_count_label ) : $category_count_label ); ?>
            </span>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<?php if ( $category_filter_bar_enabled ) : ?>
<!-- 高级分类筛选栏 -->
<div class="adv-filter-section" id="adv-filter-section">
    <div class="container">
        <?php foreach ( $adv_levels as $level ) : ?>
        <div class="adv-filter-row">
            <span class="adv-filter-label"><?php echo esc_html( $level['label'] ); ?>：</span>
            <div class="adv-filter-buttons">
                <button type="button" class="adv-filter-btn active" data-filter-key="<?php echo esc_attr( $level['key'] ); ?>" data-filter-value="">
                    <?php esc_html_e( '全部', 'developer-starter' ); ?>
                </button>
                <?php foreach ( $level['options'] as $option ) : ?>
                <button type="button" class="adv-filter-btn" data-filter-key="<?php echo esc_attr( $level['key'] ); ?>" data-filter-value="<?php echo esc_attr( $option ); ?>">
                    <?php echo esc_html( $option ); ?>
                </button>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>
        <?php if ( $category_sort_row_enabled ) : ?>
        <div class="adv-filter-row">
            <span class="adv-filter-label"><?php esc_html_e( '排序：', 'developer-starter' ); ?></span>
            <div class="adv-filter-buttons">
                <?php foreach ( $category_sort_options as $sort_key => $sort_label ) : ?>
                <button type="button" class="adv-filter-btn<?php echo $current_sort === (string) $sort_key ? ' active' : ''; ?>" data-sort-value="<?php echo esc_attr( $sort_key ); ?>">
                    <?php echo esc_html( $sort_label ); ?>
                </button>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<!-- 文章列表 -->
<section class="category-content section-padding<?php echo $archive_infinite_enabled ? ' qiling-infinite-root' : ''; ?>"<?php echo $category_infinite_attrs; ?>>
    <div class="container">
        <?php if ( have_posts() ) : ?>
            
            <div class="<?php echo esc_attr( $layout_classes['container'] ); ?>">
                <?php 
                $qiling_category_loop_index = 0;
                $qiling_category_loop_query = ( isset( $GLOBALS['wp_query'] ) && $GLOBALS['wp_query'] instanceof WP_Query ) ? $GLOBALS['wp_query'] : null;
                $qiling_category_loop_settings = array(
                    'context'     => 'category_archive',
                    'category_id' => $cat_id,
                );
                $qiling_category_loop_post_ids = array();
                if ( $qiling_category_loop_query && ! empty( $qiling_category_loop_query->posts ) ) {
                    foreach ( $qiling_category_loop_query->posts as $qiling_category_loop_post ) {
                        if ( $qiling_category_loop_post instanceof WP_Post ) {
                            $qiling_category_loop_post_ids[] = (int) $qiling_category_loop_post->ID;
                        } elseif ( is_numeric( $qiling_category_loop_post ) ) {
                            $qiling_category_loop_post_ids[] = (int) $qiling_category_loop_post;
                        }
                    }
                    $qiling_category_loop_post_ids = array_values( array_unique( array_filter( $qiling_category_loop_post_ids ) ) );
                    if ( ! empty( $qiling_category_loop_post_ids ) ) {
                        update_meta_cache( 'post', $qiling_category_loop_post_ids );
                        if ( function_exists( 'developer_starter_prime_first_video_cache' ) ) {
                            developer_starter_prime_first_video_cache( $qiling_category_loop_post_ids );
                        }
                    }
                }
                while ( have_posts() ) : the_post(); 
                    $post_id = get_the_ID();
                    $video_data = false;
                    $has_video_cover = false;
                    $has_video = false;
                    $video_meta = null;
                    $is_video_mode = false;
                    $video_rating = 0;
                    
                    if ( $video_list_enabled && $video_frontend ) {
                        $video_meta = $video_frontend->get_video_meta_public( $post_id );
                        $is_video_mode = $video_meta && ! empty( $video_meta->is_video_mode );
                        $video_rating = $is_video_mode ? floatval( $video_meta->rating ) : 0;
                    }
                    
                    $needs_first_video = ( $video_cover_enable || ( $video_badge_enable && ! $video_category_enabled ) ) && function_exists( 'developer_starter_get_first_video' );
                    if ( $needs_first_video ) {
                        $video_data = developer_starter_get_first_video( $post_id );
                        if ( $video_data ) {
                            $has_video = true;
                        }
                        if ( $video_cover_enable && $video_data && isset( $video_data['type'] ) && $video_data['type'] === 'video' ) {
                            $has_video_cover = true;
                        }
                    }
                    
                    $video_preview_src = ( $has_video_cover && ! empty( $video_data['preview_src'] ) ) ? $video_data['preview_src'] : ( $video_data['url'] ?? '' );
                    $cover_badges = function_exists( 'developer_starter_get_post_cover_badges' )
                        ? developer_starter_get_post_cover_badges(
                            $post_id,
                            array(
                                'context'                                  => 'category',
                                'theme_options'                            => $theme_options,
                                'has_video'                                => $has_video,
                                'video_data'                               => $video_data,
                                'video_meta'                               => $video_meta,
                                'video_rating'                             => $video_rating,
                                'video_category_enabled'                   => $video_category_enabled,
                                'include_video_meta_badges'                => $video_category_enabled,
                                'suppress_video_badge_when_category_mode'  => true,
                            )
                        )
                        : array();
                    $has_cover_badges = ! empty( $cover_badges );
                    
                    $cover_image = '';
                    if ( ! $hide_thumb ) {
                        if ( function_exists( 'developer_starter_get_thumbnail_url' ) ) {
                            $cover_image = developer_starter_get_thumbnail_url( $post_id, 'medium' );
                        } elseif ( has_post_thumbnail() ) {
                            $cover_image = get_the_post_thumbnail_url( $post_id, 'medium_large' );
                        } elseif ( function_exists( 'developer_starter_get_first_image' ) ) {
                            $cover_image = developer_starter_get_first_image( $post_id );
                        }
                        if ( $has_video_cover && ! empty( $video_data['poster'] ) ) {
                            $cover_image = $video_data['poster'];
                        }
                    }
                    
                    if ( $video_list_enabled && $is_video_mode && ! empty( $video_meta->cover_image ) ) {
                        $cover_image = $video_meta->cover_image;
                    }
                ?>
                    <article class="<?php echo esc_attr( $layout_classes['item'] ); ?><?php echo $has_video_cover ? ' has-video-cover' : ''; ?><?php echo $is_video_mode ? ' is-video-mode' : ''; ?>" data-aos="fade-up">
                        <?php if ( ! $hide_thumb ) : ?>
                            <?php if ( $has_video_cover ) : ?>
                                <div class="post-thumb post-video-cover is-video-cover">
                                    <a href="<?php echo esc_url( get_permalink() ); ?>" class="video-cover-link">
                                        <?php if ( $cover_image ) : ?>
                                            <img src="<?php echo esc_url( $cover_image ); ?>" alt="<?php the_title_attribute(); ?>" class="video-poster" loading="lazy" decoding="async" />
                                        <?php endif; ?>
                                    </a>
                                    <video class="video-cover-player" src="<?php echo esc_url( $video_preview_src ); ?>" muted loop playsinline preload="<?php echo $cover_image ? 'metadata' : 'auto'; ?>" <?php if ( $cover_image ) : ?>poster="<?php echo esc_url( $cover_image ); ?>"<?php endif; ?>></video>
                                    <span class="post-video-play">
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M8 5v14l11-7z"/></svg>
                                    </span>
                                    <?php if ( $has_cover_badges && function_exists( 'developer_starter_get_post_cover_badges_html' ) ) : ?>
                                        <?php echo developer_starter_get_post_cover_badges_html( $cover_badges, array( 'context' => 'category' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                                    <?php endif; ?>
                                    <a href="<?php echo esc_url( get_permalink() ); ?>" class="video-cover-overlay-link"></a>
                                </div>
                            <?php elseif ( $cover_image ) : ?>
                                <a href="<?php echo esc_url( get_permalink() ); ?>" class="post-thumb<?php echo $is_video_mode || $video_category_enabled ? ' is-video-cover' : ''; ?>">
                                    <img src="<?php echo esc_url( $cover_image ); ?>" alt="<?php the_title_attribute(); ?>" loading="lazy" decoding="async" />
                                    <?php if ( $video_category_enabled ) : ?>
                                        <span class="post-video-overlay"></span>
                                        <span class="post-video-play">
                                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M8 5v14l11-7z"/></svg>
                                        </span>
                                    <?php endif; ?>
                                    <?php if ( $has_cover_badges && function_exists( 'developer_starter_get_post_cover_badges_html' ) ) : ?>
                                        <?php echo developer_starter_get_post_cover_badges_html( $cover_badges, array( 'context' => 'category' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                                    <?php endif; ?>
                                </a>
                            <?php endif; ?>
                        <?php endif; ?>
                        
                        <div class="post-content">
                            <div class="post-meta-badges">
                                <?php 
                                // 高级分类显示逻辑
                                $show_advanced_cat = false;
                                if ( $video_category_enabled ) {
                                    $adv_cats = array();
                                    $post_adv_levels = get_post_meta( get_the_ID(), '_ds_adv_levels', true );
                                    
                                    if ( is_array( $post_adv_levels ) ) {
                                        foreach ( $post_adv_levels as $adv_value ) {
                                            if ( ! empty( $adv_value ) ) {
                                                $adv_cats[] = $adv_value;
                                            }
                                        }
                                    } else {
                                        $legacy_major = get_post_meta( $post_id, '_ds_adv_major_cat', true );
                                        $legacy_minor = get_post_meta( $post_id, '_ds_adv_minor_cat', true );
                                        if ( $legacy_major ) $adv_cats[] = $legacy_major;
                                        if ( $legacy_minor ) $adv_cats[] = $legacy_minor;
                                    }
                                    
                                    if ( ! empty( $adv_cats ) ) {
                                        $show_advanced_cat = true;
                                        foreach ( $adv_cats as $adv_cat_name ) {
                                            // 尝试通过名称查找链接，如果找不到则不链接或链接到当前分类并带参数（这里简化处理，仅显示标签）
                                            // 可扩展为搜索链接
                                            echo '<span class="post-category-tag">' . esc_html( $adv_cat_name ) . '</span>';
                                        }
                                    }
                                }
                                
                                // 如果没有显示高级分类，则显示普通分类
                                if ( ! $show_advanced_cat && ! $hide_category_tag ) : 
                                    $cats = get_the_category();
                                    if ( ! empty( $cats ) ) : ?>
                                    <a href="<?php echo esc_url( get_category_link( $cats[0]->term_id ) ); ?>" class="post-category-tag"><?php echo esc_html( $cats[0]->name ); ?></a>
                                <?php endif; endif; ?>
                            </div>
                            
                            <?php if ( ! $hide_date || ! $hide_author ) : ?>
                            <div class="post-meta-info">
                                <?php if ( ! $hide_date ) : ?>
                                <span class="post-date"><?php echo get_the_date(); ?></span>
                                <?php endif; ?>
                                <?php if ( ! $hide_author ) : ?>
                                <span class="post-author"><?php the_author(); ?></span>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                            
                            <h2 class="post-title">
                                <a href="<?php echo esc_url( get_permalink() ); ?>"><?php echo esc_html( get_the_title() ); ?></a>
                            </h2>
                            
                            <?php if ( ! $hide_excerpt ) : ?>
                            <p class="post-excerpt"><?php echo wp_trim_words( get_the_excerpt(), $excerpt_length ); ?></p>
                            <?php endif; ?>
                        </div>
                    </article>
                    <?php
                    $qiling_category_loop_index++;
                    do_action( 'qiling_blog_loop_after_item', $qiling_category_loop_index, $qiling_category_loop_query, $qiling_category_loop_settings );
                    ?>
                <?php endwhile; ?>
            </div>
            
            <!-- 分页 -->
            <nav class="ds-pagination<?php echo $archive_infinite_enabled ? ' qiling-infinite-pagination-fallback' : ''; ?>" role="navigation" aria-label="<?php esc_attr_e( '文章分页导航', 'developer-starter' ); ?>">
                <?php
                echo paginate_links( array(
                    'mid_size'  => 2,
                    'prev_text' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"></polyline></svg><span>' . __( '上一页', 'developer-starter' ) . '</span>',
                    'next_text' => '<span>' . __( '下一页', 'developer-starter' ) . '</span><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"></polyline></svg>',
                    'type'      => 'list',
                ) );
                ?>
            </nav>

            <?php if ( $archive_infinite_enabled ) : ?>
                <div class="qiling-infinite-control" data-qiling-infinite-control>
                    <button type="button" class="qiling-infinite-load-more" data-qiling-infinite-load-more><?php esc_html_e( '加载更多', 'developer-starter' ); ?></button>
                    <span class="qiling-infinite-status qiling-infinite-status--loading"><span class="qiling-infinite-spinner" aria-hidden="true"></span><?php esc_html_e( '正在加载...', 'developer-starter' ); ?></span>
                    <span class="qiling-infinite-status qiling-infinite-status--done"><?php esc_html_e( '已经到底了', 'developer-starter' ); ?></span>
                    <span class="qiling-infinite-status qiling-infinite-status--error"><?php esc_html_e( '加载失败，请稍后再试', 'developer-starter' ); ?></span>
                </div>
            <?php endif; ?>
            
        <?php else : ?>
            <div class="no-posts">
                <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                </svg>
                <p><?php esc_html_e( '该分类下暂无文章', 'developer-starter' ); ?></p>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php if ( $category_filter_bar_enabled ) : ?>
<!-- 高级分类筛选 JavaScript -->
<script>
(function() {
    'use strict';
    
    var categoryId = <?php echo intval( $cat_id ); ?>;
    var currentFilters = {};
    var currentSort = '<?php echo esc_js( $current_sort ); ?>';
    var isLoading = false;
    var filterNonce = '<?php echo esc_js( wp_create_nonce( 'ds_adv_filter_nonce' ) ); ?>';
    
    // 文章列表容器选择器
    var postsContainer = document.querySelector('.category-content .<?php echo esc_js( $layout_classes['container'] ); ?>');
    var paginationNav = document.querySelector('.ds-pagination');
    var infiniteRoot = document.querySelector('[data-qiling-infinite-scroll="1"][data-context="category"]');

    function syncInfiniteFilterState() {
        window.qilingCategoryAdvancedFilterState = {
            categoryId: categoryId,
            filters: Object.assign({}, currentFilters),
            sort: currentSort,
            nonce: filterNonce
        };
    }
    syncInfiniteFilterState();
    
    document.querySelectorAll('.adv-filter-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            if (isLoading) return;
            
            var filterKey = this.getAttribute('data-filter-key');
            var filterValue = this.getAttribute('data-filter-value');
            var sortValue = this.getAttribute('data-sort-value');
            
            var row = this.closest('.adv-filter-row');
            row.querySelectorAll('.adv-filter-btn').forEach(function(b) {
                b.classList.remove('active');
            });
            this.classList.add('active');
            
            if (filterKey) {
                currentFilters[filterKey] = filterValue;
            }
            if (sortValue) {
                currentSort = sortValue;
            }
            
            doFilter();
        });
    });
    
    function doFilter() {
        isLoading = true;
        syncInfiniteFilterState();
        
        // 显示加载状态
        if (postsContainer) {
            postsContainer.style.opacity = '0.5';
            postsContainer.style.pointerEvents = 'none';
        }
        
        // 构建请求数据
        var formData = new FormData();
        formData.append('action', 'ds_adv_category_filter');
        formData.append('category_id', categoryId);
        Object.keys(currentFilters).forEach(function(key) {
            formData.append('filters[' + key + ']', currentFilters[key]);
        });
        formData.append('sort', currentSort);
        formData.append('paged', '1');
        formData.append('nonce', filterNonce);
        
        // 发送 AJAX 请求
        fetch(<?php echo wp_json_encode( esc_url_raw( admin_url( 'admin-ajax.php' ) ) ); ?>, {
            method: 'POST',
            body: formData
        })
        .then(function(response) {
            return response.json();
        })
        .then(function(data) {
            if (data.success && postsContainer) {
                // 更新文章列表
                postsContainer.innerHTML = data.data.html;
                
                // 更新或隐藏分页
                if (paginationNav) {
                    if (data.data.pagination) {
                        paginationNav.innerHTML = data.data.pagination;
                        paginationNav.style.display = '';
                    } else {
                        paginationNav.style.display = 'none';
                    }
                }

                if (infiniteRoot && window.QilingInfiniteScroll && typeof window.QilingInfiniteScroll.refresh === 'function') {
                    window.QilingInfiniteScroll.refresh(infiniteRoot, {
                        currentPage: 1,
                        maxPages: parseInt(data.data.max_num_pages || 1, 10) || 1,
                        nextUrl: ''
                    });
                }
            }
        })
        .catch(function(error) {
            console.error('筛选请求失败:', error);
        })
        .finally(function() {
            isLoading = false;
            if (postsContainer) {
                postsContainer.style.opacity = '1';
                postsContainer.style.pointerEvents = '';
            }
        });
    }
})();
</script>
<?php endif; ?>

<?php get_footer(); ?>
