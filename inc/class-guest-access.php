<?php
/**
 * Guest Access Control
 *
 * 控制游客访问权限：全站登录、分类限制、菜单隐藏与提示框展示
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Core;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Guest_Access {

    private static $instance = null;

    public static function instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action( 'template_redirect', array( $this, 'maybe_block_guest_access' ), 5 );
        add_filter( 'pre_get_posts', array( $this, 'filter_main_query' ) );
        add_filter( 'get_terms', array( $this, 'filter_terms' ), 10, 3 );
        add_filter( 'wp_nav_menu_objects', array( $this, 'filter_menu_items' ), 10, 2 );

        if ( is_admin() ) {
            add_action( 'wp_nav_menu_item_custom_fields', array( $this, 'render_menu_item_fields' ), 10, 4 );
            add_action( 'wp_update_nav_menu_item', array( $this, 'save_menu_item_fields' ), 10, 3 );
        }
    }

    private function is_enabled() {
        return developer_starter_get_option( 'guest_access_enable', '' ) === '1';
    }

    private function is_sitewide_enabled() {
        return developer_starter_get_option( 'guest_access_sitewide', '' ) === '1';
    }

    private function get_restricted_category_ids() {
        $raw = developer_starter_get_option( 'guest_access_categories', array() );
        $ids = array_map( 'intval', (array) $raw );
        $ids = array_values( array_filter( $ids ) );
        return array_unique( $ids );
    }

    private function should_filter_categories() {
        return $this->is_enabled() && ! is_user_logged_in() && ! is_admin();
    }

    private function get_login_url() {
        $redirect_path = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( (string) $_SERVER['REQUEST_URI'] ) ) : '';
        $redirect = $redirect_path ? home_url( $redirect_path ) : home_url( '/' );
        $login_page_id = developer_starter_get_option( 'login_page_id', '' );
        if ( $login_page_id ) {
            return add_query_arg( 'redirect_to', $redirect, get_permalink( $login_page_id ) );
        }
        return wp_login_url( $redirect );
    }

    private function get_allowed_page_ids() {
        $login_page_id = (int) developer_starter_get_option( 'login_page_id', '' );
        $register_page_id = (int) developer_starter_get_option( 'register_page_id', '' );
        $forgot_page_id = (int) developer_starter_get_option( 'forgot_password_page_id', '' );
        $allowed = array_filter( array( $login_page_id, $register_page_id, $forgot_page_id ) );
        return array_values( $allowed );
    }

    private function is_request_allowed() {
        if ( is_user_logged_in() ) {
            return true;
        }
        if ( is_admin() || wp_doing_ajax() || wp_doing_cron() ) {
            return true;
        }
        if ( is_customize_preview() ) {
            return true;
        }
        if ( is_feed() ) {
            return true;
        }
        $allowed_pages = $this->get_allowed_page_ids();
        if ( ! empty( $allowed_pages ) && is_page( $allowed_pages ) ) {
            return true;
        }
        return false;
    }

    public function maybe_block_guest_access() {
        if ( ! $this->is_enabled() || is_user_logged_in() ) {
            return;
        }
        if ( $this->is_request_allowed() ) {
            return;
        }

        if ( $this->is_sitewide_enabled() ) {
            $this->render_prompt( 'sitewide' );
            exit;
        }

        $restricted = $this->get_restricted_category_ids();
        if ( empty( $restricted ) ) {
            return;
        }

        if ( is_category() ) {
            $term_id = get_queried_object_id();
            if ( $term_id && in_array( (int) $term_id, $restricted, true ) ) {
                $this->render_prompt( 'category' );
                exit;
            }
        }

        if ( is_singular( 'post' ) && has_term( $restricted, 'category' ) ) {
            $this->render_prompt( 'post' );
            exit;
        }
    }

    public function filter_main_query( $query ) {
        if ( ! $this->should_filter_categories() || ! $query->is_main_query() ) {
            return;
        }
        if ( $this->is_sitewide_enabled() ) {
            return;
        }
        $restricted = $this->get_restricted_category_ids();
        if ( empty( $restricted ) ) {
            return;
        }
        if ( $query->is_category() ) {
            return;
        }
        $existing = $query->get( 'category__not_in' );
        $existing = is_array( $existing ) ? $existing : array();
        $query->set( 'category__not_in', array_values( array_unique( array_merge( $existing, $restricted ) ) ) );
    }

    public function filter_terms( $terms, $taxonomies, $args ) {
        if ( ! $this->should_filter_categories() ) {
            return $terms;
        }
        if ( $this->is_sitewide_enabled() ) {
            return $terms;
        }
        if ( ! in_array( 'category', (array) $taxonomies, true ) ) {
            return $terms;
        }
        $restricted = $this->get_restricted_category_ids();
        if ( empty( $restricted ) || empty( $terms ) || is_wp_error( $terms ) ) {
            return $terms;
        }
        $filtered = array();
        foreach ( $terms as $term ) {
            if ( empty( $term->term_id ) ) {
                $filtered[] = $term;
                continue;
            }
            if ( in_array( (int) $term->term_id, $restricted, true ) ) {
                continue;
            }
            $filtered[] = $term;
        }
        return $filtered;
    }

    public function filter_menu_items( $items, $args ) {
        if ( empty( $items ) ) {
            return $items;
        }

        $restricted = $this->get_restricted_category_ids();
        $restricted_paths = array();
        if ( $this->should_filter_categories() && ! $this->is_sitewide_enabled() && ! empty( $restricted ) ) {
            foreach ( $restricted as $term_id ) {
                $link = get_category_link( $term_id );
                if ( is_wp_error( $link ) ) {
                    continue;
                }
                $path = trim( (string) parse_url( $link, PHP_URL_PATH ), '/' );
                if ( $path !== '' ) {
                    $restricted_paths[] = $path;
                }
            }
            $restricted_paths = array_unique( $restricted_paths );
        }

        $filtered = array();
        foreach ( $items as $item ) {
            if ( ! is_user_logged_in() ) {
                $login_only = get_post_meta( $item->ID, '_ds_menu_login_required', true );
                if ( $login_only === '1' ) {
                    continue;
                }
            }
            if ( ! empty( $restricted_paths ) ) {
                if ( $item->object === 'category' && in_array( (int) $item->object_id, $restricted, true ) ) {
                    continue;
                }
                if ( ! empty( $item->url ) ) {
                    $path = trim( (string) parse_url( $item->url, PHP_URL_PATH ), '/' );
                    if ( $path && in_array( $path, $restricted_paths, true ) ) {
                        continue;
                    }
                }
            }
            $filtered[] = $item;
        }
        return $filtered;
    }

    public function render_menu_item_fields( $item_id, $item, $depth, $args ) {
        $checked = get_post_meta( $item_id, '_ds_menu_login_required', true ) === '1';
        ?>
        <p class="description description-wide">
            <label>
                <input type="checkbox" value="1" name="ds_menu_login_required[<?php echo esc_attr( $item_id ); ?>]" <?php checked( $checked ); ?> />
                <?php esc_html_e( '仅登录用户可见', 'developer-starter' ); ?>
            </label>
        </p>
        <?php
    }

    public function save_menu_item_fields( $menu_id, $menu_item_db_id, $args ) {
        if ( ! isset( $_POST['ds_menu_login_required'] ) || ! is_array( $_POST['ds_menu_login_required'] ) ) {
            delete_post_meta( $menu_item_db_id, '_ds_menu_login_required' );
            return;
        }
        $value = isset( $_POST['ds_menu_login_required'][ $menu_item_db_id ] ) ? '1' : '0';
        if ( $value === '1' ) {
            update_post_meta( $menu_item_db_id, '_ds_menu_login_required', '1' );
        } else {
            delete_post_meta( $menu_item_db_id, '_ds_menu_login_required' );
        }
    }

    private function render_prompt( $context = 'category' ) {
        status_header( 200 );
        nocache_headers();

        $title = developer_starter_get_option( 'guest_access_prompt_title', __( '该内容仅登录用户可见', 'developer-starter' ) );
        $desc = developer_starter_get_option( 'guest_access_prompt_desc', __( '请登录后继续浏览', 'developer-starter' ) );
        $login_text = developer_starter_get_option( 'guest_access_login_button_text', __( '立即登录', 'developer-starter' ) );
        $show_login = developer_starter_get_option( 'guest_access_login_button_enable', '1' ) !== '0';
        $extra_text = trim( (string) developer_starter_get_option( 'guest_access_extra_button_text', '' ) );
        $extra_url = trim( (string) developer_starter_get_option( 'guest_access_extra_button_url', '' ) );
        $extra_newtab = developer_starter_get_option( 'guest_access_extra_button_newtab', '' ) === '1';
        $login_url = $this->get_login_url();

        $sitewide = ( $context === 'sitewide' );
        if ( $sitewide ) {
            ?>
            <!doctype html>
            <html <?php language_attributes(); ?>>
            <head>
                <meta charset="<?php bloginfo( 'charset' ); ?>">
                <meta name="viewport" content="width=device-width, initial-scale=1">
                <?php wp_head(); ?>
            </head>
            <body <?php body_class(); ?>>
            <?php
        } else {
            get_header();
        }
        ?>
        <div class="guest-access-section">
            <div class="container">
                <div class="guest-access-card">
                    <div class="guest-access-icon">🔒</div>
                    <h2 class="guest-access-title"><?php echo esc_html( $title ); ?></h2>
                    <p class="guest-access-desc"><?php echo esc_html( $desc ); ?></p>
                    <div class="guest-access-actions">
                        <?php if ( $show_login ) : ?>
                            <button type="button" class="guest-access-btn guest-access-btn-primary guest-access-login" data-login-url="<?php echo esc_url( $login_url ); ?>">
                                <?php echo esc_html( $login_text ); ?>
                            </button>
                        <?php endif; ?>
                        <?php if ( $extra_text && $extra_url ) : ?>
                            <a class="guest-access-btn guest-access-btn-ghost" href="<?php echo esc_url( $extra_url ); ?>"<?php echo $extra_newtab ? ' target="_blank" rel="noopener noreferrer"' : ''; ?>>
                                <?php echo esc_html( $extra_text ); ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <style>
            .guest-access-section {
                padding: 80px 0;
            }
            .guest-access-card {
                background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
                border: 2px dashed #cbd5e1;
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
                background: linear-gradient(135deg, var(--color-primary, #2563eb), #60a5fa);
                color: #fff;
                box-shadow: 0 10px 24px rgba(37, 99, 235, 0.25);
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
                background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
                border-color: #334155;
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
            @media (max-width: 640px) {
                .guest-access-section {
                    padding: 60px 0;
                }
                .guest-access-card {
                    padding: 36px 22px;
                }
                .guest-access-title {
                    font-size: 20px;
                }
            }
        </style>
        <script>
            (function() {
                var buttons = document.querySelectorAll('.guest-access-login');
                if (!buttons.length) return;
                buttons.forEach(function(btn) {
                    btn.addEventListener('click', function(e) {
                        e.preventDefault();
                        if (typeof window.developerStarterShowLoginModal === 'function') {
                            if (window.developerStarterShowLoginModal('login')) {
                                return;
                            }
                        }
                        var headerLoginBtn = document.getElementById('header-login-toggle');
                        if (headerLoginBtn) {
                            headerLoginBtn.click();
                            return;
                        }
                        var url = btn.getAttribute('data-login-url');
                        if (url) {
                            window.location.href = url;
                        }
                    });
                });
            })();
        </script>
        <?php
        if ( $sitewide ) {
            wp_footer();
            ?>
            </body>
            </html>
            <?php
        } else {
            get_footer();
        }
    }
}

Guest_Access::instance();
