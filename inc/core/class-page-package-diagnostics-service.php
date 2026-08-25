<?php
/**
 * 页面数据包诊断服务
 *
 * 负责多页包元信息规范化，以及依赖/样式/安全预警。
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Core;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Page_Package_Diagnostics_Service {

    /**
     * 规范化包作用域。
     *
     * @param mixed $scope 原始作用域。
     * @return string page|site
     */
    public function normalize_package_scope( $scope ) {
        $scope = sanitize_key( is_scalar( $scope ) ? (string) $scope : '' );
        if ( in_array( $scope, array( 'site', 'whole_site', 'website' ), true ) ) {
            return 'site';
        }

        return 'page';
    }

    /**
     * 规范化本地包清单。
     *
     * @param mixed             $manifest 原始清单。
     * @param string            $scope    包作用域。
     * @param array<int,string> $features 功能清单。
     * @return array<string,mixed>
     */
    public function normalize_package_manifest( $manifest, $scope = 'page', $features = array() ) {
        $manifest = is_array( $manifest ) ? $manifest : array();
        $scope    = $this->normalize_package_scope( $scope );

        return array(
            'schema'       => isset( $manifest['schema'] ) && is_scalar( $manifest['schema'] ) ? sanitize_key( (string) $manifest['schema'] ) : 'qiling-site-package',
            'kind'         => 'site' === $scope ? 'site_package' : 'page_package',
            'scope'        => $scope,
            'generated_at' => isset( $manifest['generated_at'] ) && is_scalar( $manifest['generated_at'] ) ? sanitize_text_field( (string) $manifest['generated_at'] ) : '',
            'features'     => $this->normalize_string_list( ! empty( $features ) ? $features : ( isset( $manifest['features'] ) ? $manifest['features'] : array() ) ),
            'local_only'   => true,
        );
    }

    /**
     * 规范化全局样式片段。
     *
     * @param mixed $design_system 原始全局样式。
     * @return array<string,mixed>
     */
    public function normalize_design_system_payload( $design_system ) {
        if ( ! is_array( $design_system ) ) {
            return array();
        }
        if ( empty( $design_system ) ) {
            return array();
        }

        $storage_key = class_exists( '\Developer_Starter\Core\Design_Tokens' )
            ? Design_Tokens::STORAGE_OPTION_KEY
            : 'design_system_v2';
        $options = isset( $design_system['options'] ) && is_array( $design_system['options'] )
            ? $design_system['options']
            : array();
        $design_system_v2 = array();

        foreach ( array( $storage_key, 'design_system_v2', 'designSystemV2' ) as $storage_candidate_key ) {
            if ( isset( $design_system[ $storage_candidate_key ] ) && is_array( $design_system[ $storage_candidate_key ] ) ) {
                $design_system_v2 = $this->normalize_design_system_storage_payload( $design_system[ $storage_candidate_key ] );
                break;
            }
        }

        if ( isset( $design_system['enabled'] ) ) {
            $options['design_enable_global_tokens'] = $this->normalize_bool_value( $design_system['enabled'], true ) ? '1' : '';
        }
        if ( isset( $design_system['preset'] ) && is_scalar( $design_system['preset'] ) ) {
            $options['design_preset'] = sanitize_key( (string) $design_system['preset'] );
        }
        if ( empty( $design_system_v2 ) ) {
            $design_system_v2 = $this->normalize_design_system_storage_payload(
                array(
                    'schema_version'   => isset( $design_system['schema_version'] ) ? $design_system['schema_version'] : ( isset( $design_system['schemaVersion'] ) ? $design_system['schemaVersion'] : '' ),
                    'enabled'          => isset( $design_system['enabled'] ) ? $design_system['enabled'] : true,
                    'preset'           => isset( $design_system['preset'] ) ? $design_system['preset'] : '',
                    'tokens'           => isset( $design_system['tokens'] ) && is_array( $design_system['tokens'] ) ? $design_system['tokens'] : array(),
                    'typography_system' => isset( $design_system['typography_system'] ) && is_array( $design_system['typography_system'] )
                        ? $design_system['typography_system']
                        : ( isset( $design_system['typographySystem'] ) && is_array( $design_system['typographySystem'] ) ? $design_system['typographySystem'] : array() ),
                    'layout_system'    => isset( $design_system['layout_system'] ) && is_array( $design_system['layout_system'] )
                        ? $design_system['layout_system']
                        : ( isset( $design_system['layoutSystem'] ) && is_array( $design_system['layoutSystem'] ) ? $design_system['layoutSystem'] : array() ),
                    'component_styles' => isset( $design_system['component_styles'] ) && is_array( $design_system['component_styles'] )
                        ? $design_system['component_styles']
                        : ( isset( $design_system['componentStyles'] ) && is_array( $design_system['componentStyles'] ) ? $design_system['componentStyles'] : array() ),
                    'custom_presets'   => isset( $design_system['custom_presets'] ) && is_array( $design_system['custom_presets'] )
                        ? $design_system['custom_presets']
                        : ( isset( $design_system['customPresets'] ) && is_array( $design_system['customPresets'] ) ? $design_system['customPresets'] : array() ),
                )
            );
        }

        $token_map = array(
            'primary'         => 'design_primary_color',
            'secondary'       => 'design_secondary_color',
            'accent'          => 'design_accent_color',
            'text'            => 'design_text_color',
            'text_muted'      => 'design_text_muted_color',
            'heading'         => 'design_heading_color',
            'background'      => 'design_background_color',
            'surface'         => 'design_surface_color',
            'surface_alt'     => 'design_surface_alt_color',
            'border'          => 'design_border_color',
            'font_family'     => 'design_font_family',
            'font_size_base'  => 'design_font_size_base',
            'line_height_base' => 'design_line_height_base',
            'container_width' => 'design_container_width',
            'section_padding' => 'design_section_padding',
            'card_radius'     => 'design_card_radius',
            'button_radius'   => 'design_button_radius',
            'input_radius'    => 'design_input_radius',
            'animation_speed' => 'design_animation_speed',
            'shadow_sm'       => 'design_shadow_sm',
            'shadow_md'       => 'design_shadow_md',
            'shadow_lg'       => 'design_shadow_lg',
            'dark_bg'         => 'design_dark_bg',
            'dark_surface'    => 'design_dark_surface',
            'dark_text'       => 'design_dark_text',
            'dark_text_muted' => 'design_dark_text_muted',
            'dark_border'     => 'design_dark_border',
        );

        $token_sources = array();
        if ( isset( $design_system_v2['tokens'] ) && is_array( $design_system_v2['tokens'] ) ) {
            $token_sources[] = $design_system_v2['tokens'];
        }
        if ( isset( $design_system['tokens'] ) && is_array( $design_system['tokens'] ) ) {
            $token_sources[] = $design_system['tokens'];
        }
        foreach ( $token_sources as $token_source ) {
            foreach ( $token_map as $token_key => $option_key ) {
                if ( isset( $token_source[ $token_key ] ) && is_scalar( $token_source[ $token_key ] ) && ! isset( $options[ $option_key ] ) ) {
                    $options[ $option_key ] = (string) $token_source[ $token_key ];
                }
            }
        }

        if ( isset( $design_system_v2['enabled'] ) && ! isset( $options['design_enable_global_tokens'] ) ) {
            $options['design_enable_global_tokens'] = ! empty( $design_system_v2['enabled'] ) ? '1' : '';
        }
        if ( isset( $design_system_v2['preset'] ) && is_scalar( $design_system_v2['preset'] ) && ! isset( $options['design_preset'] ) ) {
            $options['design_preset'] = sanitize_key( (string) $design_system_v2['preset'] );
        }
        if ( ! empty( $design_system_v2['typography_system'] ) && is_array( $design_system_v2['typography_system'] ) && ! isset( $options['design_typography_system'] ) ) {
            $options['design_typography_system'] = $design_system_v2['typography_system'];
        }
        if ( ! empty( $design_system_v2['layout_system'] ) && is_array( $design_system_v2['layout_system'] ) && ! isset( $options['design_layout_system'] ) ) {
            $options['design_layout_system'] = $design_system_v2['layout_system'];
        }
        if ( ! empty( $design_system_v2['custom_presets'] ) && is_array( $design_system_v2['custom_presets'] ) && ! isset( $options['design_custom_presets'] ) ) {
            $options['design_custom_presets'] = $design_system_v2['custom_presets'];
        }

        $schema_version = '';
        if ( isset( $design_system_v2['schema_version'] ) && is_scalar( $design_system_v2['schema_version'] ) ) {
            $schema_version = sanitize_text_field( (string) $design_system_v2['schema_version'] );
        } elseif ( isset( $design_system['schema_version'] ) && is_scalar( $design_system['schema_version'] ) ) {
            $schema_version = sanitize_text_field( (string) $design_system['schema_version'] );
        } elseif ( isset( $design_system['schemaVersion'] ) && is_scalar( $design_system['schemaVersion'] ) ) {
            $schema_version = sanitize_text_field( (string) $design_system['schemaVersion'] );
        }

        return array(
            'storage_key'     => $storage_key,
            'schema_version'  => $schema_version,
            'enabled'         => isset( $options['design_enable_global_tokens'] ) ? ( '1' === (string) $options['design_enable_global_tokens'] ) : ! empty( $design_system_v2['enabled'] ),
            'preset'          => isset( $options['design_preset'] ) ? sanitize_key( (string) $options['design_preset'] ) : ( isset( $design_system_v2['preset'] ) ? sanitize_key( (string) $design_system_v2['preset'] ) : '' ),
            'tokens'          => isset( $design_system_v2['tokens'] ) && is_array( $design_system_v2['tokens'] ) ? $design_system_v2['tokens'] : array(),
            'typography_system' => isset( $design_system_v2['typography_system'] ) && is_array( $design_system_v2['typography_system'] ) ? $design_system_v2['typography_system'] : array(),
            'layout_system'   => isset( $design_system_v2['layout_system'] ) && is_array( $design_system_v2['layout_system'] ) ? $design_system_v2['layout_system'] : array(),
            'component_styles' => isset( $design_system_v2['component_styles'] ) && is_array( $design_system_v2['component_styles'] ) ? $design_system_v2['component_styles'] : array(),
            'custom_presets'  => isset( $design_system_v2['custom_presets'] ) && is_array( $design_system_v2['custom_presets'] ) ? $design_system_v2['custom_presets'] : array(),
            'options'         => $options,
            'design_system_v2' => $design_system_v2,
        );
    }

    /**
     * 规范化内容模型片段。
     *
     * @param mixed $content_models 原始内容模型。
     * @return array<string,mixed>
     */
    public function normalize_content_models_payload( $content_models ) {
        if ( ! is_array( $content_models ) ) {
            return array();
        }
        if ( empty( $content_models ) ) {
            return array();
        }

        $options = isset( $content_models['options'] ) && is_array( $content_models['options'] )
            ? $content_models['options']
            : array();

        if ( isset( $content_models['enabled'] ) ) {
            $options['content_model_center_enable'] = $this->normalize_bool_value( $content_models['enabled'], true ) ? '1' : '';
        }

        if ( empty( $options['content_model_enabled_models'] ) ) {
            if ( isset( $content_models['enabled_model_ids'] ) ) {
                $options['content_model_enabled_models'] = $this->normalize_key_list( $content_models['enabled_model_ids'] );
            } elseif ( isset( $content_models['enabledModelIds'] ) ) {
                $options['content_model_enabled_models'] = $this->normalize_key_list( $content_models['enabledModelIds'] );
            }
        }

        $enabled_model_ids = isset( $options['content_model_enabled_models'] ) ? $this->normalize_key_list( $options['content_model_enabled_models'] ) : array();
        if ( in_array( 'branch', $enabled_model_ids, true ) && ! isset( $options['local_business_features_enable'] ) ) {
            $options['local_business_features_enable'] = '1';
        }
        if ( isset( $options['local_business_features_enable'] ) && '1' === (string) $options['local_business_features_enable'] && ! in_array( 'branch', $enabled_model_ids, true ) ) {
            $enabled_model_ids[] = 'branch';
        }

        if ( isset( $content_models['archive_base'] ) && is_scalar( $content_models['archive_base'] ) && empty( $options['content_model_archive_base'] ) ) {
            $options['content_model_archive_base'] = sanitize_title( (string) $content_models['archive_base'] );
        } elseif ( isset( $content_models['archiveBase'] ) && is_scalar( $content_models['archiveBase'] ) && empty( $options['content_model_archive_base'] ) ) {
            $options['content_model_archive_base'] = sanitize_title( (string) $content_models['archiveBase'] );
        }

        return array(
            'schema_version'    => isset( $content_models['schema_version'] ) && is_scalar( $content_models['schema_version'] )
                ? sanitize_text_field( (string) $content_models['schema_version'] )
                : ( isset( $content_models['schemaVersion'] ) && is_scalar( $content_models['schemaVersion'] ) ? sanitize_text_field( (string) $content_models['schemaVersion'] ) : '' ),
            'enabled_model_ids' => $enabled_model_ids,
            'options'           => $options,
            'models'            => isset( $content_models['models'] ) && is_array( $content_models['models'] ) ? $content_models['models'] : array(),
        );
    }

    /**
     * 规范化素材与模板资源清单。
     *
     * @param mixed $site_assets 原始资源清单。
     * @return array<string,mixed>
     */
    public function normalize_site_assets_payload( $site_assets ) {
        if ( ! is_array( $site_assets ) ) {
            return array();
        }
        if ( empty( $site_assets ) ) {
            return array();
        }

        $template_assets = array();
        $raw_assets      = isset( $site_assets['template_assets'] ) && is_array( $site_assets['template_assets'] ) ? $site_assets['template_assets'] : array();
        foreach ( $raw_assets as $asset ) {
            if ( ! is_array( $asset ) ) {
                continue;
            }

            $path = isset( $asset['path'] ) && is_scalar( $asset['path'] ) ? ltrim( sanitize_text_field( (string) $asset['path'] ), '/' ) : '';
            if ( '' === $path || false !== strpos( $path, '..' ) ) {
                continue;
            }

            $template_assets[] = array(
                'type'     => isset( $asset['type'] ) && is_scalar( $asset['type'] ) ? sanitize_key( (string) $asset['type'] ) : 'file',
                'handle'   => isset( $asset['handle'] ) && is_scalar( $asset['handle'] ) ? sanitize_key( (string) $asset['handle'] ) : '',
                'path'     => $path,
                'required' => $this->normalize_bool_value( isset( $asset['required'] ) ? $asset['required'] : true, true ),
            );
        }

        return array(
            'template_assets' => $template_assets,
            'media'           => isset( $site_assets['media'] ) && is_array( $site_assets['media'] ) ? $site_assets['media'] : array(),
            'notes'           => $this->normalize_string_list( isset( $site_assets['notes'] ) ? $site_assets['notes'] : array() ),
        );
    }

    /**
     * 规范化作者信息。
     *
     * @param mixed $author 原始作者信息。
     * @return array<string,string>
     */
    public function normalize_package_author( $author ) {
        if ( is_array( $author ) ) {
            return array(
                'name' => isset( $author['name'] ) && is_scalar( $author['name'] ) ? sanitize_text_field( (string) $author['name'] ) : '',
                'url'  => isset( $author['url'] ) && is_scalar( $author['url'] ) ? esc_url_raw( (string) $author['url'] ) : '',
            );
        }

        return array(
            'name' => is_scalar( $author ) ? sanitize_text_field( (string) $author ) : '',
            'url'  => '',
        );
    }

    /**
     * 规范化字符串列表。
     *
     * @param mixed $items 原始列表。
     * @return array<int,string>
     */
    public function normalize_string_list( $items ) {
        if ( is_string( $items ) ) {
            $items = preg_split( '/[\r\n,]+/', $items );
        }

        if ( ! is_array( $items ) ) {
            return array();
        }

        $normalized = array();
        foreach ( $items as $item ) {
            if ( ! is_scalar( $item ) ) {
                continue;
            }

            $value = sanitize_text_field( (string) $item );
            if ( '' === $value ) {
                continue;
            }

            $normalized[] = $value;
        }

        return array_values( array_unique( $normalized ) );
    }

    /**
     * 规范化 key 列表。
     *
     * @param mixed $items 原始列表。
     * @return array<int,string>
     */
    private function normalize_key_list( $items ) {
        if ( is_string( $items ) ) {
            $items = preg_split( '/[\r\n,\s]+/', $items );
        }

        if ( ! is_array( $items ) ) {
            return array();
        }

        $normalized = array();
        foreach ( $items as $item ) {
            if ( ! is_scalar( $item ) ) {
                continue;
            }
            $key = sanitize_key( (string) $item );
            if ( '' !== $key ) {
                $normalized[] = $key;
            }
        }

        return array_values( array_unique( $normalized ) );
    }

    /**
     * 规范化 design_system_v2 存储结构。
     *
     * @param mixed $payload 原始存储数据。
     * @return array<string,mixed>
     */
    private function normalize_design_system_storage_payload( $payload ) {
        if ( ! is_array( $payload ) ) {
            return array();
        }

        $normalized = array();
        $schema_version = '';
        if ( isset( $payload['schema_version'] ) && is_scalar( $payload['schema_version'] ) ) {
            $schema_version = sanitize_text_field( (string) $payload['schema_version'] );
        } elseif ( isset( $payload['schemaVersion'] ) && is_scalar( $payload['schemaVersion'] ) ) {
            $schema_version = sanitize_text_field( (string) $payload['schemaVersion'] );
        }
        if ( '' !== $schema_version ) {
            $normalized['schema_version'] = $schema_version;
        }

        if ( array_key_exists( 'enabled', $payload ) ) {
            $normalized['enabled'] = $this->normalize_bool_value( $payload['enabled'], true );
        }

        if ( isset( $payload['preset'] ) && is_scalar( $payload['preset'] ) ) {
            $normalized['preset'] = sanitize_key( (string) $payload['preset'] );
        }

        foreach (
            array(
                'tokens' => array( 'tokens' ),
                'typography_system' => array( 'typography_system', 'typographySystem' ),
                'layout_system' => array( 'layout_system', 'layoutSystem' ),
                'component_styles' => array( 'component_styles', 'componentStyles' ),
                'custom_presets' => array( 'custom_presets', 'customPresets' ),
                'compat' => array( 'compat' ),
            ) as $normalized_key => $candidate_keys
        ) {
            foreach ( $candidate_keys as $candidate_key ) {
                if ( isset( $payload[ $candidate_key ] ) && is_array( $payload[ $candidate_key ] ) ) {
                    $normalized[ $normalized_key ] = $this->sanitize_recursive_payload( $payload[ $candidate_key ] );
                    break;
                }
            }
        }

        return $normalized;
    }

    /**
     * 递归清洗数组结构。
     *
     * @param mixed $value 原始值。
     * @return mixed
     */
    private function sanitize_recursive_payload( $value ) {
        if ( ! is_array( $value ) ) {
            return is_scalar( $value ) ? wp_kses_post( (string) $value ) : '';
        }

        $sanitized = array();
        foreach ( $value as $key => $item ) {
            $sanitized_key = is_string( $key ) ? sanitize_key( $key ) : $key;
            $sanitized[ $sanitized_key ] = $this->sanitize_recursive_payload( $item );
        }

        return $sanitized;
    }

    /**
     * 规范化依赖声明。
     *
     * @param mixed $dependencies 原始依赖。
     * @return array<string,mixed>
     */
    public function normalize_package_dependencies( $dependencies ) {
        $normalized = array(
            'plugins' => array(),
            'notes'   => array(),
        );

        if ( is_string( $dependencies ) ) {
            $normalized['notes'] = $this->normalize_string_list( $dependencies );
            return $normalized;
        }

        if ( ! is_array( $dependencies ) ) {
            return $normalized;
        }

        if ( isset( $dependencies['plugins'] ) ) {
            $plugins = is_array( $dependencies['plugins'] ) ? $dependencies['plugins'] : array( $dependencies['plugins'] );
            foreach ( $plugins as $plugin ) {
                if ( is_array( $plugin ) ) {
                    $slug = '';
                    foreach ( array( 'slug', 'file', 'id' ) as $slug_key ) {
                        if ( ! empty( $plugin[ $slug_key ] ) && is_scalar( $plugin[ $slug_key ] ) ) {
                            $slug = sanitize_text_field( (string) $plugin[ $slug_key ] );
                            break;
                        }
                    }

                    if ( '' === $slug ) {
                        continue;
                    }

                    $normalized['plugins'][] = array(
                        'slug'     => $slug,
                        'label'    => isset( $plugin['label'] ) && is_scalar( $plugin['label'] )
                            ? sanitize_text_field( (string) $plugin['label'] )
                            : ( isset( $plugin['name'] ) && is_scalar( $plugin['name'] ) ? sanitize_text_field( (string) $plugin['name'] ) : '' ),
                        'required' => $this->normalize_bool_value(
                            isset( $plugin['required'] ) ? $plugin['required'] : true,
                            true
                        ),
                    );
                    continue;
                }

                if ( ! is_scalar( $plugin ) ) {
                    continue;
                }

                $slug = sanitize_text_field( (string) $plugin );
                if ( '' === $slug ) {
                    continue;
                }

                $normalized['plugins'][] = array(
                    'slug'     => $slug,
                    'label'    => '',
                    'required' => true,
                );
            }
        }

        if ( isset( $dependencies['notes'] ) ) {
            $normalized['notes'] = $this->normalize_string_list( $dependencies['notes'] );
        }

        return $normalized;
    }

    /**
     * 收集依赖插件兼容性提示。
     *
     * @param array<string,mixed> $dependencies 依赖声明。
     * @return array<int,string>
     */
    public function collect_package_dependency_warnings( $dependencies ) {
        $warnings = array();
        if ( empty( $dependencies['plugins'] ) || ! is_array( $dependencies['plugins'] ) ) {
            return $warnings;
        }

        if ( ! function_exists( 'get_plugins' ) && defined( 'ABSPATH' ) ) {
            $plugin_file = ABSPATH . 'wp-admin/includes/plugin.php';
            if ( file_exists( $plugin_file ) ) {
                require_once $plugin_file;
            }
        }

        $installed_plugins = function_exists( 'get_plugins' ) ? get_plugins() : array();
        foreach ( $dependencies['plugins'] as $plugin_dependency ) {
            if ( ! is_array( $plugin_dependency ) || empty( $plugin_dependency['slug'] ) ) {
                continue;
            }

            $is_required  = ! array_key_exists( 'required', $plugin_dependency ) || $this->normalize_bool_value( $plugin_dependency['required'], true );
            $plugin_match = $this->find_plugin_dependency_match(
                (string) $plugin_dependency['slug'],
                $installed_plugins
            );
            $plugin_label = ! empty( $plugin_dependency['label'] )
                ? sanitize_text_field( (string) $plugin_dependency['label'] )
                : sanitize_text_field( (string) $plugin_dependency['slug'] );

            if ( empty( $plugin_match['file'] ) ) {
                if ( ! $is_required ) {
                    continue;
                }

                $warnings[] = sprintf(
                    /* translators: %s: plugin name */
                    __( '数据包声明依赖插件 %s，但当前站点未检测到该插件。', 'developer-starter' ),
                    $plugin_label
                );
                continue;
            }

            if ( $is_required && function_exists( 'is_plugin_active' ) && ! is_plugin_active( $plugin_match['file'] ) ) {
                $warnings[] = sprintf(
                    /* translators: %s: plugin name */
                    __( '数据包依赖插件 %s 已安装但未启用，导入后可能出现样式或功能缺失。', 'developer-starter' ),
                    $plugin_label
                );
            }
        }

        return $warnings;
    }

    /**
     * 收集页面包/整站包清单级提示。
     *
     * @param array<string,mixed> $package      规范化后的包数据。
     * @param array<string,mixed> $site_options 规范化后的站点设置。
     * @return array<int,string>
     */
    public function collect_package_manifest_warnings( $package, $site_options = array() ) {
        $warnings = array();
        $scope    = isset( $package['scope'] ) ? $this->normalize_package_scope( $package['scope'] ) : 'page';

        if ( 'site' === $scope ) {
            $has_site_payload = ! empty( $site_options )
                || ! empty( $package['design_system'] )
                || ! empty( $package['content_models'] )
                || ! empty( $package['site_assets'] );

            if ( ! $has_site_payload ) {
                $warnings[] = __( '该数据包声明为整站包，但未包含全局样式、内容模型、导航或站点设置。', 'developer-starter' );
            }
        } elseif ( ! empty( $site_options ) ) {
            $warnings[] = __( '该页面包包含站点级设置；默认不会应用，只有导入时勾选“应用整站设置”才会写入当前站点。', 'developer-starter' );
        }

        if ( ! empty( $package['site_assets']['template_assets'] ) && is_array( $package['site_assets']['template_assets'] ) ) {
            foreach ( $package['site_assets']['template_assets'] as $asset ) {
                if ( ! is_array( $asset ) || empty( $asset['path'] ) || empty( $asset['required'] ) ) {
                    continue;
                }

                $asset_path = ltrim( sanitize_text_field( (string) $asset['path'] ), '/' );
                if ( '' !== $asset_path && defined( 'DEVELOPER_STARTER_DIR' ) && ! file_exists( trailingslashit( DEVELOPER_STARTER_DIR ) . $asset_path ) ) {
                    $warnings[] = sprintf(
                        /* translators: %s: asset path */
                        __( '整站包声明依赖主题资源 %s，但当前主题目录未检测到该文件。', 'developer-starter' ),
                        $asset_path
                    );
                }
            }
        }

        if ( ! empty( $site_options['navigation']['menus'] ) && is_array( $site_options['navigation']['menus'] ) ) {
            $registered_locations = function_exists( 'get_registered_nav_menus' ) ? get_registered_nav_menus() : array();
            foreach ( $site_options['navigation']['menus'] as $menu ) {
                if ( ! is_array( $menu ) || empty( $menu['location'] ) ) {
                    continue;
                }

                $location = sanitize_key( (string) $menu['location'] );
                if ( '' !== $location && is_array( $registered_locations ) && ! isset( $registered_locations[ $location ] ) ) {
                    $warnings[] = sprintf(
                        /* translators: %s: menu location */
                        __( '整站包声明菜单位置 %s，但当前主题未注册该位置，导入时只会创建菜单，不会自动挂载到该位置。', 'developer-starter' ),
                        $location
                    );
                }
            }
        }

        return array_values( array_unique( array_filter( array_map( 'strval', $warnings ) ) ) );
    }

    /**
     * 收集与页面样式兼容性直接相关的预警。
     *
     * @param array<string,mixed>            $package        原始包数据。
     * @param array<int,array<string,mixed>> $prepared_pages 通过预检的页面。
     * @return array<int,string>
     */
    public function collect_package_style_warnings( $package, $prepared_pages ) {
        $warnings = array();

        if ( empty( $package['theme'] ) ) {
            $warnings[] = __( '数据包未声明 theme 标识，跨主题或分发场景下的样式兼容性较难判断。', 'developer-starter' );
        }

        if ( empty( $package['min_theme_version'] ) ) {
            $warnings[] = __( '数据包未声明最低主题版本，跨版本导入时可能出现字段默认值变化或样式细节偏差。', 'developer-starter' );
        }

        $missing_split_css = $this->collect_missing_split_css_modules_from_pages( $prepared_pages );
        if ( ! empty( $missing_split_css ) && 'split' === $this->get_module_css_load_mode() ) {
            $warnings[] = sprintf(
                /* translators: %s: module types */
                __( '当前站点启用了拆包模块样式，但这些模块缺少独立 CSS：%s。前台会自动使用完整模块样式文件，通常不会影响显示效果。', 'developer-starter' ),
                implode( '、', $missing_split_css )
            );
        }

        if ( is_array( $prepared_pages ) ) {
            foreach ( $prepared_pages as $prepared_page ) {
                $module_count = isset( $prepared_page['modules'] ) && is_array( $prepared_page['modules'] ) ? count( $prepared_page['modules'] ) : 0;
                if ( $module_count < 20 ) {
                    continue;
                }

                $warnings[] = sprintf(
                    /* translators: 1: page title 2: module count */
                    __( '页面“%1$s”包含 %2$d 个模块，导入后建议重点检查模块间距、移动端折行和首屏层级。', 'developer-starter' ),
                    isset( $prepared_page['title'] ) ? sanitize_text_field( (string) $prepared_page['title'] ) : __( '未命名页面', 'developer-starter' ),
                    $module_count
                );
            }
        }

        return array_values( array_unique( array_filter( array_map( 'strval', $warnings ) ) ) );
    }

    /**
     * 收集包级安全风控预警。
     *
     * @param array<string,mixed>            $package        原始包数据。
     * @param array<int,array<string,mixed>> $prepared_pages 通过预检的页面。
     * @return array<int,string>
     */
    public function collect_package_security_warnings( $package, $prepared_pages ) {
        $warnings = array();

        $payload_bytes = isset( $package['payload_bytes'] ) ? absint( $package['payload_bytes'] ) : 0;
        if ( $payload_bytes >= 524288 ) {
            $warnings[] = sprintf(
                /* translators: %s: file size */
                __( '当前数据包体积约为 %s，建议仅从可信来源导入，并优先在测试环境预检。', 'developer-starter' ),
                size_format( $payload_bytes )
            );
        }

        $total_modules = 0;
        if ( is_array( $prepared_pages ) ) {
            foreach ( $prepared_pages as $prepared_page ) {
                $total_modules += isset( $prepared_page['modules'] ) && is_array( $prepared_page['modules'] ) ? count( $prepared_page['modules'] ) : 0;
            }
        }

        if ( $total_modules >= 200 ) {
            $warnings[] = sprintf(
                /* translators: %d: module count */
                __( '当前数据包共包含 %d 个模块，建议分批导入并在导入后复查前台渲染与缓存状态。', 'developer-starter' ),
                $total_modules
            );
        }

        return array_values( array_unique( array_filter( array_map( 'strval', $warnings ) ) ) );
    }

    /**
     * 规范化 bool 值。
     *
     * @param mixed $value   原值。
     * @param bool  $default 默认值。
     * @return bool
     */
    private function normalize_bool_value( $value, $default = false ) {
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
     * 检查当前数据包中的模块在 split 模式下是否缺少拆包样式文件。
     *
     * @param array<int,array<string,mixed>> $prepared_pages 通过预检的页面。
     * @return array<int,string>
     */
    private function collect_missing_split_css_modules_from_pages( $prepared_pages ) {
        $module_types = array();

        if ( ! is_array( $prepared_pages ) ) {
            return $module_types;
        }

        foreach ( $prepared_pages as $prepared_page ) {
            if ( empty( $prepared_page['module_types'] ) || ! is_array( $prepared_page['module_types'] ) ) {
                continue;
            }

            foreach ( $prepared_page['module_types'] as $module_type ) {
                $module_type = sanitize_key( (string) $module_type );
                if ( '' !== $module_type ) {
                    $module_types[] = $module_type;
                }
            }
        }

        $module_types = array_values( array_unique( $module_types ) );
        if ( empty( $module_types ) ) {
            return array();
        }

        $split_dir = trailingslashit( DEVELOPER_STARTER_DIR ) . 'assets/css/modules-split/';
        if ( ! is_dir( $split_dir ) ) {
            return $module_types;
        }

        $optional_no_css_types = apply_filters(
            'developer_starter_modules_split_optional_types',
            array(
                'chart',
                'news',
                'work_detail',
                'work_library',
                'banner',
                'dynamic_banner',
                'hero_search',
                'interact_hero',
                'app_hero',
                'fullscreen_video',
                'resume_hero',
                'resource_hero_pro',
                'brand_banner_pro',
                'qiling_video_portal_hero',
            )
        );
        $optional_no_css_types = is_array( $optional_no_css_types ) ? array_values( array_unique( array_map( 'sanitize_key', $optional_no_css_types ) ) ) : array();

        $missing = array();
        foreach ( $module_types as $module_type ) {
            if ( in_array( $module_type, $optional_no_css_types, true ) ) {
                continue;
            }

            if ( ! $this->has_split_css_file_for_module( $module_type, $split_dir ) ) {
                $missing[] = $module_type;
            }
        }

        return array_values( array_unique( $missing ) );
    }

    /**
     * 判断单个模块是否存在拆包样式文件。
     *
     * @param string $module_type 模块类型。
     * @param string $split_dir   拆包样式目录。
     * @return bool
     */
    private function has_split_css_file_for_module( $module_type, $split_dir ) {
        $module_type = sanitize_key( (string) $module_type );
        if ( '' === $module_type ) {
            return false;
        }

        $candidate_keys = array(
            $module_type,
            str_replace( '-', '_', $module_type ),
            str_replace( '_', '-', $module_type ),
        );
        $candidate_keys = array_values( array_unique( array_filter( $candidate_keys, 'strlen' ) ) );

        foreach ( $candidate_keys as $candidate_key ) {
            $candidate_file = $split_dir . $candidate_key . '.css';
            if ( file_exists( $candidate_file ) ) {
                return true;
            }
        }

        return false;
    }

    /**
     * 获取当前站点的模块 CSS 加载模式。
     *
     * @return string single|split
     */
    private function get_module_css_load_mode() {
        $mode = function_exists( 'developer_starter_get_option' )
            ? sanitize_key( (string) developer_starter_get_option( 'module_css_load_mode', 'single' ) )
            : 'single';

        if ( ! in_array( $mode, array( 'single', 'split' ), true ) ) {
            $mode = 'single';
        }

        return $mode;
    }

    /**
     * 在已安装插件列表中查找依赖插件。
     *
     * @param string                          $slug              依赖标识。
     * @param array<string,array<string,mixed>> $installed_plugins 已安装插件列表。
     * @return array<string,mixed>
     */
    private function find_plugin_dependency_match( $slug, $installed_plugins ) {
        $slug = sanitize_text_field( (string) $slug );
        if ( '' === $slug ) {
            return array();
        }

        if ( isset( $installed_plugins[ $slug ] ) ) {
            return array(
                'file' => $slug,
                'data' => $installed_plugins[ $slug ],
            );
        }

        $target_key = sanitize_key( preg_replace( '/\.php$/', '', basename( $slug ) ) );
        foreach ( $installed_plugins as $plugin_file => $plugin_data ) {
            $file_key = sanitize_key( preg_replace( '/\.php$/', '', basename( $plugin_file ) ) );
            $dir_key  = sanitize_key( dirname( $plugin_file ) );

            if ( $target_key === $file_key || $target_key === $dir_key || sanitize_key( $plugin_file ) === $target_key ) {
                return array(
                    'file' => $plugin_file,
                    'data' => $plugin_data,
                );
            }
        }

        return array();
    }
}
