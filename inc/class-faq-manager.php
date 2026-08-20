<?php
/**
 * FAQ Manager Class - 常见问题文档中心管理
 *
 * @package Developer_Starter
 * @since 1.0.0
 */

namespace Developer_Starter\Core;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class FAQ_Manager {

    public function __construct() {
        add_action( 'init', array( $this, 'register_post_type' ) );
        add_action( 'init', array( $this, 'register_taxonomy' ) );
        add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
        add_action( 'save_post_ds_faq', array( $this, 'save_meta' ) );
        
        // 添加后台菜单图标样式
        add_action( 'admin_head', array( $this, 'admin_icon_style' ) );
    }

    /**
     * 注册自定义文章类型
     */
    public function register_post_type() {
        $labels = array(
            'name'               => __( '常见问题', 'developer-starter' ),
            'singular_name'      => __( '常见问题', 'developer-starter' ),
            'menu_name'          => __( '常见问题', 'developer-starter' ),
            'add_new'            => __( '添加问题', 'developer-starter' ),
            'add_new_item'       => __( '添加新问题', 'developer-starter' ),
            'edit_item'          => __( '编辑问题', 'developer-starter' ),
            'new_item'           => __( '新问题', 'developer-starter' ),
            'view_item'          => __( '查看问题', 'developer-starter' ),
            'search_items'       => __( '搜索问题', 'developer-starter' ),
            'not_found'          => __( '未找到问题', 'developer-starter' ),
            'not_found_in_trash' => __( '回收站中无问题', 'developer-starter' ),
        );

        $args = array(
            'labels'              => $labels,
            'public'              => false,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'menu_position'       => 25,
            'menu_icon'           => 'dashicons-editor-help',
            'supports'            => array( 'title', 'editor' ),
            'has_archive'         => false,
            'rewrite'             => false,
            'capability_type'     => 'post',
        );

        register_post_type( 'ds_faq', $args );
    }

    /**
     * 注册分类法
     */
    public function register_taxonomy() {
        $labels = array(
            'name'              => __( '问题分类', 'developer-starter' ),
            'singular_name'     => __( '问题分类', 'developer-starter' ),
            'search_items'      => __( '搜索分类', 'developer-starter' ),
            'all_items'         => __( '所有分类', 'developer-starter' ),
            'parent_item'       => __( '父分类', 'developer-starter' ),
            'parent_item_colon' => __( '父分类:', 'developer-starter' ),
            'edit_item'         => __( '编辑分类', 'developer-starter' ),
            'update_item'       => __( '更新分类', 'developer-starter' ),
            'add_new_item'      => __( '添加新分类', 'developer-starter' ),
            'new_item_name'     => __( '新分类名称', 'developer-starter' ),
            'menu_name'         => __( '问题分类', 'developer-starter' ),
        );

        $args = array(
            'labels'            => $labels,
            'hierarchical'      => true,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => false,
        );

        register_taxonomy( 'faq_category', 'ds_faq', $args );
    }

    /**
     * 添加 Meta Box
     */
    public function add_meta_boxes() {
        add_meta_box(
            'faq_document_meta',
            __( '文档信息', 'developer-starter' ),
            array( $this, 'render_meta_box' ),
            'ds_faq',
            'normal',
            'high'
        );
    }

    /**
     * 渲染 Meta Box
     */
    public function render_meta_box( $post ) {
        wp_nonce_field( 'faq_document_meta', 'faq_document_nonce' );
        
        $faq_order  = get_post_meta( $post->ID, '_faq_order', true );
        $doc_name   = get_post_meta( $post->ID, '_faq_doc_name', true );
        $doc_format = get_post_meta( $post->ID, '_faq_doc_format', true );
        $doc_size   = get_post_meta( $post->ID, '_faq_doc_size', true );
        $doc_url    = get_post_meta( $post->ID, '_faq_doc_url', true );
        ?>
        <style>
            .faq-meta-table { width: 100%; border-collapse: collapse; }
            .faq-meta-table th { text-align: left; padding: 10px 10px 10px 0; width: 100px; }
            .faq-meta-table td { padding: 10px 0; }
            .faq-meta-table input[type="text"] { width: 100%; }
            .faq-meta-table select { min-width: 150px; }
            .faq-meta-desc { color: #666; font-size: 12px; margin-top: 4px; }
        </style>
        <table class="faq-meta-table">
            <tr>
                <th><?php esc_html_e( '排序', 'developer-starter' ); ?></th>
                <td>
                    <input type="number" name="faq_order" value="<?php echo esc_attr( $faq_order ? $faq_order : '0' ); ?>" style="width: 80px;" min="0" />
                    <p class="faq-meta-desc"><?php esc_html_e( '数字越大越靠前显示，默认为0', 'developer-starter' ); ?></p>
                </td>
            </tr>
            <tr>
                <th><?php esc_html_e( '文档名称', 'developer-starter' ); ?></th>
                <td>
                    <input type="text" name="faq_doc_name" value="<?php echo esc_attr( $doc_name ); ?>" placeholder="<?php esc_attr_e( '例如：产品使用手册', 'developer-starter' ); ?>" />
                    <p class="faq-meta-desc"><?php esc_html_e( '可选，留空则不显示文档下载', 'developer-starter' ); ?></p>
                </td>
            </tr>
            <tr>
                <th><?php esc_html_e( '文档格式', 'developer-starter' ); ?></th>
                <td>
                    <select name="faq_doc_format">
                        <option value=""><?php esc_html_e( '-- 选择格式 --', 'developer-starter' ); ?></option>
                        <option value="pdf" <?php selected( $doc_format, 'pdf' ); ?>>PDF</option>
                        <option value="word" <?php selected( $doc_format, 'word' ); ?>>Word</option>
                        <option value="excel" <?php selected( $doc_format, 'excel' ); ?>>Excel</option>
                        <option value="ppt" <?php selected( $doc_format, 'ppt' ); ?>>PPT</option>
                        <option value="zip" <?php selected( $doc_format, 'zip' ); ?>>ZIP</option>
                        <option value="other" <?php selected( $doc_format, 'other' ); ?>><?php esc_html_e( '其他', 'developer-starter' ); ?></option>
                    </select>
                </td>
            </tr>
            <tr>
                <th><?php esc_html_e( '文档大小', 'developer-starter' ); ?></th>
                <td>
                    <input type="text" name="faq_doc_size" value="<?php echo esc_attr( $doc_size ); ?>" placeholder="<?php esc_attr_e( '例如：2.5MB', 'developer-starter' ); ?>" style="width: 150px;" />
                </td>
            </tr>
            <tr>
                <th><?php esc_html_e( '下载链接', 'developer-starter' ); ?></th>
                <td>
                    <input type="text" name="faq_doc_url" value="<?php echo esc_attr( $doc_url ); ?>" placeholder="https://..." />
                    <p class="faq-meta-desc"><?php esc_html_e( '文档下载地址，可使用媒体库链接或外部链接', 'developer-starter' ); ?></p>
                </td>
            </tr>
        </table>
        <?php
    }

    /**
     * 保存 Meta 数据
     */
    public function save_meta( $post_id ) {
        $nonce = isset( $_POST['faq_document_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['faq_document_nonce'] ) ) : '';
        if ( '' === $nonce || ! wp_verify_nonce( $nonce, 'faq_document_meta' ) ) {
            return;
        }

        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        $fields = array(
            'faq_order'      => '_faq_order',
            'faq_doc_name'   => '_faq_doc_name',
            'faq_doc_format' => '_faq_doc_format',
            'faq_doc_size'   => '_faq_doc_size',
            'faq_doc_url'    => '_faq_doc_url',
        );

        foreach ( $fields as $post_field => $meta_key ) {
            if ( isset( $_POST[ $post_field ] ) ) {
                $value = sanitize_text_field( wp_unslash( $_POST[ $post_field ] ) );
                update_post_meta( $post_id, $meta_key, $value );
            }
        }
    }

    /**
     * 后台图标样式
     */
    public function admin_icon_style() {
        ?>
        <style>
            #adminmenu .menu-icon-ds_faq div.wp-menu-image:before {
                content: "\f223";
            }
        </style>
        <?php
    }

    /**
     * 获取所有分类
     */
    public static function get_categories() {
        return get_terms( array(
            'taxonomy'   => 'faq_category',
            'hide_empty' => true,
        ) );
    }

    /**
     * 获取 FAQ 列表
     */
    public static function get_faqs( $category_id = 0 ) {
        $args = array(
            'post_type'      => 'ds_faq',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'meta_key'       => '_faq_order',
            'orderby'        => array(
                'meta_value_num' => 'DESC',
                'date'           => 'DESC',
            ),
        );

        if ( $category_id > 0 ) {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'faq_category',
                    'field'    => 'term_id',
                    'terms'    => $category_id,
                ),
            );
        }

        return get_posts( $args );
    }

    /**
     * 获取文档格式图标
     */
    public static function get_format_icon( $format ) {
        $icons = array(
            'pdf'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><path d="M14 2v6h6"/><path d="M10 9v6"/><path d="M10 12h4"/></svg>',
            'word'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><path d="M14 2v6h6"/><path d="M8 13h8"/><path d="M8 17h8"/><path d="M8 9h2"/></svg>',
            'excel' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><path d="M14 2v6h6"/><path d="M8 13l8 4"/><path d="M16 13l-8 4"/></svg>',
            'ppt'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><path d="M14 2v6h6"/><rect x="8" y="12" width="8" height="5" rx="1"/></svg>',
            'zip'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><path d="M14 2v6h6"/><path d="M12 12v6"/><path d="M9 15h6"/></svg>',
        );

        return isset( $icons[ $format ] ) ? $icons[ $format ] : $icons['pdf'];
    }

    /**
     * 获取格式标签颜色
     */
    public static function get_format_color( $format ) {
        $colors = array(
            'pdf'   => '#ef4444',
            'word'  => '#3b82f6',
            'excel' => '#10b981',
            'ppt'   => '#f59e0b',
            'zip'   => '#8b5cf6',
            'other' => '#64748b',
        );

        return isset( $colors[ $format ] ) ? $colors[ $format ] : $colors['other'];
    }
}
