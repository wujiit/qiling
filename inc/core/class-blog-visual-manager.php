<?php
/**
 * 博客视觉预设管理器。
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Core;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Blog_Visual_Manager {

    /**
     * 全局主题配置选项键名。
     *
     * @var string
     */
    private const OPTION_KEY = 'blog_visual_preset';

    /**
     * 页面级预设覆盖的 Meta 键名。
     *
     * @var string
     */
    private const PAGE_META_KEY = '_qiling_blog_visual_preset';

    /**
     * 分类级预设覆盖的 Meta 键名。
     *
     * @var string
     */
    private const CATEGORY_META_KEY = 'ds_category_blog_visual_preset';

    /**
     * 模板中心开发者博客变体的标识 Slug。
     *
     * @var string
     */
    private const TEMPLATE_CENTER_VARIANT_DEVELOPER = 'developer_blog';

    /**
     * 模板中心极简博客变体的标识 Slug。
     *
     * @var string
     */
    private const TEMPLATE_CENTER_VARIANT_MINIMAL = 'minimal_blog';

    /**
     * 模板中心艺术博客变体的标识 Slug。
     *
     * @var string
     */
    private const TEMPLATE_CENTER_VARIANT_ARTIST = 'artist_blog';

    /**
     * 仅注册一次钩子。
     *
     * @return void
     */
    public static function bootstrap() {
        static $bootstrapped = false;

        if ( $bootstrapped ) {
            return;
        }

        $bootstrapped = true;

        add_filter( 'body_class', array( __CLASS__, 'filter_body_class' ) );
        add_filter( 'developer_starter_template_center_catalog', array( __CLASS__, 'extend_template_center_catalog' ) );
        add_action( 'developer_starter_template_center_apply_variant', array( __CLASS__, 'apply_template_center_variant' ), 10, 3 );
        add_action( 'developer_starter_after_enqueue_styles', array( __CLASS__, 'enqueue_preset_custom_css' ) );
    }

    /**
     * 获取前台博客预设选项列表。
     *
     * @return array<string,string>
     */
    public static function get_preset_choices() {
        return array(
            'default'   => __( '默认企业内容', 'developer-starter' ),
            'developer' => __( '技术开发者', 'developer-starter' ),
            'minimal'   => __( '极简', 'developer-starter' ),
            'artist'    => __( '艺术家', 'developer-starter' ),
        );
    }

    /**
     * 获取支持独立深度自定义的预设列表。
     *
     * @return array<string,string>
     */
    public static function get_customizable_preset_choices() {
        $choices = self::get_preset_choices();
        unset( $choices['default'] );

        return $choices;
    }

    /**
     * 获取模块预设选项列表。
     *
     * @return array<string,string>
     */
    public static function get_module_preset_choices() {
        return array(
            'inherit' => __( '继承页面/全局', 'developer-starter' ),
        ) + self::get_preset_choices();
    }

    /**
     * 获取预设自定义中的可见性开关选项。
     *
     * @return array<string,string>
     */
    public static function get_visibility_override_choices() {
        return array(
            ''     => __( '使用该风格内置默认值', 'developer-starter' ),
            'show' => __( '显示', 'developer-starter' ),
            'hide' => __( '隐藏', 'developer-starter' ),
        );
    }

    /**
     * 获取预设自定义中的分类布局选项。
     *
     * @param bool $include_auto Whether to include the auto option.
     * @return array<string,string>
     */
    public static function get_category_layout_choices( $include_auto = false ) {
        $choices = array(
            'card'     => __( '卡片布局', 'developer-starter' ),
            'list'     => __( '列表布局', 'developer-starter' ),
            'grid'     => __( '网格布局', 'developer-starter' ),
            'magazine' => __( '杂志布局', 'developer-starter' ),
            'video'    => __( '视频布局', 'developer-starter' ),
        );

        if ( $include_auto ) {
            return array(
                '' => __( '使用该风格内置默认值', 'developer-starter' ),
            ) + $choices;
        }

        return $choices;
    }

    /**
     * 获取预设自定义中的列数布局选项。
     *
     * @param bool $include_auto Whether to include the auto option.
     * @return array<string,string>
     */
    public static function get_columns_choices( $include_auto = false ) {
        $choices = array(
            '2' => __( '2 列', 'developer-starter' ),
            '3' => __( '3 列', 'developer-starter' ),
            '4' => __( '4 列', 'developer-starter' ),
        );

        if ( $include_auto ) {
            return array(
                '' => __( '使用该风格内置默认值', 'developer-starter' ),
            ) + $choices;
        }

        return $choices;
    }

    /**
     * 获取独立预设自定义字段的后台字段模式。
     *
     * @return array<string,array<string,mixed>>
     */
    public static function get_preset_customization_schema() {
        return array(
            'native'   => array(
                'title'       => __( '原生文章流', 'developer-starter' ),
                'description' => __( '作用于首页最新文章、归档页和原生文章卡片流。', 'developer-starter' ),
                'fields'      => array(
                    'native_columns'           => array(
                        'type'        => 'select',
                        'label'       => __( '列表列数', 'developer-starter' ),
                        'choices'     => self::get_columns_choices( true ),
                        'description' => __( '留空继续使用该风格当前的默认列数。', 'developer-starter' ),
                    ),
                    'native_thumb_height'      => array(
                        'type'        => 'number',
                        'label'       => __( '缩略图高度', 'developer-starter' ),
                        'min'         => 0,
                        'max'         => 600,
                        'suffix'      => 'px',
                        'placeholder' => '0',
                        'description' => __( '留空或填 0 时使用该风格默认缩略图高度。', 'developer-starter' ),
                    ),
                    'native_excerpt_length'    => array(
                        'type'        => 'number',
                        'label'       => __( '摘要字数', 'developer-starter' ),
                        'min'         => 0,
                        'max'         => 200,
                        'placeholder' => '0',
                        'description' => __( '留空或填 0 时使用该风格默认摘要长度。', 'developer-starter' ),
                    ),
                    'native_show_thumb'        => array(
                        'type'        => 'select',
                        'label'       => __( '缩略图显示', 'developer-starter' ),
                        'choices'     => self::get_visibility_override_choices(),
                        'description' => __( '单独覆盖该风格的缩略图显隐。', 'developer-starter' ),
                    ),
                    'native_show_excerpt'      => array(
                        'type'        => 'select',
                        'label'       => __( '摘要显示', 'developer-starter' ),
                        'choices'     => self::get_visibility_override_choices(),
                        'description' => __( '单独覆盖该风格的摘要显隐。', 'developer-starter' ),
                    ),
                    'native_show_date'         => array(
                        'type'        => 'select',
                        'label'       => __( '日期显示', 'developer-starter' ),
                        'choices'     => self::get_visibility_override_choices(),
                        'description' => __( '单独覆盖该风格的日期显隐。', 'developer-starter' ),
                    ),
                    'native_show_author'       => array(
                        'type'        => 'select',
                        'label'       => __( '作者显示', 'developer-starter' ),
                        'choices'     => self::get_visibility_override_choices(),
                        'description' => __( '单独覆盖该风格的作者信息显隐。', 'developer-starter' ),
                    ),
                    'native_show_category'     => array(
                        'type'        => 'select',
                        'label'       => __( '分类显示', 'developer-starter' ),
                        'choices'     => self::get_visibility_override_choices(),
                        'description' => __( '单独覆盖该风格的分类标签显隐。', 'developer-starter' ),
                    ),
                    'native_show_reading_time' => array(
                        'type'        => 'select',
                        'label'       => __( '阅读时长显示', 'developer-starter' ),
                        'choices'     => self::get_visibility_override_choices(),
                        'description' => __( '单独覆盖该风格的阅读时长显隐。', 'developer-starter' ),
                    ),
                ),
            ),
            'category' => array(
                'title'       => __( '分类页默认值', 'developer-starter' ),
                'description' => __( '分类未单独设置时，会优先采用当前风格这里的默认值。', 'developer-starter' ),
                'fields'      => array(
                    'category_layout'          => array(
                        'type'        => 'select',
                        'label'       => __( '默认布局', 'developer-starter' ),
                        'choices'     => self::get_category_layout_choices( true ),
                        'description' => __( '留空继续使用该风格内置的分类布局节奏。', 'developer-starter' ),
                    ),
                    'category_columns'         => array(
                        'type'        => 'select',
                        'label'       => __( '默认列数', 'developer-starter' ),
                        'choices'     => self::get_columns_choices( true ),
                        'description' => __( '仅在卡片/网格布局下生效。', 'developer-starter' ),
                    ),
                    'category_posts_per_page'  => array(
                        'type'        => 'number',
                        'label'       => __( '每页文章数', 'developer-starter' ),
                        'min'         => 0,
                        'max'         => 100,
                        'placeholder' => '0',
                        'description' => __( '留空或填 0 时回退到全局分类页设置或 WordPress 默认值。', 'developer-starter' ),
                    ),
                    'category_thumb_height'    => array(
                        'type'        => 'number',
                        'label'       => __( '缩略图高度', 'developer-starter' ),
                        'min'         => 0,
                        'max'         => 600,
                        'suffix'      => 'px',
                        'placeholder' => '0',
                        'description' => __( '分类未单独设置缩略图高度时使用。', 'developer-starter' ),
                    ),
                    'category_excerpt_length'  => array(
                        'type'        => 'number',
                        'label'       => __( '摘要字数', 'developer-starter' ),
                        'min'         => 0,
                        'max'         => 200,
                        'placeholder' => '0',
                        'description' => __( '分类未单独设置摘要字数时使用。', 'developer-starter' ),
                    ),
                    'category_show_thumb'      => array(
                        'type'        => 'select',
                        'label'       => __( '缩略图显示', 'developer-starter' ),
                        'choices'     => self::get_visibility_override_choices(),
                        'description' => __( '为当前风格的分类页设置默认显隐。', 'developer-starter' ),
                    ),
                    'category_show_excerpt'    => array(
                        'type'        => 'select',
                        'label'       => __( '摘要显示', 'developer-starter' ),
                        'choices'     => self::get_visibility_override_choices(),
                        'description' => __( '为当前风格的分类页设置默认显隐。', 'developer-starter' ),
                    ),
                    'category_show_date'       => array(
                        'type'        => 'select',
                        'label'       => __( '日期显示', 'developer-starter' ),
                        'choices'     => self::get_visibility_override_choices(),
                        'description' => __( '为当前风格的分类页设置默认显隐。', 'developer-starter' ),
                    ),
                    'category_show_category'   => array(
                        'type'        => 'select',
                        'label'       => __( '分类标签显示', 'developer-starter' ),
                        'choices'     => self::get_visibility_override_choices(),
                        'description' => __( '为当前风格的分类页设置默认显隐。', 'developer-starter' ),
                    ),
                    'category_show_author'     => array(
                        'type'        => 'select',
                        'label'       => __( '作者显示', 'developer-starter' ),
                        'choices'     => self::get_visibility_override_choices(),
                        'description' => __( '为当前风格的分类页设置默认显隐。', 'developer-starter' ),
                    ),
                    'category_show_breadcrumb' => array(
                        'type'        => 'select',
                        'label'       => __( '面包屑显示', 'developer-starter' ),
                        'choices'     => self::get_visibility_override_choices(),
                        'description' => __( '分类未单独设置时，决定当前风格是否默认显示面包屑。', 'developer-starter' ),
                    ),
                ),
            ),
            'custom'   => array(
                'title'       => __( '风格附加 CSS', 'developer-starter' ),
                'description' => __( '补充该风格的细节规则。建议写在 body.qiling-blog-preset-xxx 或 .module-blog.blog-preset-xxx 作用域下。', 'developer-starter' ),
                'fields'      => array(
                    'custom_css' => array(
                        'type'        => 'textarea',
                        'label'       => __( '附加 CSS', 'developer-starter' ),
                        'rows'        => 8,
                        'description' => __( '这里只会在博客相关页面加载。建议写带作用域的选择器，避免误伤其他页面。', 'developer-starter' ),
                    ),
                ),
            ),
        );
    }

    /**
     * 构建预设自定义配置的选项键名。
     *
     * @param string $preset Preset slug.
     * @param string $field  Schema field key.
     * @return string
     */
    public static function get_preset_customization_option_key( $preset, $field ) {
        return 'blog_preset_' . sanitize_key( $preset ) . '_' . sanitize_key( $field );
    }

    /**
     * 清理并验证预设标识符。
     *
     * @param mixed $preset Preset value.
     * @param bool  $allow_inherit Whether inherit is allowed.
     * @return string
     */
    public static function sanitize_preset( $preset, $allow_inherit = false ) {
        $preset = sanitize_key( (string) $preset );
        $choices = array_keys( self::get_preset_choices() );

        if ( $allow_inherit ) {
            $choices[] = 'inherit';
        }

        if ( in_array( $preset, $choices, true ) ) {
            return $preset;
        }

        return $allow_inherit ? 'inherit' : 'default';
    }

    /**
     * 清理独立预设的自定义配置选项。
     *
     * @param array<string,mixed> $sanitized Submitted options.
     * @param array<string,mixed> $existing_options Existing saved options.
     * @return array<string,mixed>
     */
    public static function sanitize_options( $sanitized, $existing_options = array() ) {
        $sanitized = is_array( $sanitized ) ? $sanitized : array();
        $existing_options = is_array( $existing_options ) ? $existing_options : array();
        $schema = self::get_preset_customization_schema();

        foreach ( array_keys( self::get_customizable_preset_choices() ) as $preset ) {
            foreach ( $schema as $group ) {
                $fields = isset( $group['fields'] ) && is_array( $group['fields'] ) ? $group['fields'] : array();
                foreach ( $fields as $field_key => $field_schema ) {
                    $option_key = self::get_preset_customization_option_key( $preset, $field_key );
                    if ( ! array_key_exists( $option_key, $sanitized ) && ! array_key_exists( $option_key, $existing_options ) ) {
                        continue;
                    }

                    $raw_value = array_key_exists( $option_key, $sanitized ) ? $sanitized[ $option_key ] : '';
                    $sanitized[ $option_key ] = self::sanitize_preset_customization_field_value( $raw_value, $field_schema );
                }
            }
        }

        return $sanitized;
    }

    /**
     * 获取当前页面级别的预设覆盖值。
     *
     * @param int|null $post_id Optional post ID.
     * @return string
     */
    public static function get_page_preset( $post_id = null ) {
        $post_id = null === $post_id ? self::get_current_source_post_id() : absint( $post_id );
        if ( $post_id <= 0 ) {
            return '';
        }

        $preset = get_post_meta( $post_id, self::PAGE_META_KEY, true );
        $preset = self::sanitize_preset( $preset );

        return 'default' === $preset && '' === (string) get_post_meta( $post_id, self::PAGE_META_KEY, true ) ? '' : $preset;
    }

    /**
     * 获取当前分类级别的预设覆盖值。
     *
     * @param int|null $term_id Optional term ID.
     * @return string
     */
    public static function get_category_preset( $term_id = null ) {
        $term_id = null === $term_id ? self::get_current_source_term_id() : absint( $term_id );
        if ( $term_id <= 0 ) {
            return '';
        }

        $preset_raw = get_term_meta( $term_id, self::CATEGORY_META_KEY, true );
        if ( '' === (string) $preset_raw ) {
            return '';
        }

        $preset = self::sanitize_preset( $preset_raw, true );

        return 'inherit' === $preset ? '' : $preset;
    }

    /**
     * 设置页面级预设覆盖值。
     *
     * @param int   $post_id Page ID.
     * @param mixed $preset Preset value.
     * @return void
     */
    public static function set_page_preset( $post_id, $preset ) {
        $post_id = absint( $post_id );
        if ( $post_id <= 0 ) {
            return;
        }

        update_post_meta( $post_id, self::PAGE_META_KEY, self::sanitize_preset( $preset ) );
    }

    /**
     * 解析博客相关上下文中当前生效的视觉预设。
     *
     * @return string
     */
    public static function get_current_preset() {
        $current_term_id = self::get_current_source_term_id();
        if ( $current_term_id > 0 && self::should_force_default_preset_for_category( $current_term_id ) ) {
            return 'default';
        }

        $category_preset = self::get_category_preset();
        if ( '' !== $category_preset ) {
            return $category_preset;
        }

        $page_preset = self::get_page_preset();
        if ( '' !== $page_preset ) {
            return $page_preset;
        }

        $global_preset = function_exists( 'developer_starter_get_option' )
            ? developer_starter_get_option( self::OPTION_KEY, 'default' )
            : 'default';

        return self::sanitize_preset( $global_preset );
    }

    /**
     * 解析模块预设值（支持 inherit 继承选项）。
     *
     * @param mixed $preset Preset value from module data.
     * @return string
     */
    public static function resolve_module_preset( $preset ) {
        $preset = self::sanitize_preset( $preset, true );

        return 'inherit' === $preset ? self::get_current_preset() : $preset;
    }

    /**
     * 构建用于原生文章主循环的可复用设置数组。
     *
     * @param array<string,mixed> $overrides Optional overrides.
     * @return array<string,mixed>
     */
    public static function get_native_loop_settings( $overrides = array() ) {
        $preset = isset( $overrides['preset'] ) ? self::sanitize_preset( $overrides['preset'] ) : self::get_current_preset();

        $excerpt_length = absint( function_exists( 'developer_starter_get_option' ) ? developer_starter_get_option( 'article_excerpt_length', 0 ) : 0 );
        if ( $excerpt_length <= 0 ) {
            $excerpt_length = 25;
        }

        $thumb_height = absint( function_exists( 'developer_starter_get_option' ) ? developer_starter_get_option( 'article_thumb_height', 0 ) : 0 );
        if ( $thumb_height <= 0 ) {
            $thumb_height = 220;
        }

        $settings = array(
            'preset'            => $preset,
            'grid_columns'      => 3,
            'thumb_height'      => $thumb_height,
            'thumb_fit'         => function_exists( 'developer_starter_get_thumbnail_display_mode' ) ? developer_starter_get_thumbnail_display_mode() : 'cover',
            'show_thumb'        => ! (bool) ( function_exists( 'developer_starter_get_option' ) ? developer_starter_get_option( 'hide_article_thumb', '' ) : false ),
            'show_excerpt'      => ! (bool) ( function_exists( 'developer_starter_get_option' ) ? developer_starter_get_option( 'hide_article_excerpt', '' ) : false ),
            'show_date'         => ! (bool) ( function_exists( 'developer_starter_get_option' ) ? developer_starter_get_option( 'hide_article_date', '' ) : false ),
            'show_author'       => ! (bool) ( function_exists( 'developer_starter_get_option' ) ? developer_starter_get_option( 'hide_article_author', '' ) : false ),
            'show_category'     => ! (bool) ( function_exists( 'developer_starter_get_option' ) ? developer_starter_get_option( 'hide_article_category', '' ) : false ),
            'show_views'        => (bool) ( function_exists( 'developer_starter_get_option' ) ? developer_starter_get_option( 'post_views_enable', '' ) : false ),
            'excerpt_length'    => $excerpt_length,
        );
        $settings = array_merge( $settings, self::get_native_preset_defaults( $preset, $settings ) );
        $settings = array_merge( $settings, $overrides );

        $grid_columns = isset( $settings['grid_columns'] ) ? (string) $settings['grid_columns'] : '3';
        $settings['grid_columns'] = in_array( $grid_columns, array( '2', '3', '4' ), true ) ? (int) $grid_columns : 3;
        $settings['grid_classes'] = isset( $settings['grid_classes'] ) && '' !== trim( (string) $settings['grid_classes'] )
            ? trim( (string) $settings['grid_classes'] )
            : 'news-grid grid-cols-' . $settings['grid_columns'] . ' qiling-native-blog-grid qiling-native-blog-grid-' . $preset;
        $settings['card_classes'] = isset( $settings['card_classes'] ) && '' !== trim( (string) $settings['card_classes'] )
            ? trim( (string) $settings['card_classes'] )
            : 'news-card qiling-native-blog-card qiling-native-blog-card-' . $preset;

        return $settings;
    }

    /**
     * 获取分类归档展示节奏的预设默认值。
     *
     * @param string|null $preset Optional preset slug.
     * @return array<string,mixed>
     */
    public static function get_category_archive_defaults( $preset = null ) {
        $preset = null === $preset ? self::get_current_preset() : self::sanitize_preset( $preset );

        return self::apply_category_preset_customization(
            $preset,
            self::get_builtin_category_archive_defaults( $preset )
        );
    }

    /**
     * 根据预设默认配置与分类覆盖项解析分类归档配置。
     *
     * @param int                $term_id Category term ID.
     * @param array<string,mixed> $settings Optional category settings array.
     * @return array<string,mixed>
     */
    public static function resolve_category_archive_settings( $term_id, $settings = array() ) {
        $term_id = absint( $term_id );
        $settings = is_array( $settings ) ? $settings : array();

        $preset = '';
        if ( self::should_force_default_preset_for_category( $term_id, $settings ) ) {
            $preset = 'default';
        } elseif ( ! empty( $settings['blog_visual_preset'] ) ) {
            $preset = self::sanitize_preset( $settings['blog_visual_preset'], true );
            if ( 'inherit' === $preset ) {
                $preset = '';
            }
        }

        if ( '' === $preset ) {
            $preset = self::get_category_preset( $term_id );
        }

        if ( '' === $preset ) {
            $global_preset = function_exists( 'developer_starter_get_option' )
                ? developer_starter_get_option( self::OPTION_KEY, 'default' )
                : 'default';
            $preset = self::sanitize_preset( $global_preset );
        }

        $resolved = self::get_category_archive_defaults( $preset );

        $layout = $term_id > 0 ? sanitize_key( (string) get_term_meta( $term_id, 'ds_category_layout', true ) ) : '';
        if ( in_array( $layout, array_keys( self::get_category_layout_choices() ), true ) ) {
            $resolved['layout'] = $layout;
            $resolved['has_custom_layout'] = true;
        } else {
            $resolved['has_custom_layout'] = false;
        }

        $columns = $term_id > 0 ? (string) get_term_meta( $term_id, 'ds_category_columns', true ) : '';
        if ( in_array( $columns, array( '2', '3', '4' ), true ) ) {
            $resolved['columns'] = (int) $columns;
            $resolved['has_custom_columns'] = true;
        } else {
            $resolved['has_custom_columns'] = false;
        }

        $posts_per_page = $term_id > 0 ? absint( get_term_meta( $term_id, 'ds_category_posts_per_page', true ) ) : 0;
        if ( $posts_per_page > 0 ) {
            $resolved['posts_per_page'] = $posts_per_page;
            $resolved['has_custom_posts_per_page'] = true;
        } else {
            $resolved['has_custom_posts_per_page'] = false;
        }

        $thumb_height = $term_id > 0 ? absint( get_term_meta( $term_id, 'ds_category_thumb_height', true ) ) : 0;
        if ( $thumb_height > 0 ) {
            $resolved['thumb_height'] = $thumb_height;
            $resolved['has_custom_thumb_height'] = true;
        } else {
            $resolved['has_custom_thumb_height'] = false;
        }

        $excerpt_length = $term_id > 0 ? absint( get_term_meta( $term_id, 'ds_category_excerpt_length', true ) ) : 0;
        if ( $excerpt_length > 0 ) {
            $resolved['excerpt_length'] = $excerpt_length;
            $resolved['has_custom_excerpt_length'] = true;
        } else {
            $resolved['has_custom_excerpt_length'] = false;
        }

        self::apply_boolean_term_override( $resolved, $term_id, 'ds_category_hide_thumb', 'hide_thumb' );
        self::apply_boolean_term_override( $resolved, $term_id, 'ds_category_hide_excerpt', 'hide_excerpt' );
        self::apply_boolean_term_override( $resolved, $term_id, 'ds_category_hide_date', 'hide_date' );
        self::apply_boolean_term_override( $resolved, $term_id, 'ds_category_hide_category', 'hide_category' );
        self::apply_boolean_term_override( $resolved, $term_id, 'ds_category_hide_author', 'hide_author' );
        self::apply_boolean_term_override( $resolved, $term_id, 'ds_category_hide_breadcrumb', 'hide_breadcrumb' );

        return $resolved;
    }

    /**
     * 在博客相关上下文中为 body 标签添加博客预设样式类名。
     *
     * @param array<int,string> $classes Existing classes.
     * @return array<int,string>
     */
    public static function filter_body_class( $classes ) {
        if ( ! self::should_apply_body_class() ) {
            return $classes;
        }

        $classes[] = sanitize_html_class( 'qiling-blog-preset-' . self::get_current_preset(), 'qiling-blog-preset-default' );

        return $classes;
    }

    /**
     * 获取博客预设自定义内联 CSS 的专用样式句柄。
     *
     * @return string
     */
    public static function get_preset_custom_css_handle() {
        return 'developer-starter-blog-presets-overrides';
    }

    /**
     * 获取博客视觉预设前台样式表的资源句柄。
     *
     * @param mixed $preset Preset value.
     * @return string
     */
    public static function get_preset_style_handle( $preset ) {
        $preset = self::sanitize_preset( $preset );

        switch ( $preset ) {
            case 'developer':
                return 'developer-starter-blog-presets';

            case 'minimal':
                return 'developer-starter-blog-presets-minimal';

            case 'artist':
                return 'developer-starter-blog-presets-artist';
        }

        return '';
    }

    /**
     * 在预设样式表加载完毕后追加单个预设的自定义 CSS。
     *
     * @param string|null $version Optional style version passed by the asset loader.
     * @return void
     */
    public static function enqueue_preset_custom_css( $version = null ) {
        unset( $version );

        $override_handle = self::get_preset_custom_css_handle();

        if ( ! wp_style_is( $override_handle, 'enqueued' ) ) {
            return;
        }

        $chunks = array();
        foreach ( array_keys( self::get_customizable_preset_choices() ) as $preset ) {
            $style_handle = self::get_preset_style_handle( $preset );
            if ( '' === $style_handle || ! wp_style_is( $style_handle, 'enqueued' ) ) {
                continue;
            }

            $overrides = self::get_preset_customization_values( $preset );
            $css = isset( $overrides['custom_css'] ) ? trim( (string) $overrides['custom_css'] ) : '';
            if ( '' === $css ) {
                continue;
            }

            $chunks[] = '/* ' . $preset . ' preset custom CSS */' . "\n" . $css;
        }

        if ( empty( $chunks ) ) {
            return;
        }

        wp_add_inline_style( $override_handle, implode( "\n\n", $chunks ) );
    }

    /**
     * 为模板中心追加开发者博客的虚拟条目。
     *
     * @param array<int,array<string,mixed>> $catalog Existing catalog.
     * @return array<int,array<string,mixed>>
     */
    public static function extend_template_center_catalog( $catalog ) {
        if ( ! is_array( $catalog ) || empty( $catalog ) ) {
            return $catalog;
        }

        $base_entry = null;
        foreach ( $catalog as $entry ) {
            if ( isset( $entry['template'] ) && 'templates/template-blog.php' === (string) $entry['template'] ) {
                $base_entry = $entry;
                break;
            }
        }

        if ( ! is_array( $base_entry ) ) {
            return $catalog;
        }

        $existing_ids = array();
        foreach ( $catalog as $entry ) {
            if ( isset( $entry['id'] ) ) {
                $existing_ids[] = (string) $entry['id'];
            }
        }

        $appended = false;
        foreach ( self::get_template_center_variant_definitions() as $entry_id => $definition ) {
            if ( in_array( $entry_id, $existing_ids, true ) ) {
                continue;
            }

            $variant_entry = $base_entry;
            $variant_entry['id'] = $entry_id;
            $variant_entry['label'] = $definition['label'];
            $variant_entry['description'] = $definition['description'];
            $variant_entry['scenario'] = $definition['scenario'];
            $variant_entry['badges'] = $definition['badges'];
            $variant_entry['template_variant'] = $definition['template_variant'];
            $variant_entry['order'] = isset( $base_entry['order'] ) ? absint( $base_entry['order'] ) + absint( $definition['order_offset'] ) : 111;
            $catalog[] = $variant_entry;
            $appended = true;
        }

        if ( $appended ) {
            usort(
                $catalog,
                function( $a, $b ) {
                    $order_a = isset( $a['order'] ) ? absint( $a['order'] ) : 999;
                    $order_b = isset( $b['order'] ) ? absint( $b['order'] ) : 999;
                    if ( $order_a === $order_b ) {
                        return strcmp( (string) $a['label'], (string) $b['label'] );
                    }

                    return $order_a < $order_b ? -1 : 1;
                }
            );
        }

        return $catalog;
    }

    /**
     * 应用模板中心特定变体的元数据。
     *
     * @param int    $post_id Page ID.
     * @param string $template Template slug.
     * @param string $variant Variant slug.
     * @return void
     */
    public static function apply_template_center_variant( $post_id, $template, $variant ) {
        $post_id = absint( $post_id );
        $variant = sanitize_key( (string) $variant );
        $template = str_replace( '\\', '/', sanitize_text_field( (string) $template ) );

        if ( $post_id <= 0 || 'templates/template-blog.php' !== $template ) {
            return;
        }

        $preset = '';
        if ( self::TEMPLATE_CENTER_VARIANT_DEVELOPER === $variant ) {
            $preset = 'developer';
        } elseif ( self::TEMPLATE_CENTER_VARIANT_MINIMAL === $variant ) {
            $preset = 'minimal';
        } elseif ( self::TEMPLATE_CENTER_VARIANT_ARTIST === $variant ) {
            $preset = 'artist';
        }

        if ( '' === $preset ) {
            return;
        }

        self::set_page_preset( $post_id, $preset );
        update_post_meta( $post_id, '_qiling_template_center_variant', $variant );
    }

    /**
     * 解析预设继承链中的当前页面来源 ID。
     *
     * @return int
     */
    private static function get_current_source_post_id() {
        if ( is_page() ) {
            return absint( get_queried_object_id() );
        }

        if ( is_home() ) {
            return absint( get_option( 'page_for_posts' ) );
        }

        return 0;
    }

    /**
     * 解析预设继承链中的当前分类来源 ID。
     *
     * @return int
     */
    private static function get_current_source_term_id() {
        if ( is_category() ) {
            return absint( get_queried_object_id() );
        }

        if ( is_singular( 'post' ) ) {
            return self::get_post_inherited_category_id( get_queried_object_id() );
        }

        return 0;
    }

    /**
     * 为单篇文章解析最相关的分类来源。
     *
     * 优先级匹配顺序：
     * 1. SEO 主分类（若已设置且有效）。
     * 2. 首个显式配置了博客预设的分类。
     * 3. 首个高级筛选分类。
     * 4. 首个所属文章分类。
     *
     * @param int $post_id Post ID.
     * @return int
     */
    private static function get_post_inherited_category_id( $post_id ) {
        $post_id = absint( $post_id );
        if ( $post_id <= 0 ) {
            return 0;
        }

        $categories = get_the_category( $post_id );
        if ( empty( $categories ) || ! is_array( $categories ) ) {
            return 0;
        }

        $category_ids = array_map(
            static function ( $category ) {
                return isset( $category->term_id ) ? absint( $category->term_id ) : 0;
            },
            $categories
        );
        $category_ids = array_values( array_filter( $category_ids ) );
        if ( empty( $category_ids ) ) {
            return 0;
        }

        $yoast_primary = absint( get_post_meta( $post_id, '_yoast_wpseo_primary_category', true ) );
        if ( $yoast_primary > 0 && in_array( $yoast_primary, $category_ids, true ) ) {
            return $yoast_primary;
        }

        foreach ( $category_ids as $category_id ) {
            if ( '' !== self::get_category_preset( $category_id ) ) {
                return $category_id;
            }
        }

        foreach ( $category_ids as $category_id ) {
            if ( ! empty( get_term_meta( $category_id, 'ds_adv_filter_enabled', true ) ) ) {
                return $category_id;
            }
        }

        return (int) $category_ids[0];
    }

    /**
     * 判断分类是否跳过博客预设并回退到
     * 遵循默认归档节奏。
     *
     * 高级分类筛选默认保留在基础展示链条中，
     * 而非切换到博客专属预设链条。
     *
     * @param int                $term_id Category term ID.
     * @param array<string,mixed> $settings Optional category settings array.
     * @return bool
     */
    private static function should_force_default_preset_for_category( $term_id, $settings = array() ) {
        $term_id = absint( $term_id );
        if ( $term_id <= 0 ) {
            return false;
        }

        if ( is_array( $settings ) && array_key_exists( 'adv_filter_enabled', $settings ) ) {
            return ! empty( $settings['adv_filter_enabled'] );
        }

        return ! empty( get_term_meta( $term_id, 'ds_adv_filter_enabled', true ) );
    }

    /**
     * 判断当前请求是否应在 body 上添加预设类名。
     *
     * @return bool
     */
    private static function should_apply_body_class() {
        return is_home()
            || is_category()
            || is_tag()
            || is_date()
            || is_singular( 'post' )
            || is_page_template( 'templates/template-blog.php' )
            || is_page_template( 'templates/template-topic.php' )
            || is_page_template( 'templates/template-latest-posts.php' );
    }

    /**
     * 获取模板中心博客预设的变体定义。
     *
     * @return array<string,array<string,mixed>>
     */
    private static function get_template_center_variant_definitions() {
        return array(
            'developer-blog' => array(
                'label'            => __( '开发者博客', 'developer-starter' ),
                'description'      => __( '面向技术文章、开发日志、接口文档和开源内容的博客入口，默认使用开发者风格预设。', 'developer-starter' ),
                'scenario'         => __( '技术文章、开发日志、文档型博客', 'developer-starter' ),
                'badges'           => array(
                    __( '开发者', 'developer-starter' ),
                    __( '技术内容', 'developer-starter' ),
                    __( '预设样板', 'developer-starter' ),
                ),
                'template_variant' => self::TEMPLATE_CENTER_VARIANT_DEVELOPER,
                'order_offset'     => 1,
            ),
            'minimal-blog'   => array(
                'label'            => __( '极简博客', 'developer-starter' ),
                'description'      => __( '面向个人写作、独立博客和克制型内容展示的轻量博客入口，默认使用极简风格预设。', 'developer-starter' ),
                'scenario'         => __( '个人写作、独立博客、产品随笔', 'developer-starter' ),
                'badges'           => array(
                    __( '极简', 'developer-starter' ),
                    __( '留白排版', 'developer-starter' ),
                    __( '预设样板', 'developer-starter' ),
                ),
                'template_variant' => self::TEMPLATE_CENTER_VARIANT_MINIMAL,
                'order_offset'     => 2,
            ),
            'artist-blog'    => array(
                'label'            => __( '艺术家博客', 'developer-starter' ),
                'description'      => __( '面向作品笔记、图像随笔、创作过程和视觉档案的表达型博客入口，默认使用艺术家风格预设。', 'developer-starter' ),
                'scenario'         => __( '作品笔记、图像随笔、创作档案', 'developer-starter' ),
                'badges'           => array(
                    __( '艺术家', 'developer-starter' ),
                    __( '拼贴编排', 'developer-starter' ),
                    __( '预设样板', 'developer-starter' ),
                ),
                'template_variant' => self::TEMPLATE_CENTER_VARIANT_ARTIST,
                'order_offset'     => 3,
            ),
        );
    }

    /**
     * 获取原生循环在指定预设下的默认配置。
     *
     * @param string              $preset Preset slug.
     * @param array<string,mixed> $settings Current settings.
     * @return array<string,mixed>
     */
    private static function get_native_preset_defaults( $preset, $settings ) {
        return self::apply_native_preset_customization(
            $preset,
            self::get_builtin_native_preset_defaults( $preset, $settings )
        );
    }

    /**
     * 获取用户自定义前内置的原生预设默认配置。
     *
     * @param string              $preset Preset slug.
     * @param array<string,mixed> $settings Base runtime settings.
     * @return array<string,mixed>
     */
    private static function get_builtin_native_preset_defaults( $preset, $settings ) {
        $preset = self::sanitize_preset( $preset );
        $excerpt_length = isset( $settings['excerpt_length'] ) ? absint( $settings['excerpt_length'] ) : 25;
        $thumb_height = isset( $settings['thumb_height'] ) ? absint( $settings['thumb_height'] ) : 220;

        switch ( $preset ) {
            case 'developer':
                return array(
                    'show_author'       => false,
                    'show_category'     => true,
                    'show_reading_time' => true,
                    'excerpt_length'    => max( 28, $excerpt_length ),
                );

            case 'minimal':
                return array(
                    'show_author'       => false,
                    'show_category'     => false,
                    'show_reading_time' => false,
                    'excerpt_length'    => max( 14, min( 22, $excerpt_length ) ),
                );

            case 'artist':
                return array(
                    'show_author'       => false,
                    'show_category'     => true,
                    'show_reading_time' => false,
                    'excerpt_length'    => max( 16, min( 24, $excerpt_length ) ),
                    'thumb_height'      => max( 300, $thumb_height ),
                );

            default:
                return array(
                    'show_reading_time' => false,
                );
        }
    }

    /**
     * 获取用户自定义前内置的分类归档默认配置。
     *
     * @param string $preset Preset slug.
     * @return array<string,mixed>
     */
    private static function get_builtin_category_archive_defaults( $preset ) {
        $preset = self::sanitize_preset( $preset );
        $global_posts_per_page = absint( function_exists( 'developer_starter_get_option' ) ? developer_starter_get_option( 'category_per_page', 0 ) : 0 );

        $defaults = array(
            'preset'          => $preset,
            'layout'          => 'card',
            'columns'         => 3,
            'posts_per_page'  => $global_posts_per_page,
            'thumb_height'    => 200,
            'excerpt_length'  => 40,
            'hide_thumb'      => false,
            'hide_excerpt'    => false,
            'hide_date'       => false,
            'hide_category'   => false,
            'hide_author'     => false,
            'hide_breadcrumb' => false,
        );

        switch ( $preset ) {
            case 'developer':
                $defaults['layout'] = 'list';
                $defaults['columns'] = 2;
                $defaults['thumb_height'] = 240;
                $defaults['excerpt_length'] = 34;
                break;

            case 'minimal':
                $defaults['layout'] = 'card';
                $defaults['columns'] = 2;
                $defaults['thumb_height'] = 280;
                $defaults['excerpt_length'] = 18;
                break;

            case 'artist':
                $defaults['layout'] = 'card';
                $defaults['columns'] = 2;
                $defaults['thumb_height'] = 320;
                $defaults['excerpt_length'] = 20;
                break;
        }

        return $defaults;
    }

    /**
     * 将用户自定义配置合并至原生预设默认值中。
     *
     * @param string              $preset   Preset slug.
     * @param array<string,mixed> $settings Runtime settings.
     * @return array<string,mixed>
     */
    private static function apply_native_preset_customization( $preset, $settings ) {
        $preset = self::sanitize_preset( $preset );
        if ( 'default' === $preset ) {
            return $settings;
        }

        $overrides = self::get_preset_customization_values( $preset );
        if ( ! empty( $overrides['native_columns'] ) && in_array( (string) $overrides['native_columns'], array( '2', '3', '4' ), true ) ) {
            $settings['grid_columns'] = (int) $overrides['native_columns'];
        }
        if ( ! empty( $overrides['native_thumb_height'] ) ) {
            $settings['thumb_height'] = absint( $overrides['native_thumb_height'] );
        }
        if ( ! empty( $overrides['native_excerpt_length'] ) ) {
            $settings['excerpt_length'] = absint( $overrides['native_excerpt_length'] );
        }

        $visibility_map = array(
            'native_show_thumb'        => 'show_thumb',
            'native_show_excerpt'      => 'show_excerpt',
            'native_show_date'         => 'show_date',
            'native_show_author'       => 'show_author',
            'native_show_category'     => 'show_category',
            'native_show_reading_time' => 'show_reading_time',
        );

        foreach ( $visibility_map as $field_key => $setting_key ) {
            if ( ! isset( $overrides[ $field_key ] ) ) {
                continue;
            }
            if ( 'show' === $overrides[ $field_key ] ) {
                $settings[ $setting_key ] = true;
            } elseif ( 'hide' === $overrides[ $field_key ] ) {
                $settings[ $setting_key ] = false;
            }
        }

        return $settings;
    }

    /**
     * 将用户自定义配置合并至分类预设默认值中。
     *
     * @param string              $preset   Preset slug.
     * @param array<string,mixed> $defaults Category defaults.
     * @return array<string,mixed>
     */
    private static function apply_category_preset_customization( $preset, $defaults ) {
        $preset = self::sanitize_preset( $preset );
        if ( 'default' === $preset ) {
            return $defaults;
        }

        $overrides = self::get_preset_customization_values( $preset );

        if ( ! empty( $overrides['category_layout'] ) && in_array( (string) $overrides['category_layout'], array_keys( self::get_category_layout_choices() ), true ) ) {
            $defaults['layout'] = (string) $overrides['category_layout'];
        }
        if ( ! empty( $overrides['category_columns'] ) && in_array( (string) $overrides['category_columns'], array( '2', '3', '4' ), true ) ) {
            $defaults['columns'] = (int) $overrides['category_columns'];
        }
        if ( ! empty( $overrides['category_posts_per_page'] ) ) {
            $defaults['posts_per_page'] = absint( $overrides['category_posts_per_page'] );
        }
        if ( ! empty( $overrides['category_thumb_height'] ) ) {
            $defaults['thumb_height'] = absint( $overrides['category_thumb_height'] );
        }
        if ( ! empty( $overrides['category_excerpt_length'] ) ) {
            $defaults['excerpt_length'] = absint( $overrides['category_excerpt_length'] );
        }

        $visibility_map = array(
            'category_show_thumb'      => 'hide_thumb',
            'category_show_excerpt'    => 'hide_excerpt',
            'category_show_date'       => 'hide_date',
            'category_show_category'   => 'hide_category',
            'category_show_author'     => 'hide_author',
            'category_show_breadcrumb' => 'hide_breadcrumb',
        );

        foreach ( $visibility_map as $field_key => $default_key ) {
            if ( ! isset( $overrides[ $field_key ] ) ) {
                continue;
            }
            if ( 'show' === $overrides[ $field_key ] ) {
                $defaults[ $default_key ] = false;
            } elseif ( 'hide' === $overrides[ $field_key ] ) {
                $defaults[ $default_key ] = true;
            }
        }

        return $defaults;
    }

    /**
     * 加载指定预设当前的自定义配置值。
     *
     * @param string $preset Preset slug.
     * @return array<string,mixed>
     */
    private static function get_preset_customization_values( $preset ) {
        $preset = self::sanitize_preset( $preset );
        if ( 'default' === $preset ) {
            return array();
        }

        $values = array();
        foreach ( self::get_preset_customization_schema() as $group ) {
            $fields = isset( $group['fields'] ) && is_array( $group['fields'] ) ? $group['fields'] : array();
            foreach ( $fields as $field_key => $field_schema ) {
                $option_key = self::get_preset_customization_option_key( $preset, $field_key );
                $raw_value = function_exists( 'developer_starter_get_option' )
                    ? developer_starter_get_option( $option_key, '' )
                    : '';
                $values[ $field_key ] = self::sanitize_preset_customization_field_value( $raw_value, $field_schema );
            }
        }

        return $values;
    }

    /**
     * 依据字段模式清理预设自定义字段值。
     *
     * @param mixed               $value Raw field value.
     * @param array<string,mixed> $field_schema Field schema.
     * @return mixed
     */
    private static function sanitize_preset_customization_field_value( $value, $field_schema ) {
        $field_schema = is_array( $field_schema ) ? $field_schema : array();
        $type = isset( $field_schema['type'] ) ? (string) $field_schema['type'] : 'text';

        if ( 'number' === $type ) {
            $value = is_scalar( $value ) ? trim( (string) $value ) : '';
            if ( '' === $value ) {
                return '';
            }

            $number = absint( $value );
            if ( $number <= 0 ) {
                return '';
            }

            $max = isset( $field_schema['max'] ) ? absint( $field_schema['max'] ) : 0;
            if ( $max > 0 && $number > $max ) {
                $number = $max;
            }

            return (string) $number;
        }

        if ( 'textarea' === $type ) {
            return self::sanitize_custom_css( $value );
        }

        if ( 'select' === $type ) {
            $value = sanitize_key( (string) $value );
            $choices = isset( $field_schema['choices'] ) && is_array( $field_schema['choices'] ) ? array_keys( $field_schema['choices'] ) : array();
            if ( in_array( $value, $choices, true ) ) {
                return $value;
            }

            return '';
        }

        return sanitize_text_field( (string) $value );
    }

    /**
     * 根据分类 Term Meta 布尔覆盖值更新归档配置。
     *
     * @param array<string,mixed> $resolved Resolved settings array.
     * @param int                 $term_id Term ID.
     * @param string              $meta_key Term meta key.
     * @param string              $setting_key Resolved setting key.
     * @return void
     */
    private static function apply_boolean_term_override( &$resolved, $term_id, $meta_key, $setting_key ) {
        if ( $term_id <= 0 || ! metadata_exists( 'term', $term_id, $meta_key ) ) {
            return;
        }

        $resolved[ $setting_key ] = ! empty( get_term_meta( $term_id, $meta_key, true ) );
    }

    /**
     * 清理为预设输入的原始 CSS 代码。
     *
     * @param mixed $css CSS source.
     * @return string
     */
    private static function sanitize_custom_css( $css ) {
        $css = (string) $css;
        if ( '' === trim( $css ) ) {
            return '';
        }

        $css = preg_replace( '#</?style[^>]*>#i', '', $css );
        $css = str_replace( array( '<', '>' ), '', $css );
        $css = preg_replace( "/\r\n|\r/u", "\n", $css );

        return trim( (string) $css );
    }
}
