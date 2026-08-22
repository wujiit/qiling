<?php
/**
 * Frontend Builder assets and dependency service.
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Core;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Frontend_Builder_Assets_Service {

    /**
     * 已解析的资源缓存。
     *
     * @var array<string,array<string,string>>
     */
    private $asset_cache = array();

    /**
     * 获取前台装修器依赖的第三方库资源。
     *
     * @param string $asset_key      资源键名。
     * @param string $context_filter 上下文过滤器。
     * @return array<string,string>
     */
    private function get_asset( $asset_key, $context_filter = '' ) {
        $cache_key = $asset_key . '|' . $context_filter;
        if ( isset( $this->asset_cache[ $cache_key ] ) ) {
            return $this->asset_cache[ $cache_key ];
        }

        if ( function_exists( 'developer_starter_get_third_party_asset' ) ) {
            $this->asset_cache[ $cache_key ] = developer_starter_get_third_party_asset( $asset_key, $context_filter );
            return $this->asset_cache[ $cache_key ];
        }

        $fallbacks = array(
            'swiper_css' => array(
                'url'     => DEVELOPER_STARTER_ASSETS . '/css/vendor/swiper-bundle.min.css',
                'version' => '12.0.3',
            ),
            'swiper_js'  => array(
                'url'     => DEVELOPER_STARTER_ASSETS . '/js/vendor/swiper-bundle.min.js',
                'version' => '12.0.3',
            ),
            'chart_js'   => array(
                'url'     => DEVELOPER_STARTER_ASSETS . '/js/vendor/chart.min.js',
                'version' => '2.7.2',
            ),
        );

        $asset = isset( $fallbacks[ $asset_key ] ) ? $fallbacks[ $asset_key ] : array( 'url' => '', 'version' => '' );

        $this->asset_cache[ $cache_key ] = array(
            'url'        => (string) $asset['url'],
            'version'    => (string) $asset['version'],
            'local_url'  => (string) $asset['url'],
            'option_key' => '',
        );

        return $this->asset_cache[ $cache_key ];
    }

    /**
     * 前台装修器外部依赖资源 URL。
     *
     * @return array<string,string>
     */
    public function get_external_asset_urls() {
        $swiper_css = $this->get_asset( 'swiper_css', 'developer_starter_frontend_builder_swiper_css_url' );
        $swiper_js  = $this->get_asset( 'swiper_js', 'developer_starter_frontend_builder_swiper_js_url' );
        $chart_js   = $this->get_asset( 'chart_js', 'developer_starter_frontend_builder_chart_js_url' );

        return array(
            'swiperCss' => (string) $swiper_css['url'],
            'swiperJs'  => (string) $swiper_js['url'],
            'chartJs'   => (string) $chart_js['url'],
        );
    }

    /**
     * 前台装修器外部依赖资源版本。
     *
     * @return array<string,string>
     */
    public function get_external_asset_versions() {
        $swiper_js = $this->get_asset( 'swiper_js', 'developer_starter_frontend_builder_swiper_js_url' );
        $chart_js  = $this->get_asset( 'chart_js', 'developer_starter_frontend_builder_chart_js_url' );

        return array(
            'swiper' => (string) $swiper_js['version'],
            'chart'  => (string) $chart_js['version'],
        );
    }


    /**
     * 前台装修器模块依赖映射（按模块类型决定是否加载外部库）。
     *
     * @return array<string,array<int,string>>
     */
    public function get_module_dependencies() {
        $dependencies = array(
            'swiper' => array(
                'banner',
                'products',
                'product_showcase',
                'hero_search',
                'double_column_carousel',
                'qiling_shop_showcase',
                'tabbed_carousel',
            ),
            'chart'  => array(
                'chart',
            ),
        );

        $dependencies = apply_filters( 'developer_starter_frontend_builder_module_dependencies', $dependencies );
        if ( ! is_array( $dependencies ) ) {
            return array(
                'swiper' => array(),
                'chart'  => array(),
            );
        }

        $normalized = array(
            'swiper' => array(),
            'chart'  => array(),
        );

        foreach ( $normalized as $asset_key => $empty ) {
            if ( empty( $dependencies[ $asset_key ] ) || ! is_array( $dependencies[ $asset_key ] ) ) {
                continue;
            }

            $normalized[ $asset_key ] = array_values(
                array_unique(
                    array_filter(
                        array_map(
                            'sanitize_key',
                            array_map(
                                'strval',
                                $dependencies[ $asset_key ]
                            )
                        )
                    )
                )
            );
        }

        return $normalized;
    }

    /**
     * 根据模块列表判定是否需要提前加载外部资源。
     *
     * @param array<int,mixed>                $modules 模块列表。
     * @param array<string,array<int,string>> $module_dependencies 模块依赖映射。
     * @return array<string,bool>
     */
    public function get_required_external_assets_for_modules( $modules, $module_dependencies ) {
        $required = array(
            'swiper' => false,
            'chart'  => false,
        );

        if ( empty( $modules ) || ! is_array( $modules ) ) {
            return $required;
        }

        $swiper_dependencies = isset( $module_dependencies['swiper'] ) && is_array( $module_dependencies['swiper'] )
            ? $module_dependencies['swiper']
            : array();
        $chart_dependencies = isset( $module_dependencies['chart'] ) && is_array( $module_dependencies['chart'] )
            ? $module_dependencies['chart']
            : array();
        $core_swiper_types = array(
            'banner',
            'products',
            'product_showcase',
            'hero_search',
            'double_column_carousel',
            'qiling_shop_showcase',
            'tabbed_carousel',
        );

        foreach ( $modules as $module_row ) {
            $module_id = '';
            if ( is_array( $module_row ) && isset( $module_row['type'] ) ) {
                $module_id = sanitize_key( (string) $module_row['type'] );
            } elseif ( is_string( $module_row ) ) {
                $module_id = sanitize_key( $module_row );
            }

            if ( '' === $module_id ) {
                continue;
            }

            if ( ! $required['swiper'] ) {
                if ( in_array( $module_id, $core_swiper_types, true ) ) {
                    if ( is_array( $module_row ) ) {
                        $required['swiper'] = $this->module_needs_swiper( $module_row );
                    } else {
                        $required['swiper'] = in_array( $module_id, $swiper_dependencies, true );
                    }
                } else {
                    $required['swiper'] = in_array( $module_id, $swiper_dependencies, true );
                }
            }

            if ( ! $required['chart'] ) {
                $required['chart'] = in_array( $module_id, $chart_dependencies, true );
            }

            if ( $required['swiper'] && $required['chart'] ) {
                break;
            }
        }

        return $required;
    }

    /**
     * 前台装修器：按模块配置精细判断是否需要 Swiper。
     *
     * @param array<string,mixed> $module_row 模块配置。
     * @return bool
     */
    private function module_needs_swiper( $module_row ) {
        if ( ! is_array( $module_row ) || empty( $module_row['type'] ) ) {
            return false;
        }

        $type = sanitize_key( (string) $module_row['type'] );
        $data = ( isset( $module_row['data'] ) && is_array( $module_row['data'] ) ) ? $module_row['data'] : array();

        switch ( $type ) {
            case 'banner':
                $layout = isset( $data['banner_layout'] ) ? (string) $data['banner_layout'] : 'slider';
                $slides = ( isset( $data['banner_slides'] ) && is_array( $data['banner_slides'] ) ) ? $data['banner_slides'] : array();
                return ( $layout !== 'image_text' && count( $slides ) > 1 );

            case 'products':
                $items = ( isset( $data['items'] ) && is_array( $data['items'] ) ) ? $data['items'] : array();
                $columns = isset( $data['columns'] ) ? max( 1, (int) $data['columns'] ) : 4;
                return count( $items ) > $columns;

            case 'hero_search':
                $bg_items = ( isset( $data['hs_bg_items'] ) && is_array( $data['hs_bg_items'] ) ) ? $data['hs_bg_items'] : array();
                return count( $bg_items ) > 1;

            case 'double_column_carousel':
                $slides = ( isset( $data['dcc_slides'] ) && is_array( $data['dcc_slides'] ) ) ? $data['dcc_slides'] : array();
                return count( $slides ) > 1;

            case 'product_showcase':
                $media_items = ( isset( $data['ps_media_items'] ) && is_array( $data['ps_media_items'] ) ) ? $data['ps_media_items'] : array();
                return count( $media_items ) > 1;

            case 'qiling_shop_showcase':
            case 'tabbed_carousel':
                return true;
        }

        return false;
    }
}
