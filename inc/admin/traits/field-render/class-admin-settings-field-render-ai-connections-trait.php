<?php
/**
 * Admin settings AI connections field render trait.
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Admin\Traits;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

trait Admin_Settings_Field_Render_AI_Connections_Trait {

    private function render_ai_connections_field( $options ) {
        $connections = isset( $options['ai_connections'] ) && is_array( $options['ai_connections'] )
            ? array_values( $options['ai_connections'] )
            : array();

        echo '<tr><th scope="row">' . esc_html__( 'AI 连接', 'developer-starter' ) . '</th>';
        echo '<td>';
        echo '<input type="hidden" name="' . esc_attr( $this->option_name ) . '[ai_connections_present]" value="1" />';
        echo '<div class="ds-ai-connections" id="ds-ai-connections">';

        if ( ! empty( $connections ) ) {
            foreach ( $connections as $index => $connection ) {
                echo $this->build_ai_connection_row_html( (string) $index, is_array( $connection ) ? $connection : array() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            }
        }

        echo '</div>';
        echo '<div class="ds-ai-connections-actions">';
        echo '<button type="button" class="button button-secondary" id="ds-ai-connection-add">' . esc_html__( '+ 新增连接', 'developer-starter' ) . '</button>';
        echo '<button type="button" class="button button-primary" id="ds-ai-save-settings">' . esc_html__( '保存 AI 配置', 'developer-starter' ) . '</button>';
        echo '</div>';
        echo '<p class="description" style="margin-top:10px;">' . esc_html__( '支持阿里百炼、OpenAI 以及其他兼容 OpenAI Chat Completions 的服务。接口地址必须是公网 HTTPS 地址，可填写基础地址或完整 /chat/completions 地址。', 'developer-starter' ) . '</p>';
        echo '<p class="description">' . esc_html__( '每个连接都可以单独测试连通性。测试只会发送一条简单问候，不会写入页面数据；“保存 AI 配置”只会保存当前 AI 选项卡。', 'developer-starter' ) . '</p>';
        echo '<script type="text/template" id="ds-ai-connection-template">' . $this->build_ai_connection_row_html( '__INDEX__', array(), true ) . '</script>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        echo '</td></tr>';

        $this->render_ai_connections_assets_once();
    }

    /**
     * @param string               $index row index
     * @param array<string,mixed>  $connection connection
     * @param bool                 $is_template whether template row
     * @return string
     */
    private function build_ai_connection_row_html( $index, $connection, $is_template = false ) {
        $name_prefix = $this->option_name . '[ai_connections][' . $index . ']';
        $connection_id = isset( $connection['id'] ) ? sanitize_key( (string) $connection['id'] ) : '';
        $connection_name = isset( $connection['name'] ) ? sanitize_text_field( (string) $connection['name'] ) : '';
        $endpoint = isset( $connection['endpoint'] ) ? esc_url_raw( (string) $connection['endpoint'], array( 'https' ) ) : '';
        $default_model = isset( $connection['default_model'] ) ? sanitize_text_field( (string) $connection['default_model'] ) : '';
        $models = '';
        if ( isset( $connection['models'] ) && is_array( $connection['models'] ) ) {
            $models = implode( "\n", array_filter( array_map( 'sanitize_text_field', $connection['models'] ) ) );
        }
        $has_api_key = ! empty( $connection['api_key'] );
        $enabled = ! empty( $connection['enabled'] ) && (string) $connection['enabled'] === '1';
        $json_mode = ! isset( $connection['json_mode'] ) || (string) $connection['json_mode'] === '1';
        $api_key_placeholder = $has_api_key
            ? __( '已设置，留空保持不变', 'developer-starter' )
            : __( '请输入 API Key', 'developer-starter' );
        $stored_connection_id = ( ! $is_template && $has_api_key ) ? $connection_id : '';

        ob_start();
        ?>
        <div class="ds-ai-connection-card">
            <div class="ds-ai-connection-head">
                <strong class="ds-ai-connection-title"><?php esc_html_e( '连接', 'developer-starter' ); ?></strong>
                <button type="button" class="button-link-delete ds-ai-connection-remove"><?php esc_html_e( '删除', 'developer-starter' ); ?></button>
            </div>
            <input type="hidden" class="ds-ai-connection-stored-id" value="<?php echo esc_attr( $stored_connection_id ); ?>" />
            <div class="ds-ai-connection-grid">
                <label class="ds-ai-connection-field">
                    <span><?php esc_html_e( '连接 ID', 'developer-starter' ); ?></span>
                    <input type="text" class="ds-ai-connection-input-id" name="<?php echo esc_attr( $name_prefix . '[id]' ); ?>" value="<?php echo esc_attr( $connection_id ); ?>" placeholder="aliyun_dashscope" />
                </label>
                <label class="ds-ai-connection-field">
                    <span><?php esc_html_e( '连接名称', 'developer-starter' ); ?></span>
                    <input type="text" class="ds-ai-connection-input-name" name="<?php echo esc_attr( $name_prefix . '[name]' ); ?>" value="<?php echo esc_attr( $connection_name ); ?>" placeholder="<?php esc_attr_e( '例如：阿里百炼', 'developer-starter' ); ?>" />
                </label>
                <label class="ds-ai-connection-field ds-ai-connection-field-wide">
                    <span><?php esc_html_e( '接口地址', 'developer-starter' ); ?></span>
                    <input type="url" class="ds-ai-connection-input-endpoint" name="<?php echo esc_attr( $name_prefix . '[endpoint]' ); ?>" value="<?php echo esc_attr( $endpoint ); ?>" placeholder="https://dashscope.aliyuncs.com/compatible-mode/v1" />
                </label>
                <label class="ds-ai-connection-field">
                    <span><?php esc_html_e( '默认模型', 'developer-starter' ); ?></span>
                    <input type="text" class="ds-ai-connection-input-model" name="<?php echo esc_attr( $name_prefix . '[default_model]' ); ?>" value="<?php echo esc_attr( $default_model ); ?>" placeholder="qwen-plus" />
                </label>
                <label class="ds-ai-connection-field ds-ai-connection-field-wide">
                    <span><?php esc_html_e( '备选模型', 'developer-starter' ); ?></span>
                    <textarea name="<?php echo esc_attr( $name_prefix . '[models]' ); ?>" rows="3" placeholder="<?php esc_attr_e( "每行一个，例如：\nqwen-plus\nqwen-max", 'developer-starter' ); ?>"><?php echo esc_textarea( $models ); ?></textarea>
                </label>
                <label class="ds-ai-connection-field ds-ai-connection-field-wide">
                    <span><?php esc_html_e( 'API Key', 'developer-starter' ); ?></span>
                    <input type="password" class="ds-ai-connection-input-api-key" name="<?php echo esc_attr( $name_prefix . '[api_key]' ); ?>" value="" placeholder="<?php echo esc_attr( $api_key_placeholder ); ?>" autocomplete="new-password" />
                    <?php if ( $has_api_key && ! $is_template ) : ?>
                        <input type="hidden" class="ds-ai-connection-api-key-existing" name="<?php echo esc_attr( $name_prefix . '[api_key_existing]' ); ?>" value="1" />
                    <?php endif; ?>
                </label>
                <label class="ds-ai-connection-check">
                    <input type="hidden" name="<?php echo esc_attr( $name_prefix . '[enabled]' ); ?>" value="" />
                    <input type="checkbox" name="<?php echo esc_attr( $name_prefix . '[enabled]' ); ?>" value="1" <?php checked( $enabled ); ?> />
                    <span><?php esc_html_e( '启用该连接', 'developer-starter' ); ?></span>
                </label>
                <label class="ds-ai-connection-check">
                    <input type="hidden" name="<?php echo esc_attr( $name_prefix . '[json_mode]' ); ?>" value="" />
                    <input type="checkbox" class="ds-ai-connection-input-json-mode" name="<?php echo esc_attr( $name_prefix . '[json_mode]' ); ?>" value="1" <?php checked( $json_mode ); ?> />
                    <span><?php esc_html_e( '请求 JSON 模式', 'developer-starter' ); ?></span>
                </label>
                <div class="ds-ai-connection-tools ds-ai-connection-field-wide">
                    <div class="ds-ai-connection-tool-buttons">
                        <button type="button" class="button button-secondary ds-ai-connection-test"><?php esc_html_e( '测试连通性', 'developer-starter' ); ?></button>
                        <span class="spinner ds-ai-connection-test-spinner"></span>
                    </div>
                    <p class="description ds-ai-connection-tool-desc"><?php esc_html_e( '将使用当前表单里的接口地址、默认模型和 API Key 发起一次简单测试。', 'developer-starter' ); ?></p>
                    <p class="ds-ai-connection-test-result" aria-live="polite"></p>
                </div>
            </div>
        </div>
        <?php
        return (string) ob_get_clean();
    }

    private function render_ai_connections_assets_once() {
        static $printed = false;
        if ( $printed ) {
            return;
        }
        $printed = true;
        ?>
        <style>
            .ds-ai-connections {
                display: flex;
                flex-direction: column;
                gap: 12px;
                margin-bottom: 12px;
            }
            .ds-ai-connections-actions {
                display: flex;
                align-items: center;
                gap: 10px;
                flex-wrap: wrap;
            }
            .ds-ai-connection-card {
                border: 1px solid #d0d7de;
                border-radius: 8px;
                background: #fff;
                padding: 14px;
            }
            .ds-ai-connection-head {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 12px;
            }
            .ds-ai-connection-grid {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 12px;
            }
            .ds-ai-connection-field {
                display: flex;
                flex-direction: column;
                gap: 6px;
            }
            .ds-ai-connection-field span {
                font-weight: 600;
            }
            .ds-ai-connection-field-wide {
                grid-column: 1 / -1;
            }
            .ds-ai-connection-field input,
            .ds-ai-connection-field textarea {
                width: 100%;
            }
            .ds-ai-connection-check {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                font-weight: 600;
            }
            .ds-ai-connection-tools {
                padding-top: 4px;
            }
            .ds-ai-connection-tool-buttons {
                display: inline-flex;
                align-items: center;
                gap: 8px;
            }
            .ds-ai-connection-tool-desc {
                margin: 8px 0 0;
            }
            .ds-ai-connection-test-result {
                margin: 8px 0 0;
                font-weight: 600;
            }
            @media (max-width: 960px) {
                .ds-ai-connection-grid {
                    grid-template-columns: 1fr;
                }
            }
        </style>
        <script>
        jQuery(function($){
            function refreshAiConnectionTitles() {
                $('#ds-ai-connections .ds-ai-connection-card').each(function(index){
                    $(this).find('.ds-ai-connection-title').text('<?php echo esc_js( __( '连接', 'developer-starter' ) ); ?> #' + (index + 1));
                });
            }

            $(document).on('click', '#ds-ai-connection-add', function(e){
                e.preventDefault();
                var $list = $('#ds-ai-connections');
                var template = $('#ds-ai-connection-template').html() || '';
                if (!template) {
                    return;
                }
                var index = $list.children('.ds-ai-connection-card').length;
                $list.append(template.replace(/__INDEX__/g, index));
                refreshAiConnectionTitles();
            });

            $(document).on('click', '.ds-ai-connection-remove', function(e){
                e.preventDefault();
                $(this).closest('.ds-ai-connection-card').remove();
                refreshAiConnectionTitles();
            });

            refreshAiConnectionTitles();
        });
        </script>
        <?php
    }
}
