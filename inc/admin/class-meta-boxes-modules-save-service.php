<?php
/**
 * Meta Boxes - Modules save service.
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Admin;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Meta_Boxes_Modules_Save_Service {
    public function handle_modules_save( $post_id, $post_data = array(), $request_data = array(), $callbacks = array() ) {
        $post_data = is_array( $post_data ) ? $post_data : array();
        $request_data = is_array( $request_data ) ? $request_data : array();
        $callbacks = is_array( $callbacks ) ? $callbacks : array();

        if ( ! isset( $post_data['modules_nonce'] ) || ! wp_verify_nonce( $post_data['modules_nonce'], 'developer_starter_modules_nonce' ) ) {
            return;
        }

        do_action( 'developer_starter_before_save_modules_meta', $post_id, $post_data );

        $modules_ui_loaded = isset( $post_data['developer_starter_modules_ui_loaded'] ) && wp_unslash( (string) $post_data['developer_starter_modules_ui_loaded'] ) === '1';
        $has_modules_payload = isset( $post_data['developer_starter_modules_payload'] ) && '' !== trim( (string) wp_unslash( $post_data['developer_starter_modules_payload'] ) );
        $should_preserve_existing_modules = ! $modules_ui_loaded && ! isset( $post_data['modules'] ) && ! $has_modules_payload;

        if ( ! $should_preserve_existing_modules ) {
            $modules = array();

            if ( $has_modules_payload ) {
                $normalize_payload_callback = isset( $callbacks['normalize_modules_payload_callback'] ) ? $callbacks['normalize_modules_payload_callback'] : null;
                if ( is_callable( $normalize_payload_callback ) ) {
                    $modules = call_user_func( $normalize_payload_callback, wp_unslash( (string) $post_data['developer_starter_modules_payload'] ) );
                }
            } elseif ( isset( $post_data['modules'] ) && is_array( $post_data['modules'] ) ) {
                $normalize_modules_callback = isset( $callbacks['normalize_modules_callback'] ) ? $callbacks['normalize_modules_callback'] : null;
                if ( is_callable( $normalize_modules_callback ) ) {
                    $modules = call_user_func( $normalize_modules_callback, wp_unslash( $post_data['modules'] ) );
                }
            }

            $modules = apply_filters( 'developer_starter_modules_before_save', $modules, $post_id, $post_data );
            $saved_modules = $this->persist_modules( $post_id, $modules, $post_data, $request_data );

            if ( is_array( $saved_modules ) ) {
                do_action( 'developer_starter_modules_saved', $post_id, $saved_modules );
            }
        }

        $enable_scroll_reveal = isset( $post_data['developer_starter_enable_scroll_reveal'] ) ? '1' : '0';
        update_post_meta( $post_id, '_developer_starter_enable_scroll_reveal', $enable_scroll_reveal );
    }

    public function persist_imported_page_package_settings( $post_id, $post_data = array(), $callbacks = array() ) {
        $post_data = is_array( $post_data ) ? $post_data : array();
        $callbacks = is_array( $callbacks ) ? $callbacks : array();

        $page_package_imported = isset( $post_data['developer_starter_page_package_imported'] ) && '1' === wp_unslash( (string) $post_data['developer_starter_page_package_imported'] );
        if ( ! $page_package_imported ) {
            return;
        }

        $normalize_template_callback = isset( $callbacks['normalize_page_package_template_callback'] ) ? $callbacks['normalize_page_package_template_callback'] : null;
        $default_template_callback = isset( $callbacks['default_page_package_template_callback'] ) ? $callbacks['default_page_package_template_callback'] : null;
        $normalize_bool_callback = isset( $callbacks['normalize_page_package_bool_callback'] ) ? $callbacks['normalize_page_package_bool_callback'] : null;

        $page_package_template = isset( $post_data['developer_starter_page_package_template'] )
            ? $this->maybe_call( $normalize_template_callback, wp_unslash( $post_data['developer_starter_page_package_template'] ) )
            : $this->maybe_call( $default_template_callback );

        $hide_page_header_defined = isset( $post_data['developer_starter_page_package_hide_header_defined'] )
            && '1' === wp_unslash( (string) $post_data['developer_starter_page_package_hide_header_defined'] );

        $page_settings = array(
            'pageTemplate' => $page_package_template,
            'transparentHeader' => isset( $post_data['developer_starter_page_package_transparent_header'] )
                ? (bool) $this->maybe_call( $normalize_bool_callback, wp_unslash( $post_data['developer_starter_page_package_transparent_header'] ), false )
                : false,
            'enableScrollReveal' => isset( $post_data['developer_starter_enable_scroll_reveal'] ) && '1' === wp_unslash( (string) $post_data['developer_starter_enable_scroll_reveal'] ),
        );

        if ( $hide_page_header_defined ) {
            $page_settings['hidePageHeader'] = isset( $post_data['developer_starter_page_package_hide_header'] )
                ? (bool) $this->maybe_call( $normalize_bool_callback, wp_unslash( $post_data['developer_starter_page_package_hide_header'] ), false )
                : false;
        }

        if ( isset( $post_data['developer_starter_page_package_design'] ) ) {
            $raw_page_design = wp_unslash( (string) $post_data['developer_starter_page_package_design'] );
            if ( '' !== trim( $raw_page_design ) ) {
                $decoded_page_design = json_decode( $raw_page_design, true );
                if ( is_array( $decoded_page_design ) ) {
                    $page_settings['pageDesign'] = $decoded_page_design;
                }
            }
        }

        if ( isset( $post_data['developer_starter_page_package_footer'] ) ) {
            $raw_footer_settings = wp_unslash( (string) $post_data['developer_starter_page_package_footer'] );
            if ( '' !== trim( $raw_footer_settings ) ) {
                $decoded_footer_settings = json_decode( $raw_footer_settings, true );
                if ( is_array( $decoded_footer_settings ) ) {
                    $page_settings['footer'] = $decoded_footer_settings;
                }
            }
        }

        if ( isset( $post_data['developer_starter_page_package_region_decoration'] ) ) {
            $raw_region_settings = wp_unslash( (string) $post_data['developer_starter_page_package_region_decoration'] );
            if ( '' !== trim( $raw_region_settings ) ) {
                $decoded_region_settings = json_decode( $raw_region_settings, true );
                if ( is_array( $decoded_region_settings ) ) {
                    $page_settings['regionDecoration'] = $decoded_region_settings;
                }
            }
        }

        if ( isset( $post_data['developer_starter_page_package_visual_style'] ) ) {
            $raw_visual_style = wp_unslash( (string) $post_data['developer_starter_page_package_visual_style'] );
            if ( '' !== trim( $raw_visual_style ) ) {
                $decoded_visual_style = json_decode( $raw_visual_style, true );
                if ( is_array( $decoded_visual_style ) ) {
                    $page_settings['visualStyle'] = $decoded_visual_style;
                }
            }
        }

        if ( class_exists( '\Developer_Starter\Core\AI_Decorator' ) ) {
            \Developer_Starter\Core\AI_Decorator::get_instance()->persist_post_page_settings( $post_id, $page_settings );
        }
    }

    private function persist_modules( $post_id, $modules, $post_data, $request_data ) {
        $modules = is_array( $modules ) ? $modules : array();
        $post_data = is_array( $post_data ) ? $post_data : array();
        $request_data = is_array( $request_data ) ? $request_data : array();

        if ( empty( $modules ) ) {
            $template = $this->resolve_requested_page_template( $post_id, $post_data, $request_data );
            $auto_filled = false;

            if (
                function_exists( 'developer_starter_maybe_fill_default_modules_for_page_template' )
                && is_string( $template )
                && '' !== $template
                && 'default' !== $template
            ) {
                $auto_filled = developer_starter_maybe_fill_default_modules_for_page_template( $post_id, $template );
                if ( $auto_filled ) {
                    $saved_modules = get_post_meta( $post_id, '_developer_starter_modules', true );
                    return is_array( $saved_modules ) ? $saved_modules : array();
                }
            }

            if ( ! $auto_filled ) {
                $saved_modules = $this->maybe_persist_empty_modules_by_template( $post_id, $template, $modules );
                if ( false === $saved_modules ) {
                    return null;
                }
                if ( null !== $saved_modules ) {
                    return $saved_modules;
                }
            }
        }

        update_post_meta( $post_id, '_developer_starter_modules', $modules );
        return $modules;
    }

    private function resolve_requested_page_template( $post_id, $post_data, $request_data ) {
        $template = get_post_meta( $post_id, '_wp_page_template', true );

        if ( ( ! is_string( $template ) || '' === trim( $template ) || 'default' === $template ) && function_exists( 'wp_unslash' ) ) {
            if ( isset( $post_data['meta_input'] ) && is_array( $post_data['meta_input'] ) && isset( $post_data['meta_input']['_wp_page_template'] ) ) {
                $raw_meta_template = wp_unslash( $post_data['meta_input']['_wp_page_template'] );
                if ( is_scalar( $raw_meta_template ) && '' !== trim( (string) $raw_meta_template ) ) {
                    $template = (string) $raw_meta_template;
                }
            }

            foreach ( array( 'page_template', '_wp_page_template', 'template' ) as $request_key ) {
                if ( is_string( $template ) && '' !== trim( $template ) && 'default' !== $template ) {
                    break;
                }

                if ( ! isset( $request_data[ $request_key ] ) ) {
                    continue;
                }

                $raw_template = wp_unslash( $request_data[ $request_key ] );
                if ( is_scalar( $raw_template ) && '' !== trim( (string) $raw_template ) ) {
                    $template = (string) $raw_template;
                    break;
                }
            }
        }

        if ( function_exists( 'developer_starter_normalize_page_template_slug' ) ) {
            $template = developer_starter_normalize_page_template_slug( $template );
        }

        return $template;
    }

    private function maybe_persist_empty_modules_by_template( $post_id, $template, $modules ) {
        $solutions_filled = get_post_meta( $post_id, '_solutions_modules_filled', true );
        $landing_filled = get_post_meta( $post_id, '_landing_modules_filled', true );
        $features_showcase_filled = get_post_meta( $post_id, '_features_showcase_modules_filled', true );
        $software_intro_filled = get_post_meta( $post_id, '_software_intro_modules_filled', true );
        $resource_search_filled = get_post_meta( $post_id, '_resource_search_modules_filled', true );
        $data_showcase_filled = get_post_meta( $post_id, '_data_showcase_modules_filled', true );
        $news_center_filled = get_post_meta( $post_id, '_qiling_news_center_modules_filled', true );
        $products_center_filled = get_post_meta( $post_id, '_qiling_products_center_modules_filled', true );
        $cases_center_filled = get_post_meta( $post_id, '_qiling_cases_center_modules_filled', true );

        if ( $template === 'templates/template-solutions.php' && ! $solutions_filled ) {
            return false;
        } elseif ( $template === 'templates/template-landing.php' && ! $landing_filled ) {
            return false;
        } elseif ( $template === 'templates/template-features-showcase.php' && ! $features_showcase_filled ) {
            return false;
        } elseif ( $template === 'templates/template-software-intro.php' && ! $software_intro_filled ) {
            return false;
        } elseif ( $template === 'templates/template-resource-search.php' && ! $resource_search_filled ) {
            return false;
        } elseif ( $template === 'templates/template-news.php' && ! $news_center_filled ) {
            return false;
        } elseif ( $template === 'templates/template-products.php' && ! $products_center_filled ) {
            return false;
        } elseif ( $template === 'templates/template-cases.php' && ! $cases_center_filled ) {
            return false;
        } elseif ( $template === 'templates/template-data-showcase.php' && ! $data_showcase_filled ) {
            return $this->maybe_fill_data_showcase_default_modules( $post_id, $modules );
        }

        update_post_meta( $post_id, '_developer_starter_modules', $modules );
        return $modules;
    }

    private function maybe_fill_data_showcase_default_modules( $post_id, $modules ) {
        if (
            ! class_exists( '\Developer_Starter\Core\Data_Showcase_Page_Creator' )
            && function_exists( 'developer_starter_maybe_load_page_creator_class' )
        ) {
            developer_starter_maybe_load_page_creator_class( '\Developer_Starter\Core\Data_Showcase_Page_Creator' );
        }

        if ( class_exists( '\Developer_Starter\Core\Data_Showcase_Page_Creator' ) ) {
            $creator = new \Developer_Starter\Core\Data_Showcase_Page_Creator();
            $creator->set_default_modules( $post_id );
            update_post_meta( $post_id, '_data_showcase_modules_filled', '1' );
            $saved_modules = get_post_meta( $post_id, '_developer_starter_modules', true );
            return is_array( $saved_modules ) ? $saved_modules : array();
        }

        update_post_meta( $post_id, '_developer_starter_modules', $modules );
        return $modules;
    }

    private function maybe_call( $callback ) {
        $args = func_get_args();
        array_shift( $args );

        if ( ! is_callable( $callback ) ) {
            return null;
        }

        return call_user_func_array( $callback, $args );
    }
}
