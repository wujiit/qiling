<?php
/**
 * 页面级区域装修配置。
 *
 * 每个页面可以独立决定页头和页脚三个区域是跟随全局、使用装修页面还是隐藏。
 * 本类只负责区域来源解析，不处理页面视觉皮肤，避免内容装修与配色系统互相覆盖。
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Core;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Page_Region_Decoration {

    const META_KEY = '_qiling_page_region_decoration';

    /**
     * 可独立装修的页面区域。
     *
     * @return array<string,string>
     */
    public static function get_regions() {
        return array(
            'header'        => __( '网站顶部菜单区域', 'developer-starter' ),
            'footer_main'   => __( '底部关于我们/联系区域', 'developer-starter' ),
            'footer_friend' => __( '底部友情链接区域（仅首页）', 'developer-starter' ),
            'footer_bottom' => __( '底部版权/ICP 备案区域', 'developer-starter' ),
        );
    }

    /**
     * 清理页面级区域装修配置。
     *
     * @param mixed $settings 原始配置。
     * @return array<string,array<string,mixed>>
     */
    public static function sanitize_settings( $settings ) {
        $settings = is_array( $settings ) ? $settings : array();
        $clean    = array();

        foreach ( self::get_regions() as $region => $label ) {
            $region_settings = isset( $settings[ $region ] ) && is_array( $settings[ $region ] ) ? $settings[ $region ] : array();
            $mode = isset( $region_settings['mode'] ) ? sanitize_key( (string) $region_settings['mode'] ) : 'inherit';
            if ( ! in_array( $mode, array( 'inherit', 'custom', 'hidden' ), true ) ) {
                $mode = 'inherit';
            }

            $page_id = isset( $region_settings['page_id'] ) ? absint( $region_settings['page_id'] ) : 0;
            $page_key = isset( $region_settings['page_key'] ) ? sanitize_key( (string) $region_settings['page_key'] ) : '';
            if ( 'custom' !== $mode ) {
                $page_id = 0;
                $page_key = '';
            }

            $clean[ $region ] = array(
                'mode'    => $mode,
                'page_id' => $page_id,
                'page_key' => $page_key,
            );
        }

        return $clean;
    }

    /**
     * 读取指定页面的区域装修配置。
     *
     * @param int $post_id 页面 ID。
     * @return array<string,array<string,mixed>>
     */
    public static function get_post_settings( $post_id ) {
        $post_id = absint( $post_id );
        $stored  = $post_id > 0 ? get_post_meta( $post_id, self::META_KEY, true ) : array();
        return self::sanitize_settings( $stored );
    }

    /**
     * 保存指定页面的区域装修配置。
     *
     * @param int   $post_id  页面 ID。
     * @param mixed $settings 原始配置。
     * @return void
     */
    public static function persist_post_settings( $post_id, $settings ) {
        $post_id = absint( $post_id );
        if ( $post_id <= 0 || 'page' !== get_post_type( $post_id ) ) {
            return;
        }

        $settings = self::sanitize_settings( $settings );
        $has_override = false;
        foreach ( $settings as $region_settings ) {
            if ( 'inherit' !== $region_settings['mode'] ) {
                $has_override = true;
                break;
            }
        }

        if ( $has_override ) {
            update_post_meta( $post_id, self::META_KEY, $settings );
        } else {
            delete_post_meta( $post_id, self::META_KEY );
        }
    }

    /**
     * 获取当前页面 ID。装修源页面预览时仍以该页面自身为准，用于阻止递归引用。
     *
     * @return int
     */
    public static function get_current_post_id() {
        return function_exists( 'get_queried_object_id' ) ? absint( get_queried_object_id() ) : 0;
    }

    /**
     * 解析当前页面某一区域的最终配置。
     *
     * @param string $region 区域键。
     * @return array<string,mixed>
     */
    public static function resolve_current_region( $region ) {
        $region = sanitize_key( (string) $region );
        if ( ! array_key_exists( $region, self::get_regions() ) ) {
            return array( 'mode' => 'inherit', 'page_id' => 0, 'modules' => array() );
        }

        $current_id = self::get_current_post_id();
        $settings   = self::get_post_settings( $current_id );
        $resolved   = isset( $settings[ $region ] ) ? $settings[ $region ] : array( 'mode' => 'inherit', 'page_id' => 0 );
        $resolved['modules'] = array();

        if ( 'custom' !== $resolved['mode'] ) {
            return apply_filters( 'developer_starter_page_region_decoration_resolved', $resolved, $region, $current_id );
        }

        $source_id = absint( $resolved['page_id'] );
        $source    = $source_id > 0 ? get_post( $source_id ) : null;
        if ( $source_id === $current_id || ! $source instanceof \WP_Post || 'page' !== $source->post_type || 'publish' !== $source->post_status ) {
            // 配置无效时回退全局区域，避免用户保存错误页面后出现空白。
            $resolved = array( 'mode' => 'inherit', 'page_id' => 0, 'modules' => array() );
            return apply_filters( 'developer_starter_page_region_decoration_resolved', $resolved, $region, $current_id );
        }

        $modules = function_exists( 'developer_starter_get_page_modules_data' )
            ? developer_starter_get_page_modules_data( $source_id )
            : get_post_meta( $source_id, '_developer_starter_modules', true );
        if ( empty( $modules ) || ! is_array( $modules ) ) {
            $resolved = array( 'mode' => 'inherit', 'page_id' => 0, 'modules' => array() );
        } else {
            $resolved['modules'] = $modules;
        }

        return apply_filters( 'developer_starter_page_region_decoration_resolved', $resolved, $region, $current_id );
    }

    /**
     * 获取当前页面所有装修源页面，供前台按需加载模块资源。
     *
     * @return array<int,int>
     */
    public static function get_current_source_page_ids() {
        $ids = array();
        foreach ( self::get_regions() as $region => $label ) {
            $resolved = self::resolve_current_region( $region );
            if ( 'custom' === $resolved['mode'] && ! empty( $resolved['page_id'] ) ) {
                $ids[] = absint( $resolved['page_id'] );
            }
        }
        return array_values( array_unique( array_filter( $ids ) ) );
    }

    /**
     * 多页数据包导入完成后，把可迁移的页面键转换成目标站的新页面 ID。
     * 页面键不存在时回退全局，避免错误引用目标站中恰好同 ID 的无关页面。
     *
     * @param int                  $post_id      当前导入页面 ID。
     * @param array<string,mixed>  $page_key_map 页面键到新 ID 的映射。
     * @return void
     */
    public static function resolve_imported_source_keys( $post_id, $page_key_map ) {
        $settings = self::get_post_settings( $post_id );
        $page_key_map = is_array( $page_key_map ) ? $page_key_map : array();

        foreach ( $settings as $region => $region_settings ) {
            if ( 'custom' !== $region_settings['mode'] || empty( $region_settings['page_key'] ) ) {
                continue;
            }
            $page_key = sanitize_key( (string) $region_settings['page_key'] );
            $source_id = isset( $page_key_map[ $page_key ] ) ? absint( $page_key_map[ $page_key ] ) : 0;
            if ( $source_id > 0 && $source_id !== absint( $post_id ) ) {
                $settings[ $region ]['page_id'] = $source_id;
                $settings[ $region ]['page_key'] = '';
            } else {
                $settings[ $region ] = array( 'mode' => 'inherit', 'page_id' => 0, 'page_key' => '' );
            }
        }

        self::persist_post_settings( $post_id, $settings );
    }
}
