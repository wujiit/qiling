<?php
/**
 * Setup wizard reuse checks for pages and menus.
 *
 * This service is read-only. It helps later wizard stages avoid
 * duplicate pages and menus before any creation happens.
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Core;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Setup_Wizard_Reuse_Service {

    /**
     * Find an existing page that the wizard should reuse.
     *
     * @param array<string,mixed> $args Lookup args.
     * @return array<string,mixed>
     */
    public function find_reusable_page( $args ) {
        $args = is_array( $args ) ? $args : array();

        $checks = array(
            array( 'source' => 'provided', 'id' => isset( $args['page_id'] ) ? absint( $args['page_id'] ) : 0 ),
            array( 'source' => 'option', 'id' => $this->get_page_id_from_option( isset( $args['option_key'] ) ? $args['option_key'] : '' ) ),
            array( 'source' => 'state', 'id' => $this->get_page_id_from_state( isset( $args['page_key'] ) ? $args['page_key'] : '', isset( $args['state'] ) ? $args['state'] : array() ) ),
            array( 'source' => 'slug', 'id' => $this->find_page_id_by_slug( isset( $args['slug'] ) ? $args['slug'] : '' ) ),
            array( 'source' => 'title', 'id' => $this->find_page_id_by_title( isset( $args['title'] ) ? $args['title'] : '' ) ),
            array( 'source' => 'template', 'id' => $this->find_page_id_by_template( isset( $args['template'] ) ? $args['template'] : '' ) ),
        );

        foreach ( $checks as $check ) {
            $page = $this->get_page_summary( $check['id'], $check['source'] );
            if ( ! empty( $page['id'] ) ) {
                return $page;
            }
        }

        return array(
            'id'     => 0,
            'source' => '',
            'title'  => '',
            'slug'   => '',
            'status' => '',
        );
    }

    /**
     * Find an existing nav menu that the wizard should reuse.
     *
     * @param array<string,mixed> $args Lookup args.
     * @return array<string,mixed>
     */
    public function find_reusable_menu( $args ) {
        $args = is_array( $args ) ? $args : array();

        $checks = array(
            array( 'source' => 'provided', 'id' => isset( $args['menu_id'] ) ? absint( $args['menu_id'] ) : 0 ),
            array( 'source' => 'location', 'id' => $this->get_menu_id_from_location( isset( $args['location'] ) ? $args['location'] : '' ) ),
            array( 'source' => 'state', 'id' => $this->get_menu_id_from_state( isset( $args['menu_key'] ) ? $args['menu_key'] : '', isset( $args['state'] ) ? $args['state'] : array() ) ),
            array( 'source' => 'name', 'id' => $this->get_menu_id_from_name( isset( $args['menu_name'] ) ? $args['menu_name'] : '' ) ),
        );

        foreach ( $checks as $check ) {
            $menu = $this->get_menu_summary( $check['id'], $check['source'] );
            if ( ! empty( $menu['id'] ) ) {
                return $menu;
            }
        }

        return array(
            'id'     => 0,
            'source' => '',
            'name'   => '',
            'slug'   => '',
        );
    }

    /**
     * Check whether a menu already contains a page item.
     *
     * @param int $menu_id Menu term id.
     * @param int $page_id Page id.
     * @return bool
     */
    public function menu_contains_page( $menu_id, $page_id ) {
        $menu_id = absint( $menu_id );
        $page_id = absint( $page_id );
        if ( $menu_id <= 0 || $page_id <= 0 ) {
            return false;
        }

        $items = wp_get_nav_menu_items( $menu_id, array( 'post_status' => 'any' ) );
        if ( empty( $items ) || ! is_array( $items ) ) {
            return false;
        }

        foreach ( $items as $item ) {
            if ( isset( $item->object ) && 'page' === $item->object && absint( $item->object_id ) === $page_id ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Return only page ids that are not yet present in the menu.
     *
     * @param int               $menu_id Menu term id.
     * @param array<string,int> $page_ids_by_key Page map.
     * @return array<string,int>
     */
    public function filter_missing_menu_page_items( $menu_id, $page_ids_by_key ) {
        $missing = array();
        foreach ( (array) $page_ids_by_key as $page_key => $page_id ) {
            $page_key = sanitize_key( (string) $page_key );
            $page_id  = absint( $page_id );
            if ( '' === $page_key || $page_id <= 0 ) {
                continue;
            }

            if ( ! $this->menu_contains_page( $menu_id, $page_id ) ) {
                $missing[ $page_key ] = $page_id;
            }
        }

        return $missing;
    }

    /**
     * @param mixed $option_key Option key.
     * @return int
     */
    private function get_page_id_from_option( $option_key ) {
        $option_key = sanitize_key( (string) $option_key );
        if ( '' === $option_key ) {
            return 0;
        }

        return absint( get_option( $option_key, 0 ) );
    }

    /**
     * @param mixed $page_key Page key.
     * @param mixed $state Wizard state.
     * @return int
     */
    private function get_page_id_from_state( $page_key, $state ) {
        $page_key = sanitize_key( (string) $page_key );
        if ( '' === $page_key || ! is_array( $state ) || empty( $state['created_pages'] ) || ! is_array( $state['created_pages'] ) ) {
            return 0;
        }

        return isset( $state['created_pages'][ $page_key ] ) ? absint( $state['created_pages'][ $page_key ] ) : 0;
    }

    /**
     * @param mixed $slug Page slug.
     * @return int
     */
    private function find_page_id_by_slug( $slug ) {
        $slug = sanitize_title( (string) $slug );
        if ( '' === $slug ) {
            return 0;
        }

        $pages = get_posts(
            array(
                'name'           => $slug,
                'post_type'      => 'page',
                'post_status'    => array( 'publish', 'draft', 'private' ),
                'posts_per_page' => 1,
                'fields'         => 'ids',
            )
        );

        return empty( $pages ) ? 0 : absint( $pages[0] );
    }

    /**
     * @param mixed $title Page title.
     * @return int
     */
    private function find_page_id_by_title( $title ) {
        $title = trim( wp_strip_all_tags( (string) $title ) );
        if ( '' === $title || ! function_exists( 'get_page_by_title' ) ) {
            return 0;
        }

        $page = get_page_by_title( $title, OBJECT, 'page' );
        if ( ! $page instanceof \WP_Post ) {
            return 0;
        }

        return absint( $page->ID );
    }

    /**
     * @param mixed $template Page template path.
     * @return int
     */
    private function find_page_id_by_template( $template ) {
        $template = is_scalar( $template ) ? sanitize_text_field( (string) $template ) : '';
        if ( '' === $template ) {
            return 0;
        }

        $pages = get_posts(
            array(
                'post_type'      => 'page',
                'post_status'    => array( 'publish', 'draft', 'private' ),
                'posts_per_page' => 1,
                'fields'         => 'ids',
                'meta_key'       => '_wp_page_template',
                'meta_value'     => $template,
            )
        );

        return empty( $pages ) ? 0 : absint( $pages[0] );
    }

    /**
     * @param int    $page_id Page id.
     * @param string $source Match source.
     * @return array<string,mixed>
     */
    private function get_page_summary( $page_id, $source ) {
        $page_id = absint( $page_id );
        if ( $page_id <= 0 ) {
            return array();
        }

        $page = get_post( $page_id );
        if ( ! $page instanceof \WP_Post || 'page' !== $page->post_type || 'trash' === $page->post_status ) {
            return array();
        }

        return array(
            'id'     => $page_id,
            'source' => sanitize_key( (string) $source ),
            'title'  => get_the_title( $page_id ),
            'slug'   => $page->post_name,
            'status' => $page->post_status,
        );
    }

    /**
     * @param mixed $location Menu location.
     * @return int
     */
    private function get_menu_id_from_location( $location ) {
        $location = sanitize_key( (string) $location );
        if ( '' === $location ) {
            return 0;
        }

        $locations = get_nav_menu_locations();
        if ( ! is_array( $locations ) || empty( $locations[ $location ] ) ) {
            return 0;
        }

        return absint( $locations[ $location ] );
    }

    /**
     * @param mixed $menu_key Menu key.
     * @param mixed $state Wizard state.
     * @return int
     */
    private function get_menu_id_from_state( $menu_key, $state ) {
        $menu_key = sanitize_key( (string) $menu_key );
        if ( '' === $menu_key || ! is_array( $state ) || empty( $state['created_menus'] ) || ! is_array( $state['created_menus'] ) ) {
            return 0;
        }

        return isset( $state['created_menus'][ $menu_key ] ) ? absint( $state['created_menus'][ $menu_key ] ) : 0;
    }

    /**
     * @param mixed $menu_name Menu name.
     * @return int
     */
    private function get_menu_id_from_name( $menu_name ) {
        $menu_name = trim( wp_strip_all_tags( (string) $menu_name ) );
        if ( '' === $menu_name ) {
            return 0;
        }

        $menu = wp_get_nav_menu_object( $menu_name );
        if ( ! $menu || is_wp_error( $menu ) ) {
            return 0;
        }

        return isset( $menu->term_id ) ? absint( $menu->term_id ) : 0;
    }

    /**
     * @param int    $menu_id Menu id.
     * @param string $source Match source.
     * @return array<string,mixed>
     */
    private function get_menu_summary( $menu_id, $source ) {
        $menu_id = absint( $menu_id );
        if ( $menu_id <= 0 ) {
            return array();
        }

        $menu = wp_get_nav_menu_object( $menu_id );
        if ( ! $menu || is_wp_error( $menu ) ) {
            return array();
        }

        return array(
            'id'     => absint( $menu->term_id ),
            'source' => sanitize_key( (string) $source ),
            'name'   => isset( $menu->name ) ? (string) $menu->name : '',
            'slug'   => isset( $menu->slug ) ? (string) $menu->slug : '',
        );
    }
}
