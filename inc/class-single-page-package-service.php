<?php
/**
 * Single Page Package Service
 *
 * 负责单页页面 JSON 的解析与导出。
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Core;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Single_Page_Package_Service {

    const MAX_PACKAGE_BYTES = 1048576;
    const MAX_MODULES_PER_PAGE = 60;
    const MAX_MODULE_DATA_BYTES = 131072;

    /**
     * Builder 数据服务。
     *
     * @var Builder_Data_Service|null
     */
    private $builder_data_service = null;

    /**
     * 默认页面模板。
     *
     * @return string
     */
    public function get_default_page_template() {
        return 'templates/template-fullscreen.php';
    }

    /**
     * 规范化页面模板。
     *
     * @param mixed $template 模板值。
     * @return string
     */
    public function normalize_page_template( $template ) {
        $template = is_scalar( $template ) ? sanitize_text_field( (string) $template ) : '';
        $template = str_replace( '\\', '/', trim( $template ) );

        $aliases = array(
            'fullscreen'  => 'templates/template-fullscreen.php',
            'full-screen' => 'templates/template-fullscreen.php',
            'independent' => 'templates/template-fullscreen.php',
            'standalone'  => 'templates/template-fullscreen.php',
            'fullwidth'   => 'templates/template-fullwidth.php',
            'full-width'  => 'templates/template-fullwidth.php',
            'default'     => 'default',
        );

        if ( isset( $aliases[ $template ] ) ) {
            $template = $aliases[ $template ];
        }

        $allowed_templates = array( 'default' );
        $theme_page_templates = wp_get_theme()->get_page_templates( null, 'page' );
        if ( is_array( $theme_page_templates ) ) {
            $allowed_templates = array_merge( $allowed_templates, array_values( $theme_page_templates ) );
        }

        $allowed_templates = array_values( array_unique( array_filter( array_map( 'strval', $allowed_templates ) ) ) );
        if ( in_array( $template, $allowed_templates, true ) ) {
            return $template;
        }

        return $this->get_default_page_template();
    }

    /**
     * 获取模板显示名称。
     *
     * @param mixed $template 模板值。
     * @return string
     */
    public function get_page_template_label( $template ) {
        $template = $this->normalize_page_template( $template );

        if ( 'default' === $template ) {
            return __( '默认模板', 'developer-starter' );
        }

        $templates = wp_get_theme()->get_page_templates( null, 'page' );
        if ( is_array( $templates ) ) {
            $label = array_search( $template, $templates, true );
            if ( false !== $label ) {
                return (string) $label;
            }
        }

        return $template;
    }

    /**
     * 规范化布尔值。
     *
     * @param mixed $value 原始值。
     * @param bool  $default 默认值。
     * @return bool
     */
    public function normalize_bool( $value, $default = false ) {
        if ( is_bool( $value ) ) {
            return $value;
        }

        if ( is_numeric( $value ) ) {
            return ( (int) $value ) === 1;
        }

        if ( is_string( $value ) ) {
            $value = strtolower( trim( $value ) );
            if ( in_array( $value, array( '1', 'true', 'yes', 'on' ), true ) ) {
                return true;
            }
            if ( in_array( $value, array( '0', 'false', 'no', 'off', '' ), true ) ) {
                return false;
            }
        }

        return (bool) $default;
    }

    /**
     * 解析单页页面包 JSON。
     *
     * @param string $raw_json 原始 JSON。
     * @return array<string,mixed>|\WP_Error
     */
    public function parse_package( $raw_json ) {
        $raw_json = is_string( $raw_json ) ? trim( $raw_json ) : '';
        if ( '' === $raw_json ) {
            return new \WP_Error( 'empty_json', __( 'JSON 内容为空', 'developer-starter' ) );
        }
        if ( strlen( $raw_json ) > self::MAX_PACKAGE_BYTES ) {
            return new \WP_Error( 'page_package_too_large', __( '页面 JSON 过大，请减少模块或复杂内容后再导入', 'developer-starter' ) );
        }

        $decoded = json_decode( $raw_json, true );
        if ( JSON_ERROR_NONE !== json_last_error() || ! is_array( $decoded ) ) {
            return new \WP_Error( 'invalid_json', __( 'JSON 格式错误，请检查文件内容', 'developer-starter' ) );
        }

        $package = array(
            'title'                    => '',
            'page_template'            => $this->get_default_page_template(),
            'hide_page_header'         => false,
            'hide_page_header_defined' => false,
            'transparent_header'       => false,
            'enable_scroll_reveal'     => false,
            'design_preset'            => '',
            'page_design'              => array(),
            'footer'                   => function_exists( 'developer_starter_sanitize_footer_visual_page_settings' )
                ? developer_starter_sanitize_footer_visual_page_settings( array() )
                : array(),
            'region_decoration'        => function_exists( 'developer_starter_get_post_page_region_decoration' )
                ? \Developer_Starter\Core\Page_Region_Decoration::sanitize_settings( array() )
                : array(),
            'visual_style'             => function_exists( 'developer_starter_sanitize_page_visual_style_settings' )
                ? developer_starter_sanitize_page_visual_style_settings( array() )
                : array(),
            'seo'                      => array(),
            'module_schema_version'    => $this->get_builder_data_service()->get_module_data_schema_version(),
            'builder_protocol_version' => $this->get_builder_data_service()->get_builder_protocol_version(),
            'modules'                  => array(),
        );

        if ( $this->is_list_array( $decoded ) ) {
            $modules = $decoded;
        } else {
            $settings = ( isset( $decoded['settings'] ) && is_array( $decoded['settings'] ) ) ? $decoded['settings'] : array();
            $meta     = ( isset( $decoded['meta'] ) && is_array( $decoded['meta'] ) ) ? $decoded['meta'] : array();
            $modules  = array();

            if ( isset( $decoded['modules'] ) && is_array( $decoded['modules'] ) ) {
                $modules = $decoded['modules'];
            } elseif ( isset( $decoded['page_modules'] ) && is_array( $decoded['page_modules'] ) ) {
                $modules = $decoded['page_modules'];
            } elseif ( isset( $decoded['data'] ) && is_array( $decoded['data'] ) && isset( $decoded['data']['modules'] ) && is_array( $decoded['data']['modules'] ) ) {
                $modules = $decoded['data']['modules'];
            }

            foreach ( array( 'title', 'page_title', 'name' ) as $title_key ) {
                if ( ! empty( $decoded[ $title_key ] ) && is_scalar( $decoded[ $title_key ] ) ) {
                    $package['title'] = sanitize_text_field( (string) $decoded[ $title_key ] );
                    break;
                }
            }

            $template_candidate = '';
            foreach ( array(
                isset( $decoded['page_template'] ) ? $decoded['page_template'] : '',
                isset( $decoded['template'] ) ? $decoded['template'] : '',
                isset( $settings['page_template'] ) ? $settings['page_template'] : '',
                isset( $settings['template'] ) ? $settings['template'] : '',
                isset( $meta['_wp_page_template'] ) ? $meta['_wp_page_template'] : '',
            ) as $candidate ) {
                if ( ! is_scalar( $candidate ) || '' === trim( (string) $candidate ) ) {
                    continue;
                }

                $template_candidate = (string) $candidate;
                break;
            }

            if ( '' !== $template_candidate ) {
                $package['page_template'] = $this->normalize_page_template( $template_candidate );
            }

            foreach ( array(
                array( $decoded, 'hide_page_header' ),
                array( $settings, 'hide_page_header' ),
                array( $meta, '_qiling_hide_page_header' ),
            ) as $entry ) {
                if ( isset( $entry[0][ $entry[1] ] ) ) {
                    $package['hide_page_header'] = $this->normalize_bool( $entry[0][ $entry[1] ], false );
                    $package['hide_page_header_defined'] = true;
                    break;
                }
            }

            foreach ( array(
                array( $decoded, 'transparent_header' ),
                array( $settings, 'transparent_header' ),
                array( $meta, '_qiling_transparent_header' ),
            ) as $entry ) {
                if ( isset( $entry[0][ $entry[1] ] ) ) {
                    $package['transparent_header'] = $this->normalize_bool( $entry[0][ $entry[1] ], false );
                    break;
                }
            }

            foreach ( array(
                array( $decoded, 'enable_scroll_reveal' ),
                array( $settings, 'enable_scroll_reveal' ),
                array( $meta, '_developer_starter_enable_scroll_reveal' ),
            ) as $entry ) {
                if ( isset( $entry[0][ $entry[1] ] ) ) {
                    $package['enable_scroll_reveal'] = $this->normalize_bool( $entry[0][ $entry[1] ], false );
                    break;
                }
            }

            if ( function_exists( 'developer_starter_sanitize_footer_visual_page_settings' ) ) {
                $footer_candidate = null;
                foreach ( array(
                    isset( $decoded['footer'] ) && is_array( $decoded['footer'] ) ? $decoded['footer'] : null,
                    isset( $decoded['footer_settings'] ) && is_array( $decoded['footer_settings'] ) ? $decoded['footer_settings'] : null,
                    isset( $settings['footer'] ) && is_array( $settings['footer'] ) ? $settings['footer'] : null,
                    isset( $settings['footer_settings'] ) && is_array( $settings['footer_settings'] ) ? $settings['footer_settings'] : null,
                ) as $candidate ) {
                    if ( is_array( $candidate ) ) {
                        $footer_candidate = $candidate;
                        break;
                    }
                }
                if ( is_array( $footer_candidate ) ) {
                    $package['footer'] = developer_starter_sanitize_footer_visual_page_settings( $footer_candidate );
                }
            }

            if ( function_exists( 'developer_starter_sanitize_page_visual_style_settings' ) ) {
                $visual_style_meta_key  = function_exists( 'developer_starter_get_page_visual_style_meta_key' )
                    ? developer_starter_get_page_visual_style_meta_key()
                    : '_qiling_page_visual_style';
                $visual_style_candidate = null;
                foreach ( array(
                    isset( $decoded['visual_style'] ) && is_array( $decoded['visual_style'] ) ? $decoded['visual_style'] : null,
                    isset( $decoded['visualStyle'] ) && is_array( $decoded['visualStyle'] ) ? $decoded['visualStyle'] : null,
                    isset( $settings['visual_style'] ) && is_array( $settings['visual_style'] ) ? $settings['visual_style'] : null,
                    isset( $settings['visualStyle'] ) && is_array( $settings['visualStyle'] ) ? $settings['visualStyle'] : null,
                    isset( $meta[ $visual_style_meta_key ] ) && is_array( $meta[ $visual_style_meta_key ] )
                        ? $meta[ $visual_style_meta_key ]
                        : null,
                ) as $candidate ) {
                    if ( is_array( $candidate ) ) {
                        $visual_style_candidate = $candidate;
                        break;
                    }
                }

                if ( ! is_array( $visual_style_candidate ) ) {
                    foreach ( array(
                        isset( $decoded['visual_skin'] ) ? $decoded['visual_skin'] : '',
                        isset( $decoded['visualSkin'] ) ? $decoded['visualSkin'] : '',
                        isset( $settings['visual_skin'] ) ? $settings['visual_skin'] : '',
                        isset( $settings['visualSkin'] ) ? $settings['visualSkin'] : '',
                    ) as $visual_skin_candidate ) {
                        if ( is_scalar( $visual_skin_candidate ) && '' !== trim( (string) $visual_skin_candidate ) ) {
                            $visual_style_candidate = array(
                                'mode'   => 'custom',
                                'preset' => sanitize_key( (string) $visual_skin_candidate ),
                            );
                            break;
                        }
                    }
                }

                if ( is_array( $visual_style_candidate ) ) {
                    $package['visual_style'] = developer_starter_sanitize_page_visual_style_settings( $visual_style_candidate );
                }
            }

            if ( class_exists( '\Developer_Starter\Core\Page_Region_Decoration' ) ) {
                $region_candidate = null;
                foreach ( array(
                    isset( $decoded['region_decoration'] ) && is_array( $decoded['region_decoration'] ) ? $decoded['region_decoration'] : null,
                    isset( $settings['region_decoration'] ) && is_array( $settings['region_decoration'] ) ? $settings['region_decoration'] : null,
                ) as $candidate ) {
                    if ( is_array( $candidate ) ) {
                        $region_candidate = $candidate;
                        break;
                    }
                }
                if ( is_array( $region_candidate ) ) {
                    $package['region_decoration'] = Page_Region_Decoration::sanitize_settings( $region_candidate );
                }
            }

            if ( class_exists( '\Developer_Starter\Core\Design_Tokens' ) ) {
                foreach ( array(
                    isset( $decoded['design_preset'] ) ? $decoded['design_preset'] : '',
                    isset( $decoded['designPreset'] ) ? $decoded['designPreset'] : '',
                    isset( $settings['design_preset'] ) ? $settings['design_preset'] : '',
                    isset( $settings['designPreset'] ) ? $settings['designPreset'] : '',
                    isset( $meta[ Design_Tokens::get_page_design_preset_meta_key() ] ) ? $meta[ Design_Tokens::get_page_design_preset_meta_key() ] : '',
                ) as $design_preset_candidate ) {
                    if ( ! is_scalar( $design_preset_candidate ) || '' === trim( (string) $design_preset_candidate ) ) {
                        continue;
                    }

                    $design_preset = Design_Tokens::sanitize_context_preset_key( $design_preset_candidate );
                    if ( '' === $design_preset ) {
                        continue;
                    }

                    $package['design_preset'] = $design_preset;
                    break;
                }

                foreach ( array(
                    isset( $decoded['page_design'] ) && is_array( $decoded['page_design'] ) ? $decoded['page_design'] : null,
                    isset( $settings['page_design'] ) && is_array( $settings['page_design'] ) ? $settings['page_design'] : null,
                    isset( $settings['design'] ) && is_array( $settings['design'] ) ? $settings['design'] : null,
                    isset( $meta[ Design_Tokens::get_page_design_meta_key() ] ) && is_array( $meta[ Design_Tokens::get_page_design_meta_key() ] ) ? $meta[ Design_Tokens::get_page_design_meta_key() ] : null,
                ) as $page_design_candidate ) {
                    if ( ! is_array( $page_design_candidate ) ) {
                        continue;
                    }

                    $package['page_design'] = Design_Tokens::sanitize_page_design_overrides( $page_design_candidate, 'package' );
                    break;
                }
            }

            if ( isset( $decoded['seo'] ) && is_array( $decoded['seo'] ) ) {
                $package['seo'] = $this->sanitize_seo_payload( $decoded['seo'] );
            } elseif ( isset( $settings['seo'] ) && is_array( $settings['seo'] ) ) {
                $package['seo'] = $this->sanitize_seo_payload( $settings['seo'] );
            }

            if ( isset( $decoded['module_schema_version'] ) && is_scalar( $decoded['module_schema_version'] ) ) {
                $package['module_schema_version'] = sanitize_text_field( (string) $decoded['module_schema_version'] );
            } elseif ( isset( $meta['module_schema_version'] ) && is_scalar( $meta['module_schema_version'] ) ) {
                $package['module_schema_version'] = sanitize_text_field( (string) $meta['module_schema_version'] );
            }

            if ( isset( $decoded['builder_protocol_version'] ) && is_scalar( $decoded['builder_protocol_version'] ) ) {
                $package['builder_protocol_version'] = sanitize_text_field( (string) $decoded['builder_protocol_version'] );
            } elseif ( isset( $meta['builder_protocol_version'] ) && is_scalar( $meta['builder_protocol_version'] ) ) {
                $package['builder_protocol_version'] = sanitize_text_field( (string) $meta['builder_protocol_version'] );
            }

        }

        if ( function_exists( 'developer_starter_normalize_modules_meta_types' ) ) {
            $modules = developer_starter_normalize_modules_meta_types( $modules );
        }

        if ( ! is_array( $modules ) || empty( $modules ) ) {
            return new \WP_Error( 'empty_modules', __( 'JSON 中没有可导入的模块数据', 'developer-starter' ) );
        }

        $normalized_modules = array();
        foreach ( $modules as $module ) {
            if ( ! is_array( $module ) || empty( $module['type'] ) ) {
                continue;
            }

            $type = trim( (string) $module['type'] );
            if ( '' === $type ) {
                continue;
            }

            if ( isset( $module['data'] ) && is_array( $module['data'] ) ) {
                $data = $module['data'];
            } elseif ( isset( $module['settings'] ) && is_array( $module['settings'] ) ) {
                $data = $module['settings'];
            } else {
                $data = $module;
                unset( $data['type'] );
            }

            $normalized_modules[] = array(
                'type' => $type,
                'data' => is_array( $data ) ? $data : array(),
            );
        }

        if ( empty( $normalized_modules ) ) {
            return new \WP_Error( 'invalid_modules', __( 'JSON 里的模块结构无效，至少需要一个包含 type 的模块项', 'developer-starter' ) );
        }
        if ( count( $normalized_modules ) > self::MAX_MODULES_PER_PAGE ) {
            return new \WP_Error(
                'page_package_too_many_modules',
                sprintf(
                    /* translators: %d: max single page module count */
                    __( '页面 JSON 模块数量超过上限（最多 %d 个）', 'developer-starter' ),
                    self::MAX_MODULES_PER_PAGE
                )
            );
        }

        foreach ( $normalized_modules as $module ) {
            $module_data = isset( $module['data'] ) && is_array( $module['data'] ) ? $module['data'] : array();
            $module_json = wp_json_encode( $module_data );
            if ( is_string( $module_json ) && strlen( $module_json ) > self::MAX_MODULE_DATA_BYTES ) {
                return new \WP_Error( 'page_package_module_too_large', __( '页面 JSON 中存在数据过大的模块，请减少该模块中的列表项、图片或长文本后再导入', 'developer-starter' ) );
            }
        }

        $package['modules'] = $this->get_builder_data_service()->normalize_modules_for_storage(
            $normalized_modules,
            array(
                'sanitize_data' => false,
            )
        );

        return $package;
    }

    /**
     * 构建单页页面包导出结构。
     *
     * @param int $post_id 页面 ID。
     * @return array<string,mixed>|\WP_Error
     */
    public function build_export_payload( $post_id ) {
        $post = get_post( $post_id );
        if ( ! $post || 'page' !== $post->post_type ) {
            return new \WP_Error( 'invalid_post', __( '仅支持导出页面类型', 'developer-starter' ) );
        }

        $template = function_exists( 'get_page_template_slug' ) ? get_page_template_slug( $post_id ) : '';
        if ( ! is_string( $template ) || '' === trim( $template ) ) {
            $template = (string) get_post_meta( $post_id, '_wp_page_template', true );
        }

        if ( function_exists( 'developer_starter_maybe_fill_default_modules_for_page_template' ) && '' !== $template && 'default' !== $template ) {
            developer_starter_maybe_fill_default_modules_for_page_template( $post_id, $template );
        }

        $modules = function_exists( 'developer_starter_get_raw_page_modules_meta' )
            ? developer_starter_get_raw_page_modules_meta( $post_id )
            : get_post_meta( $post_id, '_developer_starter_modules', true );

        if ( ! is_array( $modules ) || empty( $modules ) ) {
            return new \WP_Error( 'empty_modules', __( '当前页面没有可导出的模块数据', 'developer-starter' ) );
        }

        if ( function_exists( 'developer_starter_normalize_modules_meta_types' ) ) {
            $modules = developer_starter_normalize_modules_meta_types( $modules );
        }

        $normalized_modules = $this->get_builder_data_service()->normalize_modules_for_storage(
            $modules,
            array(
                'sanitize_data' => false,
            )
        );

        return array(
            'package_type'         => 'developer_starter_page_package',
            'version'              => 1,
            'module_schema_version' => $this->get_builder_data_service()->get_module_data_schema_version(),
            'builder_protocol_version' => $this->get_builder_data_service()->get_builder_protocol_version(),
            'title'                => get_the_title( $post_id ),
            'page_template'        => $this->get_default_page_template(),
            'hide_page_header'     => (bool) $this->normalize_bool( get_post_meta( $post_id, '_qiling_hide_page_header', true ), false ),
            'transparent_header'   => (bool) $this->normalize_bool( get_post_meta( $post_id, '_qiling_transparent_header', true ), false ),
            'enable_scroll_reveal' => (bool) $this->normalize_bool( get_post_meta( $post_id, '_developer_starter_enable_scroll_reveal', true ), false ),
            'design_preset'        => class_exists( '\Developer_Starter\Core\Design_Tokens' )
                ? Design_Tokens::get_page_design_preset( $post_id )
                : '',
            'page_design'          => class_exists( '\Developer_Starter\Core\Design_Tokens' )
                ? Design_Tokens::get_page_design_overrides( $post_id, 'package' )
                : array(),
            'footer'               => function_exists( 'developer_starter_get_post_footer_visual_settings' )
                ? developer_starter_get_post_footer_visual_settings( $post_id )
                : array(),
            'region_decoration'    => $this->build_region_decoration_export_payload( $post_id ),
            'visual_style'         => function_exists( 'developer_starter_get_post_page_visual_style' )
                ? developer_starter_get_post_page_visual_style( $post_id )
                : array(),
            'seo'                  => $this->get_post_seo_payload( $post_id ),
            'source_page_template' => ( is_string( $template ) && '' !== $template ) ? $template : 'default',
            'modules'              => $normalized_modules,
        );
    }

    /**
     * 单页包不携带装修源页面本体，因此只导出安全页面键，不导出可能在目标站误命中的数字 ID。
     *
     * @param int $post_id 页面 ID。
     * @return array<string,mixed>
     */
    private function build_region_decoration_export_payload( $post_id ) {
        if ( ! function_exists( 'developer_starter_get_post_page_region_decoration' ) ) {
            return array();
        }

        $settings = developer_starter_get_post_page_region_decoration( $post_id );
        foreach ( $settings as $region => $region_settings ) {
            if ( 'custom' !== $region_settings['mode'] || empty( $region_settings['page_id'] ) ) {
                continue;
            }
            $source_id = absint( $region_settings['page_id'] );
            $source = get_post( $source_id );
            $source_key = sanitize_key( (string) get_post_meta( $source_id, '_qiling_site_package_page_key', true ) );
            if ( '' === $source_key && $source instanceof \WP_Post ) {
                $source_key = sanitize_key( str_replace( '-', '_', $source->post_name ) );
            }
            $settings[ $region ]['page_id'] = 0;
            $settings[ $region ]['page_key'] = $source_key;
        }

        return $settings;
    }

    /**
     * 获取 Builder 数据服务。
     *
     * @return Builder_Data_Service
     */
    private function get_builder_data_service() {
        if ( ! $this->builder_data_service instanceof Builder_Data_Service ) {
            $this->builder_data_service = new Builder_Data_Service();
        }

        return $this->builder_data_service;
    }

    /**
     * 当前页面 SEO 导出结构。
     *
     * @param int $post_id 页面 ID。
     * @return array<string,string>
     */
    private function get_post_seo_payload( $post_id ) {
        return $this->sanitize_seo_payload(
            array(
                'title'          => get_post_meta( $post_id, '_developer_starter_seo_title', true ),
                'description'    => get_post_meta( $post_id, '_developer_starter_seo_description', true ),
                'keywords'       => get_post_meta( $post_id, '_developer_starter_seo_keywords', true ),
                'og_title'       => get_post_meta( $post_id, '_developer_starter_og_title', true ),
                'og_description' => get_post_meta( $post_id, '_developer_starter_og_description', true ),
            )
        );
    }

    /**
     * 清洗页面包里的 SEO 数据。
     *
     * @param mixed $seo SEO 输入。
     * @return array<string,string>
     */
    private function sanitize_seo_payload( $seo ) {
        $seo = is_array( $seo ) ? $seo : array();
        $keywords = '';
        if ( isset( $seo['keywords'] ) && is_array( $seo['keywords'] ) ) {
            $keywords = implode( ',', array_filter( array_map( 'sanitize_text_field', array_map( 'strval', $seo['keywords'] ) ) ) );
        } elseif ( isset( $seo['keywords'] ) && is_scalar( $seo['keywords'] ) ) {
            $keywords = sanitize_text_field( (string) $seo['keywords'] );
        } elseif ( isset( $seo['focus_keywords'] ) && is_array( $seo['focus_keywords'] ) ) {
            $keywords = implode( ',', array_filter( array_map( 'sanitize_text_field', array_map( 'strval', $seo['focus_keywords'] ) ) ) );
        } elseif ( isset( $seo['focus_keywords'] ) && is_scalar( $seo['focus_keywords'] ) ) {
            $keywords = sanitize_text_field( (string) $seo['focus_keywords'] );
        }

        $keywords = str_replace( '，', ',', $keywords );
        $keywords = preg_replace( '/\s*,\s*/', ',', (string) $keywords );
        $keywords = preg_replace( '/,+/', ',', (string) $keywords );
        $keywords = trim( (string) $keywords, " \t\n\r\0\x0B," );

        return array(
            'title'          => isset( $seo['title'] ) && is_scalar( $seo['title'] ) ? sanitize_text_field( (string) $seo['title'] ) : '',
            'description'    => isset( $seo['description'] ) && is_scalar( $seo['description'] ) ? sanitize_textarea_field( (string) $seo['description'] ) : '',
            'keywords'       => $keywords,
            'og_title'       => isset( $seo['og_title'] ) && is_scalar( $seo['og_title'] ) ? sanitize_text_field( (string) $seo['og_title'] ) : '',
            'og_description' => isset( $seo['og_description'] ) && is_scalar( $seo['og_description'] ) ? sanitize_textarea_field( (string) $seo['og_description'] ) : '',
        );
    }

    /**
     * 是否为列表数组。
     *
     * @param mixed $value 值。
     * @return bool
     */
    private function is_list_array( $value ) {
        if ( ! is_array( $value ) ) {
            return false;
        }

        return array_keys( $value ) === range( 0, count( $value ) - 1 );
    }
}
