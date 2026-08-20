<?php
/**
 * Meta Boxes Editor Service
 *
 * 负责后台模块编辑器的 HTML 片段和页面 JSON 预览/导出响应数据构建。
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Admin;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Meta_Boxes_Editor_Service {

    /**
     * 渲染单个模块项 HTML。
     *
     * @param int                       $idx           模块索引。
     * @param string                    $type          模块类型。
     * @param array<string,mixed>       $data          模块数据。
     * @param array<string,mixed>       $module_fields 模块定义。
     * @param Meta_Boxes_Module_Renderer $renderer      渲染器。
     * @param bool                      $use_defaults  是否使用默认值。
     * @return string|\WP_Error
     */
    public function render_module_item_html( $idx, $type, $data, $module_fields, Meta_Boxes_Module_Renderer $renderer, $use_defaults = false ) {
        $type = sanitize_key( (string) $type );
        if ( '' === $type || ! isset( $module_fields[ $type ] ) ) {
            return new \WP_Error( 'invalid_module_type', __( '模块不存在或未注册', 'developer-starter' ) );
        }

        ob_start();
        $renderer->render_item( $idx, $type, is_array( $data ) ? $data : array(), $module_fields, (bool) $use_defaults );
        return (string) ob_get_clean();
    }

    /**
     * 渲染模块列表 HTML。
     *
     * @param array<int,mixed>          $modules       模块数组。
     * @param array<string,mixed>       $module_fields 模块定义。
     * @param Meta_Boxes_Module_Renderer $renderer      渲染器。
     * @return array{html:string,moduleCount:int}
     */
    public function render_modules_list_payload( $modules, $module_fields, Meta_Boxes_Module_Renderer $renderer ) {
        $modules = is_array( $modules ) ? $modules : array();

        ob_start();
        $idx = 0;
        foreach ( $modules as $module ) {
            $module = $this->normalize_module_row_for_editor( $module );
            $type = isset( $module['type'] ) ? (string) $module['type'] : '';
            $data = isset( $module['data'] ) && is_array( $module['data'] ) ? $module['data'] : array();
            if ( '' === $type || ! isset( $module_fields[ $type ] ) ) {
                continue;
            }

            $render_buffer_level = ob_get_level();
            try {
                ob_start();
                $renderer->render_item( $idx, $type, $data, $module_fields, false );
                echo (string) ob_get_clean(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            } catch ( \Throwable $e ) {
                while ( ob_get_level() > $render_buffer_level ) {
                    ob_end_clean();
                }
                $this->render_recoverable_module_item( $idx, $type, $data, $module_fields, $e );
            }

            $idx++;
        }

        return array(
            'html'        => (string) ob_get_clean(),
            'moduleCount' => $idx,
        );
    }

    /**
     * Normalize legacy editor rows before rendering.
     *
     * @param mixed $module Module row.
     * @return array{type:string,data:array<string,mixed>}
     */
    private function normalize_module_row_for_editor( $module ) {
        if ( ! is_array( $module ) || empty( $module['type'] ) ) {
            return array(
                'type' => '',
                'data' => array(),
            );
        }

        $type = sanitize_key( (string) $module['type'] );
        if ( isset( $module['data'] ) && is_array( $module['data'] ) ) {
            $data = $module['data'];
        } elseif ( isset( $module['settings'] ) && is_array( $module['settings'] ) ) {
            $data = $module['settings'];
        } else {
            $data = $module;
            unset( $data['type'], $data['schemaVersion'], $data['schema_version'] );
            $data = is_array( $data ) ? $data : array();
        }

        return array(
            'type' => $type,
            'data' => $data,
        );
    }

    /**
     * Render a fallback item so one legacy/bad module cannot block adding new modules.
     *
     * @param int                         $idx           Module index.
     * @param string                      $type          Module type.
     * @param array<string,mixed>         $data          Module data.
     * @param array<string,array<string,mixed>> $module_fields Module fields.
     * @param \Throwable                  $error         Render error.
     * @return void
     */
    private function render_recoverable_module_item( $idx, $type, $data, $module_fields, \Throwable $error ) {
        $title = isset( $module_fields[ $type ]['title'] ) ? (string) $module_fields[ $type ]['title'] : $type;

        if ( function_exists( 'developer_starter_log' ) ) {
            developer_starter_log(
                'modules_editor',
                'Failed to render legacy module item in admin editor.',
                array(
                    'module_type' => $type,
                    'error'       => $error,
                ),
                'warning'
            );
        }
        ?>
        <div class="dsm-item dsm-item-error" data-type="<?php echo esc_attr( $type ); ?>">
            <div class="dsm-item-header">
                <span class="dsm-handle">::</span>
                <span class="dsm-title"><?php echo esc_html( $title ); ?></span>
                <span class="dsm-toggle">v</span>
                <a href="#" class="dsm-remove">x</a>
            </div>
            <div class="dsm-content">
                <input type="hidden" name="modules[<?php echo esc_attr( (string) $idx ); ?>][type]" value="<?php echo esc_attr( $type ); ?>"/>
                <?php $this->render_hidden_data_inputs( "modules[{$idx}][data]", $data ); ?>
                <p class="notice notice-warning" style="margin:0; padding:10px 12px;">
                    <?php esc_html_e( '该旧模块包含无法直接渲染的历史字段，已保留原始数据。你仍可以继续添加新模块；如需编辑此模块，请先导出页面 JSON 后修复字段。', 'developer-starter' ); ?>
                </p>
            </div>
        </div>
        <?php
    }

    /**
     * Preserve scalar legacy module data in fallback rows.
     *
     * @param string $name  Input name prefix.
     * @param mixed  $value Value.
     * @param int    $depth Recursion depth.
     * @return void
     */
    private function render_hidden_data_inputs( $name, $value, $depth = 0 ) {
        if ( $depth > 8 ) {
            return;
        }

        if ( is_array( $value ) ) {
            foreach ( $value as $key => $item ) {
                if ( ! is_scalar( $key ) ) {
                    continue;
                }

                $key = preg_replace( '/[^A-Za-z0-9_\-\.]/', '', (string) $key );
                if ( ! is_string( $key ) || '' === $key ) {
                    continue;
                }

                $this->render_hidden_data_inputs( $name . '[' . $key . ']', $item, $depth + 1 );
            }
            return;
        }

        if ( is_scalar( $value ) || null === $value ) {
            if ( is_bool( $value ) ) {
                $value = $value ? '1' : '0';
            } elseif ( null === $value ) {
                $value = '';
            }

            echo '<input type="hidden" name="' . esc_attr( $name ) . '" value="' . esc_attr( (string) $value ) . '"/>';
        }
    }

    /**
     * 渲染模块编辑器工具栏 HTML。
     *
     * @param array<string,mixed> $module_fields 模块定义。
     * @param array<string,mixed> $args          附加参数。
     * @return string
     */
    public function render_toolbar_html( $module_fields, $args = array() ) {
        $ai_builder_available = ! empty( $args['ai_builder_available'] );
        $ai_builder_globally_available = ! empty( $args['ai_builder_globally_available'] );
        $ai_builder_supported = ! empty( $args['ai_builder_supported'] );

        ob_start();
        foreach ( $module_fields as $key => $config ) :
            ?>
            <button type="button" class="dsm-add-btn" data-type="<?php echo esc_attr( (string) $key ); ?>">
                + <?php echo esc_html( isset( $config['title'] ) ? (string) $config['title'] : (string) $key ); ?>
            </button>
            <?php
        endforeach;
        ?>
        <button type="button" class="dsm-add-btn dsm-btn-templates">
            <?php esc_html_e( '📂 我的模版库', 'developer-starter' ); ?>
        </button>
        <?php if ( $ai_builder_available ) : ?>
        <button type="button" class="dsm-add-btn dsm-btn-ai-decorate">
            <?php esc_html_e( 'AI装修', 'developer-starter' ); ?>
        </button>
        <?php elseif ( $ai_builder_globally_available && ! $ai_builder_supported ) : ?>
        <span style="padding:6px 10px; border-radius:999px; background:rgba(255,255,255,0.18); color:#fff; font-size:12px; font-weight:600;">
            <?php esc_html_e( '积分商城页面暂不支持 AI 装修', 'developer-starter' ); ?>
        </span>
        <?php endif; ?>
        <button type="button" class="dsm-add-btn dsm-btn-page-json-import">
            <?php esc_html_e( '⬆ 导入页面JSON', 'developer-starter' ); ?>
        </button>
        <button type="button" class="dsm-add-btn dsm-btn-page-json-export">
            <?php esc_html_e( '⬇ 导出页面JSON', 'developer-starter' ); ?>
        </button>
        <div class="dsm-page-package-hint" id="dsm-page-package-hint"></div>
        <?php

        return (string) ob_get_clean();
    }

    /**
     * 构建页面 JSON 导入预览响应数据。
     *
     * @param array<string,mixed>        $package        页面包。
     * @param array<string,mixed>        $module_fields  模块定义。
     * @param Meta_Boxes_Module_Renderer $renderer       渲染器。
     * @param callable                   $template_label_cb 模板标签回调。
     * @return array<string,mixed>|\WP_Error
     */
    public function build_import_preview_payload( $package, $module_fields, Meta_Boxes_Module_Renderer $renderer, callable $template_label_cb ) {
        $unknown_types = array();
        $modules = isset( $package['modules'] ) && is_array( $package['modules'] ) ? $package['modules'] : array();

        foreach ( $modules as $module ) {
            $type = isset( $module['type'] ) ? (string) $module['type'] : '';
            if ( '' === $type || isset( $module_fields[ $type ] ) ) {
                continue;
            }

            $unknown_types[] = $type;
        }

        if ( ! empty( $unknown_types ) ) {
            $unknown_types = array_values( array_unique( array_map( 'strval', $unknown_types ) ) );
            return new \WP_Error(
                'unknown_module_types',
                sprintf(
                    /* translators: %s: module type list */
                    __( 'JSON 中包含当前主题未注册的模块：%s', 'developer-starter' ),
                    implode( ', ', $unknown_types )
                )
            );
        }

        $list_payload = $this->render_modules_list_payload( $modules, $module_fields, $renderer );

        return array(
            'list'        => $list_payload['html'],
            'moduleCount' => $list_payload['moduleCount'],
            'package'     => array(
                'pageTemplate'           => isset( $package['page_template'] ) ? (string) $package['page_template'] : '',
                'templateLabel'          => call_user_func( $template_label_cb, isset( $package['page_template'] ) ? $package['page_template'] : '' ),
                'hidePageHeader'         => ! empty( $package['hide_page_header'] ),
                'hidePageHeaderDefined'  => ! empty( $package['hide_page_header_defined'] ),
                'transparentHeader'      => ! empty( $package['transparent_header'] ),
                'enableScrollReveal'     => ! empty( $package['enable_scroll_reveal'] ),
                'pageDesign'             => isset( $package['page_design'] ) && is_array( $package['page_design'] ) ? $package['page_design'] : array(),
                'footer'                 => isset( $package['footer'] ) && is_array( $package['footer'] ) ? $package['footer'] : array(),
                'visualStyle'            => isset( $package['visual_style'] ) && is_array( $package['visual_style'] ) ? $package['visual_style'] : array(),
                'title'                  => isset( $package['title'] ) ? (string) $package['title'] : '',
                'seo'                    => isset( $package['seo'] ) && is_array( $package['seo'] ) ? $package['seo'] : array(),
            ),
        );
    }

    /**
     * 构建页面 JSON 导出响应数据。
     *
     * @param array<string,mixed> $payload 页面包 payload。
     * @param int                 $post_id 页面 ID。
     * @return array<string,string>|\WP_Error
     */
    public function build_export_payload_response( $payload, $post_id ) {
        $raw_title = isset( $payload['title'] ) ? (string) $payload['title'] : '';
        $raw_title = wp_strip_all_tags( $raw_title );
        $raw_title = wp_specialchars_decode( $raw_title, ENT_QUOTES );
        $charset   = get_bloginfo( 'charset' );
        if ( ! is_string( $charset ) || '' === trim( $charset ) ) {
            $charset = 'UTF-8';
        }

        $decoded_title = html_entity_decode( $raw_title, ENT_QUOTES | ENT_HTML5, $charset );
        $decoded_title = preg_replace( '/[\x{2010}\x{2011}\x{2012}\x{2013}\x{2014}\x{2015}\x{2212}\x{FE58}\x{FE63}\x{FF0D}]+/u', '-', (string) $decoded_title );
        $decoded_title = preg_replace( '/&#\d+;|&[a-zA-Z0-9#]+;/', '-', (string) $decoded_title );
        $title = sanitize_file_name( (string) $decoded_title );
        $title = trim( $title, " \t\n\r\0\x0B.-_" );
        if ( '' === $title ) {
            $title = 'page-' . absint( $post_id );
        }

        $timestamp = wp_date( 'Ymd-His', current_time( 'timestamp' ) );
        $filename  = $title . '-' . $timestamp . '.json';
        $json = wp_json_encode(
            $payload,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT
        );

        if ( ! is_string( $json ) || '' === $json ) {
            return new \WP_Error( 'json_encode_failed', __( 'JSON 生成失败', 'developer-starter' ) );
        }

        return array(
            'filename' => $filename,
            'json'     => $json,
        );
    }
}
