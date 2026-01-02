<?php
/**
 * 辅助函数
 *
 * @package Developer_Starter
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 获取主题选项值
 *
 * @param string $key 选项键名。
 * @param mixed  $default 默认值。
 * @return mixed
 */
if ( ! function_exists( 'developer_starter_get_option' ) ) {
    function developer_starter_get_option( $key, $default = '' ) {
        // 使用静态缓存避免重复数据库查询
        static $options = null;
        if ( $options === null ) {
            $options = get_option( 'developer_starter_options', array() );
        }
        return isset( $options[ $key ] ) ? $options[ $key ] : $default;
    }
}

/**
 * 渲染模块
 *
 * @param string $module_name 模块名称。
 * @param array  $args 模块参数。
 */
if ( ! function_exists( 'developer_starter_render_module' ) ) {
    function developer_starter_render_module( $module_name, $args = array() ) {
        $manager = \Developer_Starter\Modules\Module_Manager::get_instance();
        $manager->render_module( $module_name, $args );
    }
}

/**
 * 获取SVG图标
 *
 * @param string $icon 图标名称。
 * @param array  $args 图标参数。
 * @return string SVG HTML代码。
 */
if ( ! function_exists( 'developer_starter_get_icon' ) ) {
    function developer_starter_get_icon( $icon, $args = array() ) {
        $defaults = array(
            'class'  => '',
            'width'  => 24,
            'height' => 24,
        );
        $args = wp_parse_args( $args, $defaults );
        
        $icon_path = DEVELOPER_STARTER_DIR . '/assets/images/icons/' . $icon . '.svg';
        if ( file_exists( $icon_path ) ) {
            $svg = file_get_contents( $icon_path );
            return str_replace( '<svg', '<svg class="icon icon-' . esc_attr( $icon ) . ' ' . esc_attr( $args['class'] ) . '"', $svg );
        }
        return '';
    }
}

/**
 * 获取面包屑导航
 */
if ( ! function_exists( 'developer_starter_breadcrumb' ) ) {
    function developer_starter_breadcrumb() {
        if ( function_exists( 'yoast_breadcrumb' ) ) {
            yoast_breadcrumb( '<nav class="breadcrumb">', '</nav>' );
        } elseif ( function_exists( 'rank_math_the_breadcrumbs' ) ) {
            rank_math_the_breadcrumbs();
        } else {
            developer_starter_custom_breadcrumb();
        }
    }
}

/**
 * 自定义面包屑导航
 */
if ( ! function_exists( 'developer_starter_custom_breadcrumb' ) ) {
    function developer_starter_custom_breadcrumb() {
        $sep = '<span class="breadcrumb-sep">/</span>';
        echo '<nav class="breadcrumb" aria-label="' . esc_attr__( '面包屑导航', 'developer-starter' ) . '">';
        echo '<a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html__( '首页', 'developer-starter' ) . '</a>';
        
        if ( is_category() || is_single() ) {
            $cats = get_the_category();
            if ( ! empty( $cats ) ) {
                echo $sep . '<a href="' . esc_url( get_category_link( $cats[0]->term_id ) ) . '">' . esc_html( $cats[0]->name ) . '</a>';
            }
            if ( is_single() ) {
                echo $sep . '<span class="current">' . get_the_title() . '</span>';
            }
        } elseif ( is_page() ) {
            echo $sep . '<span class="current">' . get_the_title() . '</span>';
        } elseif ( is_search() ) {
            echo $sep . '<span class="current">' . esc_html__( '搜索结果', 'developer-starter' ) . '</span>';
        } elseif ( is_404() ) {
            echo $sep . '<span class="current">' . esc_html__( '404', 'developer-starter' ) . '</span>';
        }
        echo '</nav>';
    }
}

/**
 * 获取网站Logo
 */
if ( ! function_exists( 'developer_starter_get_logo' ) ) {
    function developer_starter_get_logo() {
        if ( has_custom_logo() ) {
            the_custom_logo();
        } else {
            echo '<a href="' . esc_url( home_url( '/' ) ) . '" class="site-name">' . esc_html( get_bloginfo( 'name' ) ) . '</a>';
        }
    }
}

/**
 * 渲染页面模块
 */
if ( ! function_exists( 'developer_starter_render_page_modules' ) ) {
    function developer_starter_render_page_modules( $post_id = null ) {
        $manager = \Developer_Starter\Modules\Module_Manager::get_instance();
        $manager->render_page_modules( $post_id );
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
        
        $post = get_post( $post_id );
        if ( ! $post ) {
            return false;
        }
        
        $content = $post->post_content;
        
        // 先尝试解析Gutenberg块中的图片
        if ( function_exists( 'parse_blocks' ) ) {
            $blocks = parse_blocks( $content );
            $image_url = developer_starter_find_image_in_blocks( $blocks );
            if ( $image_url ) {
                return $image_url;
            }
        }
        
        // 从文章内容中匹配img标签的src属性
        // 支持多种格式：src="url", src='url', srcset等
        if ( preg_match( '/<img[^>]+src=[\'"]([^\'"]+)[\'"][^>]*>/i', $content, $match ) ) {
            return $match[1];
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
            return wp_get_attachment_url( $attachments[0]->ID );
        }
        
        return false;
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
add_action( 'save_post', 'developer_starter_auto_set_featured_image', 10, 3 );
if ( ! function_exists( 'developer_starter_auto_set_featured_image' ) ) {
    function developer_starter_auto_set_featured_image( $post_id, $post, $update ) {
        // 只处理文章和产品
        if ( ! in_array( $post->post_type, array( 'post', 'product', 'page' ) ) ) {
            return;
        }
        
        // 已有特色图片则跳过
        if ( has_post_thumbnail( $post_id ) ) {
            return;
        }
        
        // 从内容中获取第一张图片的附件ID
        preg_match_all( '/<img[^>]+src=[\'"]([^\'"]+)[\'"][^>]*>/i', $post->post_content, $matches );
        
        if ( ! empty( $matches[1][0] ) ) {
            $image_url = $matches[1][0];
            
            // 尝试获取附件ID
            $attachment_id = attachment_url_to_postid( $image_url );
            
            if ( $attachment_id ) {
                set_post_thumbnail( $post_id, $attachment_id );
            }
        }
    }
}

/**
 * 图片延迟加载
 * 为文章内容中的图片添加 loading="lazy" 属性
 */
add_filter( 'the_content', 'developer_starter_lazy_load_images', 99 );
add_filter( 'post_thumbnail_html', 'developer_starter_lazy_load_images', 99 );
if ( ! function_exists( 'developer_starter_lazy_load_images' ) ) {
    function developer_starter_lazy_load_images( $content ) {
        if ( ! developer_starter_get_option( 'lazy_load_images', '' ) ) {
            return $content;
        }
        
        // 为没有 loading 属性的 img 标签添加 loading="lazy"
        $content = preg_replace(
            '/<img(?![^>]*loading=)([^>]*)>/i',
            '<img loading="lazy"$1>',
            $content
        );
        
        return $content;
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
 * WebP 图片转换
 * 上传图片时自动生成 WebP 格式副本
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
        
        // 只转换 jpg/png/gif
        if ( ! in_array( $ext, array( 'jpg', 'jpeg', 'png', 'gif' ) ) ) {
            return $metadata;
        }
        
        $quality = (int) developer_starter_get_option( 'webp_quality', '80' );
        $quality = max( 1, min( 100, $quality ) );
        
        // 转换原图
        developer_starter_convert_to_webp( $file, $quality );
        
        // 转换各尺寸
        if ( ! empty( $metadata['sizes'] ) ) {
            $upload_dir = dirname( $file );
            foreach ( $metadata['sizes'] as $size => $size_info ) {
                $size_file = $upload_dir . '/' . $size_info['file'];
                if ( file_exists( $size_file ) ) {
                    developer_starter_convert_to_webp( $size_file, $quality );
                }
            }
        }
        
        return $metadata;
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
            case 'gif':
                $image = @imagecreatefromgif( $file );
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
                $subject = sprintf( '[%s] 登录安全提醒', get_bloginfo( 'name' ) );
                $message = sprintf(
                    "用户名 %s 因多次登录失败已被临时锁定。\n\nIP 地址: %s\n时间: %s\n锁定时长: %d 分钟",
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

/**
 * 获取客户端 IP
 */
if ( ! function_exists( 'developer_starter_get_client_ip' ) ) {
    function developer_starter_get_client_ip() {
        $ip = '';
        
        if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
            $ip = explode( ',', $_SERVER['HTTP_X_FORWARDED_FOR'] )[0];
        } elseif ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        
        return sanitize_text_field( trim( $ip ) );
    }
}

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
        $username = isset( $_POST['log'] ) ? sanitize_user( $_POST['log'] ) : '';
        if ( empty( $username ) ) {
            return $error;
        }
        
        $ip = developer_starter_get_client_ip();
        $transient_key = 'login_attempts_' . md5( $ip . $username );
        $attempts = (int) get_transient( $transient_key );
        $max_attempts = (int) developer_starter_get_option( 'login_max_attempts', '5' );
        
        $remaining = $max_attempts - $attempts;
        
        if ( $remaining > 0 && $remaining < $max_attempts ) {
            $error .= sprintf( '<br><strong>剩余尝试次数：%d</strong>', $remaining );
        }
        
        return $error;
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
