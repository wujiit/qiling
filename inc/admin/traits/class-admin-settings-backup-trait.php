<?php
/**
 * Admin Settings Backup Trait
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Admin\Traits;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

trait Admin_Settings_Backup_Trait {

    private function render_backup_tab( $options ) {
        echo '<tr><th colspan="2"><h2>' . __( '备份与恢复', 'developer-starter' ) . '</h2><p class="description">' . __( '您可以导出当前主题核心设置，或导入之前备份的设置文件。当前备份包含主题主设置和招聘设置，不包含 WooCommerce 设置。', 'developer-starter' ) . '</p></th></tr>';

        // 导出
        echo '<tr><th scope="row">' . __( '导出设置', 'developer-starter' ) . '</th>';
        echo '<td>';
        echo '<form method="post" action="' . admin_url( 'admin-post.php' ) . '">';
        echo '<input type="hidden" name="action" value="ds_export_settings" />';
        wp_nonce_field( 'ds_export_settings_nonce', 'ds_export_settings_nonce' );
        echo '<button type="submit" class="button button-secondary">' . __( '导出当前设置 (JSON)', 'developer-starter' ) . '</button>';
        echo '<p class="description">' . __( '导出包含主题设置和招聘设置的 JSON 文件，可用于备份或恢复。', 'developer-starter' ) . '</p>';
        echo '</form>';
        echo '</td></tr>';

        // 导入
        echo '<tr><th scope="row">' . __( '导入设置', 'developer-starter' ) . '</th>';
        echo '<td>';
        echo '<form method="post" action="' . admin_url( 'admin-post.php' ) . '" enctype="multipart/form-data">';
        echo '<input type="hidden" name="action" value="ds_import_settings" />';
        wp_nonce_field( 'ds_import_settings_nonce', 'ds_import_settings_nonce' );
        echo '<input type="file" name="import_file" accept=".json" style="margin-bottom: 10px;" /><br/>';
        echo '<button type="submit" class="button button-primary" onclick="return confirm(\'' . esc_js( __( '确定要导入设置吗？备份内容将合并恢复到当前设置中。', 'developer-starter' ) ) . '\');">' . __( '导入恢复设置', 'developer-starter' ) . '</button>';
        echo '<p class="description" style="color: #ef4444;">' . __( '注意：导入操作会将备份内容合并恢复到当前主题设置中。建议先导出当前备份。', 'developer-starter' ) . '</p>';
        echo '</form>';
        echo '</td></tr>';
    }

    public function handle_export_settings() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        check_admin_referer( 'ds_export_settings_nonce', 'ds_export_settings_nonce' );

        $options = get_option( $this->option_name, array() );
        $careers_options = get_option( 'developer_starter_careers_options', array() );
        $payload = array(
            'backup_type'    => 'developer_starter_theme_settings',
            'format_version' => 2,
            'exported_at'    => current_time( 'mysql' ),
            'source_home'    => home_url( '/' ),
            'data'           => array(
                $this->option_name                    => is_array( $options ) ? $options : array(),
                'developer_starter_careers_options'  => is_array( $careers_options ) ? $careers_options : array(),
            ),
        );
        $filename = 'theme-settings-backup-' . date( 'Y-m-d-H-i-s' ) . '.json';

        header( 'Content-Type: application/json; charset=utf-8' );
        header( 'Content-Disposition: attachment; filename=' . $filename );
        echo wp_json_encode( $payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE );
        exit;
    }

    public function handle_import_settings() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( __( '权限不足', 'developer-starter' ) );
        }
        check_admin_referer( 'ds_import_settings_nonce', 'ds_import_settings_nonce' );

        if ( empty( $_FILES['import_file'] ) || ! is_array( $_FILES['import_file'] ) ) {
            wp_die( __( '未检测到上传文件', 'developer-starter' ) );
        }

        $file = $_FILES['import_file'];
        $upload_error = isset( $file['error'] ) ? (int) $file['error'] : UPLOAD_ERR_NO_FILE;
        if ( $upload_error !== UPLOAD_ERR_OK ) {
            wp_die( $this->get_import_upload_error_message( $upload_error ) );
        }

        $tmp_name = isset( $file['tmp_name'] ) ? (string) $file['tmp_name'] : '';
        if ( '' === $tmp_name || ! file_exists( $tmp_name ) || ! is_uploaded_file( $tmp_name ) ) {
            wp_die( __( '上传文件来源不合法，请重试。', 'developer-starter' ) );
        }

        $filename = isset( $file['name'] ) ? sanitize_file_name( wp_unslash( (string) $file['name'] ) ) : '';
        if ( '' !== $filename && substr( strtolower( $filename ), -5 ) !== '.json' ) {
            wp_die( __( '仅支持导入 .json 设置文件。', 'developer-starter' ) );
        }

        $file_size = isset( $file['size'] ) ? absint( $file['size'] ) : 0;
        $max_size  = (int) apply_filters( 'developer_starter_settings_import_max_size', 5 * MB_IN_BYTES );
        if ( $file_size < 1 ) {
            wp_die( __( '导入文件为空，请重新选择。', 'developer-starter' ) );
        }
        if ( $file_size > $max_size ) {
            wp_die(
                sprintf(
                    /* translators: %s: max size */
                    __( '导入文件超过大小上限（最大 %s）。', 'developer-starter' ),
                    size_format( $max_size )
                )
            );
        }

        if ( function_exists( 'wp_check_filetype_and_ext' ) ) {
            $checked_type = wp_check_filetype_and_ext( $tmp_name, $filename, array( 'json' => 'application/json' ) );
            $checked_ext  = isset( $checked_type['ext'] ) ? strtolower( (string) $checked_type['ext'] ) : '';
            if ( '' !== $checked_ext && 'json' !== $checked_ext ) {
                wp_die( __( '文件类型校验失败，请上传有效的 JSON 文件。', 'developer-starter' ) );
            }
        }

        $detected_mime = '';
        if ( function_exists( 'finfo_open' ) && function_exists( 'finfo_file' ) ) {
            $finfo = finfo_open( FILEINFO_MIME_TYPE );
            if ( $finfo ) {
                $detected = finfo_file( $finfo, $tmp_name );
                finfo_close( $finfo );
                if ( is_string( $detected ) ) {
                    $detected_mime = strtolower( trim( explode( ';', $detected )[0] ) );
                }
            }
        } elseif ( function_exists( 'mime_content_type' ) ) {
            $detected = mime_content_type( $tmp_name );
            if ( is_string( $detected ) ) {
                $detected_mime = strtolower( trim( explode( ';', $detected )[0] ) );
            }
        }

        if ( $detected_mime !== '' ) {
            $allowed_mimes = apply_filters(
                'developer_starter_settings_import_allowed_mimes',
                array( 'application/json', 'text/json', 'application/ld+json', 'text/plain' )
            );
            $allowed_mimes = array_map( 'strtolower', array_filter( array_map( 'trim', (array) $allowed_mimes ) ) );
            if ( empty( $allowed_mimes ) || ! in_array( $detected_mime, $allowed_mimes, true ) ) {
                wp_die( __( '导入文件 MIME 类型不合法，请使用标准 JSON 文件。', 'developer-starter' ) );
            }
        }

        if ( ! is_readable( $tmp_name ) ) {
            wp_die( __( '导入文件不可读，请重试。', 'developer-starter' ) );
        }

        $file_content = file_get_contents( $tmp_name );
        if ( ! is_string( $file_content ) || '' === trim( $file_content ) ) {
            wp_die( __( '导入文件内容为空。', 'developer-starter' ) );
        }

        // 兼容 UTF-8 BOM
        $file_content = preg_replace( '/^\xEF\xBB\xBF/', '', $file_content );
        $decoded = json_decode( $file_content, true );

        if ( json_last_error() !== JSON_ERROR_NONE || ! is_array( $decoded ) ) {
            wp_die( __( '无效的设置文件格式', 'developer-starter' ) );
        }

        if ( empty( $decoded ) ) {
            wp_die( __( '设置文件为空或格式错误', 'developer-starter' ) );
        }

        $payload = $this->normalize_import_payload( $decoded );

        if ( empty( $payload[ $this->option_name ] ) || ! is_array( $payload[ $this->option_name ] ) ) {
            wp_die( __( '备份文件中缺少有效的主题主设置', 'developer-starter' ) );
        }

        $existing_options = get_option( $this->option_name, array() );
        $merged_options = $this->merge_imported_settings(
            is_array( $existing_options ) ? $existing_options : array(),
            $payload[ $this->option_name ]
        );
        // 导入前执行字段级再清洗，避免“直接 merge 后写入”。
        $restored_options = $this->sanitize_options( $merged_options, array(
            'import_restore' => true,
        ) );

        update_option( $this->option_name, $restored_options );
        $this->sync_restored_core_options( $restored_options );

        if ( isset( $payload['developer_starter_careers_options'] ) && is_array( $payload['developer_starter_careers_options'] ) ) {
            $existing_careers_options = get_option( 'developer_starter_careers_options', array() );
            $restored_careers_options = $this->merge_imported_settings(
                is_array( $existing_careers_options ) ? $existing_careers_options : array(),
                $payload['developer_starter_careers_options']
            );
            $restored_careers_options = $this->sanitize_import_value_recursive( $restored_careers_options );
            update_option( 'developer_starter_careers_options', $restored_careers_options );
        }

        // Redirect back to settings page
        wp_safe_redirect( add_query_arg( 'settings-updated', 'true', admin_url( 'admin.php?page=developer-starter-settings' ) ) );
        exit;
    }

    private function normalize_import_payload( $decoded ) {
        if (
            isset( $decoded['backup_type'], $decoded['data'] ) &&
            $decoded['backup_type'] === 'developer_starter_theme_settings' &&
            is_array( $decoded['data'] )
        ) {
            return $decoded['data'];
        }

        // 兼容旧版：历史备份文件只有 developer_starter_options 本体。
        return array(
            $this->option_name => $decoded,
        );
    }

    private function merge_imported_settings( $existing, $imported ) {
        if ( ! is_array( $existing ) ) {
            $existing = array();
        }
        if ( ! is_array( $imported ) ) {
            return $existing;
        }

        $merged = $existing;

        foreach ( $imported as $key => $value ) {
            if (
                isset( $merged[ $key ] ) &&
                is_array( $merged[ $key ] ) &&
                is_array( $value ) &&
                ! $this->is_list_array( $merged[ $key ] ) &&
                ! $this->is_list_array( $value )
            ) {
                $merged[ $key ] = $this->merge_imported_settings( $merged[ $key ], $value );
                continue;
            }

            $merged[ $key ] = $value;
        }

        return $merged;
    }

    private function is_list_array( $value ) {
        if ( ! is_array( $value ) ) {
            return false;
        }

        if ( function_exists( 'wp_is_numeric_array' ) ) {
            return wp_is_numeric_array( $value );
        }

        return array_keys( $value ) === range( 0, count( $value ) - 1 );
    }

    private function sync_restored_core_options( $options ) {
        if ( ! is_array( $options ) ) {
            return;
        }

        if ( isset( $options['theme_license_key'] ) ) {
            update_option( 'theme_license_key', sanitize_text_field( (string) $options['theme_license_key'] ) );
        }
    }

    /**
     * 上传错误码转友好提示。
     *
     * @param int $error_code PHP 上传错误码。
     * @return string
     */
    private function get_import_upload_error_message( $error_code ) {
        $error_code = (int) $error_code;
        switch ( $error_code ) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                return __( '上传文件过大，请调整 PHP 上传限制后重试。', 'developer-starter' );
            case UPLOAD_ERR_PARTIAL:
                return __( '文件上传不完整，请重新上传。', 'developer-starter' );
            case UPLOAD_ERR_NO_FILE:
                return __( '请选择要导入的设置文件。', 'developer-starter' );
            case UPLOAD_ERR_NO_TMP_DIR:
            case UPLOAD_ERR_CANT_WRITE:
            case UPLOAD_ERR_EXTENSION:
                return __( '服务器无法处理上传文件，请检查服务器上传配置。', 'developer-starter' );
            default:
                return __( '文件上传失败，请稍后重试。', 'developer-starter' );
        }
    }

    /**
     * 导入数据递归清洗（用于非主设置 Option）。
     *
     * @param mixed $value 导入值。
     * @return mixed
     */
    private function sanitize_import_value_recursive( $value ) {
        if ( is_array( $value ) ) {
            $clean = array();
            foreach ( $value as $key => $item ) {
                $clean[ $key ] = $this->sanitize_import_value_recursive( $item );
            }
            return $clean;
        }

        if ( is_bool( $value ) || is_int( $value ) || is_float( $value ) || null === $value ) {
            return $value;
        }

        return wp_kses_post( (string) $value );
    }
}
