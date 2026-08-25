<?php
/**
 * Frontend protection and comment optimization helpers split from functions.php.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 微信内置浏览器访问拦截。
 */
function developer_starter_is_wechat_browser_request() {
    $user_agent = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( (string) $_SERVER['HTTP_USER_AGENT'] ) ) : '';
    if ( '' === $user_agent ) {
        return false;
    }

    $keywords = apply_filters(
        'developer_starter_wechat_browser_block_keywords',
        array( 'MicroMessenger' )
    );
    if ( ! is_array( $keywords ) ) {
        $keywords = array( 'MicroMessenger' );
    }

    foreach ( $keywords as $keyword ) {
        $keyword = is_string( $keyword ) ? trim( $keyword ) : '';
        if ( '' !== $keyword && false !== stripos( $user_agent, $keyword ) ) {
            return true;
        }
    }

    return false;
}

function developer_starter_maybe_block_wechat_browser() {
    if ( '1' !== (string) developer_starter_get_option( 'wechat_browser_block_enable', '' ) ) {
        return;
    }

    if (
        is_admin()
        || ( function_exists( 'wp_doing_ajax' ) && wp_doing_ajax() )
        || ( defined( 'REST_REQUEST' ) && REST_REQUEST )
        || ( defined( 'WP_CLI' ) && WP_CLI )
    ) {
        return;
    }

    if ( ! developer_starter_is_wechat_browser_request() ) {
        return;
    }

    if ( ! headers_sent() ) {
        status_header( 200 );
        nocache_headers();
        header( 'Content-Type: text/html; charset=' . get_bloginfo( 'charset' ) );
        header( 'Vary: User-Agent', false );
        header( 'X-Robots-Tag: noindex, nofollow', true );
    }

    developer_starter_render_wechat_browser_overlay_page();
    exit;
}
add_action( 'template_redirect', 'developer_starter_maybe_block_wechat_browser', 0 );

function developer_starter_render_wechat_browser_overlay_page() {
    $title = trim( (string) developer_starter_get_option( 'wechat_browser_block_title', __( '请在浏览器中打开', 'developer-starter' ) ) );
    $desc  = trim( (string) developer_starter_get_option( 'wechat_browser_block_desc', __( '当前页面在微信内可能无法正常操作，请按提示切换到系统浏览器继续访问。', 'developer-starter' ) ) );
    if ( '' === $title ) {
        $title = __( '请在浏览器中打开', 'developer-starter' );
    }
    if ( '' === $desc ) {
        $desc = __( '当前页面在微信内可能无法正常操作，请按提示切换到系统浏览器继续访问。', 'developer-starter' );
    }

    ?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="robots" content="noindex,nofollow">
    <title><?php echo esc_html( $title ); ?></title>
    <style id="developer-starter-wechat-browser-guide-css">
        html,
        body {
            width: 100%;
            min-height: 100%;
            margin: 0;
            background: var(--color-neutral-0);
            overflow: hidden;
        }

        .ds-wechat-browser-guide {
            position: fixed;
            inset: 0;
            z-index: 2147483647;
            min-height: 100vh;
            padding: calc(var(--qiling-space-80) + var(--qiling-space-6)) var(--qiling-space-24) var(--qiling-space-32);
            padding: calc(env(safe-area-inset-top, 0px) + var(--qiling-space-80) + var(--qiling-space-6)) var(--qiling-space-24) var(--qiling-space-32);
            box-sizing: border-box;
            color: var(--color-neutral-0);
            background: rgba(var(--qiling-rgb-0-0-0), 0.76);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", "PingFang SC", "Hiragino Sans GB", "Microsoft YaHei", Arial, sans-serif;
            pointer-events: auto;
        }

        .ds-wechat-browser-guide__inner {
            position: relative;
            width: min(430px, 100%);
            margin: 0 auto;
            padding-top: var(--qiling-space-72);
        }

        .ds-wechat-browser-guide__arrow {
            position: absolute;
            top: 0;
            right: 10px;
            width: 116px;
            height: 86px;
            color: var(--color-neutral-0);
        }

        .ds-wechat-browser-guide__step {
            display: flex;
            align-items: center;
            min-height: 48px;
            gap: var(--qiling-space-12);
            margin: var(--qiling-space-16) 0;
            font-size: var(--qiling-text-rem-1p75);
            line-height: 1.35;
            font-weight: 500;
            letter-spacing: 0;
            text-shadow: 0 2px 10px rgba(var(--qiling-rgb-0-0-0), 0.35);
        }

        .ds-wechat-browser-guide__number {
            flex: 0 0 auto;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--color-error);
            color: var(--color-neutral-0);
            font-size: var(--qiling-text-rem-1p25);
            font-weight: 700;
            text-shadow: none;
        }

        .ds-wechat-browser-guide__menu {
            flex: 0 0 auto;
            display: inline-flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: var(--qiling-space-4);
            width: 66px;
            height: 44px;
            border: 1px solid rgba(var(--qiling-rgb-255-255-255), 0.2);
            border-radius: 8px;
            background: linear-gradient(180deg, var(--color-neutral-600) 0%, var(--color-neutral-800) 100%);
            box-shadow: 0 2px 8px rgba(var(--qiling-rgb-0-0-0), 0.45);
        }

        .ds-wechat-browser-guide__menu span {
            display: block;
            width: 5px;
            height: 5px;
            border-radius: 50%;
            background: var(--color-neutral-0);
        }

        .ds-wechat-browser-guide__browser {
            display: inline-flex;
            align-items: center;
            min-height: 38px;
            padding: 0 var(--qiling-space-16);
            border-radius: 8px;
            color: var(--color-neutral-0);
            background: linear-gradient(180deg, var(--color-neutral-500) 0%, var(--color-neutral-700) 100%);
            box-shadow: 0 2px 8px rgba(var(--qiling-rgb-0-0-0), 0.45);
            font-size: var(--qiling-text-rem-1p5);
            line-height: 1.2;
            white-space: nowrap;
        }

        .ds-wechat-browser-guide__browser::before {
            content: "";
            width: 18px;
            height: 18px;
            margin-right: var(--qiling-space-10);
            border: 2px solid currentColor;
            border-radius: 50%;
            box-sizing: border-box;
            box-shadow: inset 0 0 0 3px rgba(var(--qiling-rgb-255-255-255), 0.18);
        }

        .ds-wechat-browser-guide__note {
            max-width: var(--qiling-measure-360);
            margin: var(--qiling-space-28) 0 0 var(--qiling-space-52);
            color: rgba(var(--qiling-rgb-255-255-255), 0.84);
            font-size: var(--qiling-text-rem-0p95);
            line-height: 1.7;
            text-shadow: 0 2px 8px rgba(var(--qiling-rgb-0-0-0), 0.38);
        }

        @media (max-width: 420px) {
            .ds-wechat-browser-guide {
                padding-right: var(--qiling-space-18);
                padding-left: var(--qiling-space-18);
            }

            .ds-wechat-browser-guide__inner {
                padding-top: var(--qiling-space-66);
            }

            .ds-wechat-browser-guide__arrow {
                right: 0;
                width: 100px;
                height: 76px;
            }

            .ds-wechat-browser-guide__step {
                gap: var(--qiling-space-10);
                font-size: calc(var(--qiling-font-size-base) * 1.4375);
            }

            .ds-wechat-browser-guide__number {
                width: 36px;
                height: 36px;
                font-size: var(--qiling-text-rem-1p125);
            }

            .ds-wechat-browser-guide__menu {
                width: 58px;
                height: 40px;
            }

            .ds-wechat-browser-guide__browser {
                min-height: 36px;
                padding: 0 var(--qiling-space-12);
                font-size: var(--qiling-text-rem-1p25);
            }

            .ds-wechat-browser-guide__note {
                margin-left: var(--qiling-space-46);
                font-size: var(--qiling-text-rem-0p875);
            }
        }

        @media (max-width: 360px) {
            .ds-wechat-browser-guide__step {
                gap: var(--qiling-space-8);
                font-size: var(--qiling-text-rem-1p25);
            }

            .ds-wechat-browser-guide__number {
                width: 34px;
                height: 34px;
                font-size: var(--qiling-text-rem-1p0625);
            }

            .ds-wechat-browser-guide__menu {
                width: 52px;
                height: 38px;
            }

            .ds-wechat-browser-guide__browser {
                padding: 0 var(--qiling-space-10);
                font-size: var(--qiling-text-rem-1p125);
            }

            .ds-wechat-browser-guide__browser::before {
                width: 16px;
                height: 16px;
                margin-right: var(--qiling-space-8);
            }
        }
    </style>
</head>
<body>
    <div class="ds-wechat-browser-guide" role="dialog" aria-modal="true" aria-label="<?php echo esc_attr( $title ); ?>">
        <div class="ds-wechat-browser-guide__inner">
            <svg class="ds-wechat-browser-guide__arrow" viewBox="0 0 116 86" aria-hidden="true" focusable="false">
                <path d="M10 76C44 63 72 42 88 13" fill="none" stroke="currentColor" stroke-width="7" stroke-linecap="round"/>
                <path d="M75 15L91 8L98 25" fill="none" stroke="currentColor" stroke-width="7" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <div class="ds-wechat-browser-guide__step">
                <span class="ds-wechat-browser-guide__number">1</span>
                <span><?php esc_html_e( '点击右上角的', 'developer-starter' ); ?></span>
                <span class="ds-wechat-browser-guide__menu" aria-hidden="true"><span></span><span></span><span></span></span>
                <span><?php esc_html_e( '按钮', 'developer-starter' ); ?></span>
            </div>
            <div class="ds-wechat-browser-guide__step">
                <span class="ds-wechat-browser-guide__number">2</span>
                <span><?php esc_html_e( '选择', 'developer-starter' ); ?></span>
                <span class="ds-wechat-browser-guide__browser"><?php esc_html_e( '在浏览器中打开', 'developer-starter' ); ?></span>
            </div>
            <?php if ( '' !== $desc ) : ?>
                <p class="ds-wechat-browser-guide__note"><?php echo nl2br( esc_html( $desc ) ); ?></p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
    <?php
}

/**
 * 内容保护（禁用右键/选择）
 */
function developer_starter_content_protection() {
    // 仅对未登录用户生效
    if ( is_user_logged_in() ) {
        return;
    }

    $disable_right_click = developer_starter_get_option( 'disable_right_click', '' );
    $disable_text_select = developer_starter_get_option( 'disable_text_select', '' );

    if ( ! $disable_right_click && ! $disable_text_select ) {
        return;
    }

    add_action( 'wp_footer', function() use ( $disable_right_click, $disable_text_select ) {
        echo '<script>';
        if ( $disable_right_click ) {
            echo 'document.addEventListener("contextmenu",function(e){e.preventDefault();});';
        }
        if ( $disable_text_select ) {
            echo 'document.addEventListener("selectstart",function(e){e.preventDefault();});';
            echo 'document.body.style.userSelect="none";document.body.style.webkitUserSelect="none";';
        }
        echo '</script>';
    }, 999 );
}
add_action( 'wp', 'developer_starter_content_protection' );

/**
 * 评论优化
 */
if ( ! function_exists( 'developer_starter_comments_feature_enabled' ) ) {
    /**
     * 前台评论功能是否启用（受主题“完全禁用评论”开关控制）。
     *
     * @return bool
     */
function developer_starter_comments_feature_enabled() {
        return ! (bool) developer_starter_get_option( 'disable_comments', '' );
    }
}

/**
 * Respect WordPress's global discussion default on the frontend.
 * The option normally affects newly created posts only; the theme also uses it
 * as the site's explicit global comment visibility switch.
 *
 * @param bool $open    Whether comments are open.
 * @param int  $post_id Post ID.
 * @return bool
 */
function developer_starter_filter_global_comments_open( $open, $post_id ) {
    $post_id = absint( $post_id );

    if ( is_admin() ) {
        return $open;
    }

    $per_post_setting = $post_id > 0 ? get_post_meta( $post_id, '_qiling_comments_enabled', true ) : '';
    // Existing posts are comment opt-in, so historical comment_status=open
    // values cannot reopen a post unless its switch is explicitly enabled.
    return '1' === (string) $per_post_setting && developer_starter_comments_feature_enabled();
}
// 放在最后，确保历史文章自身的 comment_status=open 不会覆盖全局关闭设置。
add_filter( 'comments_open', 'developer_starter_filter_global_comments_open', PHP_INT_MAX, 2 );

function developer_starter_register_late_comment_filters() {
    if ( is_admin() ) {
        return;
    }

    add_filter( 'comments_open', 'developer_starter_filter_global_comments_open', PHP_INT_MAX, 2 );
    add_filter(
        'comments_array',
        static function( $comments, $post_id ) {
            return developer_starter_filter_global_comments_open( true, $post_id ) ? $comments : array();
        },
        PHP_INT_MAX,
        2
    );
    add_filter(
        'get_comments_number',
        static function( $count, $post_id ) {
            return developer_starter_filter_global_comments_open( true, $post_id ) ? $count : 0;
        },
        PHP_INT_MAX,
        2
    );
}
add_action( 'wp', 'developer_starter_register_late_comment_filters', PHP_INT_MAX );

function developer_starter_comment_optimizations() {
    // 完全禁用评论
    if ( ! developer_starter_comments_feature_enabled() ) {
        add_action( 'admin_init', function() {
            $post_types = get_post_types();
            foreach ( $post_types as $post_type ) {
                if ( post_type_supports( $post_type, 'comments' ) ) {
                    remove_post_type_support( $post_type, 'comments' );
                    remove_post_type_support( $post_type, 'trackbacks' );
                }
            }
        } );

        add_filter( 'comments_open', '__return_false', 20, 2 );
        add_filter( 'pings_open', '__return_false', 20, 2 );

        add_filter( 'get_comments_number', function( $count, $post_id ) {
            if ( is_admin() ) {
                return $count;
            }
            return 0;
        }, 20, 2 );
        add_filter( 'comments_array', function( $comments, $post_id ) {
            if ( is_admin() ) {
                return $comments;
            }
            return array();
        }, 20, 2 );

        add_action( 'admin_menu', function() {
            remove_menu_page( 'edit-comments.php' );
        } );

        add_action( 'admin_bar_menu', function( $wp_admin_bar ) {
            $wp_admin_bar->remove_node( 'comments' );
        }, 999 );
    }

    // 评论蜜罐
    if ( developer_starter_get_option( 'comment_honeypot', '' ) ) {
        add_action( 'comment_form', function() {
            echo '<p style="display:none !important;"><label>Leave this empty: <input type="text" name="ds_hp_field" value="" autocomplete="off" /></label></p>';
        } );

        add_filter( 'preprocess_comment', function( $commentdata ) {
            $honeypot_value = isset( $_POST['ds_hp_field'] ) ? sanitize_text_field( wp_unslash( $_POST['ds_hp_field'] ) ) : '';
            if ( '' !== $honeypot_value ) {
                wp_die( __( '垃圾评论检测：提交被阻止。', 'developer-starter' ), 403 );
            }
            return $commentdata;
        } );
    }
}
add_action( 'init', 'developer_starter_comment_optimizations', 1 );
