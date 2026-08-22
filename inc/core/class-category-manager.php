<?php
/**
 * Category Manager - 文章分类增强管理
 * 
 * 为每个分类添加自定义设置：布局、背景色、图标
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Core;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Category_Manager {

    private $layout_options = array();
    private $blog_preset_options = array();

    /**
     * 构造函数
     */
    public function __construct() {
        $this->layout_options = array(
            'card'     => __( '卡片布局', 'developer-starter' ),
            'list'     => __( '列表布局', 'developer-starter' ),
            'grid'     => __( '网格布局', 'developer-starter' ),
            'magazine' => __( '杂志布局', 'developer-starter' ),
        );
        $this->blog_preset_options = class_exists( '\Developer_Starter\Core\Blog_Visual_Manager' )
            ? \Developer_Starter\Core\Blog_Visual_Manager::get_module_preset_choices()
            : array(
                'inherit'   => __( '继承全局博客风格', 'developer-starter' ),
                'default'   => __( '默认企业内容', 'developer-starter' ),
                'developer' => __( '技术开发者', 'developer-starter' ),
                'minimal'   => __( '极简', 'developer-starter' ),
                'artist'    => __( '艺术家', 'developer-starter' ),
            );
        // 分类编辑页面添加字段
        add_action( 'category_add_form_fields', array( $this, 'add_category_fields' ) );
        add_action( 'category_edit_form_fields', array( $this, 'edit_category_fields' ), 10, 2 );
        
        // 保存分类设置
        add_action( 'created_category', array( $this, 'save_category_fields' ) );
        add_action( 'edited_category', array( $this, 'save_category_fields' ) );
        add_action( 'created_term', array( $this, 'save_category_term_fields' ), 10, 3 );
        add_action( 'edited_term', array( $this, 'save_category_term_fields' ), 10, 3 );
        add_action( 'admin_init', array( $this, 'maybe_save_category_fields_from_admin_request' ) );
        
        // 分类列表添加列
        add_filter( 'manage_edit-category_columns', array( $this, 'add_category_columns' ) );
        add_filter( 'manage_category_custom_column', array( $this, 'category_column_content' ), 10, 3 );
        
        // 加载管理端资源
        add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
    }

    /**
     * 新增分类时的字段
     */
    public function add_category_fields() {
        ?>
        <input type="hidden" name="ds_category_fields_submitted" value="1" />
        <div class="form-field">
            <label for="ds_category_layout"><?php esc_html_e( '文章列表布局', 'developer-starter' ); ?></label>
            <select name="ds_category_layout" id="ds_category_layout">
                <?php foreach ( $this->layout_options as $value => $label ) : ?>
                    <option value="<?php echo esc_attr( $value ); ?>"><?php echo esc_html( $label ); ?></option>
                <?php endforeach; ?>
            </select>
            <p class="description"><?php esc_html_e( '选择该分类下文章列表的显示布局', 'developer-starter' ); ?></p>
        </div>

        <div class="form-field">
            <label for="ds_category_blog_visual_preset"><?php esc_html_e( '博客视觉风格', 'developer-starter' ); ?></label>
            <select name="ds_category_blog_visual_preset" id="ds_category_blog_visual_preset">
                <?php foreach ( $this->blog_preset_options as $value => $label ) : ?>
                    <option value="<?php echo esc_attr( $value ); ?>" <?php selected( 'inherit', $value ); ?>><?php echo esc_html( $label ); ?></option>
                <?php endforeach; ?>
            </select>
            <p class="description"><?php esc_html_e( '该分类可以单独覆盖全局博客风格。适合把教程、随笔、作品集分别切成不同风格。', 'developer-starter' ); ?></p>
        </div>

        <div class="form-field">
            <label for="ds_category_video_category"><?php esc_html_e( '视频分类', 'developer-starter' ); ?></label>
            <label style="display: inline-flex; align-items: center; gap: 6px;">
                <input type="checkbox" name="ds_category_video_category" id="ds_category_video_category" value="1" />
                <strong><?php esc_html_e( '启用视频分类模式', 'developer-starter' ); ?></strong>
            </label>
            <p class="description"><?php esc_html_e( '开启后，该分类页将按电影风格展示启灵播放器的视频模式内容', 'developer-starter' ); ?></p>
        </div>
        
        <div class="form-field">
            <label for="ds_category_hide_breadcrumb"><?php esc_html_e( '隐藏头部元素', 'developer-starter' ); ?></label>
            <label style="display: inline-flex; align-items: center; gap: 6px; margin-bottom: 5px;">
                <input type="checkbox" name="ds_category_hide_header" id="ds_category_hide_header" value="1" />
                <strong><?php esc_html_e( '隐藏整个分类页头部 (包含大标题和描述)', 'developer-starter' ); ?></strong>
            </label>
            <label style="display: inline-flex; align-items: center; gap: 6px; margin-bottom: 5px;">
                <input type="checkbox" name="ds_category_hide_breadcrumb" id="ds_category_hide_breadcrumb" value="1" />
                <strong><?php esc_html_e( '不显示分类目录面包屑', 'developer-starter' ); ?></strong>
            </label>
            <label style="display: inline-flex; align-items: center; gap: 6px; margin-bottom: 5px;">
                <input type="checkbox" name="ds_category_hide_count" id="ds_category_hide_count" value="1" />
                <strong><?php esc_html_e( '不显示分类文章数量统计', 'developer-starter' ); ?></strong>
            </label>
            <p class="description"><?php esc_html_e( '开启后，将覆盖主题的全局设置', 'developer-starter' ); ?></p>
        </div>
        
        <div class="form-field">
            <label for="ds_category_bg_color"><?php esc_html_e( '头部背景颜色', 'developer-starter' ); ?></label>
            <input type="text" name="ds_category_bg_color" id="ds_category_bg_color" class="ds-color-picker" value="" />
            <p class="description"><?php esc_html_e( '分类页面头部区域的背景颜色，留空使用默认主题色', 'developer-starter' ); ?></p>
        </div>

        <div class="form-field">
            <label for="ds_category_breadcrumb_color"><?php esc_html_e( '面包屑文字颜色', 'developer-starter' ); ?></label>
            <input type="text" name="ds_category_breadcrumb_color" id="ds_category_breadcrumb_color" class="ds-color-picker" value="" />
            <p class="description"><?php esc_html_e( '分类页面头部面包屑文字颜色，留空使用默认样式', 'developer-starter' ); ?></p>
        </div>

        <div class="form-field">
            <label for="ds_category_header_padding"><?php esc_html_e( '头部高度 (Padding)', 'developer-starter' ); ?></label>
            <input type="text" name="ds_category_header_padding" id="ds_category_header_padding" value="" placeholder="100px 0 60px" />
            <p class="description"><?php esc_html_e( '设置头部内边距控制高度。默认：100px 0 60px (上 左右 下)', 'developer-starter' ); ?></p>
        </div>
        
        <div class="form-field">
            <label for="ds_category_icon"><?php esc_html_e( '分类图标', 'developer-starter' ); ?></label>
            <input type="text" name="ds_category_icon" id="ds_category_icon" value="" />
            <p class="description"><?php esc_html_e( '输入emoji表情或图标图片URL，显示在分类名称前面（选填）', 'developer-starter' ); ?></p>
        </div>
        
        <div class="form-field">
            <label for="ds_category_columns"><?php esc_html_e( '每行列数', 'developer-starter' ); ?></label>
            <select name="ds_category_columns" id="ds_category_columns">
                <option value=""><?php esc_html_e( '默认 (3列)', 'developer-starter' ); ?></option>
                <option value="2"><?php esc_html_e( '2列', 'developer-starter' ); ?></option>
                <option value="3"><?php esc_html_e( '3列', 'developer-starter' ); ?></option>
                <option value="4"><?php esc_html_e( '4列', 'developer-starter' ); ?></option>
            </select>
            <p class="description"><?php esc_html_e( '卡片/网格布局时每行显示的文章数量', 'developer-starter' ); ?></p>
        </div>
        
        <div class="form-field">
            <label for="ds_category_posts_per_page"><?php esc_html_e( '每页显示数量', 'developer-starter' ); ?></label>
            <input type="number" name="ds_category_posts_per_page" id="ds_category_posts_per_page" value="" min="1" max="100" />
            <p class="description"><?php esc_html_e( '该分类页面每页显示的文章数量，留空使用默认设置', 'developer-starter' ); ?></p>
        </div>
        
        <div class="form-field">
            <label for="ds_category_thumb_height"><?php esc_html_e( '缩略图高度(px)', 'developer-starter' ); ?></label>
            <input type="number" name="ds_category_thumb_height" id="ds_category_thumb_height" value="" min="50" max="500" />
            <p class="description"><?php esc_html_e( '文章列表缩略图高度，留空使用默认值 200px', 'developer-starter' ); ?></p>
        </div>
        
        <div class="form-field">
            <label for="ds_category_excerpt_length"><?php esc_html_e( '摘要字数', 'developer-starter' ); ?></label>
            <input type="number" name="ds_category_excerpt_length" id="ds_category_excerpt_length" value="" min="0" max="500" />
            <p class="description"><?php esc_html_e( '文章摘要显示字数，留空使用默认值 40', 'developer-starter' ); ?></p>
        </div>
        
        <div class="form-field">
            <label><?php esc_html_e( '隐藏选项', 'developer-starter' ); ?></label>
            <fieldset style="margin-top: 5px;">
                <label style="display: block; margin-bottom: 5px;">
                    <input type="checkbox" name="ds_category_hide_thumb" value="1" />
                    <?php esc_html_e( '隐藏缩略图', 'developer-starter' ); ?>
                </label>
                <label style="display: block; margin-bottom: 5px;">
                    <input type="checkbox" name="ds_category_hide_excerpt" value="1" />
                    <?php esc_html_e( '隐藏摘要', 'developer-starter' ); ?>
                </label>
                <label style="display: block; margin-bottom: 5px;">
                    <input type="checkbox" name="ds_category_hide_date" value="1" />
                    <?php esc_html_e( '隐藏日期', 'developer-starter' ); ?>
                </label>
                <label style="display: block; margin-bottom: 5px;">
                    <input type="checkbox" name="ds_category_hide_category" value="1" />
                    <?php esc_html_e( '隐藏分类标签', 'developer-starter' ); ?>
                </label>
                <label style="display: block; margin-bottom: 5px;">
                    <input type="checkbox" name="ds_category_hide_author" value="1" />
                    <?php esc_html_e( '隐藏作者', 'developer-starter' ); ?>
                </label>
            </fieldset>
            <p class="description"><?php esc_html_e( '选择要在文章列表中隐藏的元素', 'developer-starter' ); ?></p>
        </div>

        <div class="form-field">
            <label for="ds_category_seo_title"><?php esc_html_e( 'SEO标题', 'developer-starter' ); ?></label>
            <input type="text" name="ds_category_seo_title" id="ds_category_seo_title" value="" />
            <p class="description"><?php esc_html_e( '留空默认：分类名称 + 网站名称', 'developer-starter' ); ?></p>
        </div>

        <div class="form-field">
            <label for="ds_category_seo_keywords"><?php esc_html_e( 'SEO关键词', 'developer-starter' ); ?></label>
            <input type="text" name="ds_category_seo_keywords" id="ds_category_seo_keywords" value="" />
            <p class="description"><?php esc_html_e( '留空默认：分类名称，多个关键词用英文逗号分隔', 'developer-starter' ); ?></p>
        </div>

        <div class="form-field">
            <label for="ds_category_seo_description"><?php esc_html_e( 'SEO描述', 'developer-starter' ); ?></label>
            <textarea name="ds_category_seo_description" id="ds_category_seo_description" rows="3"></textarea>
            <p class="description"><?php esc_html_e( '留空默认：分类描述', 'developer-starter' ); ?></p>
        </div>
        
        <!-- 高级分类筛选设置 -->
        <div class="form-field" style="background: #f0f6fc; padding: 15px; border-radius: 8px; margin-top: 20px; border: 1px solid #c8e1ff;">
            <h3 style="margin: 0 0 15px; font-size: 14px; color: #0366d6;"><?php esc_html_e( '🔖 高级分类筛选设置', 'developer-starter' ); ?></h3>
            
            <div style="margin-bottom: 15px;">
                <label for="ds_adv_filter_enabled" style="display: flex; align-items: center; cursor: pointer;">
                    <input type="checkbox" name="ds_adv_filter_enabled" id="ds_adv_filter_enabled" value="1" style="margin-right: 8px;" />
                    <strong><?php esc_html_e( '启用高级分类筛选', 'developer-starter' ); ?></strong>
                </label>
                <p class="description" style="margin-top: 5px; color: #666;"><?php esc_html_e( '开启后，此分类页面将显示高级筛选按钮，用户可按多层级进行筛选', 'developer-starter' ); ?></p>
            </div>
            
            <div style="margin-top: 15px;">
                <label for="ds_adv_custom_levels"><?php esc_html_e( '自定义筛选层级', 'developer-starter' ); ?></label>
                <textarea name="ds_adv_custom_levels" id="ds_adv_custom_levels" rows="4" style="width: 100%;" placeholder="<?php esc_attr_e( '例如：&#10;平台: Windows,Mac,Linux&#10;类型: 免费,收费,开源', 'developer-starter' ); ?>"></textarea>
                <p class="description"><?php esc_html_e( '每行一个层级，格式：层级名称: 选项1,选项2,选项3', 'developer-starter' ); ?></p>
            </div>
        </div>
        <?php
    }


    /**
     * 编辑分类时的字段
     */
    public function edit_category_fields( $term, $taxonomy ) {
        // 获取已保存的元数据
        $layout = get_term_meta( $term->term_id, 'ds_category_layout', true );
        $blog_visual_preset = get_term_meta( $term->term_id, 'ds_category_blog_visual_preset', true );
        $video_category_enabled = get_term_meta( $term->term_id, 'ds_category_video_category', true );
        $hide_header = get_term_meta( $term->term_id, 'ds_category_hide_header', true );
        $hide_breadcrumb = get_term_meta( $term->term_id, 'ds_category_hide_breadcrumb', true );
        $hide_count = get_term_meta( $term->term_id, 'ds_category_hide_count', true );
        $bg_color = get_term_meta( $term->term_id, 'ds_category_bg_color', true );
        $breadcrumb_color = get_term_meta( $term->term_id, 'ds_category_breadcrumb_color', true );
        $header_padding = get_term_meta( $term->term_id, 'ds_category_header_padding', true );
        $icon = get_term_meta( $term->term_id, 'ds_category_icon', true );
        $columns = get_term_meta( $term->term_id, 'ds_category_columns', true );
        $posts_per_page = get_term_meta( $term->term_id, 'ds_category_posts_per_page', true );
        $thumb_height = get_term_meta( $term->term_id, 'ds_category_thumb_height', true );
        $excerpt_length = get_term_meta( $term->term_id, 'ds_category_excerpt_length', true );
        $hide_thumb = get_term_meta( $term->term_id, 'ds_category_hide_thumb', true );
        $hide_excerpt = get_term_meta( $term->term_id, 'ds_category_hide_excerpt', true );
        $hide_date = get_term_meta( $term->term_id, 'ds_category_hide_date', true );
        $hide_category = get_term_meta( $term->term_id, 'ds_category_hide_category', true );
        $hide_author = get_term_meta( $term->term_id, 'ds_category_hide_author', true );
        $seo_title = get_term_meta( $term->term_id, 'ds_category_seo_title', true );
        $seo_keywords = get_term_meta( $term->term_id, 'ds_category_seo_keywords', true );
        $seo_description = get_term_meta( $term->term_id, 'ds_category_seo_description', true );
        
        // 高级分类筛选设置
        $adv_filter_enabled = get_term_meta( $term->term_id, 'ds_adv_filter_enabled', true );
        $adv_major_cats = get_term_meta( $term->term_id, 'ds_adv_major_cats', true );
        $adv_minor_cats = get_term_meta( $term->term_id, 'ds_adv_minor_cats', true );
        $adv_custom_levels_raw = get_term_meta( $term->term_id, 'ds_adv_custom_levels', true );
        $adv_custom_levels_text = $adv_custom_levels_raw;
        if ( $adv_custom_levels_raw && ( strpos( ltrim( $adv_custom_levels_raw ), '[' ) === 0 || strpos( ltrim( $adv_custom_levels_raw ), '{' ) === 0 ) ) {
            $decoded = json_decode( $adv_custom_levels_raw, true );
            if ( is_array( $decoded ) ) {
                $lines = array();
                foreach ( $decoded as $level ) {
                    $label = isset( $level['label'] ) ? trim( (string) $level['label'] ) : '';
                    $options = isset( $level['options'] ) && is_array( $level['options'] ) ? array_filter( array_map( 'trim', $level['options'] ) ) : array();
                    if ( $label && ! empty( $options ) ) {
                        $lines[] = $label . ': ' . implode( ',', $options );
                    }
                }
                if ( ! empty( $lines ) ) {
                    $adv_custom_levels_text = implode( "\n", $lines );
                }
            }
        }
        ?>
        <tr class="form-field">
            <th scope="row">
                <label for="ds_category_layout"><?php esc_html_e( '文章列表布局', 'developer-starter' ); ?></label>
            </th>
            <td>
                <input type="hidden" name="ds_category_fields_submitted" value="1" />
                <select name="ds_category_layout" id="ds_category_layout" style="width: 200px;">
                    <?php foreach ( $this->layout_options as $value => $label ) : ?>
                        <option value="<?php echo esc_attr( $value ); ?>" <?php selected( $layout, $value ); ?>><?php echo esc_html( $label ); ?></option>
                    <?php endforeach; ?>
                </select>
                <p class="description"><?php esc_html_e( '选择该分类下文章列表的显示布局', 'developer-starter' ); ?></p>
            </td>
        </tr>

        <tr class="form-field">
            <th scope="row">
                <label for="ds_category_blog_visual_preset"><?php esc_html_e( '博客视觉风格', 'developer-starter' ); ?></label>
            </th>
            <td>
                <select name="ds_category_blog_visual_preset" id="ds_category_blog_visual_preset" style="width: 220px;">
                    <?php foreach ( $this->blog_preset_options as $value => $label ) : ?>
                        <option value="<?php echo esc_attr( $value ); ?>" <?php selected( $blog_visual_preset ?: 'inherit', $value ); ?>><?php echo esc_html( $label ); ?></option>
                    <?php endforeach; ?>
                </select>
                <p class="description"><?php esc_html_e( '该分类可以单独覆盖全局博客风格。适合把教程、随笔、作品集分别切成不同风格。', 'developer-starter' ); ?></p>
            </td>
        </tr>

        <tr class="form-field">
            <th scope="row">
                <label for="ds_category_video_category"><?php esc_html_e( '视频分类', 'developer-starter' ); ?></label>
            </th>
            <td>
                <label style="display: inline-flex; align-items: center; gap: 6px;">
                    <input type="checkbox" name="ds_category_video_category" id="ds_category_video_category" value="1" <?php checked( $video_category_enabled, '1' ); ?> />
                    <strong><?php esc_html_e( '启用视频分类模式', 'developer-starter' ); ?></strong>
                </label>
                <p class="description"><?php esc_html_e( '开启后，该分类页将按电影风格展示启灵播放器的视频模式内容', 'developer-starter' ); ?></p>
            </td>
        </tr>
        
        <tr class="form-field">
            <th scope="row">
                <label for="ds_category_hide_breadcrumb"><?php esc_html_e( '隐藏头部元素', 'developer-starter' ); ?></label>
            </th>
            <td>
                <label style="display: block; margin-bottom: 8px;">
                    <input type="checkbox" name="ds_category_hide_header" id="ds_category_hide_header" value="1" <?php checked( $hide_header, '1' ); ?> />
                    <strong><?php esc_html_e( '隐藏整个分类页头部 (包含大标题和描述)', 'developer-starter' ); ?></strong>
                </label>
                <label style="display: block; margin-bottom: 8px;">
                    <input type="checkbox" name="ds_category_hide_breadcrumb" id="ds_category_hide_breadcrumb" value="1" <?php checked( $hide_breadcrumb, '1' ); ?> />
                    <strong><?php esc_html_e( '不显示分类目录面包屑', 'developer-starter' ); ?></strong>
                </label>
                <label style="display: block; margin-bottom: 8px;">
                    <input type="checkbox" name="ds_category_hide_count" id="ds_category_hide_count" value="1" <?php checked( $hide_count, '1' ); ?> />
                    <strong><?php esc_html_e( '不显示分类文章数量统计', 'developer-starter' ); ?></strong>
                </label>
                <p class="description"><?php esc_html_e( '开启后，将覆盖主题全局的分类页头部设置。', 'developer-starter' ); ?></p>
            </td>
        </tr>
        
        <tr class="form-field">
            <th scope="row">
                <label for="ds_category_bg_color"><?php esc_html_e( '头部背景颜色', 'developer-starter' ); ?></label>
            </th>
            <td>
                <input type="text" name="ds_category_bg_color" id="ds_category_bg_color" class="ds-color-picker" value="<?php echo esc_attr( $bg_color ); ?>" />
                <p class="description"><?php esc_html_e( '分类页面头部区域的背景颜色，留空使用默认主题色', 'developer-starter' ); ?></p>
            </td>
        </tr>

        <tr class="form-field">
            <th scope="row">
                <label for="ds_category_breadcrumb_color"><?php esc_html_e( '面包屑文字颜色', 'developer-starter' ); ?></label>
            </th>
            <td>
                <input type="text" name="ds_category_breadcrumb_color" id="ds_category_breadcrumb_color" class="ds-color-picker" value="<?php echo esc_attr( $breadcrumb_color ); ?>" />
                <p class="description"><?php esc_html_e( '分类页面头部面包屑文字颜色，留空使用默认样式', 'developer-starter' ); ?></p>
            </td>
        </tr>

        <tr class="form-field">
            <th scope="row">
                <label for="ds_category_header_padding"><?php esc_html_e( '头部高度 (Padding)', 'developer-starter' ); ?></label>
            </th>
            <td>
                <input type="text" name="ds_category_header_padding" id="ds_category_header_padding" value="<?php echo esc_attr( $header_padding ); ?>" placeholder="100px 0 60px" style="width: 300px;" />
                <p class="description"><?php echo wp_kses_post( __( '设置分类顶部的内边距，控制面包屑区域高度。<br>格式：上内边距 左右内边距 下内边距<br>默认值：100px 0 60px', 'developer-starter' ) ); ?></p>
            </td>
        </tr>
        
        <tr class="form-field">
            <th scope="row">
                <label for="ds_category_icon"><?php esc_html_e( '分类图标', 'developer-starter' ); ?></label>
            </th>
            <td>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <input type="text" name="ds_category_icon" id="ds_category_icon" value="<?php echo esc_attr( $icon ); ?>" style="width: 300px;" />
                    <?php if ( ! empty( $icon ) ) : ?>
                        <span class="ds-icon-preview" style="font-size: 24px;">
                            <?php if ( filter_var( $icon, FILTER_VALIDATE_URL ) ) : ?>
                                <img src="<?php echo esc_url( $icon ); ?>" alt="" style="width: 24px; height: 24px;" />
                            <?php else : ?>
                                <?php echo esc_html( $icon ); ?>
                            <?php endif; ?>
                        </span>
                    <?php endif; ?>
                </div>
                <p class="description"><?php esc_html_e( '输入emoji表情（如 📚）或图标图片URL，显示在分类名称前面（选填）', 'developer-starter' ); ?></p>
            </td>
        </tr>
        
        <tr class="form-field">
            <th scope="row">
                <label for="ds_category_columns"><?php esc_html_e( '每行列数', 'developer-starter' ); ?></label>
            </th>
            <td>
                <select name="ds_category_columns" id="ds_category_columns" style="width: 200px;">
                    <option value="" <?php selected( $columns, '' ); ?>><?php esc_html_e( '默认 (3列)', 'developer-starter' ); ?></option>
                    <option value="2" <?php selected( $columns, '2' ); ?>><?php esc_html_e( '2列', 'developer-starter' ); ?></option>
                    <option value="3" <?php selected( $columns, '3' ); ?>><?php esc_html_e( '3列', 'developer-starter' ); ?></option>
                    <option value="4" <?php selected( $columns, '4' ); ?>><?php esc_html_e( '4列', 'developer-starter' ); ?></option>
                </select>
                <p class="description"><?php esc_html_e( '卡片/网格布局时每行显示的文章数量', 'developer-starter' ); ?></p>
            </td>
        </tr>
        
        <tr class="form-field">
            <th scope="row">
                <label for="ds_category_posts_per_page"><?php esc_html_e( '每页显示数量', 'developer-starter' ); ?></label>
            </th>
            <td>
                <input type="number" name="ds_category_posts_per_page" id="ds_category_posts_per_page" value="<?php echo esc_attr( $posts_per_page ); ?>" min="1" max="100" style="width: 100px;" />
                <p class="description"><?php esc_html_e( '该分类页面每页显示的文章数量，留空使用默认设置', 'developer-starter' ); ?></p>
            </td>
        </tr>
        
        <tr class="form-field">
            <th scope="row">
                <label for="ds_category_thumb_height"><?php esc_html_e( '缩略图高度(px)', 'developer-starter' ); ?></label>
            </th>
            <td>
                <input type="number" name="ds_category_thumb_height" id="ds_category_thumb_height" value="<?php echo esc_attr( $thumb_height ); ?>" min="50" max="500" style="width: 100px;" />
                <p class="description"><?php esc_html_e( '文章列表缩略图高度，留空使用默认值 200px', 'developer-starter' ); ?></p>
            </td>
        </tr>
        
        <tr class="form-field">
            <th scope="row">
                <label for="ds_category_excerpt_length"><?php esc_html_e( '摘要字数', 'developer-starter' ); ?></label>
            </th>
            <td>
                <input type="number" name="ds_category_excerpt_length" id="ds_category_excerpt_length" value="<?php echo esc_attr( $excerpt_length ); ?>" min="0" max="500" style="width: 100px;" />
                <p class="description"><?php esc_html_e( '文章摘要显示字数，留空使用默认值 40', 'developer-starter' ); ?></p>
            </td>
        </tr>
        
        <tr class="form-field">
            <th scope="row">
                <label><?php esc_html_e( '隐藏选项', 'developer-starter' ); ?></label>
            </th>
            <td>
                <fieldset>
                    <label style="display: block; margin-bottom: 8px;">
                        <input type="checkbox" name="ds_category_hide_thumb" value="1" <?php checked( $hide_thumb, '1' ); ?> />
                        <?php esc_html_e( '隐藏缩略图', 'developer-starter' ); ?>
                    </label>
                    <label style="display: block; margin-bottom: 8px;">
                        <input type="checkbox" name="ds_category_hide_excerpt" value="1" <?php checked( $hide_excerpt, '1' ); ?> />
                        <?php esc_html_e( '隐藏摘要', 'developer-starter' ); ?>
                    </label>
                    <label style="display: block; margin-bottom: 8px;">
                        <input type="checkbox" name="ds_category_hide_date" value="1" <?php checked( $hide_date, '1' ); ?> />
                        <?php esc_html_e( '隐藏日期', 'developer-starter' ); ?>
                    </label>
                    <label style="display: block; margin-bottom: 8px;">
                        <input type="checkbox" name="ds_category_hide_category" value="1" <?php checked( $hide_category, '1' ); ?> />
                        <?php esc_html_e( '隐藏分类标签', 'developer-starter' ); ?>
                    </label>
                    <label style="display: block; margin-bottom: 8px;">
                        <input type="checkbox" name="ds_category_hide_author" value="1" <?php checked( $hide_author, '1' ); ?> />
                        <?php esc_html_e( '隐藏作者', 'developer-starter' ); ?>
                    </label>
                </fieldset>
                <p class="description"><?php esc_html_e( '选择要在文章列表中隐藏的元素', 'developer-starter' ); ?></p>
            </td>
        </tr>

        <tr class="form-field">
            <th scope="row">
                <label for="ds_category_seo_title"><?php esc_html_e( 'SEO标题', 'developer-starter' ); ?></label>
            </th>
            <td>
                <input type="text" name="ds_category_seo_title" id="ds_category_seo_title" value="<?php echo esc_attr( $seo_title ); ?>" style="width: 320px;" />
                <p class="description"><?php esc_html_e( '留空默认：分类名称 + 网站名称', 'developer-starter' ); ?></p>
            </td>
        </tr>

        <tr class="form-field">
            <th scope="row">
                <label for="ds_category_seo_keywords"><?php esc_html_e( 'SEO关键词', 'developer-starter' ); ?></label>
            </th>
            <td>
                <input type="text" name="ds_category_seo_keywords" id="ds_category_seo_keywords" value="<?php echo esc_attr( $seo_keywords ); ?>" style="width: 320px;" />
                <p class="description"><?php esc_html_e( '留空默认：分类名称，多个关键词用英文逗号分隔', 'developer-starter' ); ?></p>
            </td>
        </tr>

        <tr class="form-field">
            <th scope="row">
                <label for="ds_category_seo_description"><?php esc_html_e( 'SEO描述', 'developer-starter' ); ?></label>
            </th>
            <td>
                <textarea name="ds_category_seo_description" id="ds_category_seo_description" rows="4" style="width: 360px;"><?php echo esc_textarea( $seo_description ); ?></textarea>
                <p class="description"><?php esc_html_e( '留空默认：分类描述', 'developer-starter' ); ?></p>
            </td>
        </tr>
        
        <!-- 高级分类筛选设置 -->
        <tr class="form-field">
            <th colspan="2" style="padding: 0;">
                <div style="background: #f0f6fc; padding: 20px; border-radius: 8px; margin: 15px 0; border: 1px solid #c8e1ff;">
                    <h3 style="margin: 0 0 15px; font-size: 14px; color: #0366d6;"><?php esc_html_e( '🔖 高级分类筛选设置', 'developer-starter' ); ?></h3>
                    
                    <div style="margin-bottom: 15px;">
                        <label for="ds_adv_filter_enabled" style="display: flex; align-items: center; cursor: pointer;">
                            <input type="checkbox" name="ds_adv_filter_enabled" id="ds_adv_filter_enabled" value="1" <?php checked( $adv_filter_enabled, '1' ); ?> style="margin-right: 8px;" />
                            <strong><?php esc_html_e( '启用高级分类筛选', 'developer-starter' ); ?></strong>
                        </label>
                        <p class="description" style="margin-top: 5px; color: #666;"><?php esc_html_e( '开启后，此分类页面将显示高级筛选按钮，用户可按多层级进行筛选', 'developer-starter' ); ?></p>
                    </div>
                    
                    <div style="padding: 10px 0;">
                        <label for="ds_adv_custom_levels" style="display: block; margin-bottom: 5px; font-weight: 600;"><?php esc_html_e( '自定义筛选层级', 'developer-starter' ); ?></label>
                        <textarea name="ds_adv_custom_levels" id="ds_adv_custom_levels" rows="5" style="width: 100%;" placeholder="<?php esc_attr_e( '例如：&#10;平台: Windows,Mac,Linux&#10;类型: 免费,收费,开源', 'developer-starter' ); ?>"><?php echo esc_textarea( $adv_custom_levels_text ); ?></textarea>
                        <p class="description" style="margin-top: 5px;"><?php esc_html_e( '每行一个层级，格式：层级名称: 选项1,选项2,选项3', 'developer-starter' ); ?></p>
                    </div>
                </div>
            </th>
        </tr>
        <?php
    }

    /**
     * 保存分类设置
     */
    public function save_category_fields( $term_id ) {
        $term_id = absint( $term_id );
        if ( ! $term_id ) {
            return;
        }

        if ( ! $this->has_category_settings_payload() ) {
            return;
        }

        // 布局
        if ( isset( $_POST['ds_category_layout'] ) ) {
            $layout = sanitize_key( wp_unslash( $_POST['ds_category_layout'] ) );
            if ( '' !== $layout ) {
                update_term_meta( $term_id, 'ds_category_layout', $layout );
            } else {
                delete_term_meta( $term_id, 'ds_category_layout' );
            }
        }

        if ( isset( $_POST['ds_category_blog_visual_preset'] ) ) {
            $blog_visual_preset = sanitize_key( wp_unslash( $_POST['ds_category_blog_visual_preset'] ) );
            if ( '' === $blog_visual_preset || 'inherit' === $blog_visual_preset ) {
                delete_term_meta( $term_id, 'ds_category_blog_visual_preset' );
            } else {
                update_term_meta( $term_id, 'ds_category_blog_visual_preset', $blog_visual_preset );
            }
        }

        if ( isset( $_POST['ds_category_video_category'] ) && '1' === (string) wp_unslash( $_POST['ds_category_video_category'] ) ) {
            update_term_meta( $term_id, 'ds_category_video_category', '1' );
        } else {
            delete_term_meta( $term_id, 'ds_category_video_category' );
        }
        
        if ( isset( $_POST['ds_category_hide_header'] ) && '1' === (string) wp_unslash( $_POST['ds_category_hide_header'] ) ) {
            update_term_meta( $term_id, 'ds_category_hide_header', '1' );
        } else {
            delete_term_meta( $term_id, 'ds_category_hide_header' );
        }

        if ( isset( $_POST['ds_category_hide_breadcrumb'] ) && '1' === (string) wp_unslash( $_POST['ds_category_hide_breadcrumb'] ) ) {
            update_term_meta( $term_id, 'ds_category_hide_breadcrumb', '1' );
        } else {
            delete_term_meta( $term_id, 'ds_category_hide_breadcrumb' );
        }

        if ( isset( $_POST['ds_category_hide_count'] ) && '1' === (string) wp_unslash( $_POST['ds_category_hide_count'] ) ) {
            update_term_meta( $term_id, 'ds_category_hide_count', '1' );
        } else {
            delete_term_meta( $term_id, 'ds_category_hide_count' );
        }
        
        // 背景颜色
        if ( isset( $_POST['ds_category_bg_color'] ) ) {
            $bg_color = sanitize_text_field( wp_unslash( $_POST['ds_category_bg_color'] ) );
            if ( '' !== $bg_color ) {
                update_term_meta( $term_id, 'ds_category_bg_color', $bg_color );
            } else {
                delete_term_meta( $term_id, 'ds_category_bg_color' );
            }
        }

        if ( isset( $_POST['ds_category_breadcrumb_color'] ) ) {
            $breadcrumb_color = sanitize_text_field( wp_unslash( $_POST['ds_category_breadcrumb_color'] ) );
            if ( '' !== $breadcrumb_color ) {
                update_term_meta( $term_id, 'ds_category_breadcrumb_color', $breadcrumb_color );
            } else {
                delete_term_meta( $term_id, 'ds_category_breadcrumb_color' );
            }
        }

        // 头部Padding (高度)
        if ( isset( $_POST['ds_category_header_padding'] ) ) {
            $header_padding = sanitize_text_field( wp_unslash( $_POST['ds_category_header_padding'] ) );
            if ( ! empty( $header_padding ) ) {
                update_term_meta( $term_id, 'ds_category_header_padding', $header_padding );
            } else {
                delete_term_meta( $term_id, 'ds_category_header_padding' );
            }
        }
        
        // 图标
        if ( isset( $_POST['ds_category_icon'] ) ) {
            $icon = sanitize_text_field( wp_unslash( $_POST['ds_category_icon'] ) );
            update_term_meta( $term_id, 'ds_category_icon', $icon );
        }
        
        // 每行列数
        if ( isset( $_POST['ds_category_columns'] ) ) {
            $columns = sanitize_text_field( wp_unslash( $_POST['ds_category_columns'] ) );
            if ( '' !== $columns ) {
                update_term_meta( $term_id, 'ds_category_columns', $columns );
            } else {
                delete_term_meta( $term_id, 'ds_category_columns' );
            }
        }
        
        // 每页数量
        if ( isset( $_POST['ds_category_posts_per_page'] ) ) {
            $posts_per_page = absint( wp_unslash( $_POST['ds_category_posts_per_page'] ) );
            if ( $posts_per_page > 0 ) {
                update_term_meta( $term_id, 'ds_category_posts_per_page', $posts_per_page );
            } else {
                delete_term_meta( $term_id, 'ds_category_posts_per_page' );
            }
        }
        
        // 缩略图高度
        if ( isset( $_POST['ds_category_thumb_height'] ) ) {
            $thumb_height = absint( wp_unslash( $_POST['ds_category_thumb_height'] ) );
            if ( $thumb_height > 0 ) {
                update_term_meta( $term_id, 'ds_category_thumb_height', $thumb_height );
            } else {
                delete_term_meta( $term_id, 'ds_category_thumb_height' );
            }
        }
        
        // 摘要字数
        if ( isset( $_POST['ds_category_excerpt_length'] ) ) {
            $excerpt_length = absint( wp_unslash( $_POST['ds_category_excerpt_length'] ) );
            if ( $excerpt_length > 0 ) {
                update_term_meta( $term_id, 'ds_category_excerpt_length', $excerpt_length );
            } else {
                delete_term_meta( $term_id, 'ds_category_excerpt_length' );
            }
        }

        if ( isset( $_POST['ds_category_seo_title'] ) ) {
            $seo_title = sanitize_text_field( wp_unslash( $_POST['ds_category_seo_title'] ) );
            if ( $seo_title !== '' ) {
                update_term_meta( $term_id, 'ds_category_seo_title', $seo_title );
            } else {
                delete_term_meta( $term_id, 'ds_category_seo_title' );
            }
        }

        if ( isset( $_POST['ds_category_seo_keywords'] ) ) {
            $seo_keywords = sanitize_text_field( wp_unslash( $_POST['ds_category_seo_keywords'] ) );
            $seo_keywords = str_replace( '，', ',', $seo_keywords );
            $seo_keywords = preg_replace( '/\s*,\s*/', ',', $seo_keywords );
            $seo_keywords = preg_replace( '/,+/', ',', $seo_keywords );
            $seo_keywords = trim( $seo_keywords, " \t\n\r\0\x0B," );
            if ( $seo_keywords !== '' ) {
                update_term_meta( $term_id, 'ds_category_seo_keywords', $seo_keywords );
            } else {
                delete_term_meta( $term_id, 'ds_category_seo_keywords' );
            }
        }

        if ( isset( $_POST['ds_category_seo_description'] ) ) {
            $seo_description = sanitize_textarea_field( wp_unslash( $_POST['ds_category_seo_description'] ) );
            if ( $seo_description !== '' ) {
                update_term_meta( $term_id, 'ds_category_seo_description', $seo_description );
            } else {
                delete_term_meta( $term_id, 'ds_category_seo_description' );
            }
        }
        
        // 隐藏选项 - 复选框需要特殊处理
        $hide_fields = array(
            'ds_category_hide_thumb',
            'ds_category_hide_excerpt',
            'ds_category_hide_date',
            'ds_category_hide_category',
            'ds_category_hide_author',
        );
        
        foreach ( $hide_fields as $field ) {
            if ( isset( $_POST[ $field ] ) && '1' === (string) wp_unslash( $_POST[ $field ] ) ) {
                update_term_meta( $term_id, $field, '1' );
            } else {
                delete_term_meta( $term_id, $field );
            }
        }
        
        // 高级分类筛选设置
        // 启用开关
        if ( isset( $_POST['ds_adv_filter_enabled'] ) && '1' === (string) wp_unslash( $_POST['ds_adv_filter_enabled'] ) ) {
            update_term_meta( $term_id, 'ds_adv_filter_enabled', '1' );
        } else {
            delete_term_meta( $term_id, 'ds_adv_filter_enabled' );
        }
        
        // 大分类名称
        if ( isset( $_POST['ds_adv_major_cats'] ) ) {
            $major_cats = sanitize_text_field( wp_unslash( $_POST['ds_adv_major_cats'] ) );
            if ( ! empty( $major_cats ) ) {
                update_term_meta( $term_id, 'ds_adv_major_cats', $major_cats );
            } else {
                delete_term_meta( $term_id, 'ds_adv_major_cats' );
            }
        }
        
        // 小分类名称
        if ( isset( $_POST['ds_adv_minor_cats'] ) ) {
            $minor_cats = sanitize_text_field( wp_unslash( $_POST['ds_adv_minor_cats'] ) );
            if ( ! empty( $minor_cats ) ) {
                update_term_meta( $term_id, 'ds_adv_minor_cats', $minor_cats );
            } else {
                delete_term_meta( $term_id, 'ds_adv_minor_cats' );
            }
        }
        
        if ( isset( $_POST['ds_adv_custom_levels'] ) ) {
            $levels_text = sanitize_textarea_field( wp_unslash( $_POST['ds_adv_custom_levels'] ) );
            if ( $levels_text !== '' ) {
                update_term_meta( $term_id, 'ds_adv_custom_levels', $levels_text );
            } else {
                delete_term_meta( $term_id, 'ds_adv_custom_levels' );
            }
        }
    }

    /**
     * 保存分类设置的通用 term 钩子兜底。
     *
     * @param int    $term_id  分类 ID。
     * @param int    $tt_id    Term taxonomy ID。
     * @param string $taxonomy 分类法。
     * @return void
     */
    public function save_category_term_fields( $term_id, $tt_id, $taxonomy ) {
        unset( $tt_id );

        if ( 'category' !== $taxonomy ) {
            return;
        }

        $this->save_category_fields( $term_id );
    }

    /**
     * 在后台分类编辑表单提交时直接保存分类设置。
     *
     * 这是对 WordPress taxonomy 专用钩子的兜底，避免现场环境中钩子顺序、缓存或后台扩展影响时，
     * 管理员点击“更新”后启灵分类设置没有落库。
     *
     * @return void
     */
    public function maybe_save_category_fields_from_admin_request() {
        if ( ! is_admin() || 'POST' !== strtoupper( isset( $_SERVER['REQUEST_METHOD'] ) ? (string) wp_unslash( $_SERVER['REQUEST_METHOD'] ) : '' ) ) {
            return;
        }

        $action = isset( $_POST['action'] ) ? sanitize_key( wp_unslash( $_POST['action'] ) ) : '';
        if ( 'editedtag' !== $action ) {
            return;
        }

        $taxonomy = isset( $_POST['taxonomy'] ) ? sanitize_key( wp_unslash( $_POST['taxonomy'] ) ) : '';
        if ( 'category' !== $taxonomy ) {
            return;
        }

        $term_id = isset( $_POST['tag_ID'] ) ? absint( wp_unslash( $_POST['tag_ID'] ) ) : 0;
        if ( ! $term_id ) {
            return;
        }

        $this->save_category_fields( $term_id );
    }

    /**
     * 判断当前请求是否包含启灵分类设置字段。
     *
     * 不再依赖额外提交标记，避免后台表单结构或缓存导致标记缺失时整组设置无法保存。
     *
     * @return bool
     */
    private function has_category_settings_payload() {
        $fields = array(
            'ds_category_fields_submitted',
            'ds_category_layout',
            'ds_category_blog_visual_preset',
            'ds_category_video_category',
            'ds_category_hide_breadcrumb',
            'ds_category_bg_color',
            'ds_category_breadcrumb_color',
            'ds_category_header_padding',
            'ds_category_icon',
            'ds_category_columns',
            'ds_category_posts_per_page',
            'ds_category_thumb_height',
            'ds_category_excerpt_length',
            'ds_category_hide_thumb',
            'ds_category_hide_excerpt',
            'ds_category_hide_date',
            'ds_category_hide_category',
            'ds_category_hide_author',
            'ds_category_seo_title',
            'ds_category_seo_keywords',
            'ds_category_seo_description',
            'ds_adv_filter_enabled',
            'ds_adv_major_cats',
            'ds_adv_minor_cats',
            'ds_adv_custom_levels',
        );

        foreach ( $fields as $field ) {
            if ( isset( $_POST[ $field ] ) ) {
                return true;
            }
        }

        return false;
    }

    /**
     * 分类列表添加列
     */
    public function add_category_columns( $columns ) {
        $new_columns = array();
        foreach ( $columns as $key => $value ) {
            $new_columns[ $key ] = $value;
            if ( $key === 'name' ) {
                $new_columns['ds_layout'] = __( '布局', 'developer-starter' );
                $new_columns['ds_blog_preset'] = __( '博客风格', 'developer-starter' );
                $new_columns['ds_icon'] = __( '图标', 'developer-starter' );
            }
        }
        return $new_columns;
    }

    /**
     * 分类列内容
     */
    public function category_column_content( $content, $column_name, $term_id ) {
        if ( $column_name === 'ds_layout' ) {
            $layout = get_term_meta( $term_id, 'ds_category_layout', true );
            $layout = $layout ? $layout : 'card';
            return isset( $this->layout_options[ $layout ] ) ? $this->layout_options[ $layout ] : '-';
        }

        if ( $column_name === 'ds_blog_preset' ) {
            $preset = get_term_meta( $term_id, 'ds_category_blog_visual_preset', true );
            if ( empty( $preset ) || 'inherit' === $preset ) {
                return isset( $this->blog_preset_options['inherit'] ) ? $this->blog_preset_options['inherit'] : __( '继承全局博客风格', 'developer-starter' );
            }

            return isset( $this->blog_preset_options[ $preset ] ) ? $this->blog_preset_options[ $preset ] : '-';
        }

        if ( $column_name === 'ds_icon' ) {
            $icon = get_term_meta( $term_id, 'ds_category_icon', true );
            if ( empty( $icon ) ) {
                return '-';
            }
            if ( filter_var( $icon, FILTER_VALIDATE_URL ) ) {
                return '<img src="' . esc_url( $icon ) . '" alt="" style="width: 20px; height: 20px;" />';
            }
            return esc_html( $icon );
        }
        
        return $content;
    }

    /**
     * 加载管理端资源
     */
    public function admin_scripts( $hook ) {
        if ( $hook === 'edit-tags.php' || $hook === 'term.php' ) {
            wp_enqueue_style( 'wp-color-picker' );
            wp_enqueue_script( 'wp-color-picker' );
            
            // 初始化颜色选择器
            add_action( 'admin_footer', function() {
                ?>
                <script>
                jQuery(document).ready(function($) {
                    $('.ds-color-picker').wpColorPicker();
                });
                </script>
                <?php
            } );
        }
    }

    /**
     * 获取分类设置
     * 
     * @param int $term_id 分类ID
     * @return array 分类设置
     */
    public static function get_category_settings( $term_id ) {
        return array(
            'layout'             => get_term_meta( $term_id, 'ds_category_layout', true ) ?: 'card',
            'blog_visual_preset' => get_term_meta( $term_id, 'ds_category_blog_visual_preset', true ) ?: '',
            'video_category_enabled' => get_term_meta( $term_id, 'ds_category_video_category', true ) ?: '',
            'hide_header'            => get_term_meta( $term_id, 'ds_category_hide_header', true ) ?: '',
            'hide_breadcrumb'        => get_term_meta( $term_id, 'ds_category_hide_breadcrumb', true ) ?: '',
            'hide_count'             => get_term_meta( $term_id, 'ds_category_hide_count', true ) ?: '',
            'bg_color'           => get_term_meta( $term_id, 'ds_category_bg_color', true ) ?: '',
            'breadcrumb_color'   => get_term_meta( $term_id, 'ds_category_breadcrumb_color', true ) ?: '',
            'header_padding'     => get_term_meta( $term_id, 'ds_category_header_padding', true ) ?: '',
            'icon'               => get_term_meta( $term_id, 'ds_category_icon', true ) ?: '',
            'columns'            => get_term_meta( $term_id, 'ds_category_columns', true ) ?: '',
            'posts_per_page'     => get_term_meta( $term_id, 'ds_category_posts_per_page', true ) ?: '',
            'thumb_height'       => get_term_meta( $term_id, 'ds_category_thumb_height', true ) ?: '',
            'excerpt_length'     => get_term_meta( $term_id, 'ds_category_excerpt_length', true ) ?: '',
            'hide_thumb'         => get_term_meta( $term_id, 'ds_category_hide_thumb', true ) ?: '',
            'hide_excerpt'       => get_term_meta( $term_id, 'ds_category_hide_excerpt', true ) ?: '',
            'hide_date'          => get_term_meta( $term_id, 'ds_category_hide_date', true ) ?: '',
            'hide_category'      => get_term_meta( $term_id, 'ds_category_hide_category', true ) ?: '',
            'hide_author'        => get_term_meta( $term_id, 'ds_category_hide_author', true ) ?: '',
            'seo_title'          => get_term_meta( $term_id, 'ds_category_seo_title', true ) ?: '',
            'seo_keywords'       => get_term_meta( $term_id, 'ds_category_seo_keywords', true ) ?: '',
            'seo_description'    => get_term_meta( $term_id, 'ds_category_seo_description', true ) ?: '',
            // 高级分类筛选设置
            'adv_filter_enabled' => get_term_meta( $term_id, 'ds_adv_filter_enabled', true ) ?: '',
            'adv_major_cats'     => get_term_meta( $term_id, 'ds_adv_major_cats', true ) ?: '',
            'adv_minor_cats'     => get_term_meta( $term_id, 'ds_adv_minor_cats', true ) ?: '',
            'adv_custom_levels'  => get_term_meta( $term_id, 'ds_adv_custom_levels', true ) ?: '',
        );
    }

    /**
     * 获取布局选项
     * 
     * @return array
     */
    public function get_layout_options() {
        return $this->layout_options;
    }
}
