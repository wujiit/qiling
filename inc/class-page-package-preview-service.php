<?php
/**
 * 页面数据包预览服务
 *
 * 负责临时预览页面的创建与清理。
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Core;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Page_Package_Preview_Service {

    /**
     * 服务配置。
     *
     * @var array<string,mixed>
     */
    private $config = array();

    /**
     * 构造函数。
     *
     * @param array<string,mixed> $config 服务配置。
     */
    public function __construct( $config = array() ) {
        $this->config = wp_parse_args(
            is_array( $config ) ? $config : array(),
            array(
                'preview_ttl' => 7200,
                'callbacks'   => array(),
            )
        );
    }

    /**
     * 生成临时预览页面（草稿）。
     *
     * @param array<string,mixed> $prepared_package 经过预检的数据包。
     * @param array<string,mixed> $options          预览参数。
     * @return array<string,mixed>|\WP_Error
     */
    public function create_site_package_preview( $prepared_package, $options = array() ) {
        if ( ! is_array( $prepared_package ) || empty( $prepared_package['pages'] ) || ! is_array( $prepared_package['pages'] ) ) {
            return new \WP_Error( 'invalid_package', __( '没有可预览的页面数据。', 'developer-starter' ) );
        }

        $this->cleanup_expired_preview_pages();

        $meta       = isset( $prepared_package['meta'] ) && is_array( $prepared_package['meta'] ) ? $prepared_package['meta'] : array();
        $package_id = isset( $meta['package_id'] ) ? sanitize_key( (string) $meta['package_id'] ) : '';
        if ( '' === $package_id ) {
            $package_id = 'site-package';
        }

        $preview_owner_id = isset( $options['preview_owner_id'] ) ? absint( $options['preview_owner_id'] ) : 0;
        if ( $preview_owner_id <= 0 ) {
            $preview_owner_id = get_current_user_id() ? absint( get_current_user_id() ) : 1;
        }

        $this->cleanup_user_preview_pages( $preview_owner_id );

        $expires_at = time() + absint( $this->config['preview_ttl'] );
        $results    = array();
        $created    = 0;
        $errors     = 0;

        foreach ( $prepared_package['pages'] as $page ) {
            if ( ! is_array( $page ) || empty( $page['page_key'] ) ) {
                continue;
            }

            $page_key = sanitize_key( (string) $page['page_key'] );
            $title    = isset( $page['title'] ) ? sanitize_text_field( (string) $page['title'] ) : __( '预览页面', 'developer-starter' );
            $slug     = isset( $page['target_slug'] ) ? sanitize_title( (string) $page['target_slug'] ) : '';
            if ( '' === $slug && isset( $page['slug'] ) ) {
                $slug = sanitize_title( (string) $page['slug'] );
            }
            if ( '' === $slug ) {
                $slug = 'preview-page';
            }

            $preview_slug = $this->generate_unique_page_slug(
                'qiling-preview-' . $preview_owner_id . '-' . $slug
            );

            $post_id = wp_insert_post(
                array(
                    'post_title'   => sprintf( __( '[预览] %s', 'developer-starter' ), $title ),
                    'post_name'    => $preview_slug,
                    'post_status'  => 'draft',
                    'post_type'    => 'page',
                    'post_content' => '',
                    'post_author'  => $preview_owner_id,
                ),
                true
            );

            if ( is_wp_error( $post_id ) ) {
                $errors++;
                $results[] = array(
                    'page_key' => $page_key,
                    'title'    => $title,
                    'action'   => 'error',
                    'page_id'  => 0,
                    'message'  => $post_id->get_error_message(),
                    'url'      => '',
                );
                continue;
            }

            $post_id = absint( $post_id );
            $created++;

            $modules = isset( $page['modules'] ) && is_array( $page['modules'] ) ? $page['modules'] : array();
            update_post_meta( $post_id, '_developer_starter_modules', $modules );

            $template = isset( $page['template'] ) ? $this->normalize_page_template( $page['template'] ) : 'default';
            if ( '' === $template ) {
                $template = 'default';
            }
            update_post_meta( $post_id, '_wp_page_template', $template );

            $settings = isset( $page['settings'] ) && is_array( $page['settings'] ) ? $page['settings'] : array();
            $this->apply_page_settings( $post_id, $settings );

            update_post_meta( $post_id, '_qiling_site_package_preview', '1' );
            update_post_meta( $post_id, '_qiling_site_package_preview_owner', (string) $preview_owner_id );
            update_post_meta( $post_id, '_qiling_site_package_preview_package_id', $package_id );
            update_post_meta( $post_id, '_qiling_site_package_preview_expires_at', (string) $expires_at );
            update_post_meta( $post_id, '_qiling_site_package_preview_page_key', $page_key );

            $preview_url = get_preview_post_link( $post_id );
            if ( ! $preview_url ) {
                $preview_url = get_permalink( $post_id );
            }

            $results[] = array(
                'page_key' => $page_key,
                'title'    => get_the_title( $post_id ),
                'action'   => 'preview',
                'page_id'  => $post_id,
                'message'  => __( '临时预览已生成。', 'developer-starter' ),
                'url'      => (string) $preview_url,
            );
        }

        return array(
            'results'       => $results,
            'created_count' => $created,
            'error_count'   => $errors,
            'expires_at'    => $expires_at,
            'ttl'           => absint( $this->config['preview_ttl'] ),
        );
    }

    /**
     * 清理当前用户的临时预览页面。
     *
     * @param int    $user_id    用户 ID。
     * @param string $package_id 包 ID（可选）。
     * @return int
     */
    public function cleanup_user_preview_pages( $user_id, $package_id = '' ) {
        $user_id = absint( $user_id );
        if ( $user_id <= 0 ) {
            return 0;
        }

        $deleted    = 0;
        $batch_size = 100;
        $package_id = sanitize_key( (string) $package_id );

        while ( true ) {
            $meta_query = array(
                array(
                    'key'   => '_qiling_site_package_preview',
                    'value' => '1',
                ),
            );

            if ( '' !== $package_id ) {
                $meta_query[] = array(
                    'key'   => '_qiling_site_package_preview_package_id',
                    'value' => $package_id,
                );
            }

            $ids = get_posts(
                array(
                    'post_type'              => 'page',
                    'post_status'            => array( 'draft', 'publish', 'private' ),
                    'posts_per_page'         => $batch_size,
                    'fields'                 => 'ids',
                    'author'                 => $user_id,
                    'orderby'                => 'ID',
                    'order'                  => 'ASC',
                    'no_found_rows'          => true,
                    'ignore_sticky_posts'    => true,
                    'update_post_meta_cache' => false,
                    'update_post_term_cache' => false,
                    'meta_query'             => $meta_query,
                )
            );

            if ( empty( $ids ) || ! is_array( $ids ) ) {
                break;
            }

            foreach ( $ids as $id ) {
                if ( wp_delete_post( absint( $id ), true ) ) {
                    $deleted++;
                }
            }

            if ( count( $ids ) < $batch_size ) {
                break;
            }
        }

        return $deleted;
    }

    /**
     * 清理过期临时预览页面。
     *
     * @return int
     */
    public function cleanup_expired_preview_pages() {
        $deleted    = 0;
        $now        = time();
        $batch_size = 100;

        while ( true ) {
            $ids = get_posts(
                array(
                    'post_type'              => 'page',
                    'post_status'            => array( 'draft', 'publish', 'private' ),
                    'posts_per_page'         => $batch_size,
                    'fields'                 => 'ids',
                    'orderby'                => 'ID',
                    'order'                  => 'ASC',
                    'no_found_rows'          => true,
                    'ignore_sticky_posts'    => true,
                    'update_post_meta_cache' => false,
                    'update_post_term_cache' => false,
                    'meta_query'             => array(
                        array(
                            'key'   => '_qiling_site_package_preview',
                            'value' => '1',
                        ),
                        array(
                            'key'     => '_qiling_site_package_preview_expires_at',
                            'value'   => (string) $now,
                            'type'    => 'NUMERIC',
                            'compare' => '<=',
                        ),
                    ),
                )
            );

            if ( empty( $ids ) || ! is_array( $ids ) ) {
                break;
            }

            foreach ( $ids as $id ) {
                if ( wp_delete_post( absint( $id ), true ) ) {
                    $deleted++;
                }
            }

            if ( count( $ids ) < $batch_size ) {
                break;
            }
        }

        return $deleted;
    }

    /**
     * 规范化模板值。
     *
     * @param mixed $template 原模板值。
     * @return string
     */
    private function normalize_page_template( $template ) {
        $callback = $this->get_callback( 'normalize_page_template' );
        if ( $callback ) {
            return (string) call_user_func( $callback, $template );
        }

        return is_scalar( $template ) ? sanitize_text_field( (string) $template ) : 'default';
    }

    /**
     * 应用页面设置。
     *
     * @param int                $post_id   页面 ID。
     * @param array<string,mixed> $settings 页面设置。
     * @return void
     */
    private function apply_page_settings( $post_id, $settings ) {
        $callback = $this->get_callback( 'apply_page_settings' );
        if ( $callback ) {
            call_user_func( $callback, $post_id, $settings );
        }
    }

    /**
     * 生成唯一 slug。
     *
     * @param string $slug 原始 slug。
     * @return string
     */
    private function generate_unique_page_slug( $slug ) {
        $callback = $this->get_callback( 'generate_unique_page_slug' );
        if ( $callback ) {
            return (string) call_user_func( $callback, $slug );
        }

        return sanitize_title( (string) $slug );
    }

    /**
     * 获取回调。
     *
     * @param string $key 回调键名。
     * @return callable|null
     */
    private function get_callback( $key ) {
        if ( empty( $this->config['callbacks'][ $key ] ) || ! is_callable( $this->config['callbacks'][ $key ] ) ) {
            return null;
        }

        return $this->config['callbacks'][ $key ];
    }
}
