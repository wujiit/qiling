<?php
/**
 * Setup wizard menu and basic settings service.
 *
 * Phase 4 creates/reuses a primary menu when the primary location is empty
 * and writes lightweight theme-owned settings. It keeps existing menus and
 * non-empty settings by default, and never writes third-party plugin options.
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Core;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Setup_Wizard_Settings_Service {

    const MENU_LOCATION = 'primary';
    const MENU_NAME     = '启灵主菜单';

    /**
     * @var Setup_Wizard_State
     */
    private $state_service;

    /**
     * @var Setup_Wizard_Reuse_Service
     */
    private $reuse_service;

    /**
     * @var Setup_Wizard_Presets
     */
    private $presets;

    /**
     * @param Setup_Wizard_State|null         $state_service State service.
     * @param Setup_Wizard_Reuse_Service|null $reuse_service Reuse service.
     * @param Setup_Wizard_Presets|null       $presets Preset service.
     */
    public function __construct( $state_service = null, $reuse_service = null, $presets = null ) {
        $this->state_service = $state_service instanceof Setup_Wizard_State
            ? $state_service
            : Setup_Wizard_State::get_instance();

        $this->reuse_service = $reuse_service instanceof Setup_Wizard_Reuse_Service
            ? $reuse_service
            : new Setup_Wizard_Reuse_Service();

        $this->presets = $presets instanceof Setup_Wizard_Presets
            ? $presets
            : Setup_Wizard_Presets::get_instance();
    }

    /**
     * Apply menu and basic settings.
     *
     * @param array<string,mixed> $args Arguments.
     * @return array<string,mixed>
     */
    public function apply( $args ) {
        if ( ! current_user_can( 'manage_options' ) ) {
            return $this->result_with_error( 'forbidden', __( '权限不足，无法应用基础设置。', 'developer-starter' ) );
        }

        $args = is_array( $args ) ? $args : array();
        $preset = $this->presets->resolve(
            isset( $args['site_type'] ) ? $args['site_type'] : '',
            isset( $args['industry'] ) ? $args['industry'] : ''
        );

        $overwrite = ! empty( $args['overwrite_existing'] );
        $selected_pages = isset( $args['selected_pages'] ) ? $this->sanitize_key_list( $args['selected_pages'] ) : array();
        if ( empty( $selected_pages ) ) {
            $selected_pages = $this->items_to_keys( isset( $preset['recommended_pages'] ) ? $preset['recommended_pages'] : array() );
        }

        $result = array(
            'menu'     => array( 'status' => 'skipped', 'id' => 0, 'items_added' => 0 ),
            'settings' => array( 'updated' => array(), 'skipped' => array() ),
            'errors'   => array(),
        );

        if ( ! empty( $args['create_primary_menu'] ) ) {
            $result['menu'] = $this->apply_primary_menu( $selected_pages );
        }

        $settings_result = $this->apply_basic_settings(
            isset( $args['brand'] ) && is_array( $args['brand'] ) ? $args['brand'] : array(),
            isset( $args['contact'] ) && is_array( $args['contact'] ) ? $args['contact'] : array(),
            isset( $args['seo'] ) && is_array( $args['seo'] ) ? $args['seo'] : array(),
            $overwrite
        );

        $result['settings'] = $settings_result;

        $this->state_service->save_state(
            array(
                'site_type'            => isset( $preset['site_type'] ) ? $preset['site_type'] : '',
                'industry'             => isset( $preset['industry'] ) ? $preset['industry'] : '',
                'enabled_theme_models' => isset( $preset['content_model_keys'] ) ? $preset['content_model_keys'] : array(),
            )
        );

        return $result;
    }

    /**
     * Create/reuse a primary menu only when the primary location is empty.
     *
     * @param array<int,string> $selected_pages Page keys.
     * @return array<string,mixed>
     */
    private function apply_primary_menu( $selected_pages ) {
        $locations = get_nav_menu_locations();
        $locations = is_array( $locations ) ? $locations : array();

        if ( ! empty( $locations[ self::MENU_LOCATION ] ) && wp_get_nav_menu_object( absint( $locations[ self::MENU_LOCATION ] ) ) ) {
            return array(
                'status' => 'kept_existing',
                'id'     => absint( $locations[ self::MENU_LOCATION ] ),
                'items_added' => 0,
            );
        }

        $menu = wp_get_nav_menu_object( self::MENU_NAME );
        $created = false;
        if ( ! $menu || is_wp_error( $menu ) ) {
            $menu_id = wp_create_nav_menu( self::MENU_NAME );
            if ( is_wp_error( $menu_id ) ) {
                return array(
                    'status'  => 'error',
                    'id'      => 0,
                    'message' => $menu_id->get_error_message(),
                    'items_added' => 0,
                );
            }

            $created = true;
            $menu_id = absint( $menu_id );
        } else {
            $menu_id = isset( $menu->term_id ) ? absint( $menu->term_id ) : 0;
        }

        if ( $menu_id <= 0 ) {
            return array(
                'status' => 'error',
                'id'     => 0,
                'message'=> __( '主菜单 ID 无效。', 'developer-starter' ),
                'items_added' => 0,
            );
        }

        $items_added = $this->add_pages_to_menu( $menu_id, $selected_pages );
        $locations[ self::MENU_LOCATION ] = $menu_id;
        set_theme_mod( 'nav_menu_locations', $locations );

        if ( $created ) {
            $state = $this->state_service->get_state();
            $run_id = isset( $state['last_run_id'] ) ? (string) $state['last_run_id'] : '';
            $this->state_service->record_created_menu( self::MENU_LOCATION, $menu_id, $run_id );
        }

        return array(
            'status'      => $created ? 'created' : 'assigned',
            'id'          => $menu_id,
            'items_added' => $items_added,
        );
    }

    /**
     * @param int               $menu_id Menu id.
     * @param array<int,string> $selected_pages Page keys.
     * @return int
     */
    private function add_pages_to_menu( $menu_id, $selected_pages ) {
        $menu_id = absint( $menu_id );
        if ( $menu_id <= 0 ) {
            return 0;
        }

        $state = $this->state_service->get_state();
        $page_catalog = $this->presets->get_page_catalog_items();
        $template_catalog = $this->presets->get_template_catalog_items();
        $added = 0;

        foreach ( $this->filter_menu_page_keys( $selected_pages ) as $page_key ) {
            if ( ! isset( $page_catalog[ $page_key ] ) ) {
                continue;
            }

            $page = $this->find_reusable_page_for_menu( $page_key, $page_catalog[ $page_key ], $template_catalog, $state );
            if ( empty( $page['id'] ) ) {
                continue;
            }

            $page_id = absint( $page['id'] );
            if ( $this->reuse_service->menu_contains_page( $menu_id, $page_id ) ) {
                continue;
            }

            $item_id = wp_update_nav_menu_item(
                $menu_id,
                0,
                array(
                    'menu-item-object-id' => $page_id,
                    'menu-item-object'    => 'page',
                    'menu-item-type'      => 'post_type',
                    'menu-item-title'     => isset( $page['title'] ) ? (string) $page['title'] : '',
                    'menu-item-status'    => 'publish',
                )
            );

            if ( ! is_wp_error( $item_id ) && absint( $item_id ) > 0 ) {
                $added++;
            }
        }

        return $added;
    }

    /**
     * @param string                            $page_key Page key.
     * @param array<string,mixed>               $page_item Page item.
     * @param array<string,array<string,mixed>> $template_catalog Template catalog.
     * @param array<string,mixed>               $state State.
     * @return array<string,mixed>
     */
    private function find_reusable_page_for_menu( $page_key, $page_item, $template_catalog, $state ) {
        $template_id = isset( $page_item['template_id'] ) ? sanitize_key( (string) $page_item['template_id'] ) : '';
        $template = '';
        if ( '' !== $template_id && isset( $template_catalog[ $template_id ]['template'] ) ) {
            $template = (string) $template_catalog[ $template_id ]['template'];
        }

        return $this->reuse_service->find_reusable_page(
            array(
                'page_key' => $page_key,
                'state'    => $state,
                'slug'     => isset( $page_item['slug'] ) ? (string) $page_item['slug'] : $page_key,
                'title'    => isset( $page_item['label'] ) ? (string) $page_item['label'] : '',
                'template' => $template,
            )
        );
    }

    /**
     * @param array<string,mixed> $brand Brand values.
     * @param array<string,mixed> $contact Contact/footer values.
     * @param array<string,mixed> $seo SEO values.
     * @param bool                $overwrite Whether to overwrite existing values.
     * @return array<string,array<int,string>>
     */
    private function apply_basic_settings( $brand, $contact, $seo, $overwrite = false ) {
        $updated = array();
        $skipped = array();

        $this->maybe_update_core_option( 'blogname', isset( $brand['site_title'] ) ? $brand['site_title'] : '', $overwrite, $updated, $skipped );
        $this->maybe_update_core_option( 'blogdescription', isset( $brand['tagline'] ) ? $brand['tagline'] : '', $overwrite, $updated, $skipped );

        $options = get_option( 'developer_starter_options', array() );
        if ( ! is_array( $options ) ) {
            $options = array();
        }

        $patch = array();
        $this->add_theme_option_patch( $patch, $options, 'site_logo', isset( $brand['site_logo'] ) ? esc_url_raw( (string) $brand['site_logo'] ) : '', $overwrite, $updated, $skipped );
        $this->add_theme_option_patch( $patch, $options, 'mobile_logo', isset( $brand['mobile_logo'] ) ? esc_url_raw( (string) $brand['mobile_logo'] ) : '', $overwrite, $updated, $skipped );

        $primary = isset( $brand['primary_color'] ) ? sanitize_hex_color( (string) $brand['primary_color'] ) : '';
        if ( $primary ) {
            $this->add_theme_option_patch( $patch, $options, 'primary_color', $primary, $overwrite, $updated, $skipped );
            $this->add_theme_option_patch( $patch, $options, 'design_primary_color', $primary, $overwrite, $updated, $skipped );
        }

        foreach ( $this->get_contact_option_map() as $input_key => $option_key ) {
            $value = isset( $contact[ $input_key ] ) ? $this->sanitize_contact_value( $input_key, $contact[ $input_key ] ) : '';
            $this->add_theme_option_patch( $patch, $options, $option_key, $value, $overwrite, $updated, $skipped );
        }

        $this->add_theme_option_patch( $patch, $options, 'default_title', isset( $seo['default_title'] ) ? sanitize_text_field( (string) $seo['default_title'] ) : '', $overwrite, $updated, $skipped );
        $this->add_theme_option_patch( $patch, $options, 'default_description', isset( $seo['default_description'] ) ? sanitize_textarea_field( (string) $seo['default_description'] ) : '', $overwrite, $updated, $skipped );
        $this->add_theme_option_patch( $patch, $options, 'default_keywords', isset( $seo['default_keywords'] ) ? sanitize_text_field( (string) $seo['default_keywords'] ) : '', $overwrite, $updated, $skipped );

        if ( ! empty( $seo['schema_engine_enable'] ) ) {
            $this->add_theme_option_patch( $patch, $options, 'schema_engine_enable', '1', $overwrite, $updated, $skipped );
        }

        if ( ! empty( $patch ) ) {
            update_option( 'developer_starter_options', array_merge( $options, $patch ) );
        }

        return array(
            'updated' => array_values( array_unique( $updated ) ),
            'skipped' => array_values( array_unique( $skipped ) ),
        );
    }

    /**
     * @return array<string,string>
     */
    private function get_contact_option_map() {
        return array(
            'company_name'          => 'company_name',
            'company_phone'         => 'company_phone',
            'company_email'         => 'company_email',
            'company_address'       => 'company_address',
            'company_working_hours' => 'company_working_hours',
            'company_brief'         => 'company_brief',
            'icp_number'            => 'icp_number',
            'police_number'         => 'police_number',
        );
    }

    /**
     * @param string            $option Option key.
     * @param mixed             $value Value.
     * @param bool              $overwrite Overwrite existing value.
     * @param array<int,string> $updated Updated bucket.
     * @param array<int,string> $skipped Skipped bucket.
     * @return void
     */
    private function maybe_update_core_option( $option, $value, $overwrite, &$updated, &$skipped ) {
        $option = sanitize_key( (string) $option );
        if ( ! in_array( $option, array( 'blogname', 'blogdescription' ), true ) ) {
            return;
        }

        $value = sanitize_text_field( (string) $value );
        if ( '' === $value ) {
            return;
        }

        $current = get_option( $option, '' );
        if ( ! $overwrite && ! $this->is_empty_or_default_core_value( $option, $current ) ) {
            $skipped[] = $option;
            return;
        }

        if ( (string) $current !== $value ) {
            if ( 'blogname' === $option ) {
                update_option( 'blogname', $value );
            } else {
                update_option( 'blogdescription', $value );
            }
            $updated[] = $option;
        }
    }

    /**
     * @param array<string,mixed> $patch Patch.
     * @param array<string,mixed> $options Existing options.
     * @param string              $key Option key.
     * @param mixed               $value Value.
     * @param bool                $overwrite Overwrite existing value.
     * @param array<int,string>   $updated Updated bucket.
     * @param array<int,string>   $skipped Skipped bucket.
     * @return void
     */
    private function add_theme_option_patch( &$patch, $options, $key, $value, $overwrite, &$updated, &$skipped ) {
        $key = sanitize_key( (string) $key );
        if ( '' === $key || '' === (string) $value ) {
            return;
        }

        $current = isset( $options[ $key ] ) ? $options[ $key ] : '';
        if ( ! $overwrite && '' !== trim( (string) $current ) ) {
            $skipped[] = $key;
            return;
        }

        if ( (string) $current !== (string) $value ) {
            $patch[ $key ] = $value;
            $updated[] = $key;
        }
    }

    /**
     * @param string $option Option key.
     * @param mixed  $value Current value.
     * @return bool
     */
    private function is_empty_or_default_core_value( $option, $value ) {
        $value = trim( (string) $value );
        if ( '' === $value ) {
            return true;
        }

        if ( 'blogdescription' === $option && in_array( $value, array( 'Just another WordPress site', '又一个 WordPress 站点' ), true ) ) {
            return true;
        }

        if ( 'blogname' === $option && in_array( $value, array( 'WordPress', 'My Blog', '我的博客' ), true ) ) {
            return true;
        }

        return false;
    }

    /**
     * @param string $key Input key.
     * @param mixed  $value Raw value.
     * @return string
     */
    private function sanitize_contact_value( $key, $value ) {
        if ( 'company_email' === $key ) {
            return sanitize_email( (string) $value );
        }

        if ( in_array( $key, array( 'company_address', 'company_brief' ), true ) ) {
            return sanitize_textarea_field( (string) $value );
        }

        return sanitize_text_field( (string) $value );
    }

    /**
     * @param array<int,string> $page_keys Page keys.
     * @return array<int,string>
     */
    private function filter_menu_page_keys( $page_keys ) {
        $blocked = array(
            'resource_search' => true,
            'booking'         => true,
            'faq'             => true,
            'login'           => true,
            'register'        => true,
            'account'         => true,
        );

        $clean = array();
        foreach ( $page_keys as $page_key ) {
            $page_key = sanitize_key( (string) $page_key );
            if ( '' !== $page_key && empty( $blocked[ $page_key ] ) ) {
                $clean[] = $page_key;
            }
        }

        return array_values( array_unique( $clean ) );
    }

    /**
     * @param mixed $list Raw list.
     * @return array<int,string>
     */
    private function sanitize_key_list( $list ) {
        $clean = array();
        foreach ( (array) $list as $value ) {
            $value = sanitize_key( (string) $value );
            if ( '' !== $value ) {
                $clean[] = $value;
            }
        }

        return array_values( array_unique( $clean ) );
    }

    /**
     * @param array<int,array<string,mixed>>|mixed $items Items.
     * @return array<int,string>
     */
    private function items_to_keys( $items ) {
        $keys = array();
        foreach ( (array) $items as $item ) {
            if ( is_array( $item ) && ! empty( $item['id'] ) ) {
                $keys[] = sanitize_key( (string) $item['id'] );
            }
        }

        return array_values( array_unique( array_filter( $keys ) ) );
    }

    /**
     * @param string $code Error code.
     * @param string $message Error message.
     * @return array<string,mixed>
     */
    private function result_with_error( $code, $message ) {
        return array(
            'menu'     => array( 'status' => 'skipped', 'id' => 0, 'items_added' => 0 ),
            'settings' => array( 'updated' => array(), 'skipped' => array() ),
            'errors'   => array(
                sanitize_key( (string) $code ) => sanitize_text_field( (string) $message ),
            ),
        );
    }
}
