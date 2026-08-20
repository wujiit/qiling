<?php
/**
 * Thumbnail helpers split from functions.php.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! function_exists( 'developer_starter_extract_first_image_url_from_content' ) ) {
    /**
     * 从文章内容中提取第一张图片 URL。
     * 优先尝试 Gutenberg 块解析；未命中时再回退到 img 正则。
     *
     * @param string $content 文章内容。
     * @return string
     */
    function developer_starter_extract_first_image_url_from_content( $content ) {
        $content = (string) $content;
        if ( '' === $content ) {
            return '';
        }

        if ( function_exists( 'parse_blocks' ) && function_exists( 'developer_starter_find_image_in_blocks' ) ) {
            $blocks = parse_blocks( $content );
            if ( is_array( $blocks ) && ! empty( $blocks ) ) {
                $block_image = developer_starter_find_image_in_blocks( $blocks );
                if ( $block_image ) {
                    return esc_url_raw( (string) $block_image );
                }
            }
        }

        if ( preg_match( '/<img[^>]+src=["\']([^"\']+)["\']/', $content, $matches ) ) {
            return esc_url_raw( (string) $matches[1] );
        }

        return '';
    }
}

if ( ! function_exists( 'developer_starter_update_extracted_thumb_meta' ) ) {
    /**
     * 刷新文章正文首图缓存（用于缩略图回退）。
     *
     * @param int          $post_id 文章 ID。
     * @param WP_Post|null $post    文章对象（可选，传入可减少一次查询）。
     * @return string
     */
    function developer_starter_update_extracted_thumb_meta( $post_id, $post = null ) {
        $post_id = absint( $post_id );
        if ( ! $post_id ) {
            return '';
        }

        if ( ! ( $post instanceof WP_Post ) ) {
            $post = get_post( $post_id );
        }

        if ( ! ( $post instanceof WP_Post ) ) {
            return '';
        }

        $extracted_url = developer_starter_extract_first_image_url_from_content( $post->post_content );
        if ( function_exists( 'developer_starter_normalize_local_media_url' ) ) {
            $extracted_url = developer_starter_normalize_local_media_url( $extracted_url );
        }
        update_post_meta( $post_id, '_ds_extracted_thumb_url', $extracted_url );
        wp_cache_delete( 'first_image_' . $post_id, 'developer_starter_media' );

        return $extracted_url;
    }
}

if ( ! function_exists( 'developer_starter_refresh_extracted_thumb_meta_on_save' ) ) {
    /**
     * 在保存文章时预提取正文首图，避免前台循环中重复正则扫描内容。
     *
     * @param int     $post_id 文章 ID。
     * @param WP_Post $post    文章对象。
     * @return void
     */
    function developer_starter_refresh_extracted_thumb_meta_on_save( $post_id, $post ) {
        if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
            return;
        }

        if ( ! ( $post instanceof WP_Post ) ) {
            return;
        }

        $skip_types = array(
            'attachment',
            'nav_menu_item',
            'custom_css',
            'customize_changeset',
            'revision',
            'wp_block',
            'wp_navigation',
            'wp_template',
            'wp_template_part',
            'wp_global_styles',
        );
        if ( in_array( (string) $post->post_type, $skip_types, true ) ) {
            return;
        }

        // 先清除旧的首图缓存，再重新提取。
        // 这样后续的 auto_set_featured_image（优先级 25）能读到最新值。
        wp_cache_delete( 'first_image_' . $post_id, 'developer_starter_media' );

        developer_starter_update_extracted_thumb_meta( $post_id, $post );
    }
}
add_action( 'save_post', 'developer_starter_refresh_extracted_thumb_meta_on_save', 20, 2 );

/**
 * 获取优化后的文章缩略图URL
 *
 * @param int    $post_id 文章ID，默认为当前文章
 * @param string $size 尺寸：'thumbnail', 'medium', 'large', 'full', 或自定义
 * @return string 优化后的图片URL
 */
function developer_starter_get_thumbnail_url( $post_id = null, $size = 'medium' ) {
    static $thumbnail_url_cache = array();

    if ( ! $post_id ) {
        $post_id = get_the_ID();
    }
    $post_id = absint( $post_id );
    if ( ! $post_id ) {
        return '';
    }

    $size_cache_key = is_array( $size )
        ? implode( 'x', array_map( 'intval', $size ) )
        : (string) $size;
    $cache_key = $post_id . '|' . $size_cache_key;
    if ( array_key_exists( $cache_key, $thumbnail_url_cache ) ) {
        return $thumbnail_url_cache[ $cache_key ];
    }

    if ( function_exists( 'developer_starter_get_custom_featured_image_url' ) ) {
        $custom_featured = developer_starter_get_custom_featured_image_url( $post_id );
        if ( $custom_featured ) {
            $thumbnail_url_cache[ $cache_key ] = $custom_featured;
            return $custom_featured;
        }
    }

    $thumbnail_id = get_post_thumbnail_id( $post_id );
    $original_url = '';
    if ( $thumbnail_id ) {
        $original_url = wp_get_attachment_url( $thumbnail_id );
        if ( function_exists( 'developer_starter_normalize_local_media_url' ) ) {
            $original_url = developer_starter_normalize_local_media_url( $original_url );
        }
    }

    if ( ! $thumbnail_id || ! $original_url ) {
        $has_extracted_cache = function_exists( 'metadata_exists' ) && metadata_exists( 'post', $post_id, '_ds_extracted_thumb_url' );
        if ( $has_extracted_cache ) {
            $original_url = trim( (string) get_post_meta( $post_id, '_ds_extracted_thumb_url', true ) );
            if ( function_exists( 'developer_starter_normalize_local_media_url' ) ) {
                $normalized_extracted_url = developer_starter_normalize_local_media_url( $original_url );
                if ( $normalized_extracted_url !== $original_url ) {
                    update_post_meta( $post_id, '_ds_extracted_thumb_url', $normalized_extracted_url );
                }
                $original_url = $normalized_extracted_url;
            }
        } else {
            $original_url = developer_starter_update_extracted_thumb_meta( $post_id );
        }
        
        // ===== 终极兜底逻辑：如果什么都没拿到，强制提取 =====
        // 绕过 normalize_local_media_url 对老网站改域名/外链图片的物理文件严格拦截
        if ( ! $original_url ) {
            // 优先尝试获取原生特色图片（如果特色图片被上面的 normalize 拦截了）
            $fallback_thumb_id = get_post_thumbnail_id( $post_id );
            if ( $fallback_thumb_id ) {
                $original_url = wp_get_attachment_url( $fallback_thumb_id );
            }
            // 如果连特色图片都没有，再暴力提取正文第一张图
            if ( empty( $original_url ) ) {
                $post_obj = get_post( $post_id );
                if ( $post_obj && preg_match( '/<img[^>]+src=[\'"]([^\'"]+)[\'"][^>]*>/i', $post_obj->post_content, $matches ) ) {
                    $original_url = esc_url_raw( $matches[1] );
                }
            }
        }

        if ( ! $original_url ) {
            $thumbnail_url_cache[ $cache_key ] = '';
            return '';
        }
    }

    if ( ! $original_url ) {
        $thumbnail_url_cache[ $cache_key ] = '';
        return '';
    }

    $thumbnail_source = developer_starter_get_option( 'thumbnail_source', 'cropped' );
    if ( $thumbnail_source === 'original' ) {
        $thumbnail_url_cache[ $cache_key ] = $original_url;
        return $original_url;
    }

    $enable = developer_starter_get_option( 'thumbnail_optimize_enable', '' );
    if ( ! $enable ) {
        $thumbnail_url_cache[ $cache_key ] = $original_url;
        return $original_url;
    }

    $thumbnail_optimizer = function_exists( 'developer_starter_get_thumbnail_optimizer_instance' )
        ? developer_starter_get_thumbnail_optimizer_instance()
        : null;
    if ( ! $thumbnail_optimizer ) {
        $thumbnail_url_cache[ $cache_key ] = $original_url;
        return $original_url;
    }

    $default_width  = 400;
    $default_height = 300;

    switch ( $size ) {
        case 'thumbnail':
            $width  = 150;
            $height = 150;
            break;
        case 'medium':
            $width  = intval( developer_starter_get_option( 'thumbnail_width', $default_width ) ) ?: $default_width;
            $height = intval( developer_starter_get_option( 'thumbnail_height', $default_height ) ) ?: $default_height;
            break;
        case 'large':
            $width  = 800;
            $height = 600;
            break;
        case 'full':
            $thumbnail_url_cache[ $cache_key ] = $original_url;
            return $original_url;
        default:
            if ( is_array( $size ) ) {
                $width  = $size[0];
                $height = $size[1];
            } else {
                $width  = $default_width;
                $height = $default_height;
            }
    }

    $crop_position = developer_starter_get_option( 'thumbnail_crop_position', 'center' );
    $quality       = intval( developer_starter_get_option( 'thumbnail_quality', 85 ) ) ?: 85;

    $optimized_url = $thumbnail_optimizer->get_optimized_url( $original_url, $width, $height, $crop_position, $quality );
    $thumbnail_url_cache[ $cache_key ] = $optimized_url;
    return $optimized_url;
}

/**
 * 根据图片 URL 获取宽高（优先媒体库元数据，失败时回退到上传目录文件探测）。
 *
 * @param string $url 图片 URL
 * @param array  $fallback 回退宽高，如 array( 'width' => 600, 'height' => 400 )
 * @return array{width:int,height:int}
 */
function developer_starter_get_image_dimensions_by_url( $url, $fallback = array() ) {
    static $cache = array();

    $fallback_width  = isset( $fallback['width'] ) ? max( 0, (int) $fallback['width'] ) : 0;
    $fallback_height = isset( $fallback['height'] ) ? max( 0, (int) $fallback['height'] ) : 0;

    $url = is_string( $url ) ? trim( $url ) : '';
    if ( $url === '' ) {
        return array(
            'width'  => $fallback_width,
            'height' => $fallback_height,
        );
    }

    $cache_key = md5( $url . '|' . $fallback_width . '|' . $fallback_height );
    if ( isset( $cache[ $cache_key ] ) ) {
        return $cache[ $cache_key ];
    }

    $dimensions = array(
        'width'  => $fallback_width,
        'height' => $fallback_height,
    );

    $normalized_url = esc_url_raw( $url );
    if ( $normalized_url !== '' ) {
        $attachment_id = attachment_url_to_postid( $normalized_url );
        if ( ! $attachment_id ) {
            $url_without_query = strtok( $normalized_url, '?' );
            if ( is_string( $url_without_query ) && $url_without_query !== '' ) {
                $attachment_id = attachment_url_to_postid( $url_without_query );
            }
        }

        if ( $attachment_id ) {
            $meta = wp_get_attachment_metadata( $attachment_id );
            if ( is_array( $meta ) && ! empty( $meta['width'] ) && ! empty( $meta['height'] ) ) {
                $dimensions = array(
                    'width'  => (int) $meta['width'],
                    'height' => (int) $meta['height'],
                );
            }
        } else {
            $uploads  = wp_get_upload_dir();
            $base_url = isset( $uploads['baseurl'] ) ? (string) $uploads['baseurl'] : '';
            $base_dir = isset( $uploads['basedir'] ) ? (string) $uploads['basedir'] : '';

            $url_without_query = strtok( $normalized_url, '?' );
            if ( $base_url !== '' && $base_dir !== '' && is_string( $url_without_query ) && strpos( $url_without_query, $base_url ) === 0 ) {
                $relative_path = ltrim( substr( $url_without_query, strlen( $base_url ) ), '/' );
                $file_path     = trailingslashit( $base_dir ) . $relative_path;

                if ( is_file( $file_path ) ) {
                    $image_size = @getimagesize( $file_path );
                    if ( is_array( $image_size ) && ! empty( $image_size[0] ) && ! empty( $image_size[1] ) ) {
                        $dimensions = array(
                            'width'  => (int) $image_size[0],
                            'height' => (int) $image_size[1],
                        );
                    }
                }
            }
        }
    }

    $cache[ $cache_key ] = $dimensions;
    return $dimensions;
}

/**
 * 获取文章主图宽高（含特色图/自定义特色图/正文首图回退）。
 *
 * @param int|null     $post_id 文章 ID
 * @param string|array $size 图像尺寸
 * @param array        $fallback 回退宽高
 * @return array{width:int,height:int}
 */
function developer_starter_get_post_image_dimensions( $post_id = null, $size = 'medium', $fallback = array() ) {
    static $cache = array();

    if ( ! $post_id ) {
        $post_id = get_the_ID();
    }
    $post_id = absint( $post_id );

    $size_key  = is_array( $size ) ? implode( 'x', array_map( 'intval', $size ) ) : (string) $size;
    $cache_key = $post_id . '|' . $size_key . '|' . md5( wp_json_encode( $fallback ) );
    if ( isset( $cache[ $cache_key ] ) ) {
        return $cache[ $cache_key ];
    }

    $size_defaults = array(
        'thumbnail'                    => array( 'width' => 150,  'height' => 150 ),
        'medium'                       => array( 'width' => 300,  'height' => 300 ),
        'medium_large'                 => array( 'width' => 768,  'height' => 432 ),
        'large'                        => array( 'width' => 1024, 'height' => 576 ),
        'developer-starter-card'       => array( 'width' => 600,  'height' => 400 ),
        'developer-starter-thumbnail'  => array( 'width' => 300,  'height' => 200 ),
        'developer-starter-hero'       => array( 'width' => 1920, 'height' => 1080 ),
        'developer-starter-logo'       => array( 'width' => 200,  'height' => 100 ),
    );

    $base_fallback = array( 'width' => 600, 'height' => 400 );
    if ( is_string( $size ) && isset( $size_defaults[ $size ] ) ) {
        $base_fallback = $size_defaults[ $size ];
    } elseif ( is_array( $size ) && isset( $size[0], $size[1] ) ) {
        $base_fallback = array(
            'width'  => max( 0, (int) $size[0] ),
            'height' => max( 0, (int) $size[1] ),
        );
    }

    if ( isset( $fallback['width'] ) ) {
        $base_fallback['width'] = max( 0, (int) $fallback['width'] );
    }
    if ( isset( $fallback['height'] ) ) {
        $base_fallback['height'] = max( 0, (int) $fallback['height'] );
    }

    $dimensions = $base_fallback;

    if ( $post_id > 0 ) {
        $thumbnail_id = get_post_thumbnail_id( $post_id );
        if ( $thumbnail_id ) {
            $image_src = wp_get_attachment_image_src( $thumbnail_id, $size );
            if ( is_array( $image_src ) && ! empty( $image_src[1] ) && ! empty( $image_src[2] ) ) {
                $dimensions         = array(
                    'width'  => (int) $image_src[1],
                    'height' => (int) $image_src[2],
                );
                $cache[ $cache_key ] = $dimensions;
                return $dimensions;
            }

            $meta = wp_get_attachment_metadata( $thumbnail_id );
            if ( is_array( $meta ) && ! empty( $meta['width'] ) && ! empty( $meta['height'] ) ) {
                $dimensions         = array(
                    'width'  => (int) $meta['width'],
                    'height' => (int) $meta['height'],
                );
                $cache[ $cache_key ] = $dimensions;
                return $dimensions;
            }
        }

        if ( function_exists( 'developer_starter_get_custom_featured_image_url' ) ) {
            $custom_featured = developer_starter_get_custom_featured_image_url( $post_id );
            if ( $custom_featured ) {
                $dimensions         = developer_starter_get_image_dimensions_by_url( $custom_featured, $base_fallback );
                $cache[ $cache_key ] = $dimensions;
                return $dimensions;
            }
        }

        if ( function_exists( 'developer_starter_get_first_image' ) ) {
            $first_image = developer_starter_get_first_image( $post_id );
            if ( $first_image ) {
                $dimensions         = developer_starter_get_image_dimensions_by_url( $first_image, $base_fallback );
                $cache[ $cache_key ] = $dimensions;
                return $dimensions;
            }
        }
    }

    $cache[ $cache_key ] = $dimensions;
    return $dimensions;
}

/**
 * 输出优化后的文章缩略图
 *
 * @param int    $post_id 文章ID
 * @param string $size 尺寸
 * @param array  $attr 图片属性
 * @return void
 */
function developer_starter_the_thumbnail( $post_id = null, $size = 'medium', $attr = array() ) {
    $url = developer_starter_get_thumbnail_url( $post_id, $size );
    if ( ! $url ) {
        return;
    }

    $defaults = array(
        'alt'      => get_the_title( $post_id ),
        'loading'  => 'lazy',
        'decoding' => 'async',
    );

    $attr = wp_parse_args( $attr, $defaults );

    if ( empty( $attr['width'] ) || empty( $attr['height'] ) ) {
        $dimensions = developer_starter_get_post_image_dimensions( $post_id, $size );
        if ( empty( $attr['width'] ) && ! empty( $dimensions['width'] ) ) {
            $attr['width'] = (int) $dimensions['width'];
        }
        if ( empty( $attr['height'] ) && ! empty( $dimensions['height'] ) ) {
            $attr['height'] = (int) $dimensions['height'];
        }
    }

    echo '<img src="' . esc_url( $url ) . '"';
    foreach ( $attr as $name => $value ) {
        echo ' ' . esc_attr( $name ) . '="' . esc_attr( $value ) . '"';
    }
    echo ' />';
}

/**
 * 获取缩略图显示方式（object-fit CSS值）
 *
 * @return string CSS object-fit值
 */
function developer_starter_get_thumbnail_display_mode() {
    $mode = developer_starter_get_option( 'thumbnail_display_mode', 'cover' );

    $valid_modes = array( 'cover', 'contain', 'fill', 'none' );
    if ( ! in_array( $mode, $valid_modes, true ) ) {
        $mode = 'cover';
    }

    return $mode;
}

// 注意：`_ds_extracted_thumb_url` 和 `_developer_starter_auto_featured_image` 的清除
// 已合并到 `developer_starter_refresh_extracted_thumb_meta_on_save`（优先级 20）中，
// 避免同优先级钩子执行顺序不确定导致的竞态问题。
