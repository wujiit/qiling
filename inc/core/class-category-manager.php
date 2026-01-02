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

    /**
     * 布局选项
     */
    private $layout_options = array(
        'card'     => '卡片布局',
        'list'     => '列表布局',
        'grid'     => '网格布局',
        'magazine' => '杂志布局',
    );

    /**
     * 构造函数
     */
    public function __construct() {
        // 分类编辑页面添加字段
        add_action( 'category_add_form_fields', array( $this, 'add_category_fields' ) );
        add_action( 'category_edit_form_fields', array( $this, 'edit_category_fields' ), 10, 2 );
        
        // 保存分类设置
        add_action( 'created_category', array( $this, 'save_category_fields' ) );
        add_action( 'edited_category', array( $this, 'save_category_fields' ) );
        
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
        <div class="form-field">
            <label for="ds_category_layout"><?php _e( '文章列表布局', 'developer-starter' ); ?></label>
            <select name="ds_category_layout" id="ds_category_layout">
                <?php foreach ( $this->layout_options as $value => $label ) : ?>
                    <option value="<?php echo esc_attr( $value ); ?>"><?php echo esc_html( $label ); ?></option>
                <?php endforeach; ?>
            </select>
            <p class="description"><?php _e( '选择该分类下文章列表的显示布局', 'developer-starter' ); ?></p>
        </div>
        
        <div class="form-field">
            <label for="ds_category_bg_color"><?php _e( '头部背景颜色', 'developer-starter' ); ?></label>
            <input type="text" name="ds_category_bg_color" id="ds_category_bg_color" class="ds-color-picker" value="" />
            <p class="description"><?php _e( '分类页面头部区域的背景颜色，留空使用默认主题色', 'developer-starter' ); ?></p>
        </div>
        
        <div class="form-field">
            <label for="ds_category_icon"><?php _e( '分类图标', 'developer-starter' ); ?></label>
            <input type="text" name="ds_category_icon" id="ds_category_icon" value="" />
            <p class="description"><?php _e( '输入emoji表情或图标图片URL，显示在分类名称前面（选填）', 'developer-starter' ); ?></p>
        </div>
        
        <div class="form-field">
            <label for="ds_category_posts_per_page"><?php _e( '每页显示数量', 'developer-starter' ); ?></label>
            <input type="number" name="ds_category_posts_per_page" id="ds_category_posts_per_page" value="" min="1" max="100" />
            <p class="description"><?php _e( '该分类页面每页显示的文章数量，留空使用默认设置', 'developer-starter' ); ?></p>
        </div>
        <?php
    }

    /**
     * 编辑分类时的字段
     */
    public function edit_category_fields( $term, $taxonomy ) {
        $layout = get_term_meta( $term->term_id, 'ds_category_layout', true );
        $bg_color = get_term_meta( $term->term_id, 'ds_category_bg_color', true );
        $icon = get_term_meta( $term->term_id, 'ds_category_icon', true );
        $posts_per_page = get_term_meta( $term->term_id, 'ds_category_posts_per_page', true );
        ?>
        <tr class="form-field">
            <th scope="row">
                <label for="ds_category_layout"><?php _e( '文章列表布局', 'developer-starter' ); ?></label>
            </th>
            <td>
                <select name="ds_category_layout" id="ds_category_layout" style="width: 200px;">
                    <?php foreach ( $this->layout_options as $value => $label ) : ?>
                        <option value="<?php echo esc_attr( $value ); ?>" <?php selected( $layout, $value ); ?>><?php echo esc_html( $label ); ?></option>
                    <?php endforeach; ?>
                </select>
                <p class="description"><?php _e( '选择该分类下文章列表的显示布局', 'developer-starter' ); ?></p>
            </td>
        </tr>
        
        <tr class="form-field">
            <th scope="row">
                <label for="ds_category_bg_color"><?php _e( '头部背景颜色', 'developer-starter' ); ?></label>
            </th>
            <td>
                <input type="text" name="ds_category_bg_color" id="ds_category_bg_color" class="ds-color-picker" value="<?php echo esc_attr( $bg_color ); ?>" />
                <p class="description"><?php _e( '分类页面头部区域的背景颜色，留空使用默认主题色', 'developer-starter' ); ?></p>
            </td>
        </tr>
        
        <tr class="form-field">
            <th scope="row">
                <label for="ds_category_icon"><?php _e( '分类图标', 'developer-starter' ); ?></label>
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
                <p class="description"><?php _e( '输入emoji表情（如 📚）或图标图片URL，显示在分类名称前面（选填）', 'developer-starter' ); ?></p>
            </td>
        </tr>
        
        <tr class="form-field">
            <th scope="row">
                <label for="ds_category_posts_per_page"><?php _e( '每页显示数量', 'developer-starter' ); ?></label>
            </th>
            <td>
                <input type="number" name="ds_category_posts_per_page" id="ds_category_posts_per_page" value="<?php echo esc_attr( $posts_per_page ); ?>" min="1" max="100" style="width: 100px;" />
                <p class="description"><?php _e( '该分类页面每页显示的文章数量，留空使用默认设置', 'developer-starter' ); ?></p>
            </td>
        </tr>
        <?php
    }

    /**
     * 保存分类设置
     */
    public function save_category_fields( $term_id ) {
        // 布局
        if ( isset( $_POST['ds_category_layout'] ) ) {
            $layout = sanitize_text_field( $_POST['ds_category_layout'] );
            if ( array_key_exists( $layout, $this->layout_options ) ) {
                update_term_meta( $term_id, 'ds_category_layout', $layout );
            }
        }
        
        // 背景颜色
        if ( isset( $_POST['ds_category_bg_color'] ) ) {
            $bg_color = sanitize_hex_color( $_POST['ds_category_bg_color'] );
            update_term_meta( $term_id, 'ds_category_bg_color', $bg_color );
        }
        
        // 图标
        if ( isset( $_POST['ds_category_icon'] ) ) {
            $icon = sanitize_text_field( $_POST['ds_category_icon'] );
            update_term_meta( $term_id, 'ds_category_icon', $icon );
        }
        
        // 每页数量
        if ( isset( $_POST['ds_category_posts_per_page'] ) ) {
            $posts_per_page = absint( $_POST['ds_category_posts_per_page'] );
            if ( $posts_per_page > 0 ) {
                update_term_meta( $term_id, 'ds_category_posts_per_page', $posts_per_page );
            } else {
                delete_term_meta( $term_id, 'ds_category_posts_per_page' );
            }
        }
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
            'layout'         => get_term_meta( $term_id, 'ds_category_layout', true ) ?: 'card',
            'bg_color'       => get_term_meta( $term_id, 'ds_category_bg_color', true ) ?: '',
            'icon'           => get_term_meta( $term_id, 'ds_category_icon', true ) ?: '',
            'posts_per_page' => get_term_meta( $term_id, 'ds_category_posts_per_page', true ) ?: '',
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
