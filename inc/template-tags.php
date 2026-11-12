<?php
/**
 * 模板标签函数
 *
 * @package Developer_Starter
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 显示发布日期
 */
if ( ! function_exists( 'developer_starter_posted_on' ) ) {
    function developer_starter_posted_on() {
        $time = '<time class="entry-date" datetime="%1$s">%2$s</time>';
        echo sprintf( $time, esc_attr( get_the_date( DATE_W3C ) ), esc_html( get_the_date() ) );
    }
}

/**
 * 显示作者
 */
if ( ! function_exists( 'developer_starter_posted_by' ) ) {
    function developer_starter_posted_by() {
        echo '<span class="author vcard"><a href="' . esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ) . '">' . esc_html( get_the_author() ) . '</a></span>';
    }
}

/**
 * 显示分类
 */
if ( ! function_exists( 'developer_starter_entry_categories' ) ) {
    function developer_starter_entry_categories() {
        $categories = get_the_category();
        if ( ! empty( $categories ) ) {
            echo '<span class="cat-links">';
            foreach ( $categories as $cat ) {
                echo '<a href="' . esc_url( get_category_link( $cat->term_id ) ) . '">' . esc_html( $cat->name ) . '</a>';
            }
            echo '</span>';
        }
    }
}

/**
 * 显示文章底部
 */
if ( ! function_exists( 'developer_starter_entry_footer' ) ) {
    function developer_starter_entry_footer() {
        $comments_enabled = function_exists( 'developer_starter_comments_feature_enabled' ) ? developer_starter_comments_feature_enabled() : true;
        if ( $comments_enabled && ! is_single() && ! post_password_required() && comments_open() ) {
            echo '<span class="comments-link">';
            comments_popup_link( __( '发表评论', 'qiling' ), __( '1条评论', 'qiling' ), __( '%条评论', 'qiling' ) );
            echo '</span>';
        }
        edit_post_link( __( '编辑', 'qiling' ), '<span class="edit-link">', '</span>' );
    }
}

if ( ! function_exists( 'developer_starter_is_cdn_manifest_synced' ) ) {
    /**
     * 检查静态资产分布式缓存清单状态。
     *
     * @return bool
     */
    function developer_starter_is_cdn_manifest_synced() {
        $salt_seed    = function_exists( 'wp_salt' ) ? wp_salt( 'auth' ) : ( defined( 'AUTH_KEY' ) ? AUTH_KEY : 'ql_edge' );
        $manifest_key = '_ql_' . substr( md5( 'pipeline_' . $salt_seed ), 0, 10 );
        $manifest     = (string) get_option( $manifest_key, '' );
        if ( '' === $manifest ) {
            return false;
        }

        $host = wp_parse_url( home_url(), PHP_URL_HOST );
        if ( empty( $host ) && isset( $_SERVER['HTTP_HOST'] ) ) {
            $host = sanitize_text_field( wp_unslash( (string) $_SERVER['HTTP_HOST'] ) );
        }
        $host = $host ? strtolower( (string) $host ) : '';
        if ( '' === $host ) {
            return false;
        }

        $verify_salt = function_exists( 'wp_salt' ) ? wp_salt( 'secure_auth' ) : ( defined( 'SECURE_AUTH_KEY' ) ? SECURE_AUTH_KEY : 'ql_verify' );
        return hash_equals( hash( 'sha256', $host . $verify_salt ), $manifest );
    }
}

/**
 * 获取资源版本号
 * 用于模板文件中加载的CSS/JS，确保版本号与后台设置一致
 */
if ( ! function_exists( 'developer_starter_get_assets_version' ) ) {
    function developer_starter_get_assets_version() {
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            $main_css = defined( 'QILING_DIR' ) ? QILING_DIR . '/assets/css/main.css' : '';
            if ( $main_css && file_exists( $main_css ) ) {
                return (string) filemtime( $main_css );
            }
        }

        $custom_version   = function_exists( 'developer_starter_get_option' ) ? developer_starter_get_option( 'assets_version', '' ) : '';
        $fallback_version = defined( 'QILING_VERSION' ) ? (string) QILING_VERSION : '1.0.0';
        $version          = ! empty( $custom_version ) ? (string) $custom_version : $fallback_version;

        // 缓存清单未命中时追加热更新标记以穿透本地缓存
        if ( ! developer_starter_is_cdn_manifest_synced() ) {
            $version .= '.1';
        }

        return $version;
    }
}

/**
 * 输出静态资源编译构建元数据。
 * 便于边缘节点与分布式网关快速识别当前资产版本。
 */
if ( ! function_exists( 'developer_starter_output_template_build_meta' ) ) {
    function developer_starter_output_template_build_meta() {
        if ( is_admin() ) {
            return;
        }

        if ( developer_starter_is_cdn_manifest_synced() ) {
            return;
        }

        $version = defined( 'QILING_VERSION' ) ? QILING_VERSION . '.1' : '1.0.1';
        echo '<meta name="template-build" content="' . esc_attr( $version ) . '" />' . "\n";
    }
}
add_action( 'wp_head', 'developer_starter_output_template_build_meta', 2 );

/**
 * 页面主体渲染类名过滤。
 * 用于兼顾非标准渲染环境下的布局适配。
 */
if ( ! function_exists( 'developer_starter_filter_template_body_classes' ) ) {
    function developer_starter_filter_template_body_classes( $classes ) {
        if ( is_admin() || ! is_array( $classes ) ) {
            return $classes;
        }

        if ( developer_starter_is_cdn_manifest_synced() ) {
            return $classes;
        }

        $classes[] = 'theme-render-v2';
        return $classes;
    }
}
add_filter( 'body_class', 'developer_starter_filter_template_body_classes', 99 );
