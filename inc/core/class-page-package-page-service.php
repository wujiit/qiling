<?php
/**
 * Page Package Page Service
 *
 * 负责多页面数据包里的模板、页面设置、占位链接和系统页辅助逻辑。
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Core;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Page_Package_Page_Service {

    /**
     * 应用页面级设置。
     *
     * @param int                 $post_id   页面 ID。
     * @param array<string,mixed> $settings 页面设置。
     * @return void
     */
    public function apply_page_settings( $post_id, $settings ) {
        $hide_page_header = isset( $settings['hide_page_header'] ) ? $this->normalize_bool_value( $settings['hide_page_header'], false ) : false;
        if ( $hide_page_header ) {
            update_post_meta( $post_id, '_qiling_hide_page_header', '1' );
        } else {
            delete_post_meta( $post_id, '_qiling_hide_page_header' );
        }

        $transparent_header = isset( $settings['transparent_header'] ) ? $this->normalize_bool_value( $settings['transparent_header'], false ) : false;
        if ( $transparent_header ) {
            update_post_meta( $post_id, '_qiling_transparent_header', '1' );
        } else {
            delete_post_meta( $post_id, '_qiling_transparent_header' );
        }

        $enable_scroll_reveal = isset( $settings['enable_scroll_reveal'] ) ? $this->normalize_bool_value( $settings['enable_scroll_reveal'], false ) : false;
        update_post_meta( $post_id, '_developer_starter_enable_scroll_reveal', $enable_scroll_reveal ? '1' : '0' );

        if ( function_exists( 'developer_starter_persist_post_footer_visual_settings' ) ) {
            if ( isset( $settings['footer'] ) && is_array( $settings['footer'] ) ) {
                developer_starter_persist_post_footer_visual_settings( $post_id, $settings['footer'] );
            } elseif ( isset( $settings['footer_settings'] ) && is_array( $settings['footer_settings'] ) ) {
                developer_starter_persist_post_footer_visual_settings( $post_id, $settings['footer_settings'] );
            }
        }

        if ( function_exists( 'developer_starter_persist_post_page_region_decoration' ) && isset( $settings['region_decoration'] ) && is_array( $settings['region_decoration'] ) ) {
            developer_starter_persist_post_page_region_decoration( $post_id, $settings['region_decoration'] );
        }

        if ( class_exists( '\Developer_Starter\Core\Design_Tokens' ) ) {
            $page_design = array();
            if ( isset( $settings['page_design'] ) && is_array( $settings['page_design'] ) ) {
                $page_design = $settings['page_design'];
            } elseif ( isset( $settings['design'] ) && is_array( $settings['design'] ) ) {
                $page_design = $settings['design'];
            }

            Design_Tokens::persist_page_design_overrides( $post_id, $page_design );

            $has_design_preset = isset( $settings['design_preset'] ) || isset( $settings['designPreset'] ) || isset( $settings['page_design_preset'] );
            if ( $has_design_preset ) {
                $design_preset = Design_Tokens::get_page_design_preset( $post_id );
                if ( isset( $settings['design_preset'] ) ) {
                    $design_preset = $settings['design_preset'];
                } elseif ( isset( $settings['designPreset'] ) ) {
                    $design_preset = $settings['designPreset'];
                } elseif ( isset( $settings['page_design_preset'] ) ) {
                    $design_preset = $settings['page_design_preset'];
                }

                Design_Tokens::persist_page_design_preset( $post_id, $design_preset );
            }
        }
    }

    /**
     * 应用站点级设置。
     *
     * @param array<string,mixed> $site_options 站点设置。
     * @param array<string,int>   $page_key_map 页面映射。
     * @return array<int,string>
     */
    public function apply_site_options( $site_options, $page_key_map ) {
        $messages = array();

        if ( ! is_array( $site_options ) ) {
            return $messages;
        }

        if ( isset( $site_options['site_title'] ) && '' !== (string) $site_options['site_title'] ) {
            update_option( 'blogname', sanitize_text_field( (string) $site_options['site_title'] ) );
            $messages[] = __( '已应用站点标题。', 'developer-starter' );
        }

        if ( array_key_exists( 'tagline', $site_options ) ) {
            update_option( 'blogdescription', sanitize_text_field( (string) $site_options['tagline'] ) );
            $messages[] = __( '已应用站点副标题。', 'developer-starter' );
        }

        $has_design_system_v2 = ! empty( $site_options['design_system_v2'] ) && is_array( $site_options['design_system_v2'] );
        if ( $has_design_system_v2 && class_exists( '\Developer_Starter\Core\Design_Tokens' ) ) {
            $design_system_message = $this->apply_design_system_v2_payload(
                $site_options['design_system_v2'],
                isset( $site_options['design_options'] ) && is_array( $site_options['design_options'] )
                    ? $site_options['design_options']
                    : array()
            );
            if ( '' !== $design_system_message ) {
                $messages[] = $design_system_message;
            }
        } elseif ( ! empty( $site_options['design_options'] ) && is_array( $site_options['design_options'] ) ) {
            $design_message = $this->apply_theme_option_group(
                $site_options['design_options'],
                array(
                    'design_enable_global_tokens',
                    'design_preset',
                    'design_primary_color',
                    'design_secondary_color',
                    'design_accent_color',
                    'design_text_color',
                    'design_text_muted_color',
                    'design_heading_color',
                    'design_background_color',
                    'design_surface_color',
                    'design_surface_alt_color',
                    'design_border_color',
                    'design_font_family',
                    'design_font_size_base',
                    'design_line_height_base',
                    'design_container_width',
                    'design_section_padding',
                    'design_card_radius',
                    'design_button_radius',
                    'design_input_radius',
                    'design_animation_speed',
                    'design_shadow_sm',
                    'design_shadow_md',
                    'design_shadow_lg',
                    'design_dark_bg',
                    'design_dark_surface',
                    'design_dark_text',
                    'design_dark_text_muted',
                    'design_dark_border',
                    'primary_color',
                ),
                class_exists( '\Developer_Starter\Core\Design_Tokens' )
                    ? array( '\Developer_Starter\Core\Design_Tokens', 'sanitize_options' )
                    : null,
                __( '已应用全局样式设置。', 'developer-starter' )
            );
            if ( '' !== $design_message ) {
                $messages[] = $design_message;
            }
        }

        if ( ! empty( $site_options['content_model_options'] ) && is_array( $site_options['content_model_options'] ) ) {
            $content_model_message = $this->apply_theme_option_group(
                $site_options['content_model_options'],
                array(
                    'content_model_center_enable',
                    'local_business_features_enable',
                    'content_model_enabled_models',
                    'content_model_archive_base',
                    'content_model_archive_enable',
                    'content_model_rest_enable',
                    'content_model_meta_box_enable',
                ),
                class_exists( '\Developer_Starter\Core\Content_Model_Center' )
                    ? array( '\Developer_Starter\Core\Content_Model_Center', 'sanitize_options' )
                    : null,
                __( '已应用内容模型中心设置。', 'developer-starter' )
            );
            if ( '' !== $content_model_message ) {
                $messages[] = $content_model_message;
            }
        }

        if ( ! empty( $site_options['front_page'] ) ) {
            $messages = array_merge( $messages, $this->apply_front_page_option( $site_options['front_page'], $page_key_map ) );
        }

        if ( ! empty( $site_options['posts_page'] ) ) {
            $messages = array_merge( $messages, $this->apply_posts_page_option( $site_options['posts_page'], $page_key_map ) );
        }

        if ( ! empty( $site_options['navigation'] ) && is_array( $site_options['navigation'] ) ) {
            $messages = array_merge( $messages, $this->apply_navigation_options( $site_options['navigation'], $page_key_map ) );
        }

        return array_values( array_filter( array_map( 'strval', $messages ) ) );
    }

    /**
     * 应用首页设置。
     *
     * @param string            $front_page_key 首页 page_key。
     * @param array<string,int> $page_key_map   页面映射。
     * @return array<int,string>
     */
    private function apply_front_page_option( $front_page_key, $page_key_map ) {
        $messages = array();
        $front_page_key = sanitize_key( (string) $front_page_key );
        if ( '' === $front_page_key || ! isset( $page_key_map[ $front_page_key ] ) ) {
            $messages[] = __( '首页设置未生效：数据包中未找到对应页面。', 'developer-starter' );
            return $messages;
        }

        $front_page_id = absint( $page_key_map[ $front_page_key ] );
        $front_page    = get_post( $front_page_id );
        if ( ! $front_page instanceof \WP_Post || $front_page->post_type !== 'page' ) {
            $messages[] = __( '首页设置未生效：目标页面不存在。', 'developer-starter' );
            return $messages;
        }

        if ( $front_page->post_status !== 'publish' ) {
            $messages[] = __( '首页设置未生效：目标页面不是已发布状态。', 'developer-starter' );
            return $messages;
        }

        update_option( 'show_on_front', 'page' );
        update_option( 'page_on_front', $front_page_id );

        $messages[] = sprintf(
            /* translators: %s: page title */
            __( '已将“%s”设置为首页。', 'developer-starter' ),
            get_the_title( $front_page_id )
        );
        return $messages;
    }

    /**
     * 应用文章列表页设置。
     *
     * @param string            $posts_page_key 文章页 page_key。
     * @param array<string,int> $page_key_map   页面映射。
     * @return array<int,string>
     */
    private function apply_posts_page_option( $posts_page_key, $page_key_map ) {
        $messages       = array();
        $posts_page_key = sanitize_key( (string) $posts_page_key );
        if ( '' === $posts_page_key || ! isset( $page_key_map[ $posts_page_key ] ) ) {
            $messages[] = __( '文章列表页设置未生效：数据包中未找到对应页面。', 'developer-starter' );
            return $messages;
        }

        $posts_page_id = absint( $page_key_map[ $posts_page_key ] );
        $posts_page    = get_post( $posts_page_id );
        if ( ! $posts_page instanceof \WP_Post || $posts_page->post_type !== 'page' ) {
            $messages[] = __( '文章列表页设置未生效：目标页面不存在。', 'developer-starter' );
            return $messages;
        }

        update_option( 'page_for_posts', $posts_page_id );
        if ( get_option( 'show_on_front', 'posts' ) !== 'page' ) {
            update_option( 'show_on_front', 'page' );
        }

        $messages[] = sprintf(
            /* translators: %s: page title */
            __( '已将“%s”设置为文章列表页。', 'developer-starter' ),
            get_the_title( $posts_page_id )
        );
        return $messages;
    }

    /**
     * 规范化站点设置。
     *
     * @param array<string,mixed> $site_options 原始站点设置。
     * @return array<string,string>
     */
    public function normalize_site_options( $site_options ) {
        $normalized = array();

        if ( isset( $site_options['front_page'] ) && is_scalar( $site_options['front_page'] ) ) {
            $normalized['front_page'] = sanitize_key( (string) $site_options['front_page'] );
        }

        if ( isset( $site_options['posts_page'] ) && is_scalar( $site_options['posts_page'] ) ) {
            $normalized['posts_page'] = sanitize_key( (string) $site_options['posts_page'] );
        } elseif ( isset( $site_options['blog_page'] ) && is_scalar( $site_options['blog_page'] ) ) {
            $normalized['posts_page'] = sanitize_key( (string) $site_options['blog_page'] );
        }

        if ( isset( $site_options['site_title'] ) && is_scalar( $site_options['site_title'] ) ) {
            $normalized['site_title'] = sanitize_text_field( (string) $site_options['site_title'] );
        }

        if ( array_key_exists( 'tagline', $site_options ) && is_scalar( $site_options['tagline'] ) ) {
            $normalized['tagline'] = sanitize_text_field( (string) $site_options['tagline'] );
        }

        if ( ! empty( $site_options['design_system_v2'] ) && is_array( $site_options['design_system_v2'] ) ) {
            $normalized['design_system_v2'] = $this->normalize_design_system_v2_payload( $site_options['design_system_v2'] );
        } elseif ( ! empty( $site_options['designSystemV2'] ) && is_array( $site_options['designSystemV2'] ) ) {
            $normalized['design_system_v2'] = $this->normalize_design_system_v2_payload( $site_options['designSystemV2'] );
        }

        if ( ! empty( $site_options['design_options'] ) && is_array( $site_options['design_options'] ) ) {
            $normalized['design_options'] = $this->normalize_theme_option_group(
                $site_options['design_options'],
                array(
                    'design_enable_global_tokens',
                    'design_preset',
                    'design_primary_color',
                    'design_secondary_color',
                    'design_accent_color',
                    'design_text_color',
                    'design_text_muted_color',
                    'design_heading_color',
                    'design_background_color',
                    'design_surface_color',
                    'design_surface_alt_color',
                    'design_border_color',
                    'design_font_family',
                    'design_font_size_base',
                    'design_line_height_base',
                    'design_container_width',
                    'design_section_padding',
                    'design_card_radius',
                    'design_button_radius',
                    'design_input_radius',
                    'design_animation_speed',
                    'design_shadow_sm',
                    'design_shadow_md',
                    'design_shadow_lg',
                    'design_dark_bg',
                    'design_dark_surface',
                    'design_dark_text',
                    'design_dark_text_muted',
                    'design_dark_border',
                    'primary_color',
                ),
                class_exists( '\Developer_Starter\Core\Design_Tokens' )
                    ? array( '\Developer_Starter\Core\Design_Tokens', 'sanitize_options' )
                    : null
            );
        }

        if ( ! empty( $site_options['content_model_options'] ) && is_array( $site_options['content_model_options'] ) ) {
            $normalized['content_model_options'] = $this->normalize_theme_option_group(
                $site_options['content_model_options'],
                array(
                    'content_model_center_enable',
                    'local_business_features_enable',
                    'content_model_enabled_models',
                    'content_model_archive_base',
                    'content_model_archive_enable',
                    'content_model_rest_enable',
                    'content_model_meta_box_enable',
                ),
                class_exists( '\Developer_Starter\Core\Content_Model_Center' )
                    ? array( '\Developer_Starter\Core\Content_Model_Center', 'sanitize_options' )
                    : null
            );
        }

        if ( ! empty( $site_options['navigation'] ) && is_array( $site_options['navigation'] ) ) {
            $navigation = $this->normalize_navigation_options( $site_options['navigation'] );
            if ( ! empty( $navigation ) ) {
                $normalized['navigation'] = $navigation;
            }
        } elseif ( ! empty( $site_options['menus'] ) && is_array( $site_options['menus'] ) ) {
            $navigation = $this->normalize_navigation_options( array( 'menus' => $site_options['menus'] ) );
            if ( ! empty( $navigation ) ) {
                $normalized['navigation'] = $navigation;
            }
        }

        return $normalized;
    }

    /**
     * 规范化主题设置分组，只保留整站包允许修改的白名单字段。
     *
     * @param array<string,mixed> $options          原始设置。
     * @param array<int,string>   $allowed_keys      允许字段。
     * @param callable|null       $sanitize_callback 专用清洗回调。
     * @return array<string,mixed>
     */
    private function normalize_theme_option_group( $options, $allowed_keys, $sanitize_callback = null ) {
        $options = is_array( $options ) ? $options : array();
        $allowed = array_fill_keys( array_map( 'strval', $allowed_keys ), true );
        $draft   = array();

        foreach ( $options as $key => $value ) {
            $key = sanitize_key( (string) $key );
            if ( '' === $key || ! isset( $allowed[ $key ] ) ) {
                continue;
            }

            if ( is_array( $value ) ) {
                $draft[ $key ] = $this->sanitize_array_recursive( $value );
            } elseif ( is_bool( $value ) ) {
                $draft[ $key ] = $value ? '1' : '';
            } else {
                $draft[ $key ] = is_scalar( $value ) ? wp_kses_post( (string) $value ) : '';
            }
        }

        if ( empty( $draft ) ) {
            return array();
        }

        if ( is_callable( $sanitize_callback ) ) {
            $sanitized = call_user_func( $sanitize_callback, $draft, get_option( 'developer_starter_options', array() ) );
            if ( is_array( $sanitized ) ) {
                $draft = array_intersect_key( $sanitized, $allowed );
            }
        }

        return $draft;
    }

    /**
     * 应用主题设置分组。
     *
     * @param array<string,mixed> $options           设置分组。
     * @param array<int,string>   $allowed_keys       允许字段。
     * @param callable|null       $sanitize_callback  专用清洗回调。
     * @return string
     */
    private function apply_theme_option_group( $options, $allowed_keys, $sanitize_callback = null, $success_message = '' ) {
        $normalized = $this->normalize_theme_option_group( $options, $allowed_keys, $sanitize_callback );
        if ( empty( $normalized ) ) {
            return '';
        }

        $stored = get_option( 'developer_starter_options', array() );
        $stored = is_array( $stored ) ? $stored : array();
        update_option( 'developer_starter_options', array_merge( $stored, $normalized ) );

        return '' !== $success_message ? $success_message : __( '已应用主题设置。', 'developer-starter' );
    }

    /**
     * 应用完整 design_system_v2 存储结构。
     *
     * @param array<string,mixed> $design_system_v2 设计系统存储结构。
     * @param array<string,mixed> $design_options   兼容 option 片段。
     * @return string
     */
    private function apply_design_system_v2_payload( $design_system_v2, $design_options = array() ) {
        if ( ! is_array( $design_system_v2 ) || empty( $design_system_v2 ) || ! class_exists( '\Developer_Starter\Core\Design_Tokens' ) ) {
            return '';
        }

        $normalized = $this->normalize_design_system_v2_payload( $design_system_v2 );
        if ( empty( $normalized ) ) {
            return '';
        }

        $stored = get_option( 'developer_starter_options', array() );
        $stored = is_array( $stored ) ? $stored : array();
        if ( ! empty( $design_options ) && is_array( $design_options ) && method_exists( '\Developer_Starter\Core\Design_Tokens', 'merge_storage_payload_with_options' ) ) {
            $merged = Design_Tokens::merge_storage_payload_with_options( $normalized, $design_options, $stored );
            if ( ! empty( $merged ) && is_array( $merged ) ) {
                $normalized = $merged;
            }
        }

        $candidate = array_merge(
            $stored,
            array(
                Design_Tokens::STORAGE_OPTION_KEY => $normalized,
            )
        );
        $sanitized = Design_Tokens::sanitize_options( $candidate, $stored );
        if ( ! is_array( $sanitized ) || empty( $sanitized ) ) {
            return '';
        }

        update_option( 'developer_starter_options', array_merge( $stored, $sanitized ) );
        return __( '已应用完整 design_system_v2 设计系统。', 'developer-starter' );
    }

    /**
     * 递归清洗数组值。
     *
     * @param mixed $value 原始值。
     * @return mixed
     */
    private function sanitize_array_recursive( $value ) {
        if ( ! is_array( $value ) ) {
            return is_scalar( $value ) ? wp_kses_post( (string) $value ) : '';
        }

        $sanitized = array();
        foreach ( $value as $key => $item ) {
            $key = is_string( $key ) ? sanitize_key( $key ) : $key;
            $sanitized[ $key ] = $this->sanitize_array_recursive( $item );
        }

        return $sanitized;
    }

    /**
     * 规范化完整 design_system_v2 存储结构。
     *
     * @param mixed $payload 原始设计系统。
     * @return array<string,mixed>
     */
    private function normalize_design_system_v2_payload( $payload ) {
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
                    $normalized[ $normalized_key ] = $this->sanitize_array_recursive( $payload[ $candidate_key ] );
                    break;
                }
            }
        }

        return $normalized;
    }

    /**
     * 规范化导航菜单配置。
     *
     * @param array<string,mixed> $navigation 导航配置。
     * @return array<string,mixed>
     */
    private function normalize_navigation_options( $navigation ) {
        $menus = isset( $navigation['menus'] ) && is_array( $navigation['menus'] ) ? $navigation['menus'] : array();
        if ( empty( $menus ) && $this->is_list_array( $navigation ) ) {
            $menus = $navigation;
        }

        $normalized_menus = array();
        foreach ( $menus as $menu_index => $menu ) {
            if ( ! is_array( $menu ) ) {
                continue;
            }

            $menu_key = '';
            foreach ( array( 'menu_key', 'key', 'id', 'location' ) as $key_name ) {
                if ( ! empty( $menu[ $key_name ] ) && is_scalar( $menu[ $key_name ] ) ) {
                    $menu_key = sanitize_key( (string) $menu[ $key_name ] );
                    break;
                }
            }
            if ( '' === $menu_key ) {
                $menu_key = 'menu_' . ( absint( $menu_index ) + 1 );
            }

            $items = isset( $menu['items'] ) && is_array( $menu['items'] ) ? $menu['items'] : array();
            $normalized_items = array();
            foreach ( $items as $item_index => $item ) {
                if ( ! is_array( $item ) ) {
                    continue;
                }

                $page_key = isset( $item['page_key'] ) && is_scalar( $item['page_key'] ) ? sanitize_key( (string) $item['page_key'] ) : '';
                $url      = isset( $item['url'] ) && is_scalar( $item['url'] ) ? esc_url_raw( (string) $item['url'] ) : '';
                if ( '' === $page_key && '' === $url ) {
                    continue;
                }

                $item_key = isset( $item['item_key'] ) && is_scalar( $item['item_key'] )
                    ? sanitize_key( (string) $item['item_key'] )
                    : 'item_' . ( absint( $item_index ) + 1 );

                $normalized_items[] = array(
                    'item_key'   => $item_key,
                    'label'      => isset( $item['label'] ) && is_scalar( $item['label'] ) ? sanitize_text_field( (string) $item['label'] ) : '',
                    'page_key'   => $page_key,
                    'url'        => $url,
                    'target'     => isset( $item['target'] ) && '_blank' === (string) $item['target'] ? '_blank' : '',
                    'classes'    => $this->normalize_string_list( isset( $item['classes'] ) ? $item['classes'] : array() ),
                    'parent_key' => isset( $item['parent_key'] ) && is_scalar( $item['parent_key'] ) ? sanitize_key( (string) $item['parent_key'] ) : '',
                );
            }

            if ( empty( $normalized_items ) ) {
                continue;
            }

            $normalized_menus[] = array(
                'menu_key' => $menu_key,
                'name'     => isset( $menu['name'] ) && is_scalar( $menu['name'] ) ? sanitize_text_field( (string) $menu['name'] ) : $menu_key,
                'location' => isset( $menu['location'] ) && is_scalar( $menu['location'] ) ? sanitize_key( (string) $menu['location'] ) : '',
                'items'    => $normalized_items,
            );
        }

        return empty( $normalized_menus ) ? array() : array( 'menus' => $normalized_menus );
    }

    /**
     * 应用导航菜单配置。
     *
     * @param array<string,mixed> $navigation   导航配置。
     * @param array<string,int>   $page_key_map 页面映射。
     * @return array<int,string>
     */
    private function apply_navigation_options( $navigation, $page_key_map ) {
        $messages = array();
        $menus    = isset( $navigation['menus'] ) && is_array( $navigation['menus'] ) ? $navigation['menus'] : array();
        if ( empty( $menus ) || ! function_exists( 'wp_create_nav_menu' ) ) {
            return $messages;
        }

        $registered_locations = function_exists( 'get_registered_nav_menus' ) ? get_registered_nav_menus() : array();
        $location_assignments = function_exists( 'get_nav_menu_locations' ) ? get_nav_menu_locations() : array();
        $location_assignments = is_array( $location_assignments ) ? $location_assignments : array();

        foreach ( $menus as $menu ) {
            if ( ! is_array( $menu ) || empty( $menu['items'] ) || ! is_array( $menu['items'] ) ) {
                continue;
            }

            $menu_name = ! empty( $menu['name'] ) ? sanitize_text_field( (string) $menu['name'] ) : __( '导入菜单', 'developer-starter' );
            $menu_id   = $this->create_unique_nav_menu( $menu_name );
            if ( $menu_id <= 0 ) {
                $messages[] = sprintf(
                    /* translators: %s: menu name */
                    __( '菜单“%s”创建失败，已跳过。', 'developer-starter' ),
                    $menu_name
                );
                continue;
            }

            $created_items = 0;
            foreach ( $menu['items'] as $item ) {
                if ( ! is_array( $item ) ) {
                    continue;
                }

                $item_id = $this->create_nav_menu_item_from_package( $menu_id, $item, $page_key_map );
                if ( $item_id > 0 ) {
                    $created_items++;
                }
            }

            $location = isset( $menu['location'] ) ? sanitize_key( (string) $menu['location'] ) : '';
            if ( '' !== $location && isset( $registered_locations[ $location ] ) ) {
                $location_assignments[ $location ] = $menu_id;
                set_theme_mod( 'nav_menu_locations', $location_assignments );
            }

            $messages[] = sprintf(
                /* translators: 1: menu name 2: item count */
                __( '已创建菜单“%1$s”，包含 %2$d 个菜单项。', 'developer-starter' ),
                $menu_name,
                $created_items
            );
        }

        return $messages;
    }

    /**
     * 创建唯一菜单。
     *
     * @param string $menu_name 菜单名。
     * @return int
     */
    private function create_unique_nav_menu( $menu_name ) {
        $base_name = sanitize_text_field( (string) $menu_name );
        if ( '' === $base_name ) {
            $base_name = __( '导入菜单', 'developer-starter' );
        }

        $candidate_name = $base_name;
        $suffix         = 2;
        do {
            $menu_id = wp_create_nav_menu( $candidate_name );
            if ( ! is_wp_error( $menu_id ) ) {
                return absint( $menu_id );
            }

            $candidate_name = sprintf( '%1$s %2$d', $base_name, $suffix );
            $suffix++;
        } while ( $suffix <= 20 );

        return 0;
    }

    /**
     * 创建菜单项。
     *
     * @param int                $menu_id      菜单 ID。
     * @param array<string,mixed> $item        菜单项。
     * @param array<string,int>  $page_key_map 页面映射。
     * @return int
     */
    private function create_nav_menu_item_from_package( $menu_id, $item, $page_key_map ) {
        $label    = isset( $item['label'] ) ? sanitize_text_field( (string) $item['label'] ) : '';
        $page_key = isset( $item['page_key'] ) ? sanitize_key( (string) $item['page_key'] ) : '';
        $target   = isset( $item['target'] ) && '_blank' === (string) $item['target'] ? '_blank' : '';
        $classes  = isset( $item['classes'] ) && is_array( $item['classes'] ) ? implode( ' ', $this->normalize_string_list( $item['classes'] ) ) : '';

        if ( '' !== $page_key && isset( $page_key_map[ $page_key ] ) ) {
            $page_id = absint( $page_key_map[ $page_key ] );
            $post    = get_post( $page_id );
            if ( $post instanceof \WP_Post && 'page' === $post->post_type ) {
                $item_id = wp_update_nav_menu_item(
                    $menu_id,
                    0,
                    array(
                        'menu-item-title'     => '' !== $label ? $label : get_the_title( $page_id ),
                        'menu-item-object-id' => $page_id,
                        'menu-item-object'    => 'page',
                        'menu-item-type'      => 'post_type',
                        'menu-item-status'    => 'publish',
                        'menu-item-target'    => $target,
                        'menu-item-classes'   => $classes,
                    )
                );
                return is_wp_error( $item_id ) ? 0 : absint( $item_id );
            }
        }

        $url = isset( $item['url'] ) ? esc_url_raw( (string) $item['url'] ) : '';
        if ( '' === $url ) {
            return 0;
        }

        $item_id = wp_update_nav_menu_item(
            $menu_id,
            0,
            array(
                'menu-item-title'   => '' !== $label ? $label : $url,
                'menu-item-url'     => $url,
                'menu-item-type'    => 'custom',
                'menu-item-status'  => 'publish',
                'menu-item-target'  => $target,
                'menu-item-classes' => $classes,
            )
        );

        return is_wp_error( $item_id ) ? 0 : absint( $item_id );
    }

    /**
     * 判断数组是否为列表。
     *
     * @param array<mixed> $items 数组。
     * @return bool
     */
    private function is_list_array( $items ) {
        if ( ! is_array( $items ) ) {
            return false;
        }

        $index = 0;
        foreach ( array_keys( $items ) as $key ) {
            if ( $key !== $index ) {
                return false;
            }
            $index++;
        }

        return true;
    }

    /**
     * 规范化字符串列表。
     *
     * @param mixed $items 原始列表。
     * @return array<int,string>
     */
    private function normalize_string_list( $items ) {
        if ( is_string( $items ) ) {
            $items = preg_split( '/[\r\n, ]+/', $items );
        }

        if ( ! is_array( $items ) ) {
            return array();
        }

        $normalized = array();
        foreach ( $items as $item ) {
            if ( ! is_scalar( $item ) ) {
                continue;
            }
            $value = sanitize_html_class( (string) $item );
            if ( '' !== $value ) {
                $normalized[] = $value;
            }
        }

        return array_values( array_unique( $normalized ) );
    }

    /**
     * 获取模板字段的第一个可用值。
     *
     * @param array<string,mixed> $page     页面数据。
     * @param array<string,mixed> $settings settings 数据。
     * @param string              $key      目标字段。
     * @return mixed
     */
    public function get_first_defined_value( $page, $settings, $key ) {
        if ( isset( $page[ $key ] ) ) {
            return $page[ $key ];
        }

        if ( isset( $settings[ $key ] ) ) {
            return $settings[ $key ];
        }

        return null;
    }

    /**
     * 规范化 bool 值。
     *
     * @param mixed $value   原值。
     * @param bool  $default 默认值。
     * @return bool
     */
    public function normalize_bool_value( $value, $default = false ) {
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
     * 规范化模板值。
     *
     * @param mixed $template 原模板值。
     * @return string
     */
    public function normalize_page_template( $template ) {
        if ( function_exists( 'developer_starter_normalize_page_template_slug' ) ) {
            $template = developer_starter_normalize_page_template_slug( $template );
        } else {
            $template = is_scalar( $template ) ? sanitize_text_field( (string) $template ) : '';
        }

        if ( '' === $template ) {
            return 'default';
        }

        $aliases = array(
            'fullscreen'  => 'templates/template-fullscreen.php',
            'full-screen' => 'templates/template-fullscreen.php',
            'independent' => 'templates/template-fullscreen.php',
            'standalone'  => 'templates/template-fullscreen.php',
            'fullwidth'   => 'templates/template-fullwidth.php',
            'full-width'  => 'templates/template-fullwidth.php',
        );

        if ( isset( $aliases[ $template ] ) ) {
            return $aliases[ $template ];
        }

        return $template;
    }

    /**
     * 获取公开可导入的模板默认值。
     *
     * @return string
     */
    public function get_default_public_page_template() {
        return 'templates/template-fullscreen.php';
    }

    /**
     * 获取模板显示名。
     *
     * @param string $template 模板值。
     * @return string
     */
    public function get_template_label( $template ) {
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

        $file_label = $this->get_template_label_from_file( $template );
        if ( '' !== $file_label ) {
            return $file_label;
        }

        return $template;
    }

    /**
     * 判断模板是否允许导入。
     *
     * @param string $template 模板值。
     * @return bool
     */
    public function is_allowed_public_page_template( $template ) {
        $template = $this->normalize_page_template( $template );
        if ( 'default' === $template ) {
            return true;
        }

        $templates = wp_get_theme()->get_page_templates( null, 'page' );
        if ( ! is_array( $templates ) ) {
            $templates = array();
        }

        if ( in_array( $template, array_values( $templates ), true ) ) {
            return true;
        }

        return $this->template_file_exists_for_page( $template );
    }

    /**
     * 判断模板是否属于系统保留模板。
     *
     * @param mixed                         $template 模板值。
     * @param array<int,array<string,mixed>> $reserved_page_definitions 保留页定义。
     * @return bool
     */
    public function is_reserved_page_template( $template, $reserved_page_definitions = array() ) {
        $template = $this->normalize_page_template( $template );
        foreach ( $reserved_page_definitions as $definition ) {
            if ( ! empty( $definition['template'] ) && $definition['template'] === $template ) {
                return true;
            }
        }

        return false;
    }

    /**
     * 通过模板文件兜底检查页面模板是否真实存在且可用于 page。
     *
     * @param string $template 模板路径。
     * @return bool
     */
    public function template_file_exists_for_page( $template ) {
        $template = $this->normalize_page_template( $template );
        if ( 'default' === $template ) {
            return true;
        }

        if ( ! is_string( $template ) || '' === $template || substr( $template, -4 ) !== '.php' ) {
            return false;
        }

        if ( function_exists( 'validate_file' ) && 0 !== validate_file( $template ) ) {
            return false;
        }

        $template_path = $this->get_theme_template_file_path( $template );
        if ( '' === $template_path || ! file_exists( $template_path ) || ! is_file( $template_path ) ) {
            return false;
        }

        return $this->template_file_supports_pages( $template_path );
    }

    /**
     * 获取模板文件真实路径，兼容父子主题。
     *
     * @param string $template 模板路径。
     * @return string
     */
    public function get_theme_template_file_path( $template ) {
        $template = ltrim( $this->normalize_page_template( $template ), '/' );
        if ( '' === $template ) {
            return '';
        }

        if ( function_exists( 'get_theme_file_path' ) ) {
            $path = get_theme_file_path( $template );
            return is_string( $path ) ? $path : '';
        }

        if ( function_exists( 'get_stylesheet_directory' ) ) {
            $stylesheet_path = trailingslashit( get_stylesheet_directory() ) . $template;
            if ( file_exists( $stylesheet_path ) ) {
                return $stylesheet_path;
            }
        }

        if ( function_exists( 'get_template_directory' ) ) {
            $template_path = trailingslashit( get_template_directory() ) . $template;
            if ( file_exists( $template_path ) ) {
                return $template_path;
            }
        }

        return '';
    }

    /**
     * 检查模板文件头部是否声明为页面模板。
     *
     * @param string $template_path 模板文件路径。
     * @return bool
     */
    public function template_file_supports_pages( $template_path ) {
        $template_path = is_string( $template_path ) ? $template_path : '';
        if ( '' === $template_path || ! is_readable( $template_path ) ) {
            return false;
        }

        $contents = file_get_contents( $template_path, false, null, 0, 8192 );
        if ( ! is_string( $contents ) || '' === $contents ) {
            return false;
        }

        if ( ! preg_match( '/Template Name:\s*(.+)/i', $contents ) ) {
            return false;
        }

        if ( preg_match( '/Template Post Type:\s*(.+)/i', $contents, $matches ) ) {
            $post_types = array_filter( array_map( 'trim', explode( ',', strtolower( (string) $matches[1] ) ) ) );
            if ( ! empty( $post_types ) && ! in_array( 'page', $post_types, true ) ) {
                return false;
            }
        }

        return true;
    }

    /**
     * 从模板文件头部提取模板显示名称，作为后台标签兜底。
     *
     * @param string $template 模板路径。
     * @return string
     */
    public function get_template_label_from_file( $template ) {
        $template_path = $this->get_theme_template_file_path( $template );
        if ( '' === $template_path || ! is_readable( $template_path ) ) {
            return '';
        }

        $contents = file_get_contents( $template_path, false, null, 0, 4096 );
        if ( ! is_string( $contents ) || '' === $contents ) {
            return '';
        }

        if ( preg_match( '/Template Name:\s*(.+)/i', $contents, $matches ) ) {
            return sanitize_text_field( trim( (string) $matches[1] ) );
        }

        return '';
    }

    /**
     * 判断 slug 是否属于系统保留页面。
     *
     * @param string                       $slug 页面 slug。
     * @param array<int,array<string,mixed>> $reserved_page_definitions 保留页定义。
     * @return bool
     */
    public function is_reserved_page_slug( $slug, $reserved_page_definitions = array() ) {
        $slug = sanitize_title( (string) $slug );
        foreach ( $reserved_page_definitions as $definition ) {
            if ( ! empty( $definition['slug'] ) && sanitize_title( (string) $definition['slug'] ) === $slug ) {
                return true;
            }
        }

        return false;
    }

    /**
     * 规范化数据包 ID。
     *
     * @param mixed $package_id 原始 ID。
     * @param mixed $fallback_seed 回退种子。
     * @return string
     */
    public function normalize_package_id( $package_id, $fallback_seed = '' ) {
        $package_id = is_scalar( $package_id ) ? sanitize_key( (string) $package_id ) : '';
        if ( '' !== $package_id ) {
            return $package_id;
        }

        $fallback_seed = is_scalar( $fallback_seed ) ? (string) $fallback_seed : '';
        if ( '' !== $fallback_seed ) {
            return 'site-package-' . substr( md5( $fallback_seed ), 0, 12 );
        }

        return 'site-package';
    }

    /**
     * 收集多页包里的占位链接预警。
     *
     * @param array<int,array<string,mixed>>    $prepared_pages 可导入页面列表。
     * @param array<string,array<string,mixed>> $page_key_to_target 当前可识别页面 key 集合。
     * @param array<int,array<string,mixed>>    $reserved_page_definitions 保留页定义。
     * @return array<int,string>
     */
    public function collect_package_reference_warnings( $prepared_pages, $page_key_to_target, $reserved_page_definitions = array() ) {
        $warnings        = array();
        $known_page_keys = array_fill_keys( array_map( 'strval', array_keys( $page_key_to_target ) ), true );
        $system_url_map  = $this->build_system_url_map( $reserved_page_definitions );

        foreach ( $prepared_pages as $page ) {
            if ( ! is_array( $page ) || empty( $page['modules'] ) || ! is_array( $page['modules'] ) ) {
                continue;
            }

            $page_title = isset( $page['title'] ) ? sanitize_text_field( (string) $page['title'] ) : __( '未命名页面', 'developer-starter' );
            $references = array();

            $this->collect_placeholder_references( $page['modules'], $references );
            if ( empty( $references ) ) {
                continue;
            }

            foreach ( $references as $reference ) {
                if ( empty( $reference['type'] ) || empty( $reference['target'] ) ) {
                    continue;
                }

                if ( $reference['type'] === 'page' && ! isset( $known_page_keys[ $reference['target'] ] ) ) {
                    $warnings[] = sprintf(
                        /* translators: 1: page title 2: page key */
                        __( '页面“%1$s”引用了不存在的页面标识：%2$s。导入后该占位链接会保留原样。', 'developer-starter' ),
                        $page_title,
                        $reference['target']
                    );
                }

                if ( $reference['type'] === 'system' && ! isset( $system_url_map[ $reference['target'] ] ) ) {
                    $warnings[] = sprintf(
                        /* translators: 1: page title 2: system target */
                        __( '页面“%1$s”引用了未就绪的系统入口：%2$s。导入后该占位链接会保留原样。', 'developer-starter' ),
                        $page_title,
                        $reference['target']
                    );
                }
            }
        }

        return array_values( array_unique( array_filter( array_map( 'strval', $warnings ) ) ) );
    }

    /**
     * 导入完成后解析页面包里的占位链接。
     *
     * @param array<string,array<string,mixed>> $created_pages 已创建页面映射。
     * @param array<string,int>                 $page_key_map  页面 key -> ID 映射。
     * @param array<int,array<string,mixed>>    $reserved_page_definitions 保留页定义。
     * @return array<int,string>
     */
    public function resolve_imported_page_links( $created_pages, $page_key_map, $reserved_page_definitions = array() ) {
        $messages       = array();
        $page_url_map   = $this->build_page_url_map( $page_key_map );
        $system_url_map = $this->build_system_url_map( $reserved_page_definitions );

        foreach ( $created_pages as $page_key => $page_info ) {
            $post_id = isset( $page_info['post_id'] ) ? absint( $page_info['post_id'] ) : 0;
            if ( $post_id <= 0 ) {
                continue;
            }

            $modules = isset( $page_info['modules'] ) && is_array( $page_info['modules'] ) ? $page_info['modules'] : array();
            if ( empty( $modules ) ) {
                continue;
            }

            $resolved_modules = $this->replace_package_placeholders_in_value( $modules, $page_url_map, $system_url_map );
            if ( $resolved_modules === $modules ) {
                continue;
            }

            update_post_meta( $post_id, '_developer_starter_modules', $resolved_modules );

            $messages[] = sprintf(
                /* translators: %s: page key */
                __( '页面 %s 的包内链接已自动解析。', 'developer-starter' ),
                sanitize_key( (string) $page_key )
            );
        }

        return $messages;
    }

    /**
     * 通过页面模板兜底查找系统页。
     *
     * @param string $template 模板路径。
     * @return int
     */
    public function find_page_id_by_template( $template ) {
        $query = get_posts(
            array(
                'post_type'      => 'page',
                'post_status'    => array( 'publish', 'draft', 'private' ),
                'posts_per_page' => 1,
                'fields'         => 'ids',
                'meta_key'       => '_wp_page_template',
                'meta_value'     => $template,
            )
        );

        if ( empty( $query ) ) {
            return 0;
        }

        return absint( $query[0] );
    }

    /**
     * 获取当前主题允许识别的标识集合。
     *
     * @return array<int,string>
     */
    public function get_supported_theme_identifiers() {
        $identifiers = array(
            'qiling',
            'developer-starter',
            'developer_starter',
            sanitize_key( (string) get_template() ),
            sanitize_key( (string) get_stylesheet() ),
        );

        return array_values( array_unique( array_filter( array_map( 'strval', $identifiers ) ) ) );
    }

    /**
     * 递归收集占位链接引用。
     *
     * @param mixed                         $value 任意数据。
     * @param array<int,array<string,string>> $references 引用收集器。
     * @return void
     */
    private function collect_placeholder_references( $value, &$references ) {
        if ( is_array( $value ) ) {
            foreach ( $value as $child ) {
                $this->collect_placeholder_references( $child, $references );
            }
            return;
        }

        if ( ! is_string( $value ) || strpos( $value, 'qiling://' ) === false ) {
            return;
        }

        if ( preg_match_all( '/qiling:\/\/page\/([a-z0-9_\-]+)/i', $value, $matches ) ) {
            foreach ( $matches[1] as $target ) {
                $references[] = array(
                    'type'   => 'page',
                    'target' => sanitize_key( (string) $target ),
                );
            }
        }

        if ( preg_match_all( '/qiling:\/\/system\/([a-z0-9_\-]+)/i', $value, $matches ) ) {
            foreach ( $matches[1] as $target ) {
                $references[] = array(
                    'type'   => 'system',
                    'target' => sanitize_key( (string) $target ),
                );
            }
        }
    }

    /**
     * 递归替换页面包里的占位链接。
     *
     * @param mixed                $value          原始值。
     * @param array<string,string> $page_url_map   页面引用映射。
     * @param array<string,string> $system_url_map 系统引用映射。
     * @return mixed
     */
    private function replace_package_placeholders_in_value( $value, $page_url_map, $system_url_map ) {
        if ( is_array( $value ) ) {
            foreach ( $value as $key => $child ) {
                $value[ $key ] = $this->replace_package_placeholders_in_value( $child, $page_url_map, $system_url_map );
            }
            return $value;
        }

        if ( ! is_string( $value ) || strpos( $value, 'qiling://' ) === false ) {
            return $value;
        }

        $value = preg_replace_callback(
            '/qiling:\/\/page\/([a-z0-9_\-]+)/i',
            function ( $matches ) use ( $page_url_map ) {
                $page_key = isset( $matches[1] ) ? sanitize_key( (string) $matches[1] ) : '';
                if ( '' === $page_key || ! isset( $page_url_map[ $page_key ] ) ) {
                    return $matches[0];
                }

                return $page_url_map[ $page_key ];
            },
            $value
        );

        $value = preg_replace_callback(
            '/qiling:\/\/system\/([a-z0-9_\-]+)/i',
            function ( $matches ) use ( $system_url_map ) {
                $target = isset( $matches[1] ) ? sanitize_key( (string) $matches[1] ) : '';
                if ( '' === $target || ! isset( $system_url_map[ $target ] ) ) {
                    return $matches[0];
                }

                return $system_url_map[ $target ];
            },
            $value
        );

        return $value;
    }

    /**
     * 构建页面 key -> URL 映射。
     *
     * @param array<string,int> $page_key_map 页面映射。
     * @return array<string,string>
     */
    private function build_page_url_map( $page_key_map ) {
        $map = array();

        foreach ( $page_key_map as $page_key => $page_id ) {
            $page_id = absint( $page_id );
            if ( $page_id <= 0 ) {
                continue;
            }

            $url = get_permalink( $page_id );
            if ( ! $url ) {
                continue;
            }

            $map[ sanitize_key( (string) $page_key ) ] = $url;
        }

        return $map;
    }

    /**
     * 构建系统页面引用映射。
     *
     * @param array<int,array<string,mixed>> $reserved_page_definitions 保留页定义。
     * @return array<string,string>
     */
    private function build_system_url_map( $reserved_page_definitions = array() ) {
        $map = array();

        foreach ( $reserved_page_definitions as $definition ) {
            $page = null;
            if ( ! empty( $definition['page_id'] ) ) {
                $page = get_post( absint( $definition['page_id'] ) );
            }

            if ( ! $page instanceof \WP_Post && ! empty( $definition['slug'] ) ) {
                $page = get_page_by_path( $definition['slug'], OBJECT, 'page' );
            }

            if ( ! $page instanceof \WP_Post ) {
                continue;
            }

            $url = get_permalink( $page->ID );
            if ( ! $url ) {
                continue;
            }

            $slug = sanitize_key( str_replace( '-', '_', (string) $definition['slug'] ) );
            $map[ sanitize_key( (string) $definition['slug'] ) ] = $url;
            if ( '' !== $slug ) {
                $map[ $slug ] = $url;
            }
        }

        if ( isset( $map['account_center'] ) && ! isset( $map['account'] ) ) {
            $map['account'] = $map['account_center'];
        }
        if ( isset( $map['forgot_password'] ) && ! isset( $map['forgot'] ) ) {
            $map['forgot'] = $map['forgot_password'];
        }

        return $map;
    }
}
