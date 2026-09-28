<?php
/**
 * 运行时安全文件系统辅助函数。
 *
 * @package Developer_Starter
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! function_exists( 'developer_starter_filesystem_normalize_path' ) ) {
    /**
     * 规范化文件系统路径以便安全比对。
     *
     * @param string $path Filesystem path.
     * @return string
     */
    function developer_starter_filesystem_normalize_path( $path ) {
        $path = wp_normalize_path( (string) $path );
        return rtrim( $path, '/' );
    }
}

if ( ! function_exists( 'developer_starter_filesystem_upload_basedir' ) ) {
    /**
     * 获取规范化后的 uploads 上传基础目录。
     *
     * @return string
     */
    function developer_starter_filesystem_upload_basedir() {
        $uploads = wp_upload_dir( null, false );
        if ( ! empty( $uploads['error'] ) || empty( $uploads['basedir'] ) ) {
            return '';
        }

        $base_dir = (string) $uploads['basedir'];
        if ( ! is_dir( $base_dir ) && ! wp_mkdir_p( $base_dir ) ) {
            return '';
        }

        $real = realpath( $base_dir );
        return false === $real ? '' : developer_starter_filesystem_normalize_path( $real );
    }
}

if ( ! function_exists( 'developer_starter_filesystem_temp_roots' ) ) {
    /**
     * 获取 WordPress 与 PHP 上传工作流允许使用的临时根目录。
     *
     * @return array<int,string>
     */
    function developer_starter_filesystem_temp_roots() {
        $roots = array();

        if ( function_exists( 'get_temp_dir' ) ) {
            $roots[] = get_temp_dir();
        }

        $upload_tmp_dir = ini_get( 'upload_tmp_dir' );
        if ( is_string( $upload_tmp_dir ) && '' !== trim( $upload_tmp_dir ) ) {
            $roots[] = $upload_tmp_dir;
        }

        $system_tmp = sys_get_temp_dir();
        if ( is_string( $system_tmp ) && '' !== trim( $system_tmp ) ) {
            $roots[] = $system_tmp;
        }

        $normalized = array();
        foreach ( $roots as $root ) {
            $real = realpath( (string) $root );
            if ( false === $real ) {
                continue;
            }
            $normalized[] = developer_starter_filesystem_normalize_path( $real );
        }

        return array_values( array_unique( $normalized ) );
    }
}

if ( ! function_exists( 'developer_starter_filesystem_theme_generated_asset_files' ) ) {
    /**
     * 允许由后台备用 CSS 压缩器生成的资源文件白名单。
     *
     * @return array<int,string>
     */
    function developer_starter_filesystem_theme_generated_asset_files() {
        $files = array();
        if ( defined( 'DEVELOPER_STARTER_DIR' ) ) {
            $files = array(
                DEVELOPER_STARTER_DIR . '/assets/css/main.min.css',
                DEVELOPER_STARTER_DIR . '/assets/css/modules.min.css',
                DEVELOPER_STARTER_DIR . '/assets/css/modules-hero.min.css',
            );
        }

        /**
         * 过滤允许运行时安全生成的主题静态资源白名单。
         *
         * 默认白名单严格限制可写入文件，防止
         * 整个主题目录被运行时随意写入。
         *
         * @param array<int,string> $files Allowed file paths.
         */
        return array_values( array_unique( array_map( 'developer_starter_filesystem_normalize_path', (array) apply_filters( 'developer_starter_filesystem_theme_generated_asset_files', $files ) ) ) );
    }
}

if ( ! function_exists( 'developer_starter_filesystem_log_failure' ) ) {
    /**
     * 记录文件系统操作失败日志。
     *
     * @param string $operation Operation key.
     * @param string $path      Target path.
     * @param string $message   Failure message.
     * @param array  $context   Extra context.
     * @return void
     */
    function developer_starter_filesystem_log_failure( $operation, $path, $message, $context = array() ) {
        $operation = sanitize_key( (string) $operation );
        $path      = developer_starter_filesystem_normalize_path( (string) $path );
        $message   = sanitize_text_field( (string) $message );

        /**
         * 文件系统安全受限操作失败时触发。
         *
         * @param string $operation Operation key.
         * @param string $path      Normalized target path.
         * @param string $message   Failure message.
         * @param array  $context   Extra context.
         */
        do_action( 'developer_starter_filesystem_failure', $operation, $path, $message, $context );

        if ( ! apply_filters( 'developer_starter_filesystem_log_failures', true, $operation, $path, $message, $context ) ) {
            return;
        }

        developer_starter_log(
            'filesystem',
            'Guarded filesystem operation failed.',
            array_merge(
                array(
                    'operation' => $operation,
                    'path'      => $path,
                    'message'   => $message,
                ),
                is_array( $context ) ? $context : array()
            ),
            'error'
        );
    }
}

if ( ! function_exists( 'developer_starter_filesystem_path_is_within_root' ) ) {
    /**
     * 检查文件路径是否位于允许的根目录下。
     *
     * @param string $path              Target path.
     * @param string $root              Allowed root.
     * @param bool   $target_must_exist Whether the target itself must exist.
     * @return bool
     */
    function developer_starter_filesystem_path_is_within_root( $path, $root, $target_must_exist = false ) {
        $root_real = realpath( (string) $root );
        if ( false === $root_real ) {
            return false;
        }

        $target_path = $target_must_exist ? (string) $path : dirname( (string) $path );
        $target_real = realpath( $target_path );
        if ( false === $target_real ) {
            return false;
        }

        $root_real   = developer_starter_filesystem_normalize_path( $root_real );
        $target_real = developer_starter_filesystem_normalize_path( $target_real );

        return $target_real === $root_real || 0 === strpos( $target_real, $root_real . '/' );
    }
}

if ( ! function_exists( 'developer_starter_filesystem_path_is_allowed' ) ) {
    /**
     * 检查文件路径是否在允许的白名单内。
     *
     * @param string $path Target path.
     * @param array  $args Allowed roots/files and target mode.
     * @return bool
     */
    function developer_starter_filesystem_path_is_allowed( $path, $args = array() ) {
        $path = (string) $path;
        if ( '' === trim( $path ) || false !== strpos( $path, "\0" ) ) {
            return false;
        }

        $allowed_roots = array_key_exists( 'allowed_roots', $args )
            ? (array) $args['allowed_roots']
            : array_filter( array( developer_starter_filesystem_upload_basedir() ) );
        $allowed_files = array_key_exists( 'allowed_files', $args ) ? (array) $args['allowed_files'] : array();
        $target_must_exist = ! empty( $args['target_must_exist'] );

        $normalized_path = developer_starter_filesystem_normalize_path( $path );
        foreach ( $allowed_files as $allowed_file ) {
            if ( $normalized_path === developer_starter_filesystem_normalize_path( (string) $allowed_file ) ) {
                return true;
            }
        }

        foreach ( $allowed_roots as $root ) {
            if ( '' === (string) $root ) {
                continue;
            }
            if ( developer_starter_filesystem_path_is_within_root( $path, (string) $root, $target_must_exist ) ) {
                return true;
            }
        }

        return false;
    }
}

if ( ! function_exists( 'developer_starter_filesystem_write_file' ) ) {
    /**
     * 校验白名单路径后安全写入文件。
     *
     * @param string $path    Target path.
     * @param string $content File content.
     * @param array  $args    Optional allowlist/settings.
     * @return bool
     */
    function developer_starter_filesystem_write_file( $path, $content, $args = array() ) {
        $defaults = array(
            'operation'     => 'write_file',
            'flags'         => LOCK_EX,
            'create_parent' => false,
            'context'       => array(),
        );
        $args = wp_parse_args( $args, $defaults );

        if ( ! is_string( $content ) ) {
            developer_starter_filesystem_log_failure( $args['operation'], $path, 'Content must be a string.', (array) $args['context'] );
            return false;
        }

        if ( ! developer_starter_filesystem_path_is_allowed( $path, $args ) ) {
            developer_starter_filesystem_log_failure( $args['operation'], $path, 'Path is not allowlisted.', (array) $args['context'] );
            return false;
        }

        $dir = dirname( (string) $path );
        if ( ! is_dir( $dir ) ) {
            if ( empty( $args['create_parent'] ) || ! wp_mkdir_p( $dir ) ) {
                developer_starter_filesystem_log_failure( $args['operation'], $path, 'Parent directory is not available.', (array) $args['context'] );
                return false;
            }
        }

        if ( function_exists( 'wp_is_writable' ) && ! wp_is_writable( $dir ) ) {
            developer_starter_filesystem_log_failure( $args['operation'], $path, 'Parent directory is not writable.', (array) $args['context'] );
            return false;
        }

        $written = file_put_contents( (string) $path, $content, (int) $args['flags'] ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents -- Central guarded runtime filesystem helper with path allowlists.
        if ( false === $written ) {
            developer_starter_filesystem_log_failure( $args['operation'], $path, 'Unable to write file.', (array) $args['context'] );
            return false;
        }

        return true;
    }
}

if ( ! function_exists( 'developer_starter_filesystem_write_theme_generated_asset' ) ) {
    /**
     * 安全写入白名单中允许生成的主题静态资源。
     *
     * @param string $path    Target path.
     * @param string $content File content.
     * @return bool
     */
    function developer_starter_filesystem_write_theme_generated_asset( $path, $content ) {
        return developer_starter_filesystem_write_file(
            $path,
            $content,
            array(
                'operation'     => 'write_theme_generated_asset',
                'allowed_roots' => array(),
                'allowed_files' => developer_starter_filesystem_theme_generated_asset_files(),
            )
        );
    }
}

if ( ! function_exists( 'developer_starter_filesystem_write_temp_file' ) ) {
    /**
     * 在已批准的临时目录内安全写入临时文件。
     *
     * @param string $path    Temporary file path.
     * @param string $content File content.
     * @return bool
     */
    function developer_starter_filesystem_write_temp_file( $path, $content ) {
        return developer_starter_filesystem_write_file(
            $path,
            $content,
            array(
                'operation'         => 'write_temp_file',
                'allowed_roots'     => developer_starter_filesystem_temp_roots(),
                'target_must_exist' => true,
            )
        );
    }
}

if ( ! function_exists( 'developer_starter_filesystem_delete_file' ) ) {
    /**
     * 校验白名单路径后安全删除文件。
     *
     * @param string $path Target file path.
     * @param array  $args Optional allowlist/settings.
     * @return bool
     */
    function developer_starter_filesystem_delete_file( $path, $args = array() ) {
        $defaults = array(
            'operation'         => 'delete_file',
            'target_must_exist' => true,
            'context'           => array(),
        );
        $args = wp_parse_args( $args, $defaults );

        if ( ! is_file( (string) $path ) ) {
            return false;
        }

        if ( ! developer_starter_filesystem_path_is_allowed( $path, $args ) ) {
            developer_starter_filesystem_log_failure( $args['operation'], $path, 'Path is not allowlisted.', (array) $args['context'] );
            return false;
        }

        if ( function_exists( 'wp_delete_file' ) ) {
            $deleted = wp_delete_file( (string) $path );
        } else {
            $deleted = unlink( (string) $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_unlink -- Fallback inside central guarded filesystem helper.
        }
        if ( ! $deleted ) {
            developer_starter_filesystem_log_failure( $args['operation'], $path, 'Unable to delete file.', (array) $args['context'] );
            return false;
        }

        return true;
    }
}

if ( ! function_exists( 'developer_starter_filesystem_delete_temp_file' ) ) {
    /**
     * 安全删除已批准临时目录中的文件。
     *
     * @param string $path Temporary file path.
     * @return bool
     */
    function developer_starter_filesystem_delete_temp_file( $path ) {
        return developer_starter_filesystem_delete_file(
            $path,
            array(
                'operation'     => 'delete_temp_file',
                'allowed_roots' => developer_starter_filesystem_temp_roots(),
            )
        );
    }
}

if ( ! function_exists( 'developer_starter_filesystem_delete_empty_dir' ) ) {
    /**
     * 校验白名单路径后安全删除空目录。
     *
     * @param string $path Target directory path.
     * @param array  $args Optional allowlist/settings.
     * @return bool
     */
    function developer_starter_filesystem_delete_empty_dir( $path, $args = array() ) {
        $defaults = array(
            'operation'         => 'delete_empty_dir',
            'target_must_exist' => true,
            'context'           => array(),
        );
        $args = wp_parse_args( $args, $defaults );

        if ( ! is_dir( (string) $path ) ) {
            return false;
        }

        if ( ! developer_starter_filesystem_path_is_allowed( $path, $args ) ) {
            developer_starter_filesystem_log_failure( $args['operation'], $path, 'Path is not allowlisted.', (array) $args['context'] );
            return false;
        }

        if ( ! rmdir( (string) $path ) ) { // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_rmdir -- Empty-dir cleanup inside central guarded filesystem helper.
            developer_starter_filesystem_log_failure( $args['operation'], $path, 'Unable to delete directory.', (array) $args['context'] );
            return false;
        }

        return true;
    }
}

if ( ! function_exists( 'developer_starter_filesystem_move_file' ) ) {
    /**
     * 在白名单根目录内安全移动文件。
     *
     * @param string $source      Source file path.
     * @param string $destination Destination file path.
     * @param array  $args        Optional allowlist/settings.
     * @return bool
     */
    function developer_starter_filesystem_move_file( $source, $destination, $args = array() ) {
        $defaults = array(
            'operation'     => 'move_file',
            'create_parent' => false,
            'context'       => array(),
        );
        $args = wp_parse_args( $args, $defaults );

        if ( ! is_file( (string) $source ) ) {
            return false;
        }

        $source_args = $args;
        $source_args['target_must_exist'] = true;
        if ( ! developer_starter_filesystem_path_is_allowed( $source, $source_args ) ) {
            developer_starter_filesystem_log_failure( $args['operation'], $source, 'Source path is not allowlisted.', (array) $args['context'] );
            return false;
        }

        $destination_args = $args;
        $destination_args['target_must_exist'] = false;
        if ( ! developer_starter_filesystem_path_is_allowed( $destination, $destination_args ) ) {
            developer_starter_filesystem_log_failure( $args['operation'], $destination, 'Destination path is not allowlisted.', (array) $args['context'] );
            return false;
        }

        $destination_dir = dirname( (string) $destination );
        if ( ! is_dir( $destination_dir ) ) {
            if ( empty( $args['create_parent'] ) || ! wp_mkdir_p( $destination_dir ) ) {
                developer_starter_filesystem_log_failure( $args['operation'], $destination, 'Destination directory is not available.', (array) $args['context'] );
                return false;
            }
        }

        if ( rename( (string) $source, (string) $destination ) ) { // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_rename -- Cache migration inside central guarded filesystem helper.
            return true;
        }

        if ( copy( (string) $source, (string) $destination ) && developer_starter_filesystem_delete_file( $source, $args ) ) { // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_copy -- Cache migration fallback inside central guarded filesystem helper.
            return true;
        }

        developer_starter_filesystem_log_failure( $args['operation'], $destination, 'Unable to move file.', (array) $args['context'] );
        return false;
    }
}
