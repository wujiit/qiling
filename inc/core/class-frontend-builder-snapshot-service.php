<?php
/**
 * Frontend Builder snapshot service.
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Core;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Stores small server-side backups before builder saves overwrite page data.
 */
class Frontend_Builder_Snapshot_Service {

    const META_KEY = '_qiling_frontend_builder_snapshots';
    const MAX_SNAPSHOTS = 10;

    /**
     * Create a pre-save snapshot for the current persisted builder state.
     *
     * @param int                 $post_id       Page ID.
     * @param array<int,mixed>    $modules       Current persisted modules.
     * @param array<string,mixed> $page_settings Current persisted page settings.
     * @param string              $data_source   Builder source.
     * @return array<string,mixed>
     */
    public function create_pre_save_snapshot( $post_id, $modules, $page_settings, $data_source = 'theme' ) {
        $post_id = absint( $post_id );
        if ( $post_id <= 0 ) {
            return array();
        }

        $modules       = is_array( $modules ) ? $this->strip_sensitive_values( $modules ) : array();
        $page_settings = is_array( $page_settings ) ? $this->strip_sensitive_values( $page_settings ) : array();
        $data_source   = sanitize_key( (string) $data_source );
        if ( '' === $data_source ) {
            $data_source = 'theme';
        }

        $snapshot = array(
            'id'            => $this->generate_snapshot_id(),
            'type'          => 'pre_save',
            'created_at'    => current_time( 'mysql' ),
            'created_at_gmt' => current_time( 'mysql', true ),
            'user_id'       => get_current_user_id(),
            'data_source'   => $data_source,
            'module_count'  => count( $modules ),
            'modules'       => $modules,
            'page_settings' => $page_settings,
        );

        $snapshots   = $this->get_snapshots( $post_id );
        $snapshots[] = $snapshot;
        $snapshots   = array_slice( $snapshots, -self::MAX_SNAPSHOTS );

        update_post_meta( $post_id, self::META_KEY, $snapshots );

        return array(
            'id'    => $snapshot['id'],
            'count' => count( $snapshots ),
        );
    }

    /**
     * Get saved snapshots for a page.
     *
     * @param int $post_id Page ID.
     * @return array<int,array<string,mixed>>
     */
    public function get_snapshots( $post_id ) {
        $post_id = absint( $post_id );
        if ( $post_id <= 0 ) {
            return array();
        }

        $snapshots = get_post_meta( $post_id, self::META_KEY, true );
        if ( ! is_array( $snapshots ) ) {
            return array();
        }

        $clean = array();
        foreach ( $snapshots as $snapshot ) {
            if ( ! is_array( $snapshot ) ) {
                continue;
            }
            $clean[] = $snapshot;
        }

        return array_slice( $clean, -self::MAX_SNAPSHOTS );
    }

    /**
     * Get compact snapshots suitable for the builder UI.
     *
     * @param int $post_id Page ID.
     * @return array<int,array<string,mixed>>
     */
    public function get_snapshot_summaries( $post_id ) {
        $summaries = array();
        foreach ( array_reverse( $this->get_snapshots( $post_id ) ) as $snapshot ) {
            $summaries[] = array(
                'id'           => isset( $snapshot['id'] ) ? (string) $snapshot['id'] : '',
                'createdAt'    => isset( $snapshot['created_at'] ) ? (string) $snapshot['created_at'] : '',
                'userId'       => isset( $snapshot['user_id'] ) ? absint( $snapshot['user_id'] ) : 0,
                'dataSource'   => isset( $snapshot['data_source'] ) ? sanitize_key( (string) $snapshot['data_source'] ) : 'theme',
                'moduleCount'  => isset( $snapshot['module_count'] ) ? absint( $snapshot['module_count'] ) : 0,
                'type'         => isset( $snapshot['type'] ) ? sanitize_key( (string) $snapshot['type'] ) : '',
            );
        }

        return array_values(
            array_filter(
                $summaries,
                function ( $snapshot ) {
                    return ! empty( $snapshot['id'] );
                }
            )
        );
    }

    /**
     * Find a snapshot by ID.
     *
     * @param int    $post_id Page ID.
     * @param string $snapshot_id Snapshot ID.
     * @return array<string,mixed>|\WP_Error
     */
    public function get_snapshot( $post_id, $snapshot_id ) {
        $snapshot_id = is_scalar( $snapshot_id ) ? trim( (string) $snapshot_id ) : '';
        if ( '' === $snapshot_id ) {
            return new \WP_Error( 'invalid_snapshot_id', __( '快照 ID 无效。', 'developer-starter' ) );
        }

        foreach ( $this->get_snapshots( $post_id ) as $snapshot ) {
            if ( isset( $snapshot['id'] ) && (string) $snapshot['id'] === $snapshot_id ) {
                return $snapshot;
            }
        }

        return new \WP_Error( 'snapshot_not_found', __( '未找到该保存快照。', 'developer-starter' ) );
    }

    /**
     * 删除指定页面的一条装修快照。
     *
     * @param int    $post_id     页面 ID。
     * @param string $snapshot_id 快照 ID。
     * @return bool|\WP_Error
     */
    public function delete_snapshot( $post_id, $snapshot_id ) {
        $post_id     = absint( $post_id );
        $snapshot_id = is_scalar( $snapshot_id ) ? trim( (string) $snapshot_id ) : '';
        if ( $post_id <= 0 || '' === $snapshot_id ) {
            return new \WP_Error( 'invalid_snapshot', __( '装修修订参数无效。', 'developer-starter' ) );
        }

        $snapshots = $this->get_snapshots( $post_id );
        $remaining = array_values(
            array_filter(
                $snapshots,
                function ( $snapshot ) use ( $snapshot_id ) {
                    return empty( $snapshot['id'] ) || (string) $snapshot['id'] !== $snapshot_id;
                }
            )
        );
        if ( count( $remaining ) === count( $snapshots ) ) {
            return new \WP_Error( 'snapshot_not_found', __( '未找到该装修修订。', 'developer-starter' ) );
        }

        if ( empty( $remaining ) ) {
            delete_post_meta( $post_id, self::META_KEY );
        } else {
            update_post_meta( $post_id, self::META_KEY, $remaining );
        }
        return true;
    }

    /**
     * 清空指定页面的全部装修快照。
     *
     * @param int $post_id 页面 ID。
     * @return bool
     */
    public function clear_page_snapshots( $post_id ) {
        $post_id = absint( $post_id );
        return $post_id > 0 ? delete_post_meta( $post_id, self::META_KEY ) : false;
    }

    /**
     * Recursively remove sensitive values from a snapshot payload.
     *
     * @param mixed $value Payload value.
     * @param int   $depth Current recursion depth.
     * @return mixed
     */
    private function strip_sensitive_values( $value, $depth = 0 ) {
        if ( $depth > 12 ) {
            return '';
        }

        if ( is_object( $value ) ) {
            $value = (array) $value;
        }

        if ( ! is_array( $value ) ) {
            return $value;
        }

        $clean = array();
        foreach ( $value as $key => $item ) {
            if ( $this->is_sensitive_key( $key ) ) {
                continue;
            }

            $clean[ $key ] = $this->strip_sensitive_values( $item, $depth + 1 );
        }

        return $clean;
    }

    /**
     * Whether a key should never be persisted into snapshots.
     *
     * @param mixed $key Payload key.
     * @return bool
     */
    private function is_sensitive_key( $key ) {
        if ( ! is_scalar( $key ) ) {
            return false;
        }

        $normalized = strtolower( preg_replace( '/[^a-z0-9]/', '', (string) $key ) );
        return in_array(
            $normalized,
            array(
                'apikey',
                'secret',
                'secretkey',
                'password',
                'authorization',
                'bearer',
                'accesstoken',
                'refreshtoken',
                'nonce',
                'cookie',
            ),
            true
        );
    }

    /**
     * Generate a stable-ish snapshot ID.
     *
     * @return string
     */
    private function generate_snapshot_id() {
        if ( function_exists( 'wp_generate_uuid4' ) ) {
            return (string) wp_generate_uuid4();
        }

        return uniqid( 'qfb_', true );
    }
}
