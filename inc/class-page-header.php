<?php
/**
 * Page Header Manager - 页面头部面包屑管理
 * 
 * 提供灵活的页面头部控制，支持：
 * - WordPress Filter 供第三方插件控制显示
 * - 页面 Meta 设置供用户单独控制每个页面
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Core;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Page_Header 类
 */
class Page_Header {

    /**
     * 页面头部默认背景。
     *
     * @return string
     */
    protected static function get_default_background() {
        return 'linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-dark) 100%)';
    }

    /**
     * 获取页面头部描述。
     *
     * @param string   $template 模板类型。
     * @param int|null $post_id 页面 ID。
     * @return string
     */
    public static function get_description( $template = 'default', $post_id = null ) {
        if ( null === $post_id ) {
            $post_id = get_the_ID();
        }

        $description = '';
        if ( has_excerpt( $post_id ) ) {
            $description = function_exists( 'developer_starter_get_translated_post_excerpt' )
                ? developer_starter_get_translated_post_excerpt( $post_id )
                : get_the_excerpt( $post_id );
        }

        $description = is_string( $description ) ? trim( wp_strip_all_tags( $description ) ) : '';

        /**
         * Filter: 自定义页面头部描述
         *
         * @param string $description 页面摘要/副标题
         * @param string $template    模板类型
         * @param int    $post_id     当前页面 ID
         * @return string
         */
        return (string) apply_filters( 'qiling_page_header_description', $description, $template, $post_id );
    }

    /**
     * 判断是否应该显示页面头部
     *
     * @param int|null $post_id 页面 ID，默认当前页面
     * @return bool
     */
    public static function should_show( $post_id = null ) {
        if ( null === $post_id ) {
            $post_id = get_the_ID();
        }

        // 1. 首先检查页面 Meta 设置（用户在编辑页面时设置的）
        $hide_header = get_post_meta( $post_id, '_qiling_hide_page_header', true );
        $show = empty( $hide_header );

        /**
         * Filter: 控制是否显示页面头部面包屑区域
         * 
         * 第三方插件可以使用此 Filter 来禁用页面头部，例如：
         * 
         * add_filter( 'qiling_show_page_header', function( $show, $post_id ) {
         *     if ( is_page( 'my-plugin-page' ) ) {
         *         return false;
         *     }
         *     return $show;
         * }, 10, 2 );
         * 
         * @param bool $show    是否显示，true 显示，false 隐藏
         * @param int  $post_id 当前页面 ID
         * @return bool
         */
        return apply_filters( 'qiling_show_page_header', $show, $post_id );
    }

    /**
     * 获取页面头部的背景样式
     *
     * @param string $template 模板类型 (default, fullwidth, fullscreen)
     * @return string CSS 样式字符串
     */
    public static function get_background_style( $template = 'default' ) {
        // 获取全局设置的页面头部高度
        $padding = trim( (string) developer_starter_get_option( 'page_header_padding', '100px 0 60px' ) );
        if ( empty( $padding ) ) {
            $padding = '100px 0 60px';
        }

        $background = trim( (string) developer_starter_get_option( 'page_header_background', '' ) );
        if ( '' === $background ) {
            $background = self::get_default_background();
        }

        $title_color = trim( (string) developer_starter_get_option( 'page_header_title_color', '' ) );
        if ( '' === $title_color ) {
            $title_color = 'var(--color-text-inverse, #ffffff)';
        }

        $description_color = trim( (string) developer_starter_get_option( 'page_header_subtitle_color', '' ) );
        if ( '' === $description_color ) {
            $description_color = 'rgba(255, 255, 255, 0.82)';
        }

        $base_style = sprintf(
            'background: %1$s; padding: %2$s; --qiling-page-header-title-color: %3$s; --qiling-page-header-description-color: %4$s;',
            $background,
            $padding,
            $title_color,
            $description_color
        );

        /**
         * Filter: 自定义页面头部背景样式
         * 
         * @param string $style    CSS 样式字符串
         * @param string $template 模板类型
         * @param int    $post_id  当前页面 ID
         * @return string
         */
        return apply_filters( 'qiling_page_header_style', $base_style, $template, get_the_ID() );
    }

    /**
     * 获取页面头部的 CSS 类名
     *
     * @param string $template 模板类型 (default, fullwidth, fullscreen)
     * @return string 类名字符串
     */
    public static function get_classes( $template = 'default' ) {
        $classes = array( 'page-header', 'page-header--hero' );

        if ( ! empty( $template ) ) {
            $classes[] = 'page-header--' . sanitize_html_class( $template );
        }
        
        if ( 'fullscreen' === $template ) {
            $classes[] = 'fullscreen-page-header';
        }
        
        /**
         * Filter: 自定义页面头部 CSS 类名
         * 
         * @param array  $classes  CSS 类名数组
         * @param string $template 模板类型
         * @param int    $post_id  当前页面 ID
         * @return array
         */
        $classes = apply_filters( 'qiling_page_header_classes', $classes, $template, get_the_ID() );
        
        return implode( ' ', $classes );
    }

    /**
     * 渲染页面头部
     *
     * @param string $template 模板类型 (default, fullwidth, fullscreen)
     * @return void
     */
    public static function render( $template = 'default' ) {
        // 检查是否应该显示
        if ( ! self::should_show() ) {
            return;
        }

        $classes = self::get_classes( $template );
        $style   = self::get_background_style( $template );
        $post_id = get_the_ID();
        $title   = function_exists( 'developer_starter_get_translated_post_title' )
            ? developer_starter_get_translated_post_title( $post_id )
            : get_the_title( $post_id );
        $description = self::get_description( $template, $post_id );

        /**
         * Filter: 自定义页面头部标题
         * 
         * @param string $title    页面标题
         * @param string $template 模板类型
         * @param int    $post_id  当前页面 ID
         * @return string
         */
        $title = apply_filters( 'qiling_page_header_title', $title, $template, $post_id );

        /**
         * Action: 页面头部渲染前
         * 
         * @param string $template 模板类型
         * @param int    $post_id  当前页面 ID
         */
        do_action( 'qiling_before_page_header', $template, $post_id );
        ?>
        <div class="<?php echo esc_attr( $classes ); ?>" style="<?php echo esc_attr( $style ); ?>">
            <div class="container">
                <h1 class="page-title">
                    <?php echo esc_html( $title ); ?>
                </h1>
                <?php if ( '' !== $description ) : ?>
                    <p class="page-header__description">
                        <?php echo esc_html( $description ); ?>
                    </p>
                <?php endif; ?>
            </div>
        </div>
        <?php
        /**
         * Action: 页面头部渲染后
         * 
         * @param string $template 模板类型
         * @param int    $post_id  当前页面 ID
         */
        do_action( 'qiling_after_page_header', $template, $post_id );
    }
}
