<?php
/**
 * Helpers grouped split from class-helpers.php.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! function_exists( 'developer_starter_normalize_local_media_url' ) ) {
    /**
     * 规范化本地媒体地址。
     * 支持将旧域名 uploads URL 自动映射到当前站点地址，并过滤已失效的本地文件。
     *
     * @param string $url 原始媒体地址。
     * @return string
     */
    function developer_starter_normalize_local_media_url( $url ) {
        $url = is_string( $url ) ? trim( $url ) : '';
        if ( '' === $url ) {
            return '';
        }

        $normalized = esc_url_raw( $url );
        if ( '' === $normalized ) {
            return '';
        }

        if ( function_exists( 'attachment_url_to_postid' ) ) {
            $candidates = array( $normalized );
            $without_fragment = preg_replace( '/#.*$/', '', $normalized );
            if ( is_string( $without_fragment ) && '' !== $without_fragment && $without_fragment !== $normalized ) {
                $candidates[] = $without_fragment;
            }

            $base_candidate = is_string( $without_fragment ) && '' !== $without_fragment ? $without_fragment : $normalized;
            $without_query  = preg_replace( '/\?.*$/', '', $base_candidate );
            if ( is_string( $without_query ) && '' !== $without_query && ! in_array( $without_query, $candidates, true ) ) {
                $candidates[] = $without_query;
            }

            foreach ( $candidates as $candidate ) {
                $attachment_id = attachment_url_to_postid( $candidate );
                if ( $attachment_id > 0 ) {
                    $attached_file = get_attached_file( $attachment_id );
                    if ( is_string( $attached_file ) && '' !== $attached_file && ! is_file( $attached_file ) ) {
                        return '';
                    }

                    $attachment_url = wp_get_attachment_url( $attachment_id );
                    if ( is_string( $attachment_url ) && '' !== trim( $attachment_url ) ) {
                        return esc_url_raw( trim( $attachment_url ) );
                    }
                }
            }
        }

        if ( ! function_exists( 'wp_get_upload_dir' ) || ! function_exists( 'wp_parse_url' ) ) {
            return $normalized;
        }

        $uploads  = wp_get_upload_dir();
        $base_url = isset( $uploads['baseurl'] ) ? (string) $uploads['baseurl'] : '';
        $base_dir = isset( $uploads['basedir'] ) ? (string) $uploads['basedir'] : '';
        if ( '' === $base_url || '' === $base_dir ) {
            return $normalized;
        }

        $url_path      = wp_parse_url( $normalized, PHP_URL_PATH );
        $uploads_path  = wp_parse_url( $base_url, PHP_URL_PATH );
        $decoded_path  = is_string( $url_path ) ? rawurldecode( $url_path ) : '';
        $decoded_root  = is_string( $uploads_path ) ? rtrim( rawurldecode( $uploads_path ), '/' ) : '';
        if ( '' === $decoded_path || '' === $decoded_root ) {
            return $normalized;
        }

        if ( strpos( $decoded_path, $decoded_root . '/' ) !== 0 ) {
            return $normalized;
        }

        $relative_path = ltrim( substr( $decoded_path, strlen( $decoded_root ) ), '/' );
        if ( '' === $relative_path ) {
            return $normalized;
        }

        $file_path = trailingslashit( $base_dir ) . $relative_path;
        if ( ! is_file( $file_path ) ) {
            return '';
        }

        return trailingslashit( untrailingslashit( $base_url ) ) . str_replace( '%2F', '/', rawurlencode( $relative_path ) );
    }
}

if ( ! function_exists( 'developer_starter_strip_media_url_signature' ) ) {
    /**
     * 去掉 URL 的 query / fragment，便于比对媒体来源。
     *
     * @param string $url 原始 URL。
     * @return string
     */
    function developer_starter_strip_media_url_signature( $url ) {
        $url = is_string( $url ) ? trim( $url ) : '';
        if ( '' === $url ) {
            return '';
        }

        $url = preg_replace( '/#.*$/', '', $url );
        $url = preg_replace( '/\?.*$/', '', (string) $url );

        return is_string( $url ) ? trim( $url ) : '';
    }
}

if ( ! function_exists( 'developer_starter_media_urls_match' ) ) {
    /**
     * 比较两个媒体 URL 是否指向同一资源。
     *
     * @param string $first  第一个 URL。
     * @param string $second 第二个 URL。
     * @return bool
     */
    function developer_starter_media_urls_match( $first, $second ) {
        $first  = developer_starter_strip_media_url_signature( developer_starter_normalize_local_media_url( $first ) );
        $second = developer_starter_strip_media_url_signature( developer_starter_normalize_local_media_url( $second ) );

        return '' !== $first && '' !== $second && $first === $second;
    }
}

/**
 * 从文章内容获取第一张图片
 * 用于没有设置特色图片时，自动获取文章第一张图片
 *
 * @param int $post_id 文章ID。
 * @return string|false 图片URL或false。
 */

if ( ! function_exists( 'developer_starter_get_first_image' ) ) {
    function developer_starter_get_first_image( $post_id = null ) {
        if ( ! $post_id ) {
            $post_id = get_the_ID();
        }
        $post_id = absint( $post_id );
        if ( ! $post_id ) {
            return false;
        }
        
        // 尝试从缓存获取
        $cache_key = 'first_image_' . $post_id;
        $cached_url = wp_cache_get( $cache_key, 'developer_starter_media' );
        
        if ( false !== $cached_url ) {
            return $cached_url;
        }

        $skip_content_scan = false;
        if ( function_exists( 'metadata_exists' ) && metadata_exists( 'post', $post_id, '_ds_extracted_thumb_url' ) ) {
            $raw_meta_url = trim( (string) get_post_meta( $post_id, '_ds_extracted_thumb_url', true ) );
            $meta_url     = developer_starter_normalize_local_media_url( $raw_meta_url );
            if ( $meta_url !== '' ) {
                if ( $meta_url !== $raw_meta_url ) {
                    update_post_meta( $post_id, '_ds_extracted_thumb_url', $meta_url );
                }
                wp_cache_set( $cache_key, $meta_url, 'developer_starter_media', DAY_IN_SECONDS );
                return $meta_url;
            }

            if ( $raw_meta_url !== '' && function_exists( 'developer_starter_update_extracted_thumb_meta' ) ) {
                $meta_url = trim( (string) developer_starter_update_extracted_thumb_meta( $post_id ) );
                $meta_url = developer_starter_normalize_local_media_url( $meta_url );
                if ( $meta_url !== '' ) {
                    update_post_meta( $post_id, '_ds_extracted_thumb_url', $meta_url );
                    wp_cache_set( $cache_key, $meta_url, 'developer_starter_media', DAY_IN_SECONDS );
                    return $meta_url;
                }
            }

            // 已有空值缓存说明正文首图不存在，跳过正文扫描。
            $skip_content_scan = true;
        } elseif ( function_exists( 'developer_starter_update_extracted_thumb_meta' ) ) {
            // 历史文章首次访问时回填缓存，减少后续请求的正则扫描。
            $meta_url = trim( (string) developer_starter_update_extracted_thumb_meta( $post_id ) );
            $meta_url = developer_starter_normalize_local_media_url( $meta_url );
            if ( $meta_url !== '' ) {
                wp_cache_set( $cache_key, $meta_url, 'developer_starter_media', DAY_IN_SECONDS );
                return $meta_url;
            }
            // update_extracted_thumb_meta 内部已完成块解析/正则扫描，无需重复扫描正文。
            $skip_content_scan = true;
        }
        
        if ( ! $skip_content_scan ) {
            $post = get_post( $post_id );
            if ( $post ) {
                $content = (string) $post->post_content;

                // 先尝试解析 Gutenberg 块中的图片
                if ( function_exists( 'parse_blocks' ) ) {
                    $blocks = parse_blocks( $content );
                    $image_url = developer_starter_find_image_in_blocks( $blocks );
                    $image_url = developer_starter_normalize_local_media_url( $image_url );
                    if ( $image_url ) {
                        wp_cache_set( $cache_key, $image_url, 'developer_starter_media', DAY_IN_SECONDS );
                        if ( function_exists( 'update_post_meta' ) ) {
                            update_post_meta( $post_id, '_ds_extracted_thumb_url', esc_url_raw( (string) $image_url ) );
                        }
                        return $image_url;
                    }
                }

                // 从文章内容中匹配 img 标签 src
                if ( preg_match( '/<img[^>]+src=[\'"]([^\'"]+)[\'"][^>]*>/i', $content, $match ) ) {
                    $resolved = esc_url_raw( (string) $match[1] );
                    $resolved = developer_starter_normalize_local_media_url( $resolved );
                    if ( ! $resolved ) {
                        $resolved = '';
                    }
                    if ( $resolved ) {
                        wp_cache_set( $cache_key, $resolved, 'developer_starter_media', DAY_IN_SECONDS );
                        if ( function_exists( 'update_post_meta' ) ) {
                            update_post_meta( $post_id, '_ds_extracted_thumb_url', $resolved );
                        }
                        return $resolved;
                    }
                }
            }
        }
        
        // 尝试从附件中获取
        $attachments = get_posts( array(
            'post_parent'    => $post_id,
            'post_type'      => 'attachment',
            'post_mime_type' => 'image',
            'posts_per_page' => 1,
            'orderby'        => 'menu_order',
            'order'          => 'ASC',
        ) );
        
        if ( ! empty( $attachments ) ) {
            $url = wp_get_attachment_url( $attachments[0]->ID );
            $url = developer_starter_normalize_local_media_url( $url );
            if ( $url && function_exists( 'update_post_meta' ) ) {
                update_post_meta( $post_id, '_ds_extracted_thumb_url', esc_url_raw( (string) $url ) );
            }
            wp_cache_set( $cache_key, $url, 'developer_starter_media', DAY_IN_SECONDS );
            return $url;
        }
        
        // 未找到图片，缓存空字符串避免重复查询
        if ( function_exists( 'update_post_meta' ) ) {
            update_post_meta( $post_id, '_ds_extracted_thumb_url', '' );
        }
        wp_cache_set( $cache_key, '', 'developer_starter_media', DAY_IN_SECONDS );
        return false;
    }
}

if ( ! function_exists( 'developer_starter_get_custom_featured_image_url' ) ) {
    function developer_starter_get_custom_featured_image_url( $post_id = null ) {
        if ( ! $post_id ) {
            $post_id = get_the_ID();
        }

        $url = get_post_meta( $post_id, '_developer_starter_featured_image_url', true );
        if ( ! is_string( $url ) ) {
            return '';
        }

        $url = trim( $url );
        if ( $url === '' ) {
            return '';
        }

        $url = developer_starter_normalize_local_media_url( $url );
        if ( '' === $url ) {
            return '';
        }

        return esc_url_raw( $url );
    }
}

if ( ! function_exists( 'developer_starter_get_featured_image_url' ) ) {
    function developer_starter_get_featured_image_url( $post_id = null, $size = 'large' ) {
        if ( ! $post_id ) {
            $post_id = get_the_ID();
        }

        if ( function_exists( 'developer_starter_qiapp_get_screenshot_url' ) ) {
            $software_screenshot = developer_starter_qiapp_get_screenshot_url( $post_id );
            if ( $software_screenshot ) {
                return $software_screenshot;
            }
        }

        $custom = developer_starter_get_custom_featured_image_url( $post_id );
        if ( $custom ) {
            return $custom;
        }

        if ( has_post_thumbnail( $post_id ) ) {
            $thumbnail_url = get_the_post_thumbnail_url( $post_id, $size );
            $thumbnail_url = developer_starter_normalize_local_media_url( $thumbnail_url );
            if ( $thumbnail_url ) {
                return $thumbnail_url;
            }
        }

        $first_image = developer_starter_get_first_image( $post_id );
        if ( $first_image ) {
            return $first_image;
        }

        return '';
    }
}

/**
 * 递归查找块中的图片
 */
if ( ! function_exists( 'developer_starter_find_image_in_blocks' ) ) {
    function developer_starter_find_image_in_blocks( $blocks ) {
        foreach ( $blocks as $block ) {
            // 图片块
            if ( $block['blockName'] === 'core/image' && ! empty( $block['attrs']['url'] ) ) {
                return $block['attrs']['url'];
            }
            
            // 从图片块的ID获取URL
            if ( $block['blockName'] === 'core/image' && ! empty( $block['attrs']['id'] ) ) {
                return wp_get_attachment_url( $block['attrs']['id'] );
            }
            
            // 媒体文本块
            if ( $block['blockName'] === 'core/media-text' && ! empty( $block['attrs']['mediaUrl'] ) ) {
                return $block['attrs']['mediaUrl'];
            }
            
            // 封面块
            if ( $block['blockName'] === 'core/cover' && ! empty( $block['attrs']['url'] ) ) {
                return $block['attrs']['url'];
            }
            
            // 画廊块
            if ( $block['blockName'] === 'core/gallery' && ! empty( $block['attrs']['ids'][0] ) ) {
                return wp_get_attachment_url( $block['attrs']['ids'][0] );
            }
            
            // 从innerHTML中提取图片
            if ( ! empty( $block['innerHTML'] ) && preg_match( '/<img[^>]+src=[\'"]([^\'"]+)[\'"][^>]*>/i', $block['innerHTML'], $match ) ) {
                return $match[1];
            }
            
            // 递归检查内部块
            if ( ! empty( $block['innerBlocks'] ) ) {
                $result = developer_starter_find_image_in_blocks( $block['innerBlocks'] );
                if ( $result ) {
                    return $result;
                }
            }
        }
        return false;
    }
}

/**
 * 从文章第一张图片自动设置特色图片
 * 保存文章时自动设置特色图片
 */
add_action( 'save_post', 'developer_starter_auto_set_featured_image', 25, 3 );
if ( ! function_exists( 'developer_starter_auto_set_featured_image' ) ) {
    function developer_starter_auto_set_featured_image( $post_id, $post, $update ) {
        if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
            return;
        }

        if ( ! ( $post instanceof WP_Post ) ) {
            return;
        }

        // 只处理文章和产品
        if ( ! in_array( $post->post_type, array( 'post', 'product', 'page' ), true ) ) {
            return;
        }

        $auto_meta_key   = '_developer_starter_auto_featured_image';
        $custom_featured = get_post_meta( $post_id, '_developer_starter_featured_image_url', true );
        if ( ! empty( $custom_featured ) ) {
            delete_post_meta( $post_id, $auto_meta_key );
            return;
        }

        $existing_thumbnail_id  = get_post_thumbnail_id( $post_id );
        $existing_thumbnail_url = $existing_thumbnail_id ? wp_get_attachment_url( $existing_thumbnail_id ) : '';
        $existing_thumbnail_url = developer_starter_normalize_local_media_url( $existing_thumbnail_url );
        // refresh_extracted_thumb_meta_on_save（优先级 20）已写入最新首图
        $previous_extracted_url = trim( (string) get_post_meta( $post_id, '_ds_extracted_thumb_url', true ) );
        $auto_managed           = '1' === (string) get_post_meta( $post_id, $auto_meta_key, true );
        $existing_matches_old   = $existing_thumbnail_url && $previous_extracted_url && developer_starter_media_urls_match( $existing_thumbnail_url, $previous_extracted_url );

        if ( $auto_managed && $existing_thumbnail_id && ! $existing_matches_old ) {
            // 用户手动改过特色图后，停止自动同步，避免误覆盖。
            $auto_managed = false;
            delete_post_meta( $post_id, $auto_meta_key );
        }

        $current_first_image_url = '';
        if ( function_exists( 'developer_starter_extract_first_image_url_from_content' ) ) {
            $current_first_image_url = developer_starter_extract_first_image_url_from_content( $post->post_content );
        } elseif ( preg_match( '/<img[^>]+src=[\'"]([^\'"]+)[\'"][^>]*>/i', (string) $post->post_content, $match ) ) {
            $current_first_image_url = $match[1];
        }

        $current_first_image_url = developer_starter_normalize_local_media_url( $current_first_image_url );
        $current_first_image_id  = 0;
        if ( $current_first_image_url && function_exists( 'attachment_url_to_postid' ) ) {
            $current_first_image_id = attachment_url_to_postid( $current_first_image_url );
        }

        $should_sync_auto_featured = $auto_managed || $existing_matches_old;

        if ( $current_first_image_id > 0 ) {
            if ( ! $existing_thumbnail_id ) {
                set_post_thumbnail( $post_id, $current_first_image_id );
                update_post_meta( $post_id, $auto_meta_key, '1' );
                return;
            }

            if ( $should_sync_auto_featured ) {
                if ( (int) $existing_thumbnail_id !== (int) $current_first_image_id ) {
                    set_post_thumbnail( $post_id, $current_first_image_id );
                }
                update_post_meta( $post_id, $auto_meta_key, '1' );
            }

            return;
        }

        if ( $should_sync_auto_featured ) {
            if ( $existing_thumbnail_id ) {
                delete_post_thumbnail( $post_id );
            }

            if ( $current_first_image_url !== '' ) {
                update_post_meta( $post_id, $auto_meta_key, '1' );
            } else {
                delete_post_meta( $post_id, $auto_meta_key );
            }
        } elseif ( $existing_thumbnail_id && ! $existing_thumbnail_url ) {
            // 即使不是自动管理的特色图，如果它指向一个不存在的文件（死链），也清除掉。
            // 这覆盖了"文章图片已删除但特色图仍指向旧图"的场景。
            $attached_file = get_attached_file( $existing_thumbnail_id );
            if ( is_string( $attached_file ) && '' !== $attached_file && ! is_file( $attached_file ) ) {
                delete_post_thumbnail( $post_id );
                delete_post_meta( $post_id, $auto_meta_key );
            }
        }
    }
}

/**
 * 图片延迟加载
 * 为文章内容中的图片添加 loading="lazy" 属性
 */
if ( ! function_exists( 'developer_starter_lazy_load_placeholder_enabled' ) ) {
    /**
     * 判断是否启用懒加载图片的渐进式占位效果。
     *
     * @return bool
     */
    function developer_starter_lazy_load_placeholder_enabled() {
        if ( is_admin() || is_feed() ) {
            return false;
        }

        return (bool) developer_starter_get_option( 'lazy_load_images', '' )
            && (bool) developer_starter_get_option( 'lazy_load_placeholder_enable', '' );
    }
}

if ( ! function_exists( 'developer_starter_image_tag_get_attribute' ) ) {
    /**
     * 从 img 标签中读取属性值。
     *
     * @param string $tag       图片标签。
     * @param string $attribute 属性名。
     * @return string
     */
    function developer_starter_image_tag_get_attribute( $tag, $attribute ) {
        $attribute = preg_quote( (string) $attribute, '/' );
        if ( preg_match( '/\s' . $attribute . '\s*=\s*([\'"])(.*?)\1/i', (string) $tag, $match ) ) {
            return (string) $match[2];
        }
        if ( preg_match( '/\s' . $attribute . '\s*=\s*([^\s>]+)/i', (string) $tag, $match ) ) {
            return trim( (string) $match[1], "\"'" );
        }

        return '';
    }
}

if ( ! function_exists( 'developer_starter_image_tag_has_attribute' ) ) {
    /**
     * 判断 img 标签是否已有某个属性。
     *
     * @param string $tag       图片标签。
     * @param string $attribute 属性名。
     * @return bool
     */
    function developer_starter_image_tag_has_attribute( $tag, $attribute ) {
        return preg_match( '/\s' . preg_quote( (string) $attribute, '/' ) . '\s*=/i', (string) $tag ) === 1;
    }
}

if ( ! function_exists( 'developer_starter_add_image_tag_attribute' ) ) {
    /**
     * 给 img 标签补充属性，不覆盖已有属性。
     *
     * @param string $tag       图片标签。
     * @param string $attribute 属性名。
     * @param string $value     属性值。
     * @return string
     */
    function developer_starter_add_image_tag_attribute( $tag, $attribute, $value ) {
        if ( developer_starter_image_tag_has_attribute( $tag, $attribute ) ) {
            return $tag;
        }

        return (string) preg_replace(
            '/<img\b/i',
            '<img ' . sanitize_key( (string) $attribute ) . '="' . esc_attr( (string) $value ) . '"',
            (string) $tag,
            1
        );
    }
}

if ( ! function_exists( 'developer_starter_add_image_tag_class' ) ) {
    /**
     * 给 img 标签合并 class。
     *
     * @param string $tag   图片标签。
     * @param string $class class 名称。
     * @return string
     */
    function developer_starter_add_image_tag_class( $tag, $class ) {
        $class = sanitize_html_class( (string) $class );
        if ( '' === $class ) {
            return $tag;
        }

        if ( preg_match( '/\sclass\s*=\s*([\'"])(.*?)\1/i', (string) $tag, $match ) ) {
            $quote = $match[1];
            $classes = preg_split( '/\s+/', trim( (string) $match[2] ) );
            $classes = is_array( $classes ) ? array_filter( $classes ) : array();
            if ( in_array( $class, $classes, true ) ) {
                return $tag;
            }
            $classes[] = $class;
            $class_attr = ' class=' . $quote . esc_attr( implode( ' ', $classes ) ) . $quote;

            return (string) preg_replace( '/\sclass\s*=\s*([\'"])(.*?)\1/i', $class_attr, (string) $tag, 1 );
        }

        return developer_starter_add_image_tag_attribute( $tag, 'class', $class );
    }
}

if ( ! function_exists( 'developer_starter_lazy_load_placeholder_body_class' ) ) {
    /**
     * 为启用渐进式占位的页面添加 body class。
     *
     * @param array<int,string> $classes Body class 列表。
     * @return array<int,string>
     */
    function developer_starter_lazy_load_placeholder_body_class( $classes ) {
        if ( developer_starter_lazy_load_placeholder_enabled() ) {
            $classes[] = 'qiling-lazy-image-placeholders';
        }

        return $classes;
    }
}
add_filter( 'body_class', 'developer_starter_lazy_load_placeholder_body_class' );

add_filter( 'the_content', 'developer_starter_lazy_load_images', 99 );
add_filter( 'post_thumbnail_html', 'developer_starter_lazy_load_images', 99 );
if ( ! function_exists( 'developer_starter_lazy_load_images' ) ) {
    function developer_starter_lazy_load_images( $content ) {
        if ( ! developer_starter_get_option( 'lazy_load_images', '' ) ) {
            return $content;
        }

        if ( stripos( (string) $content, '<img' ) === false ) {
            return $content;
        }

        $placeholder_enabled = developer_starter_lazy_load_placeholder_enabled();

        $content = preg_replace_callback(
            '/<img\b[^>]*>/i',
            function( $matches ) use ( $placeholder_enabled ) {
                $tag = isset( $matches[0] ) ? (string) $matches[0] : '';
                if ( '' === $tag ) {
                    return $tag;
                }

                $tag = developer_starter_add_image_tag_attribute( $tag, 'loading', 'lazy' );

                $loading = strtolower( developer_starter_image_tag_get_attribute( $tag, 'loading' ) );
                if ( $placeholder_enabled && 'lazy' === $loading ) {
                    $tag = developer_starter_add_image_tag_class( $tag, 'qiling-progressive-image' );
                    $tag = developer_starter_add_image_tag_attribute( $tag, 'data-qiling-progressive-image', '1' );
                }

                return $tag;
            },
            $content
        );

        return is_string( $content ) ? $content : '';
    }
}

/**
 * 图片 SEO 优化
 * 为文章内容中的图片补充 title 属性（默认使用文章标题）
 */
add_filter( 'the_content', 'developer_starter_add_image_title_from_post_title', 98 );
if ( ! function_exists( 'developer_starter_add_image_title_from_post_title' ) ) {
    function developer_starter_add_image_title_from_post_title( $content ) {
        if ( is_admin() ) {
            return $content;
        }

        if ( ! is_singular( 'post' ) || ! in_the_loop() ) {
            return $content;
        }

        if ( stripos( $content, '<img' ) === false ) {
            return $content;
        }

        // 启灵播放器在文章内容中会输出内联 script/style，DOMDocument 重排会导致脚本丢失或错位
        // 这里直接跳过该优化，避免普通文章中的播放器无法初始化
        if (
            stripos( $content, 'artplayer-wrapper' ) !== false ||
            stripos( $content, 'wpartplayer/artplayer' ) !== false ||
            stripos( $content, '[artplayer' ) !== false
        ) {
            return $content;
        }

        $post_title = get_the_title();
        $post_title = $post_title ? wp_strip_all_tags( $post_title ) : '';
        if ( $post_title === '' ) {
            return $content;
        }

        $flags = 0;
        if ( defined( 'LIBXML_HTML_NOIMPLIED' ) ) {
            $flags |= LIBXML_HTML_NOIMPLIED;
        }
        if ( defined( 'LIBXML_HTML_NODEFDTD' ) ) {
            $flags |= LIBXML_HTML_NODEFDTD;
        }

        $dom = new \DOMDocument();
        $prev = libxml_use_internal_errors( true );
        $loaded = $dom->loadHTML( '<?xml encoding="utf-8" ?>' . $content, $flags );
        libxml_clear_errors();
        libxml_use_internal_errors( $prev );

        if ( ! $loaded ) {
            return $content;
        }

        $images = $dom->getElementsByTagName( 'img' );
        if ( $images->length === 0 ) {
            return $content;
        }

        foreach ( $images as $img ) {
            if ( $img->hasAttribute( 'title' ) && trim( $img->getAttribute( 'title' ) ) !== '' ) {
                continue;
            }
            $img->setAttribute( 'title', $post_title );
        }

        // 提取 body 内部 HTML，避免额外包裹
        $body = $dom->getElementsByTagName( 'body' )->item( 0 );
        if ( $body ) {
            $new_html = '';
            foreach ( $body->childNodes as $child ) {
                $new_html .= $dom->saveHTML( $child );
            }
            return $new_html;
        }

        return $dom->saveHTML();
    }
}

/**
 * iframe 延迟加载
 */
add_filter( 'the_content', 'developer_starter_lazy_load_iframes', 99 );
if ( ! function_exists( 'developer_starter_lazy_load_iframes' ) ) {
    function developer_starter_lazy_load_iframes( $content ) {
        if ( ! developer_starter_get_option( 'lazy_load_iframes', '' ) ) {
            return $content;
        }
        
        // 为 iframe 添加 loading="lazy"
        $content = preg_replace(
            '/<iframe(?![^>]*loading=)([^>]*)>/i',
            '<iframe loading="lazy"$1>',
            $content
        );
        
        return $content;
    }
}

/**
 * SVG upload support.
 */
add_filter( 'upload_mimes', 'developer_starter_allow_svg_upload_mime' );
add_filter( 'wp_check_filetype_and_ext', 'developer_starter_fix_svg_upload_filetype', 10, 5 );
add_filter( 'wp_handle_upload_prefilter', 'developer_starter_sanitize_svg_upload_file' );
add_filter( 'wp_prepare_attachment_for_js', 'developer_starter_prepare_svg_attachment_for_js', 10, 3 );
add_filter( 'upload_mimes', 'developer_starter_allow_custom_font_upload_mimes' );
add_filter( 'wp_check_filetype_and_ext', 'developer_starter_fix_custom_font_upload_filetype', 10, 5 );
add_filter( 'wp_handle_upload_prefilter', 'developer_starter_validate_custom_font_upload_file' );

if ( ! function_exists( 'developer_starter_svg_uploads_enabled' ) ) {
    function developer_starter_svg_uploads_enabled() {
        return '1' === (string) developer_starter_get_option( 'svg_upload_enable', '' );
    }
}

if ( ! function_exists( 'developer_starter_current_user_can_upload_svg' ) ) {
    function developer_starter_current_user_can_upload_svg() {
        if ( ! developer_starter_svg_uploads_enabled() || ! function_exists( 'current_user_can' ) ) {
            return false;
        }

        $capability = apply_filters( 'developer_starter_svg_upload_capability', 'manage_options' );
        if ( ! is_string( $capability ) || '' === trim( $capability ) ) {
            $capability = 'manage_options';
        }

        return current_user_can( $capability );
    }
}

if ( ! function_exists( 'developer_starter_is_svg_filename' ) ) {
    function developer_starter_is_svg_filename( $filename ) {
        $extension = strtolower( (string) pathinfo( (string) $filename, PATHINFO_EXTENSION ) );
        return 'svg' === $extension;
    }
}

if ( ! function_exists( 'developer_starter_validate_svg_upload_content' ) ) {
    function developer_starter_validate_svg_upload_content( $content ) {
        $content = is_string( $content ) ? trim( $content ) : '';
        if ( '' === $content || false === stripos( $content, '<svg' ) ) {
            return new WP_Error( 'invalid_svg_file', __( '请上传有效的 SVG 图片文件。', 'developer-starter' ) );
        }

        if ( preg_match( '/<!\s*(doctype|entity)\b/i', $content ) ) {
            return new WP_Error( 'unsafe_svg_file', __( 'SVG 文件包含不安全的文档声明或实体定义。', 'developer-starter' ) );
        }

        if ( preg_match( '/<\s*(script|style|iframe|object|embed|foreignObject)\b/i', $content ) ) {
            return new WP_Error( 'unsafe_svg_file', __( 'SVG 文件包含不允许的脚本、样式或嵌入内容。', 'developer-starter' ) );
        }

        return true;
    }
}

if ( ! function_exists( 'developer_starter_sanitize_svg_upload_content' ) ) {
    function developer_starter_sanitize_svg_upload_content( $content ) {
        $content = is_string( $content ) ? trim( $content ) : '';
        if ( '' === $content ) {
            return '';
        }

        $content = preg_replace( '/^\xEF\xBB\xBF/', '', $content );
        $content = preg_replace( '/<\?xml[^>]*\?>/i', '', (string) $content );

        if ( ! class_exists( 'DOMDocument' ) || ! function_exists( 'developer_starter_sanitize_svg' ) ) {
            return '';
        }

        $content = developer_starter_sanitize_svg( $content );

        return false !== stripos( (string) $content, '<svg' ) ? trim( (string) $content ) : '';
    }
}

if ( ! function_exists( 'developer_starter_allow_svg_upload_mime' ) ) {
    function developer_starter_allow_svg_upload_mime( $mimes ) {
        if ( developer_starter_current_user_can_upload_svg() && is_array( $mimes ) ) {
            $mimes['svg'] = 'image/svg+xml';
        }

        return $mimes;
    }
}

if ( ! function_exists( 'developer_starter_fix_svg_upload_filetype' ) ) {
    function developer_starter_fix_svg_upload_filetype( $data, $file, $filename, $mimes, $real_mime = '' ) {
        if ( ! developer_starter_current_user_can_upload_svg() || ! developer_starter_is_svg_filename( $filename ) ) {
            return $data;
        }

        $real_mime = strtolower( (string) $real_mime );
        $allowed_real_mimes = array(
            '',
            'image/svg+xml',
            'image/svg',
            'text/plain',
            'text/xml',
            'application/xml',
            'application/octet-stream',
        );

        if ( '' !== $real_mime && ! in_array( $real_mime, $allowed_real_mimes, true ) ) {
            return $data;
        }

        if ( is_string( $file ) && is_readable( $file ) ) {
            $content = file_get_contents( $file );
            $valid = developer_starter_validate_svg_upload_content( $content );
            if ( is_wp_error( $valid ) ) {
                return $data;
            }
        }

        return array(
            'ext'             => 'svg',
            'type'            => 'image/svg+xml',
            'proper_filename' => is_array( $data ) && isset( $data['proper_filename'] ) ? $data['proper_filename'] : false,
        );
    }
}

if ( ! function_exists( 'developer_starter_sanitize_svg_upload_file' ) ) {
    function developer_starter_sanitize_svg_upload_file( $file ) {
        $filename = isset( $file['name'] ) ? (string) $file['name'] : '';
        if ( ! developer_starter_is_svg_filename( $filename ) ) {
            return $file;
        }

        if ( ! developer_starter_svg_uploads_enabled() ) {
            $file['error'] = __( '当前站点未开启 SVG 上传支持。', 'developer-starter' );
            return $file;
        }

        if ( ! developer_starter_current_user_can_upload_svg() ) {
            $file['error'] = __( '当前账号没有上传 SVG 图片的权限。', 'developer-starter' );
            return $file;
        }

        $tmp_name = isset( $file['tmp_name'] ) ? (string) $file['tmp_name'] : '';
        if ( '' === $tmp_name || ! is_readable( $tmp_name ) || ! is_writable( $tmp_name ) ) {
            $file['error'] = __( 'SVG 临时文件不可读写，请重新上传。', 'developer-starter' );
            return $file;
        }

        $default_max = defined( 'MB_IN_BYTES' ) ? 2 * MB_IN_BYTES : 2 * 1024 * 1024;
        $max_bytes = absint( apply_filters( 'developer_starter_svg_upload_max_bytes', $default_max ) );
        $file_size = isset( $file['size'] ) ? absint( $file['size'] ) : (int) @filesize( $tmp_name );
        if ( $max_bytes > 0 && $file_size > $max_bytes ) {
            $file['error'] = sprintf(
                /* translators: %s: max file size */
                __( 'SVG 文件不能超过 %s。', 'developer-starter' ),
                size_format( $max_bytes )
            );
            return $file;
        }

        $content = file_get_contents( $tmp_name );
        $valid = developer_starter_validate_svg_upload_content( $content );
        if ( is_wp_error( $valid ) ) {
            $file['error'] = $valid->get_error_message();
            return $file;
        }

        $sanitized = developer_starter_sanitize_svg_upload_content( $content );
        if ( '' === $sanitized ) {
            $file['error'] = __( 'SVG 文件未通过安全过滤，请检查文件内容。', 'developer-starter' );
            return $file;
        }

        if ( ! developer_starter_filesystem_write_temp_file( $tmp_name, $sanitized ) ) {
            $file['error'] = __( 'SVG 文件清洗失败，请重新上传。', 'developer-starter' );
            return $file;
        }

        $file['type'] = 'image/svg+xml';

        return $file;
    }
}

if ( ! function_exists( 'developer_starter_get_custom_font_upload_mimes' ) ) {
    function developer_starter_get_custom_font_upload_mimes() {
        $mimes = array(
            'woff2' => 'font/woff2',
            'woff'  => 'font/woff',
            'ttf'   => 'font/ttf',
            'otf'   => 'font/otf',
        );

        return apply_filters( 'developer_starter_custom_font_upload_mimes', $mimes );
    }
}

if ( ! function_exists( 'developer_starter_current_user_can_upload_custom_fonts' ) ) {
    function developer_starter_current_user_can_upload_custom_fonts() {
        if ( ! function_exists( 'current_user_can' ) ) {
            return false;
        }

        $capability = apply_filters( 'developer_starter_custom_font_upload_capability', 'manage_options' );
        if ( ! is_string( $capability ) || '' === trim( $capability ) ) {
            $capability = 'manage_options';
        }

        return current_user_can( $capability );
    }
}

if ( ! function_exists( 'developer_starter_is_custom_font_filename' ) ) {
    function developer_starter_is_custom_font_filename( $filename ) {
        $extension = strtolower( (string) pathinfo( (string) $filename, PATHINFO_EXTENSION ) );
        return isset( developer_starter_get_custom_font_upload_mimes()[ $extension ] );
    }
}

if ( ! function_exists( 'developer_starter_allow_custom_font_upload_mimes' ) ) {
    function developer_starter_allow_custom_font_upload_mimes( $mimes ) {
        if ( developer_starter_current_user_can_upload_custom_fonts() && is_array( $mimes ) ) {
            foreach ( developer_starter_get_custom_font_upload_mimes() as $extension => $mime ) {
                $mimes[ $extension ] = $mime;
            }
        }

        return $mimes;
    }
}

if ( ! function_exists( 'developer_starter_fix_custom_font_upload_filetype' ) ) {
    function developer_starter_fix_custom_font_upload_filetype( $data, $file, $filename, $mimes, $real_mime = '' ) {
        unset( $mimes );

        if ( ! developer_starter_current_user_can_upload_custom_fonts() || ! developer_starter_is_custom_font_filename( $filename ) ) {
            return $data;
        }

        $extension = strtolower( (string) pathinfo( (string) $filename, PATHINFO_EXTENSION ) );
        $font_mimes = developer_starter_get_custom_font_upload_mimes();
        if ( empty( $font_mimes[ $extension ] ) ) {
            return $data;
        }

        $real_mime = strtolower( (string) $real_mime );
        $allowed_real_mimes = array(
            '',
            $font_mimes[ $extension ],
            'application/octet-stream',
            'application/x-font-woff',
            'application/font-woff',
            'application/x-font-woff2',
            'application/font-woff2',
            'application/x-font-ttf',
            'application/x-font-truetype',
            'application/font-sfnt',
            'font/sfnt',
            'application/vnd.ms-opentype',
        );

        if ( '' !== $real_mime && ! in_array( $real_mime, array_unique( $allowed_real_mimes ), true ) ) {
            return $data;
        }

        return array(
            'ext'             => $extension,
            'type'            => $font_mimes[ $extension ],
            'proper_filename' => is_array( $data ) && isset( $data['proper_filename'] ) ? $data['proper_filename'] : false,
        );
    }
}

if ( ! function_exists( 'developer_starter_validate_custom_font_upload_file' ) ) {
    function developer_starter_validate_custom_font_upload_file( $file ) {
        $filename = isset( $file['name'] ) ? (string) $file['name'] : '';
        if ( ! developer_starter_is_custom_font_filename( $filename ) ) {
            return $file;
        }

        if ( ! developer_starter_current_user_can_upload_custom_fonts() ) {
            $file['error'] = __( '当前账号没有上传字体文件的权限。', 'developer-starter' );
            return $file;
        }

        $default_max = 20 * 1024 * 1024;
        $max_bytes = absint( apply_filters( 'developer_starter_custom_font_upload_max_bytes', $default_max ) );
        $size = isset( $file['size'] ) ? absint( $file['size'] ) : 0;
        if ( $max_bytes > 0 && $size > $max_bytes ) {
            $file['error'] = sprintf(
                /* translators: %s: maximum file size in MB */
                __( '字体文件过大，请上传不超过 %s MB 的文件。', 'developer-starter' ),
                number_format_i18n( $max_bytes / 1024 / 1024, 1 )
            );
        }

        return $file;
    }
}

if ( ! function_exists( 'developer_starter_get_svg_file_dimensions' ) ) {
    function developer_starter_get_svg_file_dimensions( $file ) {
        $dimensions = array(
            'width'  => 512,
            'height' => 512,
        );

        if ( ! is_string( $file ) || '' === $file || ! is_readable( $file ) ) {
            return $dimensions;
        }

        $content = file_get_contents( $file, false, null, 0, 20000 );
        if ( ! is_string( $content ) || '' === $content ) {
            return $dimensions;
        }

        if (
            preg_match( '/\bwidth\s*=\s*["\']([0-9.]+)(?:px)?["\']/i', $content, $width_match )
            && preg_match( '/\bheight\s*=\s*["\']([0-9.]+)(?:px)?["\']/i', $content, $height_match )
        ) {
            $width = (int) round( (float) $width_match[1] );
            $height = (int) round( (float) $height_match[1] );
            if ( $width > 0 && $height > 0 ) {
                return array(
                    'width'  => $width,
                    'height' => $height,
                );
            }
        }

        if ( preg_match( '/\bviewBox\s*=\s*["\']\s*[-0-9.]+\s+[-0-9.]+\s+([0-9.]+)\s+([0-9.]+)\s*["\']/i', $content, $viewbox_match ) ) {
            $width = (int) round( (float) $viewbox_match[1] );
            $height = (int) round( (float) $viewbox_match[2] );
            if ( $width > 0 && $height > 0 ) {
                return array(
                    'width'  => $width,
                    'height' => $height,
                );
            }
        }

        return $dimensions;
    }
}

if ( ! function_exists( 'developer_starter_prepare_svg_attachment_for_js' ) ) {
    function developer_starter_prepare_svg_attachment_for_js( $response, $attachment, $meta ) {
        if ( empty( $response['mime'] ) || 'image/svg+xml' !== $response['mime'] || empty( $response['url'] ) ) {
            return $response;
        }

        $file = get_attached_file( $attachment->ID );
        $dimensions = developer_starter_get_svg_file_dimensions( $file );
        $orientation = $dimensions['width'] >= $dimensions['height'] ? 'landscape' : 'portrait';

        $response['type'] = 'image';
        $response['width'] = $dimensions['width'];
        $response['height'] = $dimensions['height'];
        $response['icon'] = $response['url'];
        $response['image'] = array(
            'src'    => $response['url'],
            'width'  => $dimensions['width'],
            'height' => $dimensions['height'],
        );

        if ( empty( $response['sizes'] ) || ! is_array( $response['sizes'] ) ) {
            $response['sizes'] = array();
        }

        $response['sizes']['full'] = array(
            'url'         => $response['url'],
            'width'       => $dimensions['width'],
            'height'      => $dimensions['height'],
            'orientation' => $orientation,
        );

        return $response;
    }
}

/**
 * WebP 图片转换
 * 上传图片时将附件主文件与各尺寸转换为 WebP
 */
add_filter( 'wp_generate_attachment_metadata', 'developer_starter_generate_webp', 10, 2 );
if ( ! function_exists( 'developer_starter_generate_webp' ) ) {
    function developer_starter_generate_webp( $metadata, $attachment_id ) {
        if ( ! developer_starter_get_option( 'webp_enable', '' ) ) {
            return $metadata;
        }
        
        // 检查 GD 库 WebP 支持
        if ( ! function_exists( 'imagewebp' ) ) {
            return $metadata;
        }
        
        $file = get_attached_file( $attachment_id );
        if ( ! $file || ! file_exists( $file ) ) {
            return $metadata;
        }
        
        $info = pathinfo( $file );
        $ext = strtolower( $info['extension'] ?? '' );
        
        // 只转换 jpg/jpeg/png，跳过 gif（避免动图丢失动画）
        if ( ! in_array( $ext, array( 'jpg', 'jpeg', 'png' ), true ) ) {
            return $metadata;
        }
        
        $quality = (int) developer_starter_get_option( 'webp_quality', '80' );
        $quality = max( 1, min( 100, $quality ) );

        // 转换主图，并把附件主文件指针切换到 webp。
        $main_webp = developer_starter_convert_to_webp( $file, $quality );
        if ( ! $main_webp || ! file_exists( $main_webp ) ) {
            return $metadata;
        }

        if ( ! empty( $metadata['file'] ) && is_string( $metadata['file'] ) ) {
            $metadata['file'] = developer_starter_replace_file_extension_with_webp( $metadata['file'] );
        }

        update_attached_file( $attachment_id, $main_webp );
        developer_starter_delete_original_image_after_webp( $file, $main_webp );

        $attachment = get_post( $attachment_id );
        if ( $attachment && 'image/webp' !== $attachment->post_mime_type ) {
            wp_update_post(
                array(
                    'ID'             => $attachment_id,
                    'post_mime_type' => 'image/webp',
                )
            );
        }

        // 转换各尺寸，并把元数据文件名切换到 webp。
        if ( ! empty( $metadata['sizes'] ) && is_array( $metadata['sizes'] ) ) {
            $upload_dir = dirname( $file );
            foreach ( $metadata['sizes'] as $size => $size_info ) {
                if ( empty( $size_info['file'] ) || ! is_string( $size_info['file'] ) ) {
                    continue;
                }

                $size_file = $upload_dir . '/' . $size_info['file'];
                if ( ! file_exists( $size_file ) ) {
                    continue;
                }

                $size_webp = developer_starter_convert_to_webp( $size_file, $quality );
                if ( ! $size_webp || ! file_exists( $size_webp ) ) {
                    continue;
                }

                $metadata['sizes'][ $size ]['file'] = basename( $size_webp );
                $metadata['sizes'][ $size ]['mime-type'] = 'image/webp';
                developer_starter_delete_original_image_after_webp( $size_file, $size_webp );
            }
        }

        // 处理 WordPress 5.3+ 可能存在的 original_image 字段。
        if ( ! empty( $metadata['original_image'] ) && is_string( $metadata['original_image'] ) ) {
            $original_file = dirname( $file ) . '/' . $metadata['original_image'];
            if ( file_exists( $original_file ) ) {
                $original_webp = developer_starter_convert_to_webp( $original_file, $quality );
                if ( $original_webp && file_exists( $original_webp ) ) {
                    $metadata['original_image'] = basename( $original_webp );
                    developer_starter_delete_original_image_after_webp( $original_file, $original_webp );
                }
            }
        }
        
        return $metadata;
    }
}

if ( ! function_exists( 'developer_starter_replace_file_extension_with_webp' ) ) {
    function developer_starter_replace_file_extension_with_webp( $filename ) {
        if ( ! is_string( $filename ) || '' === $filename ) {
            return $filename;
        }

        if ( preg_match( '/\.[^\.\/\\\\]+$/', $filename ) ) {
            return (string) preg_replace( '/\.[^\.\/\\\\]+$/', '.webp', $filename );
        }

        return $filename . '.webp';
    }
}

if ( ! function_exists( 'developer_starter_delete_original_image_after_webp' ) ) {
    function developer_starter_delete_original_image_after_webp( $original_file, $webp_file ) {
        if ( ! is_string( $original_file ) || ! is_string( $webp_file ) ) {
            return false;
        }

        $original_file = trim( $original_file );
        $webp_file = trim( $webp_file );
        if ( '' === $original_file || '' === $webp_file ) {
            return false;
        }

        if ( ! file_exists( $original_file ) || ! file_exists( $webp_file ) ) {
            return false;
        }

        $original_real = realpath( $original_file );
        $webp_real = realpath( $webp_file );
        if ( false === $original_real || false === $webp_real || $original_real === $webp_real ) {
            return false;
        }

        $webp_size = @filesize( $webp_real );
        if ( ! is_int( $webp_size ) || $webp_size <= 0 ) {
            return false;
        }

        return developer_starter_filesystem_delete_file(
            $original_real,
            array(
                'operation' => 'delete_original_after_webp',
                'context'   => array( 'component' => 'webp_conversion' ),
            )
        );
    }
}

/**
 * 将图片转换为 WebP
 */
if ( ! function_exists( 'developer_starter_convert_to_webp' ) ) {
    function developer_starter_convert_to_webp( $file, $quality = 80 ) {
        $info = pathinfo( $file );
        $ext = strtolower( $info['extension'] ?? '' );
        $webp_file = $info['dirname'] . '/' . $info['filename'] . '.webp';
        
        // 如果已存在则跳过
        if ( file_exists( $webp_file ) ) {
            return $webp_file;
        }
        
        $image = null;
        
        switch ( $ext ) {
            case 'jpg':
            case 'jpeg':
                $image = @imagecreatefromjpeg( $file );
                break;
            case 'png':
                $image = @imagecreatefrompng( $file );
                if ( $image ) {
                    imagepalettetotruecolor( $image );
                    imagealphablending( $image, true );
                    imagesavealpha( $image, true );
                }
                break;
        }
        
        if ( $image ) {
            imagewebp( $image, $webp_file, $quality );
            imagedestroy( $image );
            return $webp_file;
        }
        
        return false;
    }
}



/**
 * 登录失败限制
 */

add_filter( 'authenticate', 'developer_starter_check_login_attempts', 30, 3 );
if ( ! function_exists( 'developer_starter_check_login_attempts' ) ) {
    function developer_starter_check_login_attempts( $user, $username, $password ) {
        if ( ! developer_starter_get_option( 'login_limit_enable', '' ) ) {
            return $user;
        }
        
        if ( empty( $username ) ) {
            return $user;
        }
        
        $ip = developer_starter_get_client_ip();
        $transient_key = 'login_attempts_' . md5( $ip . $username );
        $lockout_key = 'login_lockout_' . md5( $ip . $username );
        
        // 检查是否被锁定
        if ( get_transient( $lockout_key ) ) {
            $lockout_duration = (int) developer_starter_get_option( 'login_lockout_duration', '15' );
            return new WP_Error(
                'too_many_attempts',
                sprintf( 
                    __( '登录尝试次数过多，请在 %d 分钟后再试。', 'developer-starter' ),
                    $lockout_duration 
                )
            );
        }
        
        return $user;
    }
}

/**
 * 记录登录失败
 */
add_action( 'wp_login_failed', 'developer_starter_record_login_failure' );
if ( ! function_exists( 'developer_starter_record_login_failure' ) ) {
    function developer_starter_record_login_failure( $username ) {
        if ( ! developer_starter_get_option( 'login_limit_enable', '' ) ) {
            return;
        }
        
        $ip = developer_starter_get_client_ip();
        $transient_key = 'login_attempts_' . md5( $ip . $username );
        $lockout_key = 'login_lockout_' . md5( $ip . $username );
        
        $max_attempts = (int) developer_starter_get_option( 'login_max_attempts', '5' );
        $lockout_duration = (int) developer_starter_get_option( 'login_lockout_duration', '15' );
        
        $attempts = (int) get_transient( $transient_key );
        $attempts++;
        
        if ( $attempts >= $max_attempts ) {
            // 锁定账户
            set_transient( $lockout_key, true, $lockout_duration * MINUTE_IN_SECONDS );
            delete_transient( $transient_key );
            
            // 通知管理员
            if ( developer_starter_get_option( 'login_notify_admin', '' ) ) {
                $admin_email = get_option( 'admin_email' );
                $subject = sprintf( __( '[%s] 登录安全提醒', 'developer-starter' ), get_bloginfo( 'name' ) );
                $message = sprintf(
                    __( "用户名 %s 因多次登录失败已被临时锁定。\n\nIP 地址: %s\n时间: %s\n锁定时长: %d 分钟", 'developer-starter' ),
                    $username,
                    $ip,
                    current_time( 'mysql' ),
                    $lockout_duration
                );
                wp_mail( $admin_email, $subject, $message );
            }
        } else {
            // 记录尝试次数，1小时内有效
            set_transient( $transient_key, $attempts, HOUR_IN_SECONDS );
        }
    }
}

if ( ! function_exists( 'developer_starter_capture_real_comment_ip' ) ) {
    function developer_starter_capture_real_comment_ip( $commentdata ) {
        if ( ! is_array( $commentdata ) ) {
            return $commentdata;
        }

        $real_ip = developer_starter_get_client_ip();
        if ( filter_var( $real_ip, FILTER_VALIDATE_IP ) ) {
            $commentdata['comment_author_IP'] = $real_ip;
        }

        return $commentdata;
    }
}
add_filter( 'preprocess_comment', 'developer_starter_capture_real_comment_ip', 5 );

/**
 * 登录成功后清除失败记录
 */
add_action( 'wp_login', 'developer_starter_clear_login_attempts', 10, 2 );
if ( ! function_exists( 'developer_starter_clear_login_attempts' ) ) {
    function developer_starter_clear_login_attempts( $user_login, $user ) {
        $ip = developer_starter_get_client_ip();
        $transient_key = 'login_attempts_' . md5( $ip . $user_login );
        delete_transient( $transient_key );
    }
}

/**
 * 在登录错误信息中显示剩余次数
 */
add_filter( 'login_errors', 'developer_starter_login_error_message' );
if ( ! function_exists( 'developer_starter_login_error_message' ) ) {
    function developer_starter_login_error_message( $error ) {
        if ( ! developer_starter_get_option( 'login_limit_enable', '' ) ) {
            return $error;
        }
        
        if ( ! developer_starter_get_option( 'login_show_remaining', '1' ) ) {
            return $error;
        }
        
        // 从 POST 获取用户名
        $username = isset( $_POST['log'] ) ? sanitize_user( wp_unslash( $_POST['log'] ) ) : '';
        if ( empty( $username ) ) {
            return $error;
        }
        
        $ip = developer_starter_get_client_ip();
        $transient_key = 'login_attempts_' . md5( $ip . $username );
        $attempts = (int) get_transient( $transient_key );
        $max_attempts = (int) developer_starter_get_option( 'login_max_attempts', '5' );
        
        $remaining = $max_attempts - $attempts;
        
        if ( $remaining > 0 && $remaining < $max_attempts ) {
            $error .= sprintf( '<br><strong>' . __( '剩余尝试次数：%d', 'developer-starter' ) . '</strong>', $remaining );
        }
        
        return $error;
    }
}

/**
 * 缓存清理函数
 * 在文章保存时清理媒体缓存
 */
add_action( 'save_post', 'developer_starter_clean_media_cache' );
if ( ! function_exists( 'developer_starter_clean_media_cache' ) ) {
    function developer_starter_clean_media_cache( $post_id ) {
        if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
            return;
        }

        // 清理缓存
        wp_cache_delete( 'first_image_' . $post_id, 'developer_starter_media' );
        wp_cache_delete( 'first_video_' . $post_id, 'developer_starter_media' );
        wp_cache_delete( 'first_video_v2_' . $post_id, 'developer_starter_media' );
        wp_cache_delete( 'first_video_v3_' . $post_id, 'developer_starter_media' );
    }
}



/**
 * 颜色加深函数
 * 用于生成渐变色的深色部分
 *
 * @param string $hex HEX颜色值
 * @param int $percent 加深百分比
 * @return string 加深后的HEX颜色
 */

if ( ! function_exists( 'developer_starter_darken_color' ) ) {
    function developer_starter_darken_color( $hex, $percent = 20 ) {
        // 移除 # 符号
        $hex = ltrim( $hex, '#' );
        
        // 确保是6位
        if ( strlen( $hex ) === 3 ) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        
        // 转换为 RGB
        $r = hexdec( substr( $hex, 0, 2 ) );
        $g = hexdec( substr( $hex, 2, 2 ) );
        $b = hexdec( substr( $hex, 4, 2 ) );
        
        // 加深
        $r = max( 0, $r - ( $r * $percent / 100 ) );
        $g = max( 0, $g - ( $g * $percent / 100 ) );
        $b = max( 0, $b - ( $b * $percent / 100 ) );
        
        return sprintf( '#%02x%02x%02x', $r, $g, $b );
    }
}

/**
 * 颜色变亮函数
 *
 * @param string $hex HEX颜色值
 * @param int $percent 变亮百分比
 * @return string 变亮后的HEX颜色
 */
if ( ! function_exists( 'developer_starter_lighten_color' ) ) {
    function developer_starter_lighten_color( $hex, $percent = 20 ) {
        // 移除 # 符号
        $hex = ltrim( $hex, '#' );
        
        // 确保是6位
        if ( strlen( $hex ) === 3 ) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        
        // 转换为 RGB
        $r = hexdec( substr( $hex, 0, 2 ) );
        $g = hexdec( substr( $hex, 2, 2 ) );
        $b = hexdec( substr( $hex, 4, 2 ) );
        
        // 变亮
        $r = min( 255, $r + ( ( 255 - $r ) * $percent / 100 ) );
        $g = min( 255, $g + ( ( 255 - $g ) * $percent / 100 ) );
        $b = min( 255, $b + ( ( 255 - $b ) * $percent / 100 ) );
        
        return sprintf( '#%02x%02x%02x', $r, $g, $b );
    }
}

/**
 * 从文章内容获取第一个视频
 * 用于视频文章封面功能
 *
 * @param int $post_id 文章ID。
 * @return array|false 包含视频信息的数组或false。
 *                     返回格式：['url' => 视频URL, 'type' => 'video'|'iframe'|'bilibili'|'youku', 'poster' => 封面图URL]
 */
if ( ! function_exists( 'developer_starter_get_video_attachment_id_by_url' ) ) {
    /**
     * 根据本地视频 URL 反查附件 ID。
     *
     * @param string $video_url 视频 URL。
     * @return int
     */
    function developer_starter_get_video_attachment_id_by_url( $video_url ) {
        if ( ! function_exists( 'attachment_url_to_postid' ) ) {
            return 0;
        }

        $video_url = is_string( $video_url ) ? trim( $video_url ) : '';
        if ( '' === $video_url ) {
            return 0;
        }

        $candidates = array( $video_url );

        $without_fragment = preg_replace( '/#.*$/', '', $video_url );
        if ( is_string( $without_fragment ) && '' !== $without_fragment && $without_fragment !== $video_url ) {
            $candidates[] = $without_fragment;
        }

        $base_candidate = is_string( $without_fragment ) && '' !== $without_fragment ? $without_fragment : $video_url;
        $without_query  = preg_replace( '/\?.*$/', '', $base_candidate );
        if ( is_string( $without_query ) && '' !== $without_query && ! in_array( $without_query, $candidates, true ) ) {
            $candidates[] = $without_query;
        }

        foreach ( $candidates as $candidate ) {
            $attachment_id = attachment_url_to_postid( $candidate );
            if ( $attachment_id > 0 ) {
                return (int) $attachment_id;
            }
        }

        return 0;
    }
}

if ( ! function_exists( 'developer_starter_get_video_attachment_poster_url' ) ) {
    /**
     * 优先从视频附件缩略图中获取海报图。
     *
     * @param int    $attachment_id 视频附件 ID。
     * @param string $size 图片尺寸。
     * @return string
     */
    function developer_starter_get_video_attachment_poster_url( $attachment_id, $size = 'large' ) {
        $attachment_id = absint( $attachment_id );
        if ( ! $attachment_id ) {
            return '';
        }

        $poster_id = get_post_thumbnail_id( $attachment_id );
        if ( ! $poster_id ) {
            return '';
        }

        $poster_url = wp_get_attachment_image_url( $poster_id, $size );
        if ( ! $poster_url ) {
            $poster_url = wp_get_attachment_url( $poster_id );
        }

        return is_string( $poster_url ) ? trim( $poster_url ) : '';
    }
}

if ( ! function_exists( 'developer_starter_get_video_preview_src' ) ) {
    /**
     * 获取列表封面场景使用的视频预览地址。
     * 无 poster 时，为本地视频补一个轻量时间片段，帮助浏览器展示首帧。
     *
     * @param string $video_url 视频地址。
     * @param string $poster    海报地址。
     * @return string
     */
    function developer_starter_get_video_preview_src( $video_url, $poster = '' ) {
        $video_url = is_string( $video_url ) ? trim( $video_url ) : '';
        $poster    = is_string( $poster ) ? trim( $poster ) : '';

        if ( '' === $video_url ) {
            return '';
        }

        if ( '' !== $poster || strpos( $video_url, '#' ) !== false ) {
            return $video_url;
        }

        if ( ! preg_match( '/\.(?:mp4|m4v|webm|ogg|mov)(?:$|[?#])/i', $video_url ) ) {
            return $video_url;
        }

        return $video_url . '#t=0.001';
    }
}

if ( ! function_exists( 'developer_starter_build_local_video_data' ) ) {
    /**
     * 统一补齐本地视频封面相关数据。
     *
     * @param string $video_url      视频地址。
     * @param string $poster         显式海报地址。
     * @param int    $attachment_id  视频附件 ID。
     * @return array<string,mixed>
     */
    function developer_starter_build_local_video_data( $video_url, $poster = '', $attachment_id = 0 ) {
        $video_url     = developer_starter_normalize_local_media_url( $video_url );
        $poster        = developer_starter_normalize_local_media_url( $poster );
        $attachment_id = absint( $attachment_id );

        if ( '' === $video_url ) {
            return array(
                'url'           => '',
                'type'          => 'video',
                'poster'        => '',
                'attachment_id' => 0,
                'preview_src'   => '',
            );
        }

        if ( ! $attachment_id ) {
            $attachment_id = developer_starter_get_video_attachment_id_by_url( $video_url );
        }

        if ( '' === $poster && $attachment_id ) {
            $poster = developer_starter_get_video_attachment_poster_url( $attachment_id, 'large' );
        }

        return array(
            'url'           => $video_url,
            'type'          => 'video',
            'poster'        => $poster,
            'attachment_id' => $attachment_id,
            'preview_src'   => developer_starter_get_video_preview_src( $video_url, $poster ),
        );
    }
}

if ( ! function_exists( 'developer_starter_get_first_video_cache_key' ) ) {
    /**
     * 获取文章首个视频 object cache key。
     *
     * @param int $post_id 文章 ID。
     * @return string
     */
    function developer_starter_get_first_video_cache_key( $post_id ) {
        return 'first_video_v3_' . absint( $post_id );
    }
}

if ( ! function_exists( 'developer_starter_get_first_video_summary_meta_key' ) ) {
    /**
     * 获取文章首个视频摘要 meta key。
     *
     * @return string
     */
    function developer_starter_get_first_video_summary_meta_key() {
        return '_ds_first_video_summary_v1';
    }
}

if ( ! function_exists( 'developer_starter_normalize_first_video_summary' ) ) {
    /**
     * 将保存的首个视频摘要规范为 object cache 可用值。
     *
     * @param mixed    $summary 摘要 meta。
     * @param \WP_Post $post    文章对象。
     * @return array|null 有效摘要返回视频数据或空数组；null 表示摘要不可用。
     */
    function developer_starter_normalize_first_video_summary( $summary, $post ) {
        if ( ! is_array( $summary ) || ! array_key_exists( 'found', $summary ) ) {
            return null;
        }

        $stored_modified = isset( $summary['modified_gmt'] ) ? (string) $summary['modified_gmt'] : '';
        $current_modified = ( $post instanceof WP_Post && isset( $post->post_modified_gmt ) ) ? (string) $post->post_modified_gmt : '';
        if ( '' !== $stored_modified && '' !== $current_modified && $stored_modified !== $current_modified ) {
            return null;
        }

        if ( ! empty( $summary['found'] ) && ! empty( $summary['data'] ) && is_array( $summary['data'] ) ) {
            return $summary['data'];
        }

        return array();
    }
}

if ( ! function_exists( 'developer_starter_set_first_video_cache' ) ) {
    /**
     * 写入首个视频 object cache；空数组代表已确认无视频。
     *
     * @param int        $post_id    文章 ID。
     * @param array|bool $video_data 视频数据或 false。
     * @return void
     */
    function developer_starter_set_first_video_cache( $post_id, $video_data ) {
        wp_cache_set(
            developer_starter_get_first_video_cache_key( $post_id ),
            ( ! empty( $video_data ) && is_array( $video_data ) ) ? $video_data : array(),
            'developer_starter_media',
            DAY_IN_SECONDS
        );
    }
}

if ( ! function_exists( 'developer_starter_update_first_video_summary' ) ) {
    /**
     * 保存首个视频摘要 meta，并同步 object cache。
     *
     * @param int             $post_id    文章 ID。
     * @param array|bool|null $video_data 视频数据或 false。
     * @param \WP_Post|null   $post       文章对象。
     * @return void
     */
    function developer_starter_update_first_video_summary( $post_id, $video_data, $post = null ) {
        $post_id = absint( $post_id );
        if ( ! $post_id || wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
            return;
        }

        if ( ! ( $post instanceof WP_Post ) ) {
            $post = get_post( $post_id );
        }
        if ( ! $post ) {
            return;
        }

        $found = ! empty( $video_data ) && is_array( $video_data );
        $summary = array(
            'found'        => $found ? 1 : 0,
            'modified_gmt' => isset( $post->post_modified_gmt ) ? (string) $post->post_modified_gmt : '',
            'data'         => $found ? $video_data : array(),
        );

        update_post_meta( $post_id, developer_starter_get_first_video_summary_meta_key(), $summary );
        developer_starter_set_first_video_cache( $post_id, $video_data );
    }
}

if ( ! function_exists( 'developer_starter_get_first_video_summary_cache_value' ) ) {
    /**
     * 从摘要 meta 读取 object cache 可用值。
     *
     * @param int      $post_id 文章 ID。
     * @param \WP_Post $post    文章对象。
     * @return array|null 视频数据、空数组或 null。
     */
    function developer_starter_get_first_video_summary_cache_value( $post_id, $post ) {
        $post_id = absint( $post_id );
        if ( ! $post_id || ! metadata_exists( 'post', $post_id, developer_starter_get_first_video_summary_meta_key() ) ) {
            return null;
        }

        return developer_starter_normalize_first_video_summary(
            get_post_meta( $post_id, developer_starter_get_first_video_summary_meta_key(), true ),
            $post
        );
    }
}

if ( ! function_exists( 'developer_starter_extract_first_video_from_content' ) ) {
    /**
     * 从文章内容中提取首个视频数据；只做解析，不读写缓存。
     *
     * @param string $content 文章内容。
     * @return array|false
     */
    function developer_starter_extract_first_video_from_content( $content ) {
        $content = (string) $content;
        if ( '' === trim( $content ) ) {
            return false;
        }

        $maybe_has_video = (
            false !== stripos( $content, 'video' ) ||
            false !== stripos( $content, 'iframe' ) ||
            false !== stripos( $content, 'youtube' ) ||
            false !== stripos( $content, 'vimeo' ) ||
            false !== stripos( $content, 'youku' ) ||
            false !== stripos( $content, 'bilibili' ) ||
            preg_match( '/\.(?:mp4|m4v|webm|ogg|mov)(?:$|[?#"\'\s<])/i', $content )
        );
        if ( ! $maybe_has_video ) {
            return false;
        }

        // 先尝试解析 Gutenberg 块中的视频。
        if ( function_exists( 'parse_blocks' ) ) {
            $blocks = parse_blocks( $content );
            $video_data = developer_starter_find_video_in_blocks( $blocks );
            if ( $video_data ) {
                return $video_data;
            }
        }

        // 匹配 video 标签。
        if ( preg_match( '/<video[^>]*src=[\'"]([^\'"]+)[\'"][^>]*>/i', $content, $match ) ) {
            $poster = '';
            if ( preg_match( '/poster=[\'"]([^\'"]+)[\'"]/i', $match[0], $poster_match ) ) {
                $poster = $poster_match[1];
            }
            $result = developer_starter_build_local_video_data( $match[1], $poster );
            if ( ! empty( $result['url'] ) ) {
                return $result;
            }
        }

        // 匹配 video 标签内的 source。
        if ( preg_match( '/<video[^>]*>.*?<source[^>]+src=[\'"]([^\'"]+)[\'"][^>]*>.*?<\/video>/is', $content, $match ) ) {
            $poster = '';
            if ( preg_match( '/poster=[\'"]([^\'"]+)[\'"]/i', $match[0], $poster_match ) ) {
                $poster = $poster_match[1];
            }
            $result = developer_starter_build_local_video_data( $match[1], $poster );
            if ( ! empty( $result['url'] ) ) {
                return $result;
            }
        }

        // 匹配 WordPress [video] 短代码。
        if ( preg_match( '/\[video[^\]]*(?:src|mp4|webm|ogg)=[\'"]([^\'"]+)[\'"][^\]]*\]/i', $content, $match ) ) {
            $poster = '';
            if ( preg_match( '/poster=[\'"]([^\'"]+)[\'"]/i', $match[0], $poster_match ) ) {
                $poster = $poster_match[1];
            }
            $result = developer_starter_build_local_video_data( $match[1], $poster );
            if ( ! empty( $result['url'] ) ) {
                return $result;
            }
        }

        // 匹配 B 站视频嵌入。
        if ( preg_match( '/(?:bilibili\.com|player\.bilibili\.com)[^"\']*(?:bvid=|video\/)(BV[a-zA-Z0-9]+)/i', $content, $match ) ) {
            return array(
                'url'    => $match[1],
                'type'   => 'bilibili',
                'poster' => '',
            );
        }

        // 匹配优酷视频嵌入。
        if ( preg_match( '/youku\.com[^"\']*(?:vid=|embed\/)([a-zA-Z0-9=]+)/i', $content, $match ) ) {
            return array(
                'url'    => $match[1],
                'type'   => 'youku',
                'poster' => '',
            );
        }

        // 匹配通用 iframe 视频（如 YouTube、Vimeo 等）。
        if ( preg_match( '/<iframe[^>]+src=[\'"]([^\'"]*(?:youtube|vimeo|youku|bilibili)[^\'"]*)[\'"][^>]*>/i', $content, $match ) ) {
            return array(
                'url'    => $match[1],
                'type'   => 'iframe',
                'poster' => '',
            );
        }

        // 匹配直接的 mp4/webm/ogg/mov 链接。
        if ( preg_match( '/[\'"]([^\'"\s]+\.(?:mp4|m4v|webm|ogg|mov)(?:[?#][^\'"\s]*)?)[\'"]/i', $content, $match ) ) {
            $result = developer_starter_build_local_video_data( $match[1], '' );
            if ( ! empty( $result['url'] ) ) {
                return $result;
            }
        }

        return false;
    }
}

if ( ! function_exists( 'developer_starter_prime_first_video_cache' ) ) {
    /**
     * 批量从摘要 meta 预热首个视频 object cache，不在列表页主动解析正文。
     *
     * @param array $post_ids 文章 ID 列表。
     * @return void
     */
    function developer_starter_prime_first_video_cache( $post_ids ) {
        $post_ids = array_values( array_unique( array_filter( array_map( 'absint', (array) $post_ids ) ) ) );
        if ( empty( $post_ids ) ) {
            return;
        }

        foreach ( $post_ids as $post_id ) {
            $cache_key = developer_starter_get_first_video_cache_key( $post_id );
            if ( false !== wp_cache_get( $cache_key, 'developer_starter_media' ) ) {
                continue;
            }

            $post = get_post( $post_id );
            if ( ! $post ) {
                continue;
            }

            $summary_value = developer_starter_get_first_video_summary_cache_value( $post_id, $post );
            if ( null !== $summary_value ) {
                developer_starter_set_first_video_cache( $post_id, $summary_value );
            }
        }
    }
}

if ( ! function_exists( 'developer_starter_get_first_video' ) ) {
    function developer_starter_get_first_video( $post_id = null ) {
        if ( ! $post_id ) {
            $post_id = get_the_ID();
        }
        $post_id = absint( $post_id );
        if ( ! $post_id ) {
            return false;
        }
        
        // 尝试从缓存获取
        $cache_key = developer_starter_get_first_video_cache_key( $post_id );
        // 使用 $found 变量来区分"未缓存"和"缓存了false/空值"的情况
        // 兼容旧缓存：array() 代表无视频，false 代表未缓存
        $cached_video = wp_cache_get( $cache_key, 'developer_starter_media' );
        
        if ( false !== $cached_video ) {
            return ! empty( $cached_video ) ? $cached_video : false;
        }
        
        $post = get_post( $post_id );
        if ( ! $post ) {
            return false;
        }

        $summary_value = developer_starter_get_first_video_summary_cache_value( $post_id, $post );
        if ( null !== $summary_value ) {
            developer_starter_set_first_video_cache( $post_id, $summary_value );
            return ! empty( $summary_value ) ? $summary_value : false;
        }

        $video_data = developer_starter_extract_first_video_from_content( $post->post_content );
        developer_starter_update_first_video_summary( $post_id, $video_data, $post );

        return $video_data ? $video_data : false;
    }
}

if ( ! function_exists( 'developer_starter_refresh_first_video_summary_on_save' ) ) {
    /**
     * 保存文章时预计算首个视频摘要。
     *
     * @param int      $post_id 文章 ID。
     * @param \WP_Post $post    文章对象。
     * @return void
     */
    function developer_starter_refresh_first_video_summary_on_save( $post_id, $post = null ) {
        $post_id = absint( $post_id );
        if ( ! $post_id || wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
            return;
        }

        if ( ! ( $post instanceof WP_Post ) ) {
            $post = get_post( $post_id );
        }
        if ( ! $post ) {
            return;
        }

        developer_starter_update_first_video_summary(
            $post_id,
            developer_starter_extract_first_video_from_content( $post->post_content ),
            $post
        );
    }
}
add_action( 'save_post', 'developer_starter_refresh_first_video_summary_on_save', 35, 2 );

/**
 * 递归查找块中的视频
 */
if ( ! function_exists( 'developer_starter_find_video_in_blocks' ) ) {
    function developer_starter_find_video_in_blocks( $blocks ) {
        foreach ( $blocks as $block ) {
            // 视频块
            if ( $block['blockName'] === 'core/video' ) {
                $url = '';
                $poster = '';
                $attachment_id = 0;
                
                // 从属性获取
                if ( ! empty( $block['attrs']['src'] ) ) {
                    $url = $block['attrs']['src'];
                }
                if ( ! empty( $block['attrs']['poster'] ) ) {
                    $poster = $block['attrs']['poster'];
                }
                
                // 从附件ID获取
                if ( empty( $url ) && ! empty( $block['attrs']['id'] ) ) {
                    $attachment_id = absint( $block['attrs']['id'] );
                    $url = wp_get_attachment_url( $attachment_id );
                } elseif ( ! empty( $block['attrs']['id'] ) ) {
                    $attachment_id = absint( $block['attrs']['id'] );
                }
                
                // 从innerHTML提取
                if ( empty( $url ) && ! empty( $block['innerHTML'] ) ) {
                    if ( preg_match( '/src=[\'"]([^\'"]+\.(?:mp4|webm|ogg))[\'"]/', $block['innerHTML'], $match ) ) {
                        $url = $match[1];
                    }
                    if ( empty( $poster ) && preg_match( '/poster=[\'"]([^\'"]+)[\'"]/', $block['innerHTML'], $match ) ) {
                        $poster = $match[1];
                    }
                }
                
                if ( $url ) {
                    $result = developer_starter_build_local_video_data( $url, $poster, $attachment_id );
                    if ( ! empty( $result['url'] ) ) {
                        return $result;
                    }
                }
            }
            
            // 嵌入块（YouTube、Vimeo、B站等）
            if ( $block['blockName'] === 'core/embed' ) {
                $url = '';
                if ( ! empty( $block['attrs']['url'] ) ) {
                    $url = $block['attrs']['url'];
                }
                
                $provider = ! empty( $block['attrs']['providerNameSlug'] ) ? $block['attrs']['providerNameSlug'] : '';
                
                if ( $url ) {
                    // B站
                    if ( strpos( $url, 'bilibili.com' ) !== false ) {
                        if ( preg_match( '/(BV[a-zA-Z0-9]+)/', $url, $match ) ) {
                            return array(
                                'url'    => $match[1],
                                'type'   => 'bilibili',
                                'poster' => '',
                            );
                        }
                    }
                    // 其他视频平台
                    if ( in_array( $provider, array( 'youtube', 'vimeo', 'youku' ) ) || 
                         strpos( $url, 'youtube' ) !== false || 
                         strpos( $url, 'vimeo' ) !== false ) {
                        return array(
                            'url'    => $url,
                            'type'   => 'iframe',
                            'poster' => '',
                        );
                    }
                }
            }
            
            // 递归检查内部块
            if ( ! empty( $block['innerBlocks'] ) ) {
                $result = developer_starter_find_video_in_blocks( $block['innerBlocks'] );
                if ( $result ) {
                    return $result;
                }
            }
        }
        return false;
    }
}

/**
 * 检查文章是否包含视频
 *
 * @param int $post_id 文章ID。
 * @return bool
 */
if ( ! function_exists( 'developer_starter_has_video' ) ) {
    function developer_starter_has_video( $post_id = null ) {
        return (bool) developer_starter_get_first_video( $post_id );
    }
}

/**
 * 获取站内通知管理器实例
 *
 * @return \Developer_Starter\Core\Notification_Manager|null
 */
if ( ! function_exists( 'developer_starter_notifications' ) ) {
    function developer_starter_notifications() {
        if ( class_exists( 'Developer_Starter\\Core\\Notification_Manager' ) ) {
            return \Developer_Starter\Core\Notification_Manager::instance();
        }
        return null;
    }
}

/**
 * 添加站内通知
 *
 * @param int    $user_id
 * @param string $title
 * @param string $content
 * @param array  $args
 * @return int 通知ID
 */
if ( ! function_exists( 'developer_starter_add_user_notification' ) ) {
    function developer_starter_add_user_notification( $user_id, $title, $content = '', $args = array() ) {
        $manager = developer_starter_notifications();
        if ( ! $manager ) {
            return 0;
        }
        return $manager->add_notification( $user_id, $title, $content, $args );
    }
}

/**
 * 获取用户通知列表
 */
if ( ! function_exists( 'developer_starter_get_user_notifications' ) ) {
    function developer_starter_get_user_notifications( $user_id, $args = array() ) {
        $manager = developer_starter_notifications();
        if ( ! $manager ) {
            return array();
        }
        return $manager->get_user_notifications( $user_id, $args );
    }
}

/**
 * 获取用户通知数量
 */
if ( ! function_exists( 'developer_starter_get_user_notification_count' ) ) {
    function developer_starter_get_user_notification_count( $user_id, $status = 'all' ) {
        $manager = developer_starter_notifications();
        if ( ! $manager ) {
            return 0;
        }
        return $manager->count_user_notifications( $user_id, $status );
    }
}

/**
 * 获取未读通知数量
 */
if ( ! function_exists( 'developer_starter_get_unread_notification_count' ) ) {
    function developer_starter_get_unread_notification_count( $user_id ) {
        return developer_starter_get_user_notification_count( $user_id, 'unread' );
    }
}

/**
 * 标记通知已读
 */
if ( ! function_exists( 'developer_starter_mark_notification_read' ) ) {
    function developer_starter_mark_notification_read( $notice_id, $user_id = 0 ) {
        $manager = developer_starter_notifications();
        if ( ! $manager ) {
            return false;
        }
        return $manager->mark_read( $notice_id, $user_id );
    }
}

/**
 * 标记全部通知已读
 */
if ( ! function_exists( 'developer_starter_mark_all_notifications_read' ) ) {
    function developer_starter_mark_all_notifications_read( $user_id ) {
        $manager = developer_starter_notifications();
        if ( ! $manager ) {
            return false;
        }
        return $manager->mark_all_read( $user_id );
    }
}

/**
 * 清空用户全部通知
 */
if ( ! function_exists( 'developer_starter_clear_all_notifications' ) ) {
    function developer_starter_clear_all_notifications( $user_id ) {
        $manager = developer_starter_notifications();
        if ( ! $manager ) {
            return false;
        }
        return $manager->clear_all( $user_id );
    }
}
