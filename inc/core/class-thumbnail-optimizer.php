<?php
/**
 * 缩略图优化器
 * 
 * 提供动态缩略图裁剪功能，支持本地图片和CDN外链图片
 * 不依赖WordPress多尺寸缩略图机制
 * 
 * @package Developer_Starter
 */

namespace Developer_Starter\Core;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Thumbnail_Optimizer {

    /**
     * 缓存目录
     */
    private $cache_dir;

    /**
     * 缓存URL
     */
    private $cache_url;

    /**
     * 构造函数
     */
    public function __construct() {
        $upload_dir = wp_upload_dir();
        $this->cache_dir = $upload_dir['basedir'] . '/thumbnail-cache/';
        $this->cache_url = $upload_dir['baseurl'] . '/thumbnail-cache/';

        // 确保缓存目录存在
        if ( ! file_exists( $this->cache_dir ) ) {
            wp_mkdir_p( $this->cache_dir );
        }

        // 添加清除缓存钩子
        add_action( 'delete_attachment', array( $this, 'clear_attachment_cache' ) );
    }

    /**
     * 规范化用于缓存键计算的图片 URL。
     *
     * @param string $url 图片 URL。
     * @return string
     */
    private function normalize_cache_source_url( $url ) {
        $url = trim( (string) $url );
        if ( '' === $url ) {
            return '';
        }

        if ( function_exists( 'developer_starter_normalize_local_media_url' ) ) {
            $url = developer_starter_normalize_local_media_url( $url );
        }
        if ( function_exists( 'developer_starter_strip_media_url_signature' ) ) {
            $url = developer_starter_strip_media_url_signature( $url );
        }

        return trim( (string) $url );
    }

    /**
     * 获取用于兼容匹配的 URL 变体。
     *
     * @param string $url 图片 URL。
     * @return array<int,string>
     */
    private function get_cache_url_variants( $url ) {
        $variants = array();

        $raw_url = trim( (string) $url );
        if ( '' !== $raw_url ) {
            $variants[] = $raw_url;
            $raw_no_query = strtok( $raw_url, '?' );
            if ( is_string( $raw_no_query ) && '' !== $raw_no_query ) {
                $variants[] = $raw_no_query;
            }
        }

        $normalized_url = $this->normalize_cache_source_url( $raw_url );
        if ( '' !== $normalized_url ) {
            $variants[] = $normalized_url;
            $normalized_no_query = strtok( $normalized_url, '?' );
            if ( is_string( $normalized_no_query ) && '' !== $normalized_no_query ) {
                $variants[] = $normalized_no_query;
            }
        }

        $variants = array_values( array_unique( array_filter( $variants, 'strlen' ) ) );

        return $variants;
    }

    /**
     * 生成当前版本缓存键（源图哈希 + 变体哈希）。
     *
     * @param string $url 图片 URL。
     * @param int    $width 目标宽度。
     * @param int    $height 目标高度。
     * @param string $crop_position 裁剪位置。
     * @param int    $quality 图片质量。
     * @return string
     */
    private function build_cache_key( $url, $width, $height, $crop_position, $quality ) {
        $normalized_url = $this->normalize_cache_source_url( $url );
        if ( '' === $normalized_url ) {
            $normalized_url = trim( (string) $url );
        }

        $source_hash  = md5( $normalized_url );
        $variant_hash = md5(
            (int) $width . '|' .
            (int) $height . '|' .
            sanitize_key( (string) $crop_position ) . '|' .
            (int) $quality
        );

        return $source_hash . '_' . $variant_hash;
    }

    /**
     * 生成历史版本缓存键（兼容旧缓存文件）。
     *
     * @param string $url 图片 URL。
     * @param int    $width 目标宽度。
     * @param int    $height 目标高度。
     * @param string $crop_position 裁剪位置。
     * @param int    $quality 图片质量。
     * @return array<int,string>
     */
    private function build_legacy_cache_keys( $url, $width, $height, $crop_position, $quality ) {
        $keys = array();
        $variants = $this->get_cache_url_variants( $url );

        foreach ( $variants as $variant_url ) {
            $keys[] = md5( $variant_url . (int) $width . (int) $height . (string) $crop_position . (int) $quality );
        }

        return array_values( array_unique( $keys ) );
    }

    /**
     * 推断缓存文件扩展名。
     *
     * @param string $url 图片 URL。
     * @return string
     */
    private function resolve_cache_extension( $url ) {
        $extension = pathinfo( (string) parse_url( (string) $url, PHP_URL_PATH ), PATHINFO_EXTENSION );
        $extension = strtolower( trim( (string) $extension ) );
        if ( ! preg_match( '/^[a-z0-9]{2,6}$/', $extension ) ) {
            $extension = 'jpg';
        }

        return $extension;
    }

    /**
     * 获取优化后的缩略图URL
     * 
     * @param string $image_url 原始图片URL
     * @param int $width 目标宽度
     * @param int $height 目标高度
     * @param string $crop_position 裁剪位置: center, top, bottom, left, right
     * @param int $quality 图片质量 1-100
     * @return string 优化后的图片URL
     */
    public function get_optimized_url( $image_url, $width = 400, $height = 300, $crop_position = 'center', $quality = 85 ) {
        if ( empty( $image_url ) ) {
            return '';
        }

        // 检查是否启用缩略图优化
        $enable = developer_starter_get_option( 'thumbnail_optimize_enable', '' );
        if ( ! $enable ) {
            return $image_url;
        }

        // 检测CDN类型并处理
        $cdn_type = $this->detect_cdn_type( $image_url );
        
        if ( $cdn_type ) {
            return $this->get_cdn_resized_url( $image_url, $width, $height, $crop_position, $quality, $cdn_type );
        }

        // 检查是否为本地图片
        if ( $this->is_local_image( $image_url ) ) {
            return $this->get_local_resized_url( $image_url, $width, $height, $crop_position, $quality );
        }

        // 无法处理的图片，返回原始URL
        return $image_url;
    }

    /**
     * 检测CDN类型
     * 
     * @param string $url 图片URL
     * @return string|false CDN类型或false
     */
    private function detect_cdn_type( $url ) {
        // 阿里云OSS
        if ( preg_match( '/\.oss(-[a-z0-9-]+)?\.aliyuncs\.com/i', $url ) ) {
            return 'aliyun_oss';
        }

        // 腾讯云COS
        if ( preg_match( '/\.cos\.[a-z0-9-]+\.myqcloud\.com/i', $url ) ) {
            return 'tencent_cos';
        }

        // 七牛云
        if ( preg_match( '/\.(qiniucdn|qnimg|clouddn)\.com/i', $url ) ) {
            return 'qiniu';
        }

        // 又拍云
        if ( preg_match( '/\.upaiyun\.com/i', $url ) || preg_match( '/\.upyun\.com/i', $url ) ) {
            return 'upyun';
        }

        // 用户自定义CDN域名检测
        $custom_cdn_domain = developer_starter_get_option( 'thumbnail_cdn_domain', '' );
        if ( $custom_cdn_domain && strpos( $url, $custom_cdn_domain ) !== false ) {
            $custom_cdn_type = developer_starter_get_option( 'thumbnail_cdn_type', 'aliyun_oss' );
            return $custom_cdn_type;
        }

        return false;
    }

    /**
     * 获取CDN裁剪后的URL
     * 
     * @param string $url 原始URL
     * @param int $width 宽度
     * @param int $height 高度
     * @param string $crop_position 裁剪位置
     * @param int $quality 质量
     * @param string $cdn_type CDN类型
     * @return string 处理后的URL
     */
    private function get_cdn_resized_url( $url, $width, $height, $crop_position, $quality, $cdn_type ) {
        // 移除已有的处理参数
        $url = preg_replace( '/\?(x-oss-process|imageMogr2|imageView2|\/fw\/).*$/i', '', $url );

        // 获取裁剪重心点
        $gravity = $this->get_cdn_gravity( $crop_position, $cdn_type );

        switch ( $cdn_type ) {
            case 'aliyun_oss':
                // 阿里云OSS图片处理
                // 使用 m_fill 模式进行裁剪填充
                $params = "x-oss-process=image/resize,m_fill,w_{$width},h_{$height}";
                if ( $crop_position !== 'center' ) {
                    $params .= "/crop,w_{$width},h_{$height},g_{$gravity}";
                }
                $params .= "/quality,q_{$quality}";
                break;

            case 'tencent_cos':
                // 腾讯云COS图片处理
                $params = "imageMogr2/thumbnail/{$width}x{$height}!/crop/{$width}x{$height}/gravity/{$gravity}/quality/{$quality}";
                break;

            case 'qiniu':
                // 七牛云图片处理
                $params = "imageView2/1/w/{$width}/h/{$height}/q/{$quality}";
                break;

            case 'upyun':
                // 又拍云图片处理
                $params = "/fw/{$width}/fh/{$height}/fwfh/sq/quality/{$quality}";
                break;

            default:
                return $url;
        }

        $separator = strpos( $url, '?' ) !== false ? '&' : '?';
        return $url . $separator . $params;
    }

    /**
     * 获取CDN裁剪重心点参数
     * 
     * @param string $crop_position 裁剪位置
     * @param string $cdn_type CDN类型
     * @return string 重心点参数
     */
    private function get_cdn_gravity( $crop_position, $cdn_type ) {
        $gravity_map = array(
            'aliyun_oss' => array(
                'center' => 'center',
                'top'    => 'north',
                'bottom' => 'south',
                'left'   => 'west',
                'right'  => 'east',
            ),
            'tencent_cos' => array(
                'center' => 'center',
                'top'    => 'north',
                'bottom' => 'south',
                'left'   => 'west',
                'right'  => 'east',
            ),
            'qiniu' => array(
                'center' => 'center',
                'top'    => 'north',
                'bottom' => 'south',
                'left'   => 'west',
                'right'  => 'east',
            ),
            'upyun' => array(
                'center' => 'center',
                'top'    => 'top',
                'bottom' => 'bottom',
                'left'   => 'left',
                'right'  => 'right',
            ),
        );

        if ( isset( $gravity_map[ $cdn_type ][ $crop_position ] ) ) {
            return $gravity_map[ $cdn_type ][ $crop_position ];
        }

        return 'center';
    }

    /**
     * 从上传目录URL中解析相对路径
     *
     * @param string $url 图片URL
     * @param string $base_url 上传目录基础URL
     * @return string|false 上传目录相对路径或false
     */
    private function get_upload_relative_path( $url, $base_url ) {
        $url = trim( (string) $url );
        $base_url = trim( (string) $base_url );
        if ( '' === $url || '' === $base_url ) {
            return false;
        }

        $url_parts  = wp_parse_url( $url );
        $base_parts = wp_parse_url( $base_url );
        if ( ! is_array( $url_parts ) || ! is_array( $base_parts ) ) {
            return false;
        }

        $url_host  = isset( $url_parts['host'] ) ? strtolower( (string) $url_parts['host'] ) : '';
        $base_host = isset( $base_parts['host'] ) ? strtolower( (string) $base_parts['host'] ) : '';

        if ( '' !== $url_host && $url_host !== $base_host ) {
            return false;
        }

        if ( '' === $url_host && isset( $url_parts['scheme'] ) ) {
            return false;
        }

        if ( '' !== $url_host ) {
            $url_port  = isset( $url_parts['port'] ) ? (int) $url_parts['port'] : null;
            $base_port = isset( $base_parts['port'] ) ? (int) $base_parts['port'] : null;
            if ( $url_port !== $base_port ) {
                return false;
            }
        }

        $url_path  = isset( $url_parts['path'] ) ? rawurldecode( (string) $url_parts['path'] ) : '';
        $base_path = isset( $base_parts['path'] ) ? rawurldecode( (string) $base_parts['path'] ) : '';

        $url_path  = wp_normalize_path( '/' . ltrim( $url_path, '/' ) );
        $base_path = wp_normalize_path( '/' . trim( $base_path, '/' ) );

        if ( '/' !== $base_path ) {
            $base_path = rtrim( $base_path, '/' );
        }

        if ( $url_path === $base_path ) {
            return false;
        }

        if ( '/' !== $base_path && 0 !== strncmp( $url_path, $base_path . '/', strlen( $base_path ) + 1 ) ) {
            return false;
        }

        $relative_path = '/' === $base_path
            ? ltrim( $url_path, '/' )
            : ltrim( substr( $url_path, strlen( $base_path ) ), '/' );

        if ( '' === $relative_path || false !== strpos( $relative_path, "\0" ) ) {
            return false;
        }

        return $relative_path;
    }

    /**
     * 检查是否为本地图片
     * 
     * @param string $url 图片URL
     * @return bool
     */
    private function is_local_image( $url ) {
        $upload_dir = wp_upload_dir();

        if ( empty( $upload_dir['baseurl'] ) ) {
            return false;
        }

        return false !== $this->get_upload_relative_path( $url, (string) $upload_dir['baseurl'] );
    }

    /**
     * 获取本地图片裁剪后的URL
     * 
     * @param string $url 原始URL
     * @param int $width 宽度
     * @param int $height 高度
     * @param string $crop_position 裁剪位置
     * @param int $quality 质量
     * @return string 处理后的URL
     */
    private function get_local_resized_url( $url, $width, $height, $crop_position, $quality ) {
        // 本地图片文件已删除时，直接回退原URL，避免继续进入裁剪流程报错
        $original_path = $this->url_to_path( $url );
        if ( ! $original_path || ! file_exists( $original_path ) ) {
            return $url;
        }

        // 生成缓存文件名
        $cache_key = $this->build_cache_key( $url, $width, $height, $crop_position, $quality );
        $extension = $this->resolve_cache_extension( $url );
        $cache_filename = $cache_key . '.' . $extension;
        $cache_path = $this->cache_dir . $cache_filename;
        $cache_url = $this->cache_url . $cache_filename;

        $resolved_cache_path = $cache_path;
        $resolved_cache_url  = $cache_url;

        // 兼容历史缓存命名：命中后优先迁移到新键，确保后续可被精准清理。
        if ( ! file_exists( $cache_path ) ) {
            $legacy_keys = $this->build_legacy_cache_keys( $url, $width, $height, $crop_position, $quality );
            foreach ( $legacy_keys as $legacy_key ) {
                $legacy_filename = $legacy_key . '.' . $extension;
                $legacy_path = $this->cache_dir . $legacy_filename;
                if ( ! file_exists( $legacy_path ) ) {
                    continue;
                }

                if ( developer_starter_filesystem_move_file(
                    $legacy_path,
                    $cache_path,
                    array(
                        'operation'     => 'move_thumbnail_cache',
                        'allowed_roots' => array( $this->cache_dir ),
                        'context'       => array( 'component' => 'thumbnail_cache' ),
                    )
                ) ) {
                    $resolved_cache_path = $cache_path;
                    $resolved_cache_url  = $cache_url;
                } else {
                    $resolved_cache_path = $legacy_path;
                    $resolved_cache_url  = $this->cache_url . $legacy_filename;
                }
                break;
            }
        }

        // 如果缓存存在且未过期，直接返回
        if ( file_exists( $resolved_cache_path ) ) {
            // 检查原图是否更新（通过比较修改时间）
            if ( $original_path && file_exists( $original_path ) ) {
                if ( filemtime( $resolved_cache_path ) >= filemtime( $original_path ) ) {
                    return $this->filter_cache_url( $resolved_cache_url, $resolved_cache_path, $url );
                }
            } else {
                // 远程图片，缓存有效期7天
                if ( time() - filemtime( $resolved_cache_path ) < 7 * DAY_IN_SECONDS ) {
                    return $this->filter_cache_url( $resolved_cache_url, $resolved_cache_path, $url );
                }
            }
        }

        // 创建裁剪后的图片
        $result = $this->create_resized_image( $url, $cache_path, $width, $height, $crop_position, $quality );

        if ( $result ) {
            return $this->filter_cache_url( $cache_url, $cache_path, $url );
        }

        // 裁剪失败，返回原始URL
        return $url;
    }

    /**
     * 允许云存储插件在返回前同步动态生成的缩略图。
     *
     * @param string $cache_url  缓存文件 URL。
     * @param string $cache_path 缓存文件本地路径。
     * @param string $source_url 原图 URL。
     * @return string
     */
    private function filter_cache_url( $cache_url, $cache_path, $source_url ) {
        return (string) apply_filters(
            'developer_starter_thumbnail_cache_url',
            $cache_url,
            $cache_path,
            $source_url
        );
    }

    /**
     * 创建裁剪后的图片
     * 
     * @param string $source_url 原始图片URL
     * @param string $dest_path 目标路径
     * @param int $width 宽度
     * @param int $height 高度
     * @param string $crop_position 裁剪位置
     * @param int $quality 质量
     * @return bool 是否成功
     */
    private function create_resized_image( $source_url, $dest_path, $width, $height, $crop_position, $quality ) {
        // 获取图片编辑器
        $source_path = $this->url_to_path( $source_url );

        // 本地URL但文件已不存在时，不要当远程图下载
        if ( $this->is_local_image( $source_url ) && ( ! $source_path || ! file_exists( $source_path ) ) ) {
            return false;
        }
        
        if ( $source_path && file_exists( $source_path ) ) {
            // 本地文件
            $editor = wp_get_image_editor( $source_path );
        } else {
            // 远程文件，先下载
            if ( ! \function_exists( 'download_url' ) ) {
                require_once ABSPATH . 'wp-admin/includes/file.php';
            }
            $temp_file = \download_url( $source_url, 30 );
            if ( is_wp_error( $temp_file ) ) {
                return false;
            }
            $editor = wp_get_image_editor( $temp_file );
            // 处理完成后删除临时文件
            register_shutdown_function( function() use ( $temp_file ) {
                developer_starter_filesystem_delete_temp_file( $temp_file );
            });
        }

        if ( is_wp_error( $editor ) ) {
            return false;
        }

        // 获取原始尺寸
        $original_size = $editor->get_size();
        $orig_width = $original_size['width'];
        $orig_height = $original_size['height'];

        // 计算裁剪区域
        $crop_args = $this->calculate_crop_area( $orig_width, $orig_height, $width, $height, $crop_position );

        // 裁剪
        $result = $editor->crop( 
            $crop_args['x'], 
            $crop_args['y'], 
            $crop_args['src_width'], 
            $crop_args['src_height'], 
            $width, 
            $height 
        );

        if ( is_wp_error( $result ) ) {
            return false;
        }

        // 设置质量
        $editor->set_quality( $quality );

        // 保存
        $saved = $editor->save( $dest_path );

        return ! is_wp_error( $saved );
    }

    /**
     * 计算裁剪区域
     * 
     * @param int $orig_width 原始宽度
     * @param int $orig_height 原始高度
     * @param int $dest_width 目标宽度
     * @param int $dest_height 目标高度
     * @param string $crop_position 裁剪位置
     * @return array 裁剪参数
     */
    private function calculate_crop_area( $orig_width, $orig_height, $dest_width, $dest_height, $crop_position ) {
        // 计算缩放比例
        $ratio_w = $orig_width / $dest_width;
        $ratio_h = $orig_height / $dest_height;
        $ratio = min( $ratio_w, $ratio_h );

        // 源图片裁剪尺寸
        $src_width = round( $dest_width * $ratio );
        $src_height = round( $dest_height * $ratio );

        // 计算裁剪起点
        switch ( $crop_position ) {
            case 'top':
                $x = round( ( $orig_width - $src_width ) / 2 );
                $y = 0;
                break;
            case 'bottom':
                $x = round( ( $orig_width - $src_width ) / 2 );
                $y = $orig_height - $src_height;
                break;
            case 'left':
                $x = 0;
                $y = round( ( $orig_height - $src_height ) / 2 );
                break;
            case 'right':
                $x = $orig_width - $src_width;
                $y = round( ( $orig_height - $src_height ) / 2 );
                break;
            case 'center':
            default:
                $x = round( ( $orig_width - $src_width ) / 2 );
                $y = round( ( $orig_height - $src_height ) / 2 );
                break;
        }

        return array(
            'x' => max( 0, $x ),
            'y' => max( 0, $y ),
            'src_width' => $src_width,
            'src_height' => $src_height,
        );
    }

    /**
     * URL转换为本地路径
     * 
     * @param string $url 图片URL
     * @return string|false 本地路径或false
     */
    private function url_to_path( $url ) {
        $upload_dir = wp_upload_dir();

        if ( empty( $upload_dir['baseurl'] ) || empty( $upload_dir['basedir'] ) ) {
            return false;
        }

        $base_url = (string) $upload_dir['baseurl'];
        $base_dir = (string) $upload_dir['basedir'];

        $uploads_real = realpath( $base_dir );
        if ( false === $uploads_real ) {
            return false;
        }

        $relative_path = $this->get_upload_relative_path( $url, $base_url );
        if ( false === $relative_path ) {
            return false;
        }

        $candidate_path = trailingslashit( $base_dir ) . $relative_path;
        $candidate_real = realpath( $candidate_path );
        if ( false === $candidate_real ) {
            return false;
        }

        $uploads_real  = rtrim( wp_normalize_path( $uploads_real ), '/' );
        $candidate_real = wp_normalize_path( $candidate_real );

        if ( $candidate_real !== $uploads_real && 0 !== strncmp( $candidate_real, $uploads_real . '/', strlen( $uploads_real ) + 1 ) ) {
            return false;
        }

        return $candidate_real;
    }

    /**
     * 清除附件缓存
     * 
     * @param int $attachment_id 附件ID
     */
    public function clear_attachment_cache( $attachment_id ) {
        $url = wp_get_attachment_url( $attachment_id );
        if ( ! $url || ! is_dir( $this->cache_dir ) ) {
            return;
        }

        $files = glob( $this->cache_dir . '*' );
        if ( ! is_array( $files ) || empty( $files ) ) {
            return;
        }

        $normalized_url = $this->normalize_cache_source_url( $url );
        if ( '' === $normalized_url ) {
            $normalized_url = trim( (string) $url );
        }
        $source_hash = md5( $normalized_url );
        $legacy_file_index = array();

        foreach ( $files as $file ) {
            if ( ! is_file( $file ) ) {
                continue;
            }

            $filename = pathinfo( basename( $file ), PATHINFO_FILENAME );
            if ( '' === $filename ) {
                continue;
            }

            // 新版本键：source_hash + '_' + variant_hash
            if ( strpos( $filename, $source_hash . '_' ) === 0 ) {
                developer_starter_filesystem_delete_file(
                    $file,
                    array(
                        'operation'     => 'delete_thumbnail_cache',
                        'allowed_roots' => array( $this->cache_dir ),
                        'context'       => array( 'component' => 'thumbnail_cache' ),
                    )
                );
                continue;
            }

            // 兼容旧清理逻辑：曾尝试按 md5(url) 前缀删除。
            if ( strpos( $filename, substr( md5( (string) $url ), 0, 8 ) ) === 0 ) {
                developer_starter_filesystem_delete_file(
                    $file,
                    array(
                        'operation'     => 'delete_thumbnail_legacy_cache',
                        'allowed_roots' => array( $this->cache_dir ),
                        'context'       => array( 'component' => 'thumbnail_cache' ),
                    )
                );
                continue;
            }

            // 历史缓存键是纯 32 位 md5：建立索引后按候选键精准删除。
            if ( preg_match( '/^[a-f0-9]{32}$/', $filename ) ) {
                if ( ! isset( $legacy_file_index[ $filename ] ) ) {
                    $legacy_file_index[ $filename ] = array();
                }
                $legacy_file_index[ $filename ][] = $file;
            }
        }

        if ( empty( $legacy_file_index ) ) {
            return;
        }

        $legacy_sizes = array(
            array( 150, 150 ),
            array(
                (int) developer_starter_get_option( 'thumbnail_width', 400 ),
                (int) developer_starter_get_option( 'thumbnail_height', 300 ),
            ),
            array( 800, 600 ),
            array( 400, 300 ),
        );

        if ( function_exists( 'wp_get_registered_image_subsizes' ) ) {
            $registered_sizes = wp_get_registered_image_subsizes();
            if ( is_array( $registered_sizes ) ) {
                foreach ( $registered_sizes as $size_data ) {
                    if ( ! is_array( $size_data ) ) {
                        continue;
                    }
                    $size_w = isset( $size_data['width'] ) ? (int) $size_data['width'] : 0;
                    $size_h = isset( $size_data['height'] ) ? (int) $size_data['height'] : 0;
                    if ( $size_w > 0 && $size_h > 0 ) {
                        $legacy_sizes[] = array( $size_w, $size_h );
                    }
                }
            }
        }

        $normalized_sizes = array();
        foreach ( $legacy_sizes as $size_pair ) {
            $size_w = isset( $size_pair[0] ) ? (int) $size_pair[0] : 0;
            $size_h = isset( $size_pair[1] ) ? (int) $size_pair[1] : 0;
            if ( $size_w < 1 || $size_h < 1 ) {
                continue;
            }
            $normalized_sizes[ $size_w . 'x' . $size_h ] = array( $size_w, $size_h );
        }

        $crop_positions = array( 'center', 'top', 'bottom', 'left', 'right' );
        $current_crop = sanitize_key( (string) developer_starter_get_option( 'thumbnail_crop_position', 'center' ) );
        if ( $current_crop !== '' ) {
            $crop_positions[] = $current_crop;
        }
        $crop_positions = array_values( array_unique( array_filter( $crop_positions, 'strlen' ) ) );

        $quality_values = array( 85, (int) developer_starter_get_option( 'thumbnail_quality', 85 ) );
        $quality_values = array_values( array_unique( array_filter( array_map( 'intval', $quality_values ) ) ) );
        if ( empty( $quality_values ) ) {
            $quality_values = array( 85 );
        }

        $legacy_hashes = array();
        foreach ( $normalized_sizes as $size_pair ) {
            foreach ( $crop_positions as $crop_position ) {
                foreach ( $quality_values as $quality ) {
                    $keys = $this->build_legacy_cache_keys( $url, $size_pair[0], $size_pair[1], $crop_position, $quality );
                    foreach ( $keys as $key ) {
                        $legacy_hashes[ $key ] = true;
                    }
                }
            }
        }

        foreach ( array_keys( $legacy_hashes ) as $legacy_hash ) {
            if ( empty( $legacy_file_index[ $legacy_hash ] ) ) {
                continue;
            }
            foreach ( $legacy_file_index[ $legacy_hash ] as $legacy_file ) {
                developer_starter_filesystem_delete_file(
                    $legacy_file,
                    array(
                        'operation'     => 'delete_thumbnail_legacy_cache',
                        'allowed_roots' => array( $this->cache_dir ),
                        'context'       => array( 'component' => 'thumbnail_cache' ),
                    )
                );
            }
        }
    }

    /**
     * 清除所有缓存
     */
    public function clear_all_cache() {
        $deleted = 0;
        $freed = 0;
        $failed = 0;
        $files = is_dir( $this->cache_dir ) ? glob( $this->cache_dir . '*' ) : array();
        foreach ( is_array( $files ) ? $files : array() as $file ) {
            if ( ! is_file( $file ) ) {
                continue;
            }
            $size = (int) filesize( $file );
            if ( developer_starter_filesystem_delete_file(
                $file,
                array(
                    'operation'     => 'delete_thumbnail_cache',
                    'allowed_roots' => array( $this->cache_dir ),
                    'context'       => array( 'component' => 'thumbnail_cache' ),
                )
            ) ) {
                $deleted++;
                $freed += $size;
            } else {
                $failed++;
            }
        }
        return array( 'deleted_files' => $deleted, 'freed_bytes' => $freed, 'failed_files' => $failed );
    }

    /**
     * 返回缓存目录及文件统计。
     *
     * @return array<string,mixed>
     */
    public function get_cache_stats() {
        $count = 0;
        $bytes = 0;
        $files = is_dir( $this->cache_dir ) ? glob( $this->cache_dir . '*' ) : array();
        foreach ( is_array( $files ) ? $files : array() as $file ) {
            if ( is_file( $file ) ) {
                $count++;
                $bytes += (int) filesize( $file );
            }
        }

        return array(
            'count'      => $count,
            'bytes'      => $bytes,
            'size_human' => function_exists( 'size_format' ) ? size_format( $bytes, 2 ) : $bytes . ' B',
            'dir'        => $this->cache_dir,
        );
    }

    /**
     * 清除没有被当前文章/页面封面配置使用的缓存文件。
     *
     * @return array<string,int>
     */
    public function clear_unused_cache() {
        if ( function_exists( 'wp_raise_memory_limit' ) ) {
            wp_raise_memory_limit( 'admin' );
        }

        $active_source_hashes = array();
        $active_legacy_keys = array();
        $post_ids = get_posts( array(
            'post_type'      => 'any',
            'post_status'    => array( 'publish', 'private', 'future', 'pending', 'draft' ),
            'posts_per_page' => -1,
            'fields'         => 'ids',
            'no_found_rows'  => true,
        ) );

        foreach ( is_array( $post_ids ) ? $post_ids : array() as $post_id ) {
            $sources = array();
            if ( function_exists( 'developer_starter_get_custom_featured_image_url' ) ) {
                $sources[] = (string) developer_starter_get_custom_featured_image_url( $post_id );
            }
            $thumbnail_id = get_post_thumbnail_id( $post_id );
            if ( $thumbnail_id ) {
                $sources[] = (string) wp_get_attachment_url( $thumbnail_id );
            }
            $sources[] = (string) get_post_meta( $post_id, '_ds_extracted_thumb_url', true );
            if ( function_exists( 'developer_starter_extract_first_image_url_from_content' ) ) {
                $post = get_post( $post_id );
                if ( $post instanceof \WP_Post ) {
                    $sources[] = developer_starter_extract_first_image_url_from_content( $post->post_content );
                }
            }

            $sizes = array( array( 150, 150 ), array( 800, 600 ), array( 768, 432 ) );
            $sizes[] = array(
                (int) developer_starter_get_option( 'thumbnail_width', 400 ),
                (int) developer_starter_get_option( 'thumbnail_height', 300 ),
            );
            $crop = (string) developer_starter_get_option( 'thumbnail_crop_position', 'center' );
            $quality = (int) developer_starter_get_option( 'thumbnail_quality', 85 );
            foreach ( array_unique( array_filter( array_map( 'trim', $sources ), 'strlen' ) ) as $source ) {
                $normalized_source = $this->normalize_cache_source_url( $source );
                if ( '' === $normalized_source ) {
                    $normalized_source = $source;
                }
                $active_source_hashes[ md5( $normalized_source ) ] = true;
                foreach ( $sizes as $size ) {
                    foreach ( $this->build_legacy_cache_keys( $source, $size[0], $size[1], $crop, $quality ) as $legacy_key ) {
                        $active_legacy_keys[ $legacy_key ] = true;
                    }
                }
            }
        }

        $deleted = 0;
        $freed = 0;
        $failed = 0;
        $files = is_dir( $this->cache_dir ) ? glob( $this->cache_dir . '*' ) : array();
        foreach ( is_array( $files ) ? $files : array() as $file ) {
            if ( ! is_file( $file ) ) {
                continue;
            }
            $name = pathinfo( basename( $file ), PATHINFO_FILENAME );
            $source_hash = false !== strpos( $name, '_' ) ? strstr( $name, '_', true ) : '';
            // 旧格式只有组合哈希，无法可靠反推出源图；仅删除能明确判断归属的新格式缓存。
            if ( '' === $source_hash && preg_match( '/^[a-f0-9]{32}$/', $name ) && ! isset( $active_legacy_keys[ $name ] ) ) {
                continue;
            }
            if ( isset( $active_source_hashes[ $source_hash ] ) || isset( $active_legacy_keys[ $name ] ) ) {
                continue;
            }
            $size = (int) filesize( $file );
            if ( function_exists( 'developer_starter_filesystem_delete_file' )
                ? developer_starter_filesystem_delete_file( $file, array( 'operation' => 'delete_unused_thumbnail_cache', 'allowed_roots' => array( $this->cache_dir ), 'context' => array( 'component' => 'thumbnail_cache' ) ) )
                : @unlink( $file ) ) {
                $deleted++;
                $freed += $size;
            } else {
                $failed++;
            }
        }

        return array( 'deleted_files' => $deleted, 'freed_bytes' => $freed, 'failed_files' => $failed );
    }

    /**
     * 获取缓存大小
     * 
     * @return int 缓存大小（字节）
     */
    public function get_cache_size() {
        $size = 0;
        $files = glob( $this->cache_dir . '*' );
        foreach ( $files as $file ) {
            $size += filesize( $file );
        }
        return $size;
    }
}
