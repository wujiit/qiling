<?php
/**
 * Setup wizard page generation service.
 *
 * Phase 3 creates/reuses pages, sets the front page when the site has no
 * static front page yet, binds page templates for newly created pages and
 * imports homepage modules through the existing official template package
 * service. It does not create menus or touch third-party plugin settings.
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Core;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Setup_Wizard_Import_Service {

    const MAX_SELECTED_PAGES = 30;

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
     * Generate the selected pages.
     *
     * @param array<string,mixed> $args Import arguments.
     * @return array<string,mixed>
     */
    public function generate_pages( $args ) {
        if ( ! current_user_can( 'manage_options' ) ) {
            return $this->result_with_error( 'forbidden', __( '权限不足，无法生成页面。', 'developer-starter' ) );
        }

        $args = is_array( $args ) ? $args : array();
        $preset = $this->presets->resolve(
            isset( $args['site_type'] ) ? $args['site_type'] : '',
            isset( $args['industry'] ) ? $args['industry'] : ''
        );

        $template_id = isset( $args['template_id'] ) ? sanitize_key( (string) $args['template_id'] ) : '';
        if ( '' === $template_id ) {
            $template_id = $this->get_default_template_id( $preset );
        }

        $has_selected_pages = array_key_exists( 'selected_pages', $args );
        $selected_pages = $this->sanitize_selected_pages( $has_selected_pages ? $args['selected_pages'] : array() );
        if ( ! $has_selected_pages && empty( $selected_pages ) ) {
            $selected_pages = $this->items_to_keys( isset( $preset['recommended_pages'] ) ? $preset['recommended_pages'] : array() );
        }

        $include_auth_pages = isset( $args['include_auth_pages'] ) ? (bool) $args['include_auth_pages'] : true;
        $set_front_page     = isset( $args['set_front_page'] ) ? (bool) $args['set_front_page'] : true;
        $import_home        = isset( $args['import_home_modules'] ) ? (bool) $args['import_home_modules'] : true;

        $run_id = $this->state_service->start_run();
        $state  = $this->state_service->get_state();

        $result = array(
            'run_id'          => $run_id,
            'site_type'       => isset( $preset['site_type'] ) ? $preset['site_type'] : '',
            'industry'        => isset( $preset['industry'] ) ? $preset['industry'] : '',
            'template_id'     => $template_id,
            'created_pages'   => array(),
            'reused_pages'    => array(),
            'skipped_pages'   => array(),
            'errors'          => array(),
            'front_page'      => array(
                'status' => 'skipped',
                'id'     => 0,
            ),
            'modules_filled'  => false,
        );

        $page_catalog     = $this->presets->get_page_catalog_items();
        $template_catalog = $this->presets->get_template_catalog_items();

        foreach ( $selected_pages as $page_key ) {
            if ( ! isset( $page_catalog[ $page_key ] ) ) {
                $result['skipped_pages'][ $page_key ] = __( '页面预设不存在。', 'developer-starter' );
                continue;
            }

            $definition = $this->build_page_definition( $page_key, $page_catalog[ $page_key ], $template_catalog, $template_id );
            $page_result = $this->create_or_reuse_page(
                $page_key,
                $definition,
                $run_id,
                $state,
                ( 'home' === $page_key && $import_home )
            );

            $result = $this->merge_page_result( $result, $page_key, $page_result );
        }

        if ( $include_auth_pages ) {
            foreach ( $this->get_auth_page_definitions() as $page_key => $definition ) {
                $page_result = $this->create_or_reuse_page( $page_key, $definition, $run_id, $state, false );
                $result = $this->merge_page_result( $result, $page_key, $page_result );

                if ( ! empty( $page_result['id'] ) ) {
                    $this->sync_auth_page_option( $page_key, absint( $page_result['id'] ) );
                }
            }
        }

        if ( $set_front_page && ! empty( $result['created_pages']['home']['id'] ) ) {
            $result['front_page'] = $this->maybe_set_front_page( absint( $result['created_pages']['home']['id'] ) );
        } elseif ( $set_front_page && ! empty( $result['reused_pages']['home']['id'] ) ) {
            $result['front_page'] = $this->maybe_set_front_page( absint( $result['reused_pages']['home']['id'] ) );
        }

        $this->state_service->save_state(
            array(
                'site_type'              => isset( $preset['site_type'] ) ? $preset['site_type'] : '',
                'industry'               => isset( $preset['industry'] ) ? $preset['industry'] : '',
                'template_id'            => $template_id,
                'enabled_theme_models'   => isset( $preset['content_model_keys'] ) ? $preset['content_model_keys'] : array(),
            )
        );

        return $result;
    }

    /**
     * @param string              $page_key Page key.
     * @param array<string,mixed> $catalog_item Page catalog item.
     * @param array<string,array<string,mixed>> $template_catalog Template catalog.
     * @param string              $home_template_id Selected homepage template id.
     * @return array<string,mixed>
     */
    private function build_page_definition( $page_key, $catalog_item, $template_catalog, $home_template_id ) {
        $template_id = isset( $catalog_item['template_id'] ) ? sanitize_key( (string) $catalog_item['template_id'] ) : '';
        if ( 'home' === $page_key && '' !== $home_template_id && isset( $template_catalog[ $home_template_id ] ) ) {
            $template_id = $home_template_id;
        }

        $template = '';
        if ( '' !== $template_id && isset( $template_catalog[ $template_id ]['template'] ) ) {
            $template = (string) $template_catalog[ $template_id ]['template'];
        }

        return array(
            'title'    => isset( $catalog_item['label'] ) ? (string) $catalog_item['label'] : $this->humanize_key( $page_key ),
            'slug'     => isset( $catalog_item['slug'] ) ? (string) $catalog_item['slug'] : $page_key,
            'template' => $template,
        );
    }

    /**
     * @param string              $page_key Page key.
     * @param array<string,mixed> $definition Page definition.
     * @param string              $run_id Run id.
     * @param array<string,mixed> $state Current wizard state.
     * @param bool                $import_modules Whether to import modules when created.
     * @return array<string,mixed>
     */
    private function create_or_reuse_page( $page_key, $definition, $run_id, $state, $import_modules = false ) {
        $page_key = sanitize_key( (string) $page_key );
        $title    = isset( $definition['title'] ) ? sanitize_text_field( (string) $definition['title'] ) : $this->humanize_key( $page_key );
        $slug     = isset( $definition['slug'] ) ? sanitize_title( (string) $definition['slug'] ) : sanitize_title( $page_key );
        $template = isset( $definition['template'] ) ? $this->normalize_template( $definition['template'] ) : '';

        $reuse_args = array(
            'page_key' => $page_key,
            'state'    => $state,
            'template' => $template,
        );
        if ( empty( $definition['strict_template_reuse'] ) ) {
            $reuse_args['slug']  = $slug;
            $reuse_args['title'] = $title;
        }

        $reusable = $this->reuse_service->find_reusable_page( $reuse_args );

        if ( ! empty( $reusable['id'] ) ) {
            return array(
                'status' => 'reused',
                'id'     => absint( $reusable['id'] ),
                'title'  => isset( $reusable['title'] ) ? (string) $reusable['title'] : $title,
                'slug'   => isset( $reusable['slug'] ) ? (string) $reusable['slug'] : $slug,
                'source' => isset( $reusable['source'] ) ? (string) $reusable['source'] : 'existing',
            );
        }

        $post_data = array(
            'post_type'    => 'page',
            'post_status'  => 'publish',
            'post_title'   => $title,
            'post_name'    => $slug,
            'post_content' => '',
            'post_author'  => get_current_user_id() ? get_current_user_id() : 1,
        );

        $post_id = wp_insert_post( $post_data, true );
        if ( is_wp_error( $post_id ) ) {
            return array(
                'status'  => 'error',
                'message' => $post_id->get_error_message(),
            );
        }

        $post_id = absint( $post_id );
        if ( '' !== $template ) {
            update_post_meta( $post_id, '_wp_page_template', $template );
        }

        update_post_meta( $post_id, '_qiling_setup_wizard_created', '1' );
        update_post_meta( $post_id, '_qiling_setup_wizard_run_id', $run_id );

        $modules_filled = false;
        if ( $import_modules && '' !== $template ) {
            $modules_filled = $this->try_fill_page_modules( $post_id, $template );
        }

        $this->state_service->record_created_page( $page_key, $post_id, $run_id );

        return array(
            'status'         => 'created',
            'id'             => $post_id,
            'title'          => $title,
            'slug'           => $slug,
            'template'       => $template,
            'modules_filled' => $modules_filled,
        );
    }

    /**
     * @param int    $post_id Page id.
     * @param string $template Template.
     * @return bool
     */
    private function try_fill_page_modules( $post_id, $template ) {
        if ( class_exists( '\Developer_Starter\Core\Official_Template_Package_Service' ) ) {
            $service = new Official_Template_Package_Service();
            if ( $service->has_package_for_template( $template ) ) {
                $result = $service->apply_package_to_page( $post_id, $template );
                return ! is_wp_error( $result ) && (bool) $result;
            }
        }

        if ( function_exists( 'developer_starter_maybe_fill_default_modules_for_page_template' ) ) {
            return (bool) developer_starter_maybe_fill_default_modules_for_page_template( $post_id, $template );
        }

        if ( function_exists( 'developer_starter_maybe_fill_official_template_package_for_page_template' ) ) {
            return (bool) developer_starter_maybe_fill_official_template_package_for_page_template( $post_id, $template );
        }

        return false;
    }

    /**
     * @param array<string,mixed> $result Overall result.
     * @param string              $page_key Page key.
     * @param array<string,mixed> $page_result Page result.
     * @return array<string,mixed>
     */
    private function merge_page_result( $result, $page_key, $page_result ) {
        if ( ! is_array( $page_result ) || empty( $page_result['status'] ) ) {
            $result['skipped_pages'][ $page_key ] = __( '页面处理结果为空。', 'developer-starter' );
            return $result;
        }

        if ( 'created' === $page_result['status'] ) {
            $result['created_pages'][ $page_key ] = $page_result;
            if ( ! empty( $page_result['modules_filled'] ) ) {
                $result['modules_filled'] = true;
            }
        } elseif ( 'reused' === $page_result['status'] ) {
            $result['reused_pages'][ $page_key ] = $page_result;
        } elseif ( 'error' === $page_result['status'] ) {
            $result['errors'][ $page_key ] = isset( $page_result['message'] ) ? (string) $page_result['message'] : __( '创建失败。', 'developer-starter' );
        } else {
            $result['skipped_pages'][ $page_key ] = isset( $page_result['message'] ) ? (string) $page_result['message'] : __( '已跳过。', 'developer-starter' );
        }

        return $result;
    }

    /**
     * @param int $page_id Page id.
     * @return array<string,mixed>
     */
    private function maybe_set_front_page( $page_id ) {
        $page_id = absint( $page_id );
        if ( $page_id <= 0 || ! get_post_status( $page_id ) ) {
            return array(
                'status' => 'invalid',
                'id'     => 0,
            );
        }

        $current_front = absint( get_option( 'page_on_front', 0 ) );
        if ( $current_front > 0 && $current_front !== $page_id ) {
            return array(
                'status'      => 'kept_existing',
                'id'          => $current_front,
                'candidate_id'=> $page_id,
            );
        }

        update_option( 'show_on_front', 'page' );
        update_option( 'page_on_front', $page_id );

        return array(
            'status' => $current_front === $page_id ? 'already_set' : 'set',
            'id'     => $page_id,
        );
    }

    /**
     * @return array<string,array<string,mixed>>
     */
    private function get_auth_page_definitions() {
        return array(
            'login'    => array(
                'title'    => __( '用户登录', 'developer-starter' ),
                'slug'     => 'login',
                'template' => 'templates/template-login.php',
                'strict_template_reuse' => true,
            ),
            'register' => array(
                'title'    => __( '用户注册', 'developer-starter' ),
                'slug'     => 'register',
                'template' => 'templates/template-register.php',
                'strict_template_reuse' => true,
            ),
            'account'  => array(
                'title'    => __( '个人中心', 'developer-starter' ),
                'slug'     => 'account-center',
                'template' => 'templates/template-account.php',
                'strict_template_reuse' => true,
            ),
        );
    }

    /**
     * @param string $page_key Auth page key.
     * @param int    $page_id Page id.
     * @return void
     */
    private function sync_auth_page_option( $page_key, $page_id ) {
        $page_key = sanitize_key( (string) $page_key );
        $page_id  = absint( $page_id );
        if ( $page_id <= 0 ) {
            return;
        }

        if ( 'account' === $page_key ) {
            update_option( 'developer_starter_account_page_id', $page_id );
            return;
        }

        $option_key = '';
        if ( 'login' === $page_key ) {
            $option_key = 'login_page_id';
        } elseif ( 'register' === $page_key ) {
            $option_key = 'register_page_id';
        }

        if ( '' === $option_key ) {
            return;
        }

        $options = get_option( 'developer_starter_options', array() );
        if ( ! is_array( $options ) ) {
            $options = array();
        }

        $options[ $option_key ] = $page_id;
        update_option( 'developer_starter_options', $options );
    }

    /**
     * @param array<string,mixed> $preset Resolved preset.
     * @return string
     */
    private function get_default_template_id( $preset ) {
        $templates = isset( $preset['recommended_templates'] ) && is_array( $preset['recommended_templates'] ) ? $preset['recommended_templates'] : array();
        foreach ( $templates as $template ) {
            if ( is_array( $template ) && ! empty( $template['id'] ) ) {
                return sanitize_key( (string) $template['id'] );
            }
        }

        return 'home';
    }

    /**
     * @param mixed $pages Raw pages.
     * @return array<int,string>
     */
    private function sanitize_selected_pages( $pages ) {
        $clean = array();
        foreach ( (array) $pages as $page_key ) {
            $page_key = sanitize_key( (string) $page_key );
            if ( '' !== $page_key ) {
                $clean[] = $page_key;
            }

            if ( count( $clean ) >= self::MAX_SELECTED_PAGES ) {
                break;
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
     * @param mixed $template Template.
     * @return string
     */
    private function normalize_template( $template ) {
        $template = sanitize_text_field( (string) $template );
        $template = str_replace( '\\', '/', trim( $template ) );

        if ( function_exists( 'developer_starter_normalize_page_template_slug' ) ) {
            $template = developer_starter_normalize_page_template_slug( $template );
        }

        return preg_replace( '/[^A-Za-z0-9_\-\.\/]/', '', $template );
    }

    /**
     * @param string $key Key.
     * @return string
     */
    private function humanize_key( $key ) {
        $key = str_replace( array( '_', '-' ), ' ', (string) $key );
        $key = trim( $key );

        return '' !== $key ? ucwords( $key ) : __( '未命名', 'developer-starter' );
    }

    /**
     * @param string $code Error code.
     * @param string $message Error message.
     * @return array<string,mixed>
     */
    private function result_with_error( $code, $message ) {
        return array(
            'run_id'        => '',
            'created_pages' => array(),
            'reused_pages'  => array(),
            'skipped_pages' => array(),
            'errors'        => array(
                sanitize_key( (string) $code ) => sanitize_text_field( (string) $message ),
            ),
            'front_page'    => array(
                'status' => 'skipped',
                'id'     => 0,
            ),
            'modules_filled'=> false,
        );
    }
}
