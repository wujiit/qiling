<?php
/**
 * Setup wizard safe cleanup service.
 *
 * Phase 7 provides an explicit, confirmed cleanup path for content recorded
 * by the setup wizard. It never installs, activates, deactivates or configures
 * third-party plugins.
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Core;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Setup_Wizard_Cleanup_Service {

    /**
     * @var Setup_Wizard_State
     */
    private $state_service;

    /**
     * @param Setup_Wizard_State|null $state_service State service.
     */
    public function __construct( $state_service = null ) {
        $this->state_service = $state_service instanceof Setup_Wizard_State
            ? $state_service
            : Setup_Wizard_State::get_instance();
    }

    /**
     * Build a read-only cleanup preview.
     *
     * @return array<string,mixed>
     */
    public function get_preview() {
        $candidates = $this->state_service->get_last_run_cleanup_candidates();
        $run_id     = isset( $candidates['run_id'] ) ? (string) $candidates['run_id'] : '';
        $pages      = array();
        $menus      = array();

        foreach ( (array) ( isset( $candidates['pages'] ) ? $candidates['pages'] : array() ) as $page_key => $page_id ) {
            $pages[] = $this->get_page_candidate( $page_key, $page_id, $run_id );
        }

        foreach ( (array) ( isset( $candidates['menus'] ) ? $candidates['menus'] : array() ) as $menu_key => $menu_id ) {
            $menus[] = $this->get_menu_candidate( $menu_key, $menu_id );
        }

        return array(
            'run_id'       => $run_id,
            'draft_exists' => false !== get_option( Setup_Wizard_State::DRAFT_OPTION, false ),
            'pages'        => $pages,
            'menus'        => $menus,
            'counts'       => array(
                'pages'          => count( $pages ),
                'menus'          => count( $menus ),
                'eligible_pages' => $this->count_eligible( $pages ),
                'eligible_menus' => $this->count_eligible( $menus ),
            ),
        );
    }

    /**
     * Execute selected cleanup actions after explicit confirmation.
     *
     * @param array<string,mixed> $args Cleanup args.
     * @return array<string,mixed>
     */
    public function cleanup( $args ) {
        if ( ! current_user_can( 'manage_options' ) ) {
            return $this->result_with_error( 'forbidden', __( '权限不足，无法执行向导清理。', 'developer-starter' ) );
        }

        $args = is_array( $args ) ? $args : array();
        $result = array(
            'trashed_pages' => 0,
            'skipped_pages' => 0,
            'deleted_menus' => 0,
            'skipped_menus' => 0,
            'draft_deleted' => false,
            'records_reset' => false,
            'errors'        => array(),
        );

        if ( empty( $args['confirm'] ) ) {
            $result['errors']['confirm_required'] = __( '请先勾选确认，再执行清理。', 'developer-starter' );
            return $result;
        }

        if ( empty( $args['trash_pages'] ) && empty( $args['delete_menus'] ) && empty( $args['reset_tracking'] ) && empty( $args['delete_draft'] ) ) {
            $result['errors']['empty_action'] = __( '请至少选择一个清理动作。', 'developer-starter' );
            return $result;
        }

        $preview          = $this->get_preview();
        $removed_page_ids = array();
        $removed_menu_ids = array();

        if ( ! empty( $args['trash_pages'] ) ) {
            foreach ( (array) ( isset( $preview['pages'] ) ? $preview['pages'] : array() ) as $page ) {
                if ( empty( $page['eligible'] ) || empty( $page['id'] ) ) {
                    $result['skipped_pages']++;
                    continue;
                }

                $page_id = absint( $page['id'] );
                $trashed = wp_trash_post( $page_id );
                if ( $trashed ) {
                    $result['trashed_pages']++;
                    $removed_page_ids[] = $page_id;
                } else {
                    $result['skipped_pages']++;
                    $result['errors'][ 'page_' . $page_id ] = sprintf( __( '页面 #%d 移入回收站失败。', 'developer-starter' ), $page_id );
                }
            }
        }

        if ( ! empty( $args['delete_menus'] ) ) {
            foreach ( (array) ( isset( $preview['menus'] ) ? $preview['menus'] : array() ) as $menu ) {
                if ( empty( $menu['eligible'] ) || empty( $menu['id'] ) ) {
                    $result['skipped_menus']++;
                    continue;
                }

                $menu_id = absint( $menu['id'] );
                $this->remove_menu_from_locations( $menu_id );
                $deleted = wp_delete_nav_menu( $menu_id );
                if ( ! is_wp_error( $deleted ) && $deleted ) {
                    $result['deleted_menus']++;
                    $removed_menu_ids[] = $menu_id;
                } else {
                    $result['skipped_menus']++;
                    $result['errors'][ 'menu_' . $menu_id ] = is_wp_error( $deleted )
                        ? $deleted->get_error_message()
                        : sprintf( __( '菜单 #%d 删除失败。', 'developer-starter' ), $menu_id );
                }
            }
        }

        if ( ! empty( $args['delete_draft'] ) ) {
            $result['draft_deleted'] = $this->state_service->delete_draft();
        }

        if ( ! empty( $args['reset_tracking'] ) || ! empty( $removed_page_ids ) || ! empty( $removed_menu_ids ) ) {
            $result['records_reset'] = $this->cleanup_tracking(
                $removed_page_ids,
                $removed_menu_ids,
                ! empty( $args['reset_tracking'] )
            );
        }

        return $result;
    }

    /**
     * @param mixed  $page_key Page key.
     * @param mixed  $page_id Page id.
     * @param string $run_id Run id.
     * @return array<string,mixed>
     */
    private function get_page_candidate( $page_key, $page_id, $run_id ) {
        $page_key = sanitize_key( (string) $page_key );
        $page_id  = absint( $page_id );
        $base     = array(
            'key'      => $page_key,
            'id'       => $page_id,
            'title'    => '',
            'status'   => '',
            'eligible' => false,
            'reason'   => '',
        );

        if ( $page_id <= 0 ) {
            $base['reason'] = __( '页面 ID 无效。', 'developer-starter' );
            return $base;
        }

        $post = get_post( $page_id );
        if ( ! $post || 'page' !== $post->post_type ) {
            $base['reason'] = __( '页面不存在，或已不是页面类型。', 'developer-starter' );
            return $base;
        }

        $base['title']  = get_the_title( $page_id );
        $base['status'] = get_post_status( $page_id );

        if ( 'trash' === $base['status'] ) {
            $base['reason'] = __( '页面已经在回收站。', 'developer-starter' );
            return $base;
        }

        if ( absint( get_option( 'page_on_front', 0 ) ) === $page_id ) {
            $base['reason'] = __( '当前静态首页受保护，请先手动更换首页后再清理。', 'developer-starter' );
            return $base;
        }

        if ( '1' !== (string) get_post_meta( $page_id, '_qiling_setup_wizard_created', true ) ) {
            $base['reason'] = __( '缺少向导创建标记，已跳过。', 'developer-starter' );
            return $base;
        }

        $page_run_id = (string) get_post_meta( $page_id, '_qiling_setup_wizard_run_id', true );
        if ( '' !== $run_id && $page_run_id !== $run_id ) {
            $base['reason'] = __( '页面批次与最近一次向导记录不一致，已跳过。', 'developer-starter' );
            return $base;
        }

        $base['eligible'] = true;
        $base['reason']   = __( '可移入回收站。', 'developer-starter' );

        return $base;
    }

    /**
     * @param mixed $menu_key Menu key.
     * @param mixed $menu_id Menu id.
     * @return array<string,mixed>
     */
    private function get_menu_candidate( $menu_key, $menu_id ) {
        $menu_key = sanitize_key( (string) $menu_key );
        $menu_id  = absint( $menu_id );
        $base     = array(
            'key'        => $menu_key,
            'id'         => $menu_id,
            'title'      => '',
            'item_count' => 0,
            'eligible'   => false,
            'reason'     => '',
        );

        if ( $menu_id <= 0 ) {
            $base['reason'] = __( '菜单 ID 无效。', 'developer-starter' );
            return $base;
        }

        $menu = wp_get_nav_menu_object( $menu_id );
        if ( ! $menu || is_wp_error( $menu ) ) {
            $base['reason'] = __( '菜单不存在。', 'developer-starter' );
            return $base;
        }

        $items = wp_get_nav_menu_items( $menu_id );
        $base['title']      = isset( $menu->name ) ? (string) $menu->name : '';
        $base['item_count'] = is_array( $items ) ? count( $items ) : 0;
        $base['eligible']   = true;
        $base['reason']     = __( '可删除该向导创建的菜单。', 'developer-starter' );

        return $base;
    }

    /**
     * Remove a deleted menu from assigned theme locations.
     *
     * @param int $menu_id Menu id.
     * @return void
     */
    private function remove_menu_from_locations( $menu_id ) {
        $menu_id   = absint( $menu_id );
        $locations = get_nav_menu_locations();
        if ( $menu_id <= 0 || ! is_array( $locations ) ) {
            return;
        }

        $changed = false;
        foreach ( $locations as $location => $assigned_menu_id ) {
            if ( absint( $assigned_menu_id ) === $menu_id ) {
                unset( $locations[ $location ] );
                $changed = true;
            }
        }

        if ( $changed ) {
            set_theme_mod( 'nav_menu_locations', $locations );
        }
    }

    /**
     * Clear only setup-wizard tracking records.
     *
     * @param array<int,int> $removed_page_ids Removed page ids.
     * @param array<int,int> $removed_menu_ids Removed menu ids.
     * @param bool           $reset_last_run Reset last-run buckets only.
     * @return bool
     */
    private function cleanup_tracking( $removed_page_ids, $removed_menu_ids, $reset_last_run ) {
        $state = $this->state_service->get_state();

        $state = $this->remove_ids_from_state_map( $state, 'created_pages', $removed_page_ids );
        $state = $this->remove_ids_from_state_map( $state, 'last_run_created_pages', $removed_page_ids );
        $state = $this->remove_ids_from_state_map( $state, 'created_menus', $removed_menu_ids );
        $state = $this->remove_ids_from_state_map( $state, 'last_run_created_menus', $removed_menu_ids );

        if ( $reset_last_run ) {
            unset( $state['last_run_id'], $state['last_run_created_pages'], $state['last_run_created_menus'] );
            return $this->state_service->save_state( $state, false );
        }

        return $this->state_service->save_state( $state, false );
    }

    /**
     * @param array<string,mixed> $state State.
     * @param string              $key Map key.
     * @param array<int,int>      $ids Removed ids.
     * @return array<string,mixed>
     */
    private function remove_ids_from_state_map( $state, $key, $ids ) {
        if ( empty( $ids ) || empty( $state[ $key ] ) || ! is_array( $state[ $key ] ) ) {
            return $state;
        }

        $id_map = array();
        foreach ( $ids as $id ) {
            $id = absint( $id );
            if ( $id > 0 ) {
                $id_map[ $id ] = true;
            }
        }

        foreach ( $state[ $key ] as $item_key => $item_id ) {
            if ( isset( $id_map[ absint( $item_id ) ] ) ) {
                unset( $state[ $key ][ $item_key ] );
            }
        }

        return $state;
    }

    /**
     * @param array<int,array<string,mixed>> $items Candidate items.
     * @return int
     */
    private function count_eligible( $items ) {
        $count = 0;
        foreach ( $items as $item ) {
            if ( ! empty( $item['eligible'] ) ) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * @param string $code Error code.
     * @param string $message Error message.
     * @return array<string,mixed>
     */
    private function result_with_error( $code, $message ) {
        return array(
            'trashed_pages' => 0,
            'skipped_pages' => 0,
            'deleted_menus' => 0,
            'skipped_menus' => 0,
            'draft_deleted' => false,
            'records_reset' => false,
            'errors'        => array(
                sanitize_key( (string) $code ) => sanitize_text_field( (string) $message ),
            ),
        );
    }
}
