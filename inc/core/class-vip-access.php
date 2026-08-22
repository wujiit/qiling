<?php
/**
 * VIP Access Control
 *
 * 控制 VIP 权限：分类限制、菜单隐藏与提示框展示
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Core;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class VIP_Access {

    private static $instance = null;

    public static function instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // 分类前端拦截
        add_action( 'template_redirect', array( $this, 'maybe_block_vip_access' ), 4 ); // 优先级稍微比 guest-access 高一点
        add_filter( 'pre_get_posts', array( $this, 'filter_main_query' ) );
        add_filter( 'get_terms', array( $this, 'filter_terms' ), 10, 3 );
        
        // 菜单前端过滤
        add_filter( 'wp_nav_menu_objects', array( $this, 'filter_menu_items' ), 15, 2 ); // 优先级排在 guest access (10) 之后

        if ( is_admin() ) {
            // 菜单后台设置
            add_action( 'wp_nav_menu_item_custom_fields', array( $this, 'render_menu_item_fields' ), 20, 4 );
            add_action( 'wp_update_nav_menu_item', array( $this, 'save_menu_item_fields' ), 20, 3 );
            
            // 分类后台设置
            add_action( 'category_add_form_fields', array( $this, 'render_category_add_fields' ) );
            add_action( 'category_edit_form_fields', array( $this, 'render_category_edit_fields' ) );
            add_action( 'create_category', array( $this, 'save_category_fields' ) );
            add_action( 'edited_category', array( $this, 'save_category_fields' ) );
            
            // 支持自定义分类法 qiapp_software_category
            add_action( 'qiapp_software_category_add_form_fields', array( $this, 'render_category_add_fields' ) );
            add_action( 'qiapp_software_category_edit_form_fields', array( $this, 'render_category_edit_fields' ) );
            add_action( 'create_qiapp_software_category', array( $this, 'save_category_fields' ) );
            add_action( 'edited_qiapp_software_category', array( $this, 'save_category_fields' ) );
        }
    }

    /**
     * 判断商城插件和 VIP 模块是否启用
     */
    private function is_qilingshop_vip_active() {
        return class_exists( 'QilingShop_VIP' );
    }

    /**
     * 获取商城中所有的 VIP 等级
     */
    private function get_vip_levels() {
        if ( ! $this->is_qilingshop_vip_active() ) {
            return array();
        }
        return \QilingShop_VIP::instance()->get_levels();
    }

    /**
     * 检查用户是否达到指定的 VIP 等级
     */
    private function check_user_vip_level( $min_level ) {
        if ( ! $this->is_qilingshop_vip_active() ) {
            return true; // 如果未安装插件，则不拦截
        }
        $min_level = (int) $min_level;
        if ( $min_level <= 0 ) {
            return true;
        }
        if ( ! is_user_logged_in() ) {
            return false;
        }
        $user_id = get_current_user_id();
        return \QilingShop_VIP::instance()->is_vip( $user_id, $min_level );
    }

    // ========== 菜单项设置逻辑 ==========

    public function render_menu_item_fields( $item_id, $item, $depth, $args ) {
        if ( ! $this->is_qilingshop_vip_active() ) {
            return;
        }
        $levels = $this->get_vip_levels();
        if ( empty( $levels ) ) {
            return;
        }
        $current_level = get_post_meta( $item_id, '_ds_menu_vip_level', true );
        ?>
        <p class="description description-wide">
            <label for="ds_menu_vip_level_<?php echo esc_attr( $item_id ); ?>">
                <?php esc_html_e( '要求最低 VIP 等级', 'developer-starter' ); ?><br/>
                <select id="ds_menu_vip_level_<?php echo esc_attr( $item_id ); ?>" name="ds_menu_vip_level[<?php echo esc_attr( $item_id ); ?>]" style="width: 100%;">
                    <option value="0"><?php esc_html_e( '不限制', 'developer-starter' ); ?></option>
                    <?php foreach ( $levels as $level ) : ?>
                        <option value="<?php echo esc_attr( $level->id ); ?>" <?php selected( $current_level, $level->id ); ?>>
                            <?php echo esc_html( $level->level_name ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <span class="description"><?php esc_html_e( '仅对该级别及以上的 VIP 用户显示此菜单项。此项拥有更高优先级。', 'developer-starter' ); ?></span>
            </label>
        </p>
        <?php
    }

    public function save_menu_item_fields( $menu_id, $menu_item_db_id, $args ) {
        if ( isset( $_POST['ds_menu_vip_level'][ $menu_item_db_id ] ) ) {
            $value = intval( $_POST['ds_menu_vip_level'][ $menu_item_db_id ] );
            if ( $value > 0 ) {
                update_post_meta( $menu_item_db_id, '_ds_menu_vip_level', $value );
            } else {
                delete_post_meta( $menu_item_db_id, '_ds_menu_vip_level' );
            }
        }
    }

    public function filter_menu_items( $items, $args ) {
        if ( empty( $items ) || ! $this->is_qilingshop_vip_active() ) {
            return $items;
        }
        $filtered = array();
        foreach ( $items as $item ) {
            $required_vip_level = (int) get_post_meta( $item->ID, '_ds_menu_vip_level', true );
            if ( $required_vip_level > 0 ) {
                if ( ! $this->check_user_vip_level( $required_vip_level ) ) {
                    continue; // 权限不足，直接过滤掉该菜单项
                }
            }
            $filtered[] = $item;
        }
        return $filtered;
    }

    // ========== 分类目录设置逻辑 ==========

    public function render_category_add_fields( $taxonomy ) {
        if ( ! $this->is_qilingshop_vip_active() ) {
            return;
        }
        $levels = $this->get_vip_levels();
        if ( empty( $levels ) ) {
            return;
        }
        ?>
        <div class="form-field">
            <label for="ds_vip_level_required"><?php esc_html_e( '要求最低 VIP 等级', 'developer-starter' ); ?></label>
            <select name="ds_vip_level_required" id="ds_vip_level_required">
                <option value="0"><?php esc_html_e( '不限制', 'developer-starter' ); ?></option>
                <?php foreach ( $levels as $level ) : ?>
                    <option value="<?php echo esc_attr( $level->id ); ?>">
                        <?php echo esc_html( $level->level_name ); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <p class="description"><?php esc_html_e( '仅允许该级别及以上 VIP 用户访问该分类及其文章。', 'developer-starter' ); ?></p>
        </div>
        <?php
    }

    public function render_category_edit_fields( $term ) {
        if ( ! $this->is_qilingshop_vip_active() ) {
            return;
        }
        $levels = $this->get_vip_levels();
        if ( empty( $levels ) ) {
            return;
        }
        $current_level = get_term_meta( $term->term_id, '_ds_vip_level_required', true );
        ?>
        <tr class="form-field">
            <th scope="row"><label for="ds_vip_level_required"><?php esc_html_e( '要求最低 VIP 等级', 'developer-starter' ); ?></label></th>
            <td>
                <select name="ds_vip_level_required" id="ds_vip_level_required">
                    <option value="0"><?php esc_html_e( '不限制', 'developer-starter' ); ?></option>
                    <?php foreach ( $levels as $level ) : ?>
                        <option value="<?php echo esc_attr( $level->id ); ?>" <?php selected( $current_level, $level->id ); ?>>
                            <?php echo esc_html( $level->level_name ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <p class="description"><?php esc_html_e( '仅允许该级别及以上 VIP 用户访问该分类及其文章。', 'developer-starter' ); ?></p>
            </td>
        </tr>
        <?php
    }

    public function save_category_fields( $term_id ) {
        if ( isset( $_POST['ds_vip_level_required'] ) ) {
            $value = intval( $_POST['ds_vip_level_required'] );
            if ( $value > 0 ) {
                update_term_meta( $term_id, '_ds_vip_level_required', $value );
            } else {
                delete_term_meta( $term_id, '_ds_vip_level_required' );
            }
        }
    }

    // ========== 前端分类与文章拦截逻辑 ==========

    /**
     * 获取受限的 VIP 分类及其要求等级
     * 
     * @return array [ term_id => min_vip_level ]
     */
    private function get_restricted_vip_categories() {
        static $restricted_cache = null;
        if ( $restricted_cache !== null ) {
            return $restricted_cache;
        }
        
        $restricted_cache = array();
        
        // 获取所有设置了 VIP 门槛的 taxonomy term
        global $wpdb;
        $results = $wpdb->get_results( "SELECT term_id, meta_value FROM {$wpdb->termmeta} WHERE meta_key = '_ds_vip_level_required'" );
        if ( $results ) {
            foreach ( $results as $row ) {
                $level = intval( $row->meta_value );
                if ( $level > 0 ) {
                    $restricted_cache[ (int) $row->term_id ] = $level;
                }
            }
        }
        
        return $restricted_cache;
    }

    private function is_request_allowed() {
        if ( is_admin() || wp_doing_ajax() || wp_doing_cron() ) {
            return true;
        }
        if ( is_customize_preview() ) {
            return true;
        }
        if ( is_feed() ) {
            return true;
        }
        $login_page_id = (int) developer_starter_get_option( 'login_page_id', '' );
        $register_page_id = (int) developer_starter_get_option( 'register_page_id', '' );
        $forgot_page_id = (int) developer_starter_get_option( 'forgot_password_page_id', '' );
        $allowed = array_values( array_filter( array( $login_page_id, $register_page_id, $forgot_page_id ) ) );
        if ( ! empty( $allowed ) && is_page( $allowed ) ) {
            return true;
        }
        return false;
    }

    public function maybe_block_vip_access() {
        if ( ! $this->is_qilingshop_vip_active() ) {
            return;
        }
        if ( $this->is_request_allowed() ) {
            return;
        }

        $restricted_cats = $this->get_restricted_vip_categories();
        if ( empty( $restricted_cats ) ) {
            return;
        }

        // 1. 判断是否是分类列表页
        if ( is_category() || is_tax('qiapp_software_category') ) {
            $term_id = get_queried_object_id();
            if ( $term_id && isset( $restricted_cats[ $term_id ] ) ) {
                $required_level = $restricted_cats[ $term_id ];
                if ( ! $this->check_user_vip_level( $required_level ) ) {
                    $this->execute_vip_denied_action();
                    exit;
                }
            }
        }

        // 2. 判断是否是单篇文章，且属于 VIP 分类
        if ( is_singular( 'post' ) || is_singular( 'qiapp_software' ) ) {
            // 获取文章关联的分类ID
            $post_id = get_queried_object_id();
            $terms = wp_get_post_terms( $post_id, array( 'category', 'qiapp_software_category' ), array( 'fields' => 'ids' ) );
            if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
                $max_required_level = 0;
                foreach ( $terms as $term_id ) {
                    if ( isset( $restricted_cats[ $term_id ] ) ) {
                        $max_required_level = max( $max_required_level, $restricted_cats[ $term_id ] );
                    }
                }
                if ( $max_required_level > 0 && ! $this->check_user_vip_level( $max_required_level ) ) {
                    $this->execute_vip_denied_action();
                    exit;
                }
            }
        }
    }

    private function execute_vip_denied_action() {
        $action = developer_starter_get_option( 'vip_denied_action', 'prompt' );
        if ( $action === 'redirect' ) {
            $redirect_url = developer_starter_get_option( 'vip_denied_redirect_url', '' );
            if ( ! empty( $redirect_url ) ) {
                wp_safe_redirect( esc_url_raw( $redirect_url ) );
                exit;
            }
        }
        
        // 默认显示提示界面
        $this->render_vip_prompt();
    }

    private function render_vip_prompt() {
        status_header( 200 );
        nocache_headers();

        $title = developer_starter_get_option( 'vip_denied_prompt_title', __( '该内容仅限 VIP 会员查看', 'developer-starter' ) );
        $desc = developer_starter_get_option( 'vip_denied_prompt_desc', __( '您的权限不足，请开通或升级 VIP 后继续浏览', 'developer-starter' ) );
        $btn_text = developer_starter_get_option( 'vip_denied_btn_text', __( '立即升级 VIP', 'developer-starter' ) );
        $btn_url = developer_starter_get_option( 'vip_denied_btn_url', '' );

        // 尝试自动获取商城的 VIP 开通地址
        if ( empty( $btn_url ) ) {
            $btn_url = home_url('/user/vip'); 
        }

        get_header();
        ?>
        <div class="guest-access-section">
            <div class="container">
                <div class="guest-access-card">
                    <div class="guest-access-icon">👑</div>
                    <h2 class="guest-access-title"><?php echo esc_html( $title ); ?></h2>
                    <p class="guest-access-desc"><?php echo esc_html( $desc ); ?></p>
                    <div class="guest-access-actions">
                        <a class="guest-access-btn guest-access-btn-primary" href="<?php echo esc_url( $btn_url ); ?>">
                            <?php echo esc_html( $btn_text ); ?>
                        </a>
                        <a class="guest-access-btn guest-access-btn-ghost" href="<?php echo esc_url( home_url( '/' ) ); ?>">
                            <?php esc_html_e( '返回首页', 'developer-starter' ); ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <style>
            .guest-access-section {
                padding: 80px 0;
            }
            .guest-access-card {
                background: linear-gradient(135deg, #fdfbfb 0%, #ebedee 100%);
                border: 2px dashed #d4af37;
                border-radius: 18px;
                padding: 48px 32px;
                text-align: center;
                max-width: 720px;
                margin: 0 auto;
            }
            .guest-access-icon {
                font-size: 52px;
                margin-bottom: 16px;
            }
            .guest-access-title {
                font-size: 22px;
                font-weight: 700;
                color: #1e293b;
                margin: 0 0 10px;
            }
            .guest-access-desc {
                font-size: 14px;
                color: #64748b;
                margin: 0 0 24px;
            }
            .guest-access-actions {
                display: inline-flex;
                flex-wrap: wrap;
                gap: 12px;
                justify-content: center;
            }
            .guest-access-btn {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                padding: 12px 30px;
                border-radius: 999px;
                text-decoration: none;
                border: 1px solid transparent;
                font-size: 14px;
                font-weight: 600;
                cursor: pointer;
                transition: all 0.2s ease;
            }
            .guest-access-btn-primary {
                background: linear-gradient(135deg, #d4af37, #f1c40f);
                color: #fff;
                box-shadow: 0 10px 24px rgba(212, 175, 55, 0.25);
            }
            .guest-access-btn-ghost {
                background: #fff;
                color: #334155;
                border-color: #e2e8f0;
            }
            .guest-access-btn:hover {
                transform: translateY(-1px);
            }
            html.dark-mode .guest-access-card {
                background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
                border-color: #d4af37;
            }
            html.dark-mode .guest-access-title {
                color: #e2e8f0;
            }
            html.dark-mode .guest-access-desc {
                color: #94a3b8;
            }
            html.dark-mode .guest-access-btn-ghost {
                background: transparent;
                color: #e2e8f0;
                border-color: rgba(226, 232, 240, 0.2);
            }
        </style>
        <?php
        get_footer();
    }

    public function filter_main_query( $query ) {
        if ( ! $this->is_qilingshop_vip_active() ) {
            return;
        }
        if ( is_admin() || ! $query->is_main_query() ) {
            return;
        }

        $restricted_cats = $this->get_restricted_vip_categories();
        if ( empty( $restricted_cats ) ) {
            return;
        }

        // 在混合查询列表中，过滤掉当前用户 VIP 级别不足的分类
        if ( ! is_category() && ! is_tax('qiapp_software_category') && ! is_singular() ) {
            $exclude_cat_ids = array();
            foreach ( $restricted_cats as $term_id => $required_level ) {
                if ( ! $this->check_user_vip_level( $required_level ) ) {
                    $exclude_cat_ids[] = $term_id;
                }
            }
            if ( ! empty( $exclude_cat_ids ) ) {
                $existing = $query->get( 'category__not_in' );
                $existing = is_array( $existing ) ? $existing : array();
                $query->set( 'category__not_in', array_values( array_unique( array_merge( $existing, $exclude_cat_ids ) ) ) );
            }
        }
    }

    public function filter_terms( $terms, $taxonomies, $args ) {
        if ( ! $this->is_qilingshop_vip_active() ) {
            return $terms;
        }
        if ( is_admin() ) {
            return $terms;
        }
        if ( empty( $terms ) || is_wp_error( $terms ) ) {
            return $terms;
        }
        
        $restricted_cats = $this->get_restricted_vip_categories();
        if ( empty( $restricted_cats ) ) {
            return $terms;
        }

        $filtered = array();
        foreach ( $terms as $term ) {
            if ( empty( $term->term_id ) ) {
                $filtered[] = $term;
                continue;
            }
            if ( isset( $restricted_cats[ $term->term_id ] ) ) {
                $required_level = $restricted_cats[ $term->term_id ];
                if ( ! $this->check_user_vip_level( $required_level ) ) {
                    continue; // 过滤掉不达标的分类
                }
            }
            $filtered[] = $term;
        }
        return $filtered;
    }
}

VIP_Access::instance();
