<?php
/**
 * Setup wizard state storage.
 *
 * Keeps the setup wizard database footprint intentionally small:
 * one persistent state option and one temporary draft option, both
 * created with autoload disabled.
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Core;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Setup_Wizard_State {

    const STATE_OPTION = 'developer_starter_setup_wizard_state';
    const DRAFT_OPTION = 'developer_starter_setup_wizard_draft';
    const VERSION      = '1.0.0';

    const MAX_MAP_ITEMS    = 120;
    const MAX_PLUGIN_ITEMS = 30;
    const MAX_LIST_ITEMS   = 60;

    /**
     * @var Setup_Wizard_State|null
     */
    private static $instance = null;

    /**
     * @return Setup_Wizard_State
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Get persistent wizard state.
     *
     * @return array<string,mixed>
     */
    public function get_state() {
        $state = get_option( self::STATE_OPTION, array() );
        return $this->normalize_state( is_array( $state ) ? $state : array() );
    }

    /**
     * Get temporary wizard draft.
     *
     * @return array<string,mixed>
     */
    public function get_draft() {
        $draft = get_option( self::DRAFT_OPTION, array() );
        return $this->normalize_draft( is_array( $draft ) ? $draft : array() );
    }

    /**
     * Save persistent wizard state.
     *
     * @param array<string,mixed> $state State patch or full state.
     * @param bool                $merge Whether to merge with current state.
     * @return bool
     */
    public function save_state( $state, $merge = true ) {
        $state = is_array( $state ) ? $state : array();
        if ( $merge ) {
            $state = array_merge( $this->get_state(), $state );
        }

        return $this->persist_option( self::STATE_OPTION, $this->normalize_state( $state ) );
    }

    /**
     * Save temporary wizard draft. Draft is deleted on complete/skip.
     *
     * @param array<string,mixed> $draft Draft data.
     * @return bool
     */
    public function save_draft( $draft ) {
        return $this->persist_option( self::DRAFT_OPTION, $this->normalize_draft( $draft ) );
    }

    /**
     * Delete temporary wizard draft.
     *
     * @return bool
     */
    public function delete_draft() {
        return delete_option( self::DRAFT_OPTION );
    }

    /**
     * Start a new run and reset only last-run tracking buckets.
     *
     * @param string $run_id Optional caller-provided run id.
     * @return string
     */
    public function start_run( $run_id = '' ) {
        $run_id = $this->sanitize_run_id( $run_id );
        if ( '' === $run_id ) {
            $run_id = 'setup_' . gmdate( 'Ymd_His' ) . '_' . wp_generate_password( 6, false, false );
            $run_id = $this->sanitize_run_id( $run_id );
        }

        $this->save_state(
            array(
                'last_run_id'            => $run_id,
                'last_run_created_pages' => array(),
                'last_run_created_menus' => array(),
            )
        );

        return $run_id;
    }

    /**
     * Mark the wizard as pending after theme activation.
     *
     * @return bool
     */
    public function mark_activation_redirect_pending() {
        $state = $this->get_state();
        if ( ! empty( $state['completed'] ) || ! empty( $state['skipped'] ) ) {
            return false;
        }

        $state['activation_redirect_pending'] = true;
        return $this->save_state( $state, false );
    }

    /**
     * Whether an activation redirect is pending.
     *
     * @return bool
     */
    public function has_activation_redirect_pending() {
        $state = $this->get_state();
        return ! empty( $state['activation_redirect_pending'] );
    }

    /**
     * Consume the activation redirect flag.
     *
     * @return bool
     */
    public function consume_activation_redirect_pending() {
        $state = $this->get_state();
        if ( empty( $state['activation_redirect_pending'] ) ) {
            return false;
        }

        $state['activation_redirect_pending'] = false;
        return $this->save_state( $state, false );
    }

    /**
     * Mark wizard as completed and remove draft.
     *
     * @param array<string,mixed> $state Completion data.
     * @return bool
     */
    public function mark_completed( $state = array() ) {
        $state = is_array( $state ) ? $state : array();
        $state = array_merge(
            $state,
            array(
                'completed'    => true,
                'skipped'      => false,
                'completed_at' => time(),
                'version'      => self::VERSION,
            )
        );

        $saved = $this->save_state( $state );
        $this->delete_draft();

        return $saved;
    }

    /**
     * Mark wizard as skipped and remove draft.
     *
     * @param array<string,mixed> $state Skip context.
     * @return bool
     */
    public function mark_skipped( $state = array() ) {
        $state = is_array( $state ) ? $state : array();
        $state = array_merge(
            $state,
            array(
                'completed'  => false,
                'skipped'    => true,
                'skipped_at' => time(),
                'version'    => self::VERSION,
            )
        );

        $saved = $this->save_state( $state );
        $this->delete_draft();

        return $saved;
    }

    /**
     * Record a page created by the wizard.
     *
     * @param string $page_key Page key.
     * @param int    $post_id Page id.
     * @param string $run_id Run id.
     * @return bool
     */
    public function record_created_page( $page_key, $post_id, $run_id = '' ) {
        $page_key = sanitize_key( (string) $page_key );
        $post_id  = absint( $post_id );
        if ( '' === $page_key || $post_id <= 0 ) {
            return false;
        }

        $state = $this->get_state();
        $state['created_pages'][ $page_key ] = $post_id;

        $run_id = $this->sanitize_run_id( $run_id );
        if ( '' === $run_id || ( ! empty( $state['last_run_id'] ) && $run_id === $state['last_run_id'] ) ) {
            $state['last_run_created_pages'][ $page_key ] = $post_id;
        }

        return $this->save_state( $state, false );
    }

    /**
     * Record a menu created by the wizard.
     *
     * @param string $menu_key Menu key or location.
     * @param int    $menu_id Menu term id.
     * @param string $run_id Run id.
     * @return bool
     */
    public function record_created_menu( $menu_key, $menu_id, $run_id = '' ) {
        $menu_key = sanitize_key( (string) $menu_key );
        $menu_id  = absint( $menu_id );
        if ( '' === $menu_key || $menu_id <= 0 ) {
            return false;
        }

        $state = $this->get_state();
        $state['created_menus'][ $menu_key ] = $menu_id;

        $run_id = $this->sanitize_run_id( $run_id );
        if ( '' === $run_id || ( ! empty( $state['last_run_id'] ) && $run_id === $state['last_run_id'] ) ) {
            $state['last_run_created_menus'][ $menu_key ] = $menu_id;
        }

        return $this->save_state( $state, false );
    }

    /**
     * Get content that is eligible for an optional last-run cleanup preview.
     *
     * @return array<string,mixed>
     */
    public function get_last_run_cleanup_candidates() {
        $state = $this->get_state();

        return array(
            'run_id' => isset( $state['last_run_id'] ) ? (string) $state['last_run_id'] : '',
            'pages'  => isset( $state['last_run_created_pages'] ) && is_array( $state['last_run_created_pages'] )
                ? $state['last_run_created_pages']
                : array(),
            'menus'  => isset( $state['last_run_created_menus'] ) && is_array( $state['last_run_created_menus'] )
                ? $state['last_run_created_menus']
                : array(),
        );
    }

    /**
     * Persist an option with autoload disabled.
     *
     * @param string $option Option name.
     * @param mixed  $value Option value.
     * @return bool
     */
    private function persist_option( $option, $value ) {
        $option = sanitize_key( (string) $option );
        if ( '' === $option ) {
            return false;
        }

        if ( false === get_option( $option, false ) ) {
            return add_option( $option, $value, '', 'no' );
        }

        return update_option( $option, $value, false );
    }

    /**
     * Normalize persistent state and drop unknown/heavy payloads.
     *
     * @param array<string,mixed> $state State.
     * @return array<string,mixed>
     */
    private function normalize_state( $state ) {
        $normalized = array(
            'completed'              => ! empty( $state['completed'] ),
            'skipped'                => ! empty( $state['skipped'] ),
            'completed_at'           => isset( $state['completed_at'] ) ? absint( $state['completed_at'] ) : 0,
            'skipped_at'             => isset( $state['skipped_at'] ) ? absint( $state['skipped_at'] ) : 0,
            'version'                => isset( $state['version'] ) ? $this->sanitize_short_text( $state['version'], 20 ) : self::VERSION,
            'site_type'              => isset( $state['site_type'] ) ? sanitize_key( (string) $state['site_type'] ) : '',
            'industry'               => isset( $state['industry'] ) ? sanitize_key( (string) $state['industry'] ) : '',
            'template_id'            => isset( $state['template_id'] ) ? sanitize_key( (string) $state['template_id'] ) : '',
            'last_run_id'            => isset( $state['last_run_id'] ) ? $this->sanitize_run_id( $state['last_run_id'] ) : '',
            'activation_redirect_pending' => ! empty( $state['activation_redirect_pending'] ),
            'created_pages'          => $this->sanitize_id_map( isset( $state['created_pages'] ) ? $state['created_pages'] : array() ),
            'created_menus'          => $this->sanitize_id_map( isset( $state['created_menus'] ) ? $state['created_menus'] : array() ),
            'last_run_created_pages' => $this->sanitize_id_map( isset( $state['last_run_created_pages'] ) ? $state['last_run_created_pages'] : array() ),
            'last_run_created_menus' => $this->sanitize_id_map( isset( $state['last_run_created_menus'] ) ? $state['last_run_created_menus'] : array() ),
            'enabled_theme_models'   => $this->sanitize_key_list( isset( $state['enabled_theme_models'] ) ? $state['enabled_theme_models'] : array() ),
            'detected_plugins'       => $this->sanitize_plugin_snapshot( isset( $state['detected_plugins'] ) ? $state['detected_plugins'] : array() ),
        );

        return array_filter(
            $normalized,
            function ( $value ) {
                return ! ( '' === $value || array() === $value || 0 === $value || false === $value );
            }
        );
    }

    /**
     * Normalize temporary draft. Draft may hold current step choices only.
     *
     * @param array<string,mixed> $draft Draft.
     * @return array<string,mixed>
     */
    private function normalize_draft( $draft ) {
        $allowed = array(
            'current_step',
            'site_type',
            'industry',
            'template_id',
            'selected_pages',
            'brand',
            'contact',
            'options',
            'updated_at',
        );

        $normalized = array();
        foreach ( $allowed as $key ) {
            if ( ! array_key_exists( $key, $draft ) ) {
                continue;
            }

            if ( in_array( $key, array( 'selected_pages', 'brand', 'contact', 'options' ), true ) ) {
                $normalized[ $key ] = $this->sanitize_light_value( $draft[ $key ], 3 );
            } elseif ( 'updated_at' === $key ) {
                $normalized[ $key ] = absint( $draft[ $key ] );
            } else {
                $normalized[ $key ] = sanitize_key( (string) $draft[ $key ] );
            }
        }

        if ( empty( $normalized['updated_at'] ) ) {
            $normalized['updated_at'] = time();
        }

        return $normalized;
    }

    /**
     * @param mixed $map Raw map.
     * @return array<string,int>
     */
    private function sanitize_id_map( $map ) {
        $clean = array();
        if ( ! is_array( $map ) ) {
            return $clean;
        }

        foreach ( $map as $key => $value ) {
            $key = sanitize_key( (string) $key );
            $id  = absint( $value );
            if ( '' !== $key && $id > 0 ) {
                $clean[ $key ] = $id;
            }

            if ( count( $clean ) >= self::MAX_MAP_ITEMS ) {
                break;
            }
        }

        return $clean;
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

            if ( count( $clean ) >= self::MAX_LIST_ITEMS ) {
                break;
            }
        }

        return array_values( array_unique( $clean ) );
    }

    /**
     * @param mixed $snapshot Raw plugin snapshot.
     * @return array<string,string>
     */
    private function sanitize_plugin_snapshot( $snapshot ) {
        $clean   = array();
        $allowed = array( 'active' => true, 'inactive' => true, 'missing' => true, 'unknown' => true );
        if ( ! is_array( $snapshot ) ) {
            return $clean;
        }

        foreach ( $snapshot as $plugin_key => $status ) {
            $plugin_key = sanitize_key( (string) $plugin_key );
            $status     = sanitize_key( (string) $status );
            if ( '' !== $plugin_key && isset( $allowed[ $status ] ) ) {
                $clean[ $plugin_key ] = $status;
            }

            if ( count( $clean ) >= self::MAX_PLUGIN_ITEMS ) {
                break;
            }
        }

        return $clean;
    }

    /**
     * @param mixed $value Raw value.
     * @param int   $depth Max recursion depth.
     * @return mixed
     */
    private function sanitize_light_value( $value, $depth = 2 ) {
        if ( $depth <= 0 ) {
            return is_scalar( $value ) ? $this->sanitize_short_text( $value, 160 ) : '';
        }

        if ( is_array( $value ) ) {
            $clean = array();
            $count = 0;
            foreach ( $value as $key => $child ) {
                $key = is_int( $key ) ? $key : sanitize_key( (string) $key );
                if ( '' === (string) $key ) {
                    continue;
                }
                $clean[ $key ] = $this->sanitize_light_value( $child, $depth - 1 );
                $count++;
                if ( $count >= self::MAX_LIST_ITEMS ) {
                    break;
                }
            }
            return $clean;
        }

        if ( is_bool( $value ) ) {
            return $value ? '1' : '';
        }

        return is_scalar( $value ) ? $this->sanitize_short_text( $value, 300 ) : '';
    }

    /**
     * @param mixed $value Raw text.
     * @param int   $max_length Max length.
     * @return string
     */
    private function sanitize_short_text( $value, $max_length = 120 ) {
        $value = sanitize_text_field( (string) $value );
        if ( strlen( $value ) > $max_length ) {
            $value = substr( $value, 0, $max_length );
        }

        return $value;
    }

    /**
     * @param mixed $run_id Run id.
     * @return string
     */
    private function sanitize_run_id( $run_id ) {
        $run_id = preg_replace( '/[^A-Za-z0-9_\-]/', '', (string) $run_id );
        return substr( (string) $run_id, 0, 80 );
    }
}
