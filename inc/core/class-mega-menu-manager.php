<?php
/**
 * Mega Menu Manager Class - 超级菜单管理系统
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Core;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Mega_Menu_Manager {

    const ACTIVE_CACHE_GROUP = 'developer_starter_mega_menu';
    const ACTIVE_CACHE_PREFIX = 'ds_mega_active_primary_v2_';
    const ACTIVE_CACHE_VERSION_OPTION = 'developer_starter_mega_menu_cache_version';

    /**
     * Request-local cache for primary menu mega state.
     *
     * @var array<string,bool>
     */
    private static $active_mega_menu_runtime_cache = array();

    public function __construct() {
        // 后台字段 (使用 add_action 而不是 add_filter)
        add_action( 'wp_nav_menu_item_custom_fields', array( $this, 'add_custom_fields' ), 10, 4 );
        add_action( 'wp_update_nav_menu_item', array( $this, 'save_custom_fields' ), 10, 3 );
        add_action( 'wp_update_nav_menu', array( __CLASS__, 'flush_active_mega_menu_cache' ), 20 );
        add_action( 'wp_delete_nav_menu', array( __CLASS__, 'flush_active_mega_menu_cache' ), 20 );
        add_action( 'wp_create_nav_menu', array( __CLASS__, 'flush_active_mega_menu_cache' ), 20 );
        add_action( 'after_switch_theme', array( __CLASS__, 'flush_active_mega_menu_cache' ) );
        add_filter( 'pre_set_theme_mod_nav_menu_locations', array( $this, 'flush_active_mega_menu_cache_for_locations' ), 10, 2 );
        
        // 加载后台资源（媒体上传器）
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
        
        // 前端资源
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_scripts' ) );
        add_filter( 'nav_menu_css_class', array( $this, 'add_display_type_class' ), 20, 4 );
        add_filter( 'nav_menu_link_attributes', array( $this, 'filter_display_type_link_attributes' ), 20, 4 );
    }

    /**
     * 加载后台脚本
     */
    public function enqueue_admin_scripts( $hook ) {
        if ( 'nav-menus.php' !== $hook ) {
            return;
        }
        wp_enqueue_media();
        $admin_js_file = get_template_directory() . '/assets/js/admin-mega-menu.js';
        $admin_js_ver  = file_exists( $admin_js_file ) ? (string) filemtime( $admin_js_file ) : (string) DEVELOPER_STARTER_VERSION;
        wp_enqueue_script(
            'ds-mega-menu-admin',
            get_template_directory_uri() . '/assets/js/admin-mega-menu.js',
            array( 'jquery' ),
            $admin_js_ver,
            true
        );
        wp_register_style( 'ds-mega-menu-admin', false, array(), $admin_js_ver );
        wp_enqueue_style( 'ds-mega-menu-admin' );

        wp_localize_script(
            'ds-mega-menu-admin',
            'dsMegaMenuAdmin',
            array(
                'title'      => __( '选择图片', 'developer-starter' ),
                'buttonText' => __( '使用此图片', 'developer-starter' ),
                'mediaError' => __( '媒体库未加载，请刷新页面重试。', 'developer-starter' ),
            )
        );

        wp_add_inline_style( 'ds-mega-menu-admin', $this->get_admin_fields_css() );
    }

    /**
     * 菜单编辑页字段样式。
     *
     * @return string
     */
    private function get_admin_fields_css() {
        return '
        .ds-mega-menu-fields {
            clear: both;
            margin: 12px 0;
            padding: 0;
        }
        .ds-mega-menu-panel {
            border: 1px solid #dcdcde;
            border-radius: 8px;
            background: #fff;
            overflow: hidden;
        }
        .ds-mega-menu-panel__header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            padding: 10px 12px;
            background: #f6f7f7;
            border-bottom: 1px solid #dcdcde;
        }
        .ds-mega-menu-panel__header strong {
            color: #1d2327;
        }
        .ds-mega-menu-panel__header span {
            color: #646970;
            font-size: 12px;
        }
        .ds-mega-menu-section {
            padding: 12px;
            border-top: 1px solid #f0f0f1;
        }
        .ds-mega-menu-section:first-child {
            border-top: 0;
        }
        .ds-mega-menu-section h4 {
            margin: 0 0 8px;
            font-size: 13px;
        }
        .ds-mega-menu-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
            gap: 10px 14px;
        }
        .ds-mega-menu-fields .description {
            margin-top: 0;
        }
        .ds-menu-image-actions {
            display: flex;
            align-items: center;
            gap: 6px;
            margin-top: 6px;
            flex-wrap: wrap;
        }
        .ds-menu-image-preview img,
        .ds-menu-image-preview-img {
            max-width: 100%;
            height: auto;
            margin-top: 10px;
            border-radius: 4px;
        }
        .ds-mega-menu-help {
            margin: 0 0 8px;
            color: #646970;
        }
        @media (max-width: 782px) {
            .ds-mega-menu-grid {
                grid-template-columns: 1fr;
            }
            .ds-mega-menu-panel__header {
                align-items: flex-start;
                flex-direction: column;
            }
        }';
    }

    /**
     * 加载前端脚本
     */
    public function enqueue_frontend_scripts() {
        // 仅当主菜单存在启用超级菜单的一级项时才加载资源。
        if ( ! $this->has_active_mega_menu() ) {
            return;
        }

        $css_file = get_template_directory() . '/assets/css/mega-menu.css';
        $js_file  = get_template_directory() . '/assets/js/mega-menu.js';
        $css_mtime = file_exists( $css_file ) ? (int) filemtime( $css_file ) : 0;
        $js_mtime  = file_exists( $js_file ) ? (int) filemtime( $js_file ) : 0;
        $version   = ( $css_mtime > 0 || $js_mtime > 0 )
            ? (string) max( $css_mtime, $js_mtime )
            : (string) DEVELOPER_STARTER_VERSION;

        wp_enqueue_style( 'ds-mega-menu', get_template_directory_uri() . '/assets/css/mega-menu.css', array(), $version );
        wp_enqueue_script( 'ds-mega-menu', get_template_directory_uri() . '/assets/js/mega-menu.js', array(), $version, true );
    }

    /**
     * 检查当前主菜单是否包含启用了超级菜单的项目
     */
    public static function has_active_mega_menu_for_primary() {
        $locations = get_nav_menu_locations();
        $primary_menu_id = isset( $locations['primary'] ) ? (int) $locations['primary'] : 0;
        $cache_key = self::get_active_mega_menu_cache_key( $primary_menu_id );

        if ( isset( self::$active_mega_menu_runtime_cache[ $cache_key ] ) ) {
            return (bool) self::$active_mega_menu_runtime_cache[ $cache_key ];
        }

        $cached = self::get_persistent_active_mega_menu_cache( $cache_key );
        if ( null !== $cached ) {
            self::$active_mega_menu_runtime_cache[ $cache_key ] = (bool) $cached;
            return (bool) $cached;
        }

        // 检查 'primary' 位置是否有菜单
        if ( $primary_menu_id <= 0 ) {
            self::$active_mega_menu_runtime_cache[ $cache_key ] = false;
            self::set_persistent_active_mega_menu_cache( $cache_key, false );
            return false;
        }

        $menu_object = wp_get_nav_menu_object( $primary_menu_id );
        if ( ! $menu_object ) {
            self::$active_mega_menu_runtime_cache[ $cache_key ] = false;
            self::set_persistent_active_mega_menu_cache( $cache_key, false );
            return false;
        }

        // 获取菜单项 (WordPress 会缓存此结果，不会造成重复查询压力)
        $menu_items = wp_get_nav_menu_items( $menu_object->term_id );
        if ( empty( $menu_items ) ) {
            self::$active_mega_menu_runtime_cache[ $cache_key ] = false;
            self::set_persistent_active_mega_menu_cache( $cache_key, false );
            return false;
        }

        foreach ( $menu_items as $item ) {
            // 仅在一级菜单上检查
            if ( $item->menu_item_parent == 0 ) {
                if ( get_post_meta( $item->ID, '_menu_item_mega_enable', true ) ) {
                    self::$active_mega_menu_runtime_cache[ $cache_key ] = true;
                    self::set_persistent_active_mega_menu_cache( $cache_key, true );
                    return true;
                }
            }
        }

        self::$active_mega_menu_runtime_cache[ $cache_key ] = false;
        self::set_persistent_active_mega_menu_cache( $cache_key, false );
        return false;
    }

    /**
     * Get cache version for primary mega-menu detection.
     *
     * @return string
     */
    private static function get_active_mega_menu_cache_version() {
        $version = get_option( self::ACTIVE_CACHE_VERSION_OPTION, '' );
        if ( ! is_string( $version ) || '' === $version ) {
            $version = (string) microtime( true );
            update_option( self::ACTIVE_CACHE_VERSION_OPTION, $version, false );
        }

        return $version;
    }

    /**
     * Build persistent cache key for the current primary menu assignment.
     *
     * @param int $primary_menu_id Primary menu term ID.
     * @return string
     */
    private static function get_active_mega_menu_cache_key( $primary_menu_id ) {
        return self::ACTIVE_CACHE_PREFIX . get_current_blog_id() . '_' . (int) $primary_menu_id . '_' . md5( self::get_active_mega_menu_cache_version() );
    }

    /**
     * Read persistent primary menu mega state.
     *
     * @param string $cache_key Cache key.
     * @return bool|null
     */
    private static function get_persistent_active_mega_menu_cache( $cache_key ) {
        $cached = wp_using_ext_object_cache()
            ? wp_cache_get( $cache_key, self::ACTIVE_CACHE_GROUP )
            : get_transient( $cache_key );

        if ( '1' === $cached ) {
            return true;
        }

        if ( '0' === $cached ) {
            return false;
        }

        return null;
    }

    /**
     * Store persistent primary menu mega state.
     *
     * @param string $cache_key Cache key.
     * @param bool   $active    Whether primary menu has active mega item.
     * @return void
     */
    private static function set_persistent_active_mega_menu_cache( $cache_key, $active ) {
        $value = $active ? '1' : '0';
        $ttl = (int) apply_filters( 'developer_starter_mega_menu_active_cache_ttl', DAY_IN_SECONDS );
        $ttl = max( MINUTE_IN_SECONDS, $ttl );

        if ( wp_using_ext_object_cache() ) {
            wp_cache_set( $cache_key, $value, self::ACTIVE_CACHE_GROUP, $ttl );
            return;
        }

        set_transient( $cache_key, $value, $ttl );
    }

    /**
     * Invalidate primary mega-menu detection cache.
     *
     * @return void
     */
    public static function flush_active_mega_menu_cache() {
        self::$active_mega_menu_runtime_cache = array();
        update_option( self::ACTIVE_CACHE_VERSION_OPTION, (string) microtime( true ), false );
    }

    /**
     * Flush cache when menu location assignments change.
     *
     * @param mixed $value     New theme mod value.
     * @param mixed $old_value Previous theme mod value.
     * @return mixed
     */
    public function flush_active_mega_menu_cache_for_locations( $value, $old_value = null ) {
        if ( $value !== $old_value ) {
            self::flush_active_mega_menu_cache();
        }

        return $value;
    }

    /**
     * 检查当前主菜单是否包含启用了超级菜单的项目
     */
    private function has_active_mega_menu() {
        return self::has_active_mega_menu_for_primary();
    }

    /**
     * 菜单项显示类型 class。
     */
    public function add_display_type_class( $classes, $menu_item, $args, $depth ) {
        unset( $args, $depth );

        $classes = is_array( $classes ) ? $classes : array();

        if ( ! is_object( $menu_item ) || ! isset( $menu_item->ID ) ) {
            return $classes;
        }

        $display_type = $this->normalize_display_type( get_post_meta( (int) $menu_item->ID, '_menu_item_display_type', true ) );
        if ( 'normal' !== $display_type ) {
            $classes[] = 'qiling-menu-item-display-' . $display_type;
        }

        $menu_badge = get_post_meta( (int) $menu_item->ID, '_menu_item_badge_text', true );
        $menu_title = isset( $menu_item->title ) ? (string) $menu_item->title : '';
        if (
            '' !== trim( (string) $menu_badge )
            || false !== stripos( $menu_title, '<t' )
            || false !== stripos( $menu_title, 'menu-badge' )
        ) {
            $classes[] = 'qiling-menu-item-has-badge';
        }

        return $classes;
    }

    /**
     * 菜单项显示类型链接属性。
     */
    public function filter_display_type_link_attributes( $atts, $menu_item, $args, $depth ) {
        unset( $args, $depth );

        $atts = is_array( $atts ) ? $atts : array();

        if ( ! is_object( $menu_item ) || ! isset( $menu_item->ID ) ) {
            return $atts;
        }

        $display_type = $this->normalize_display_type( get_post_meta( (int) $menu_item->ID, '_menu_item_display_type', true ) );
        if ( 'divider' === $display_type ) {
            unset( $atts['href'] );
            $atts['aria-disabled'] = 'true';
            $atts['tabindex'] = '-1';
        } elseif ( 'icon' === $display_type && empty( $atts['aria-label'] ) ) {
            $label = trim( wp_strip_all_tags( (string) $menu_item->title ) );
            if ( '' !== $label ) {
                $atts['aria-label'] = $label;
            }
        }

        return $atts;
    }

    /**
     * 添加自定义字段到菜单编辑器
     */
    public function add_custom_fields( $item_id, $item, $depth, $args ) {
        unset( $item, $args );

        $item_id     = (int) $item_id;
        $depth       = (int) $depth;
        $mega_enable = get_post_meta( $item_id, '_menu_item_mega_enable', true );
        $mega_style  = $this->normalize_mega_style( get_post_meta( $item_id, '_menu_item_mega_style', true ) );
        $menu_image  = get_post_meta( $item_id, '_menu_item_image', true );
        $menu_icon   = get_post_meta( $item_id, '_menu_item_icon', true );
        $menu_badge  = get_post_meta( $item_id, '_menu_item_badge_text', true );
        $menu_desc   = get_post_meta( $item_id, '_menu_item_description', true );
        $display_type = $this->normalize_display_type( get_post_meta( $item_id, '_menu_item_display_type', true ) );
        $item_id_attr = esc_attr( (string) $item_id );
        ?>
        <div class="ds-mega-menu-fields field-custom clear-fix description-wide" data-menu-depth="<?php echo esc_attr( (string) $depth ); ?>">
            <div class="ds-mega-menu-panel">
                <div class="ds-mega-menu-panel__header">
                    <strong><?php esc_html_e( '启灵菜单增强', 'developer-starter' ); ?></strong>
                    <span><?php esc_html_e( '展示与超级菜单', 'developer-starter' ); ?></span>
                </div>

                <div class="ds-mega-menu-section">
                    <h4><?php esc_html_e( '菜单项展示', 'developer-starter' ); ?></h4>
                    <div class="ds-mega-menu-grid">
                        <p class="field-menu-display-type description description-thin">
                            <label for="edit-menu-item-display-type-<?php echo $item_id_attr; ?>">
                                <?php esc_html_e( '显示类型', 'developer-starter' ); ?><br>
                                <select id="edit-menu-item-display-type-<?php echo $item_id_attr; ?>"
                                        name="menu-item-display-type[<?php echo $item_id_attr; ?>]" class="widefat">
                                    <option value="normal" <?php selected( $display_type, 'normal' ); ?>><?php esc_html_e( '普通链接', 'developer-starter' ); ?></option>
                                    <option value="button" <?php selected( $display_type, 'button' ); ?>><?php esc_html_e( '按钮', 'developer-starter' ); ?></option>
                                    <option value="icon" <?php selected( $display_type, 'icon' ); ?>><?php esc_html_e( '图标入口', 'developer-starter' ); ?></option>
                                    <option value="divider" <?php selected( $display_type, 'divider' ); ?>><?php esc_html_e( '分割标题', 'developer-starter' ); ?></option>
                                </select>
                            </label>
                        </p>

                        <p class="field-menu-icon description description-thin">
                            <label for="edit-menu-item-icon-<?php echo $item_id_attr; ?>">
                                <?php esc_html_e( '菜单图标', 'developer-starter' ); ?><br>
                                <input type="text" id="edit-menu-item-icon-<?php echo $item_id_attr; ?>"
                                       class="widefat code"
                                       name="menu-item-icon[<?php echo $item_id_attr; ?>]"
                                       value="<?php echo esc_attr( $menu_icon ); ?>"
                                       placeholder="<?php esc_attr_e( '例如: icon-home、🔥 或 SVG 代码', 'developer-starter' ); ?>" />
                            </label>
                            <span class="description"><?php esc_html_e( '主导航、移动菜单和移动端底部菜单都会优先复用这里的图标。', 'developer-starter' ); ?></span>
                        </p>

                        <p class="field-menu-badge description description-thin">
                            <label for="edit-menu-item-badge-text-<?php echo $item_id_attr; ?>">
                                <?php esc_html_e( '角标文字', 'developer-starter' ); ?><br>
                                <input type="text" id="edit-menu-item-badge-text-<?php echo $item_id_attr; ?>"
                                       class="widefat code"
                                       name="menu-item-badge-text[<?php echo $item_id_attr; ?>]"
                                       value="<?php echo esc_attr( $menu_badge ); ?>"
                                       placeholder="<?php esc_attr_e( '例如: HOT、NEW、VIP', 'developer-starter' ); ?>" />
                            </label>
                        </p>
                    </div>

                    <p class="field-menu-desc description description-wide">
                        <label for="edit-menu-item-description-<?php echo $item_id_attr; ?>">
                            <?php esc_html_e( '描述文本', 'developer-starter' ); ?><br>
                            <textarea id="edit-menu-item-description-<?php echo $item_id_attr; ?>"
                                      class="widefat" rows="2"
                                      name="menu-item-description[<?php echo $item_id_attr; ?>]"
                                      placeholder="<?php esc_attr_e( '用于 Mega Menu 或支持描述的菜单样式。', 'developer-starter' ); ?>"><?php echo esc_textarea( $menu_desc ); ?></textarea>
                        </label>
                    </p>
                </div>

                <div class="ds-mega-menu-section">
                    <h4><?php esc_html_e( '图片 / Mega 预览', 'developer-starter' ); ?></h4>
                    <p class="field-menu-image description description-wide">
                        <label for="edit-menu-item-image-<?php echo $item_id_attr; ?>">
                            <?php esc_html_e( '菜单图片', 'developer-starter' ); ?><br>
                            <input type="text" id="edit-menu-item-image-<?php echo $item_id_attr; ?>"
                                   class="widefat code edit-menu-item-image"
                                   name="menu-item-image[<?php echo $item_id_attr; ?>]"
                                   value="<?php echo esc_url( $menu_image ); ?>"
                                   placeholder="<?php esc_attr_e( '输入图片地址或点击选择', 'developer-starter' ); ?>" />
                        </label>
                        <span class="description"><?php esc_html_e( '用于 Mega Menu 图文网格、侧栏预览等场景。普通菜单图标请使用上方“菜单图标”。', 'developer-starter' ); ?></span>
                        <span class="ds-menu-image-actions">
                            <button type="button" class="button ds-menu-image-upload"
                                    data-input="edit-menu-item-image-<?php echo $item_id_attr; ?>"
                                    data-preview="preview-menu-item-image-<?php echo $item_id_attr; ?>"><?php esc_html_e( '选择图片', 'developer-starter' ); ?></button>
                            <button type="button" class="button ds-menu-image-remove"
                                    data-input="edit-menu-item-image-<?php echo $item_id_attr; ?>"
                                    data-preview="preview-menu-item-image-<?php echo $item_id_attr; ?>"
                                    style="<?php echo empty( $menu_image ) ? 'display:none;' : ''; ?>"><?php esc_html_e( '移除', 'developer-starter' ); ?></button>
                        </span>
                        <span id="preview-menu-item-image-<?php echo $item_id_attr; ?>" class="ds-menu-image-preview">
                            <?php if ( ! empty( $menu_image ) ) : ?>
                                <img class="ds-menu-image-preview-img" src="<?php echo esc_url( $menu_image ); ?>" alt="" />
                            <?php endif; ?>
                        </span>
                    </p>
                </div>

                <div class="ds-mega-menu-section">
                    <h4><?php esc_html_e( '超级菜单', 'developer-starter' ); ?></h4>
                    <p class="ds-mega-menu-help description">
                        <?php esc_html_e( '超级菜单建议只在一级菜单项启用；前台主导航仅一级菜单项会触发 Mega Menu 布局。', 'developer-starter' ); ?>
                    </p>
                    <p class="field-mega-enable description description-wide">
                        <label for="edit-menu-item-mega-enable-<?php echo $item_id_attr; ?>">
                            <input type="checkbox" id="edit-menu-item-mega-enable-<?php echo $item_id_attr; ?>"
                                   name="menu-item-mega-enable[<?php echo $item_id_attr; ?>]" value="1"
                                   <?php checked( $mega_enable, 1 ); ?> />
                            <strong><?php esc_html_e( '启用超级菜单', 'developer-starter' ); ?></strong>
                        </label>
                    </p>

                    <p class="field-mega-style description description-wide">
                        <label for="edit-menu-item-mega-style-<?php echo $item_id_attr; ?>">
                            <?php esc_html_e( '布局模式', 'developer-starter' ); ?><br>
                            <select id="edit-menu-item-mega-style-<?php echo $item_id_attr; ?>"
                                    name="menu-item-mega-style[<?php echo $item_id_attr; ?>]" class="widefat">
                                <option value="columns" <?php selected( $mega_style, 'columns' ); ?>><?php esc_html_e( '标准多栏', 'developer-starter' ); ?></option>
                                <option value="grid" <?php selected( $mega_style, 'grid' ); ?>><?php esc_html_e( '图文网格（上图下文）', 'developer-starter' ); ?></option>
                                <option value="sidebar" <?php selected( $mega_style, 'sidebar' ); ?>><?php esc_html_e( '动态侧栏（左文右图）', 'developer-starter' ); ?></option>
                            </select>
                        </label>
                    </p>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * 保存自定义字段
     */
    public function save_custom_fields( $menu_id, $menu_item_db_id, $args ) {
        unset( $menu_id, $args );

        $menu_item_db_id = (int) $menu_item_db_id;

        // 启用开关
        if ( isset( $_POST['menu-item-mega-enable'][ $menu_item_db_id ] ) ) {
            update_post_meta( $menu_item_db_id, '_menu_item_mega_enable', 1 );
        } else {
            delete_post_meta( $menu_item_db_id, '_menu_item_mega_enable' );
        }

        // 样式模式
        if ( isset( $_POST['menu-item-mega-style'][ $menu_item_db_id ] ) ) {
            $mega_style = $this->normalize_mega_style( wp_unslash( $_POST['menu-item-mega-style'][ $menu_item_db_id ] ) );
            if ( 'columns' === $mega_style ) {
                delete_post_meta( $menu_item_db_id, '_menu_item_mega_style' );
            } else {
                update_post_meta( $menu_item_db_id, '_menu_item_mega_style', $mega_style );
            }
        } else {
            delete_post_meta( $menu_item_db_id, '_menu_item_mega_style' );
        }

        // 显示类型
        if ( isset( $_POST['menu-item-display-type'][ $menu_item_db_id ] ) ) {
            $display_type = $this->normalize_display_type( wp_unslash( $_POST['menu-item-display-type'][ $menu_item_db_id ] ) );
            if ( 'normal' === $display_type ) {
                delete_post_meta( $menu_item_db_id, '_menu_item_display_type' );
            } else {
                update_post_meta( $menu_item_db_id, '_menu_item_display_type', $display_type );
            }
        } else {
            delete_post_meta( $menu_item_db_id, '_menu_item_display_type' );
        }
        
        // 图片
        if ( isset( $_POST['menu-item-image'][ $menu_item_db_id ] ) ) {
            $menu_image = esc_url_raw( trim( (string) wp_unslash( $_POST['menu-item-image'][ $menu_item_db_id ] ) ) );
            if ( '' !== $menu_image ) {
                update_post_meta( $menu_item_db_id, '_menu_item_image', $menu_image );
            } else {
                delete_post_meta( $menu_item_db_id, '_menu_item_image' );
            }
        } else {
            delete_post_meta( $menu_item_db_id, '_menu_item_image' );
        }

        // 图标
        if ( isset( $_POST['menu-item-icon'][ $menu_item_db_id ] ) ) {
            $menu_icon_raw = trim( (string) wp_unslash( $_POST['menu-item-icon'][ $menu_item_db_id ] ) );
            $menu_icon = trim( preg_replace( '/\s+/', ' ', $menu_icon_raw ) );

            if ( '' !== $menu_icon && ( false !== strpos( $menu_icon, '<' ) || false !== strpos( $menu_icon, '>' ) ) ) {
                $allowed_tags = array(
                    'span'   => array(
                        'class'       => true,
                        'style'       => true,
                        'aria-hidden' => true,
                    ),
                    'i'      => array(
                        'class'       => true,
                        'style'       => true,
                        'aria-hidden' => true,
                    ),
                    'em'     => array(
                        'class'       => true,
                        'style'       => true,
                        'aria-hidden' => true,
                    ),
                    'strong' => array(
                        'class'       => true,
                        'style'       => true,
                        'aria-hidden' => true,
                    ),
                    'b'      => array(
                        'class'       => true,
                        'style'       => true,
                        'aria-hidden' => true,
                    ),
                    'small'  => array(
                        'class'       => true,
                        'style'       => true,
                        'aria-hidden' => true,
                    ),
                    'img'    => array(
                        'class'       => true,
                        'style'       => true,
                        'src'         => true,
                        'alt'         => true,
                        'width'       => true,
                        'height'      => true,
                        'aria-hidden' => true,
                    ),
                    'svg'    => array(
                        'class'       => true,
                        'id'          => true,
                        'width'       => true,
                        'height'      => true,
                        'viewbox'     => true,
                        'fill'        => true,
                        'stroke'      => true,
                        'stroke-width' => true,
                        'stroke-linecap' => true,
                        'stroke-linejoin' => true,
                        'xmlns'       => true,
                        'aria-hidden' => true,
                        'role'        => true,
                        'focusable'   => true,
                        'style'       => true,
                    ),
                    'path'   => array(
                        'd'            => true,
                        'fill'         => true,
                        'stroke'       => true,
                        'stroke-width' => true,
                        'stroke-linecap' => true,
                        'stroke-linejoin' => true,
                        'fill-rule'    => true,
                        'clip-rule'    => true,
                        'opacity'      => true,
                        'transform'    => true,
                    ),
                    'g'      => array(
                        'fill'         => true,
                        'stroke'       => true,
                        'transform'    => true,
                        'opacity'      => true,
                        'id'           => true,
                        'class'        => true,
                    ),
                    'use'    => array(
                        'xlink:href' => true,
                        'href'       => true,
                        'x'          => true,
                        'y'          => true,
                        'width'      => true,
                        'height'     => true,
                    ),
                );

                if ( false !== stripos( $menu_icon, '<svg' ) && function_exists( 'developer_starter_sanitize_svg' ) ) {
                    $menu_icon = developer_starter_sanitize_svg( $menu_icon );
                } else {
                    $menu_icon = wp_kses( $menu_icon, $allowed_tags );
                }
            }

            if ( '' !== $menu_icon ) {
                update_post_meta( $menu_item_db_id, '_menu_item_icon', $menu_icon );
            } else {
                delete_post_meta( $menu_item_db_id, '_menu_item_icon' );
            }
        } else {
            delete_post_meta( $menu_item_db_id, '_menu_item_icon' );
        }

        // 徽标
        if ( isset( $_POST['menu-item-badge-text'][ $menu_item_db_id ] ) ) {
            $menu_badge = sanitize_text_field( wp_unslash( $_POST['menu-item-badge-text'][ $menu_item_db_id ] ) );
            if ( '' !== $menu_badge ) {
                update_post_meta( $menu_item_db_id, '_menu_item_badge_text', $menu_badge );
            } else {
                delete_post_meta( $menu_item_db_id, '_menu_item_badge_text' );
            }
        } else {
            delete_post_meta( $menu_item_db_id, '_menu_item_badge_text' );
        }

        // 描述
        if ( isset( $_POST['menu-item-description'][ $menu_item_db_id ] ) ) {
            $menu_desc = sanitize_textarea_field( wp_unslash( $_POST['menu-item-description'][ $menu_item_db_id ] ) );
            if ( '' !== $menu_desc ) {
                update_post_meta( $menu_item_db_id, '_menu_item_description', $menu_desc );
            } else {
                delete_post_meta( $menu_item_db_id, '_menu_item_description' );
            }
        } else {
            delete_post_meta( $menu_item_db_id, '_menu_item_description' );
        }

        self::flush_active_mega_menu_cache();
    }

    /**
     * 规范 Mega Menu 布局模式。
     *
     * @param mixed $style Raw style.
     * @return string
     */
    private function normalize_mega_style( $style ) {
        $style = sanitize_key( (string) $style );
        return in_array( $style, array( 'columns', 'grid', 'sidebar' ), true ) ? $style : 'columns';
    }

    /**
     * 规范菜单项显示类型。
     *
     * @param mixed $type Raw type.
     * @return string
     */
    private function normalize_display_type( $type ) {
        $type = sanitize_key( (string) $type );
        return in_array( $type, array( 'normal', 'button', 'icon', 'divider' ), true ) ? $type : 'normal';
    }
}

/**
 * 自定义 Walker 类
 */
class Walker_Nav_Menu_Mega extends \Walker_Nav_Menu {
    
    // 当前处理的父级菜单是否启用了 Mega Menu 以及模式
    private $active_mega_menu = false;
    private $active_mega_mode = '';
    private $active_mega_image = ''; // 父级图片，用于 Sidebar 模式默认展示
    private $menu_image_dimension_cache = array();

    /**
     * Mega Menu image fallback dimensions by visual slot.
     *
     * @param string $context Image context.
     * @return array<string,int>
     */
    private function get_menu_image_fallback_dimensions( $context ) {
        switch ( $context ) {
            case 'icon':
                return array( 'width' => 20, 'height' => 20 );

            case 'grid':
                return array( 'width' => 240, 'height' => 150 );

            case 'preview':
            default:
                return array( 'width' => 900, 'height' => 360 );
        }
    }

    /**
     * Resolve image dimensions without touching remote URLs.
     *
     * @param string $image_url Image URL.
     * @param string $context   Visual slot context.
     * @return array<string,int>
     */
    private function get_menu_image_dimensions( $image_url, $context ) {
        $image_url = (string) $image_url;
        $context = sanitize_key( (string) $context );
        $cache_key = md5( $context . '|' . $image_url );

        if ( isset( $this->menu_image_dimension_cache[ $cache_key ] ) ) {
            return $this->menu_image_dimension_cache[ $cache_key ];
        }

        $dimensions = $this->get_menu_image_fallback_dimensions( $context );

        if ( preg_match( '/-(\d{2,5})x(\d{2,5})\.(?:jpe?g|png|webp|gif|avif)(?:\?.*)?$/i', $image_url, $matches ) ) {
            $dimensions = array(
                'width'  => max( 1, (int) $matches[1] ),
                'height' => max( 1, (int) $matches[2] ),
            );
        } elseif ( function_exists( 'attachment_url_to_postid' ) && function_exists( 'wp_get_attachment_image_src' ) ) {
            $attachment_id = attachment_url_to_postid( $image_url );
            if ( $attachment_id ) {
                $attachment_src = wp_get_attachment_image_src( $attachment_id, 'full' );
                if ( is_array( $attachment_src ) && ! empty( $attachment_src[1] ) && ! empty( $attachment_src[2] ) ) {
                    $dimensions = array(
                        'width'  => max( 1, (int) $attachment_src[1] ),
                        'height' => max( 1, (int) $attachment_src[2] ),
                    );
                }
            }
        }

        $this->menu_image_dimension_cache[ $cache_key ] = $dimensions;
        return $dimensions;
    }

    /**
     * Build a lazy image tag for Mega Menu media.
     *
     * @param string $image_url Image URL.
     * @param string $context   Visual slot context.
     * @return string
     */
    private function render_menu_image( $image_url, $context ) {
        $image_url = trim( (string) $image_url );
        if ( '' === $image_url ) {
            return '';
        }

        $dimensions = $this->get_menu_image_dimensions( $image_url, $context );

        return '<img src="' . esc_url( $image_url ) . '" alt="" width="' . esc_attr( (string) $dimensions['width'] ) . '" height="' . esc_attr( (string) $dimensions['height'] ) . '" loading="lazy" decoding="async" />';
    }

    /**
     * Build dimension data attributes for JS-created preview images.
     *
     * @param string $image_url Image URL.
     * @param string $context   Visual slot context.
     * @return string
     */
    private function get_menu_image_dimension_data_attributes( $image_url, $context ) {
        $dimensions = $this->get_menu_image_dimensions( $image_url, $context );

        return ' data-preview-width="' . esc_attr( (string) $dimensions['width'] ) . '" data-preview-height="' . esc_attr( (string) $dimensions['height'] ) . '"';
    }

    public function start_lvl( &$output, $depth = 0, $args = null ) {
        if ( isset( $args->item_spacing ) && 'discard' === $args->item_spacing ) {
            $t = '';
            $n = '';
        } else {
            $t = "\t";
            $n = "\n";
        }
        $indent = str_repeat( $t, $depth );

        // 检查当前父级是否启用了 Mega Menu (在 start_el 中设置)
        if ( $depth === 0 && $this->active_mega_menu ) {
            
            // 关键修改：移除 'sub-menu' 类，彻底切断主题默认下拉菜单样式的干扰
            // 只保留 mega-menu-dropdown 和模式类
            $classes = array( 'mega-menu-dropdown' );
            $classes[] = 'mega-mode-' . ( $this->active_mega_mode ? $this->active_mega_mode : 'columns' );
            
            $class_names = join( ' ', apply_filters( 'nav_menu_submenu_css_class', $classes, $args, $depth ) );
            $class_names = $class_names ? ' class="' . esc_attr( $class_names ) . '"' : '';

            $output .= "{$n}{$indent}<div{$class_names}>{$n}";
            $output .= "{$indent}<div class=\"container\">{$n}"; // 容器
            $output .= "{$indent}<ul class=\"mega-menu-list\">{$n}";

        } else {
            // 普通下拉菜单 (保持 sub-menu 以继承主题样式)
            $classes = array( 'sub-menu' );
            $class_names = join( ' ', apply_filters( 'nav_menu_submenu_css_class', $classes, $args, $depth ) );
            $class_names = $class_names ? ' class="' . esc_attr( $class_names ) . '"' : '';

            $output .= "{$n}{$indent}<ul$class_names>{$n}";
        }
    }

    public function end_lvl( &$output, $depth = 0, $args = null ) {
        if ( isset( $args->item_spacing ) && 'discard' === $args->item_spacing ) {
            $t = '';
            $n = '';
        } else {
            $t = "\t";
            $n = "\n";
        }
        $indent = str_repeat( $t, $depth );

        if ( $depth === 0 && $this->active_mega_menu ) {
            $output .= "{$indent}</ul>{$n}"; // Close .mega-menu-list
            
            // 如果是 Sidebar 模式，添加预览区域
            if ( $this->active_mega_mode === 'sidebar' ) {
                $output .= "{$indent}<div class=\"mega-menu-preview\">{$n}";
                
                // 默认图片（如果没有，则显示灰色占位）
                if ( $this->active_mega_image ) {
                     $output .= "{$indent}<div class=\"preview-image-box default-active\">" . $this->render_menu_image( $this->active_mega_image, 'preview' ) . "</div>{$n}";
                } else {
                     $output .= "{$indent}<div class=\"preview-image-box default-active placeholder\"></div>{$n}";
                }
                
                $output .= "{$indent}</div>{$n}"; // Close .mega-menu-preview
            }
            
            $output .= "{$indent}</div>{$n}"; // Close .container
            $output .= "{$indent}</div>{$n}"; // Close .mega-menu-dropdown
        } else {
            $output .= "$indent</ul>{$n}";
        }
    }

    public function start_el( &$output, $data_object, $depth = 0, $args = null, $current_object_id = 0 ) {
        // 恢复对象
        $menu_item = $data_object;
        
        // 获取自定义字段
        $mega_enable = get_post_meta( $menu_item->ID, '_menu_item_mega_enable', true );
        $mega_style = $this->normalize_mega_style( get_post_meta( $menu_item->ID, '_menu_item_mega_style', true ) );
        $menu_image = get_post_meta( $menu_item->ID, '_menu_item_image', true );
        $menu_badge = get_post_meta( $menu_item->ID, '_menu_item_badge_text', true );
        $menu_desc = get_post_meta( $menu_item->ID, '_menu_item_description', true );

        // 仅处理一级菜单的状态设置
        if ( $depth === 0 ) {
            $this->active_mega_menu = ! empty( $mega_enable );
            $this->active_mega_mode = $mega_style;
            $this->active_mega_image = $menu_image; // 侧栏模式的默认封面
        }

        // 构建 Class
        $classes = empty( $menu_item->classes ) ? array() : (array) $menu_item->classes;
        $classes[] = 'menu-item-' . $menu_item->ID;
        
        if ( $depth === 0 && $this->active_mega_menu ) {
            $classes[] = 'has-mega-menu';
        }
        
        // 数据属性用于侧栏模式的预览
        $attributes_str = '';
        if ( $depth === 1 && $this->active_mega_mode === 'sidebar' && ! empty( $menu_image ) ) {
             $attributes_str .= ' data-preview-image="' . esc_url( $menu_image ) . '"';
             $attributes_str .= $this->get_menu_image_dimension_data_attributes( $menu_image, 'preview' );
        }

        $args = apply_filters( 'nav_menu_item_args', $args, $menu_item, $depth );
        
        $class_names = join( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $menu_item, $args, $depth ) );
        $class_names = $class_names ? ' class="' . esc_attr( $class_names ) . '"' : '';

        $id = apply_filters( 'nav_menu_item_id', 'menu-item-'. $menu_item->ID, $menu_item, $args, $depth );
        $id = $id ? ' id="' . esc_attr( $id ) . '"' : '';

        $output .= $depth === 0 ? '' : ''; // 可以在这里添加前缀
        $output .= '<li' . $id . $class_names . $attributes_str . '>';

        $atts = array();
        $atts['title']  = ! empty( $menu_item->attr_title ) ? $menu_item->attr_title : '';
        $atts['target'] = ! empty( $menu_item->target )     ? $menu_item->target     : '';
        $atts['rel']    = ! empty( $menu_item->xfn )        ? $menu_item->xfn        : '';
        $atts['href']   = ! empty( $menu_item->url )        ? $menu_item->url        : '';

        $atts = apply_filters( 'nav_menu_link_attributes', $atts, $menu_item, $args, $depth );

        $attributes = '';
        foreach ( $atts as $attr => $value ) {
            if ( ! empty( $value ) ) {
                $value = ( 'href' === $attr ) ? esc_url( $value ) : esc_attr( $value );
                $attributes .= ' ' . $attr . '="' . $value . '"';
            }
        }

        $title = apply_filters( 'the_title', $menu_item->title, $menu_item->ID );
        $title = apply_filters( 'nav_menu_item_title', $title, $menu_item, $args, $depth );

        // === 开始构建内容输出 === //
        
        $item_output = $args->before;
        
        // 链接开始
        $item_output .= '<a'. $attributes .'>';
        
        // [Grid模式] 子菜单项展示图片和描述
        if ( $depth === 1 && $this->active_mega_mode === 'grid' ) {
            $item_output .= '<div class="mega-grid-item">';
            // 图片
            if ( ! empty( $menu_image ) ) {
                $item_output .= '<div class="mega-item-image">' . $this->render_menu_image( $menu_image, 'grid' ) . '</div>';
            }
            // 内容包装
            $item_output .= '<div class="mega-item-content">';
            $item_output .= '<div class="mega-item-title">' . $title;
             // 角标
            if ( ! empty( $menu_badge ) ) {
                $item_output .= ' <span class="mega-badge">' . esc_html( $menu_badge ) . '</span>';
            }
            $item_output .= '</div>';
            // 描述
            if ( ! empty( $menu_desc ) ) {
                $item_output .= '<div class="mega-item-desc">' . esc_html( $menu_desc ) . '</div>';
            }
            $item_output .= '</div>'; // end content
            $item_output .= '</div>'; // end grid item
        } 
        // [Sidebar模式] 子菜单项展示简单图标和文字
        elseif ( $depth === 1 && $this->active_mega_mode === 'sidebar' ) {
             $item_output .= '<span class="mega-item-inner">';
             // 小图标 (如果有)
             // if ( $menu_icon ) ... 
             $item_output .= '<span class="mega-item-title">' . $title . '</span>';
              // 角标
            if ( ! empty( $menu_badge ) ) {
                $item_output .= ' <span class="mega-badge">' . esc_html( $menu_badge ) . '</span>';
            }
            // 箭头指示
            $item_output .= '<svg class="mega-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M9 18l6-6-6-6"/></svg>';
            $item_output .= '</span>';
        }
        // [Columns模式 / 普通]
        else {
            // 显示图标（如果有图片，且为depth=1的子菜单项）
            if ( $depth === 1 && ! empty( $menu_image ) ) {
                $item_output .= '<span class="mega-column-icon">' . $this->render_menu_image( $menu_image, 'icon' ) . '</span>';
            }
            $item_output .= $args->link_before . $title . $args->link_after;
            // 角标
            if ( ! empty( $menu_badge ) ) {
                $item_output .= ' <span class="mega-badge">' . esc_html( $menu_badge ) . '</span>';
            }
             // 描述 (普通菜单也可以有)
            if ( ! empty( $menu_desc ) ) {
                $item_output .= '<span class="menu-desc-inline">' . esc_html( $menu_desc ) . '</span>';
            }
        }
        
        $item_output .= '</a>';
        $item_output .= $args->after;

        $output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $menu_item, $depth, $args );
    }

    /**
     * 规范 Mega Menu 布局模式，兼容历史脏数据。
     *
     * @param mixed $style Raw style.
     * @return string
     */
    private function normalize_mega_style( $style ) {
        $style = sanitize_key( (string) $style );
        return in_array( $style, array( 'columns', 'grid', 'sidebar' ), true ) ? $style : 'columns';
    }
}
