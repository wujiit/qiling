<?php
/**
 * Admin Settings Field Trait
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Admin\Traits;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

trait Admin_Settings_Field_Trait {

    private function build_attrs( $attrs ) {
        if ( ! is_array( $attrs ) ) {
            return '';
        }
        $html = '';
        foreach ( $attrs as $key => $value ) {
            if ( $value === null || $value === '' ) {
                continue;
            }
            $html .= ' ' . $key . '="' . esc_attr( $value ) . '"';
        }
        return $html;
    }

    /**
     * 收集可用于后台搜索的文本片段。
     *
     * @param mixed $value 原始字段值。
     * @return array<int,string>
     */
    private function collect_searchable_text_fragments( $value ) {
        if ( is_string( $value ) || is_numeric( $value ) ) {
            $text = trim( wp_strip_all_tags( (string) $value ) );
            return '' === $text ? array() : array( $text );
        }

        if ( ! is_array( $value ) ) {
            return array();
        }

        $fragments = array();
        foreach ( $value as $item ) {
            $fragments = array_merge( $fragments, $this->collect_searchable_text_fragments( $item ) );
        }

        return $fragments;
    }

    private function get_row_attr( $field, $options ) {
        $row_class = $field['row_class'] ?? '';
        if ( is_callable( $row_class ) ) {
            $row_class = call_user_func( $row_class, $options, $field );
        }
        $row_style = $field['row_style'] ?? '';
        if ( is_callable( $row_style ) ) {
            $row_style = call_user_func( $row_style, $options, $field );
        }
        $row_class = is_string( $row_class ) ? $row_class : '';
        if ( ( $field['type'] ?? '' ) === 'section' ) {
            $row_class = trim( $row_class . ' ds-section-row' );
        }
        $setting_id = '';
        if ( ! empty( $field['id'] ) && is_string( $field['id'] ) ) {
            $setting_id = $field['id'];
            if ( $this->is_favorite( $setting_id ) ) {
                $row_class = trim( $row_class . ' ds-favorite' );
            }
        }
        $search_sources = array(
            $field['label'] ?? '',
            $field['desc'] ?? '',
            $field['title'] ?? '',
            $field['content'] ?? '',
            $field['choices'] ?? array(),
        );

        $search_terms = $field['search_terms'] ?? array();
        if ( is_callable( $search_terms ) ) {
            $search_terms = call_user_func( $search_terms, $options, $field );
        }
        $search_sources[] = $search_terms;

        $search_text = implode(
            ' ',
            array_unique(
                array_filter(
                    $this->collect_searchable_text_fragments( $search_sources )
                )
            )
        );

        $attr = '';
        if ( $row_class ) {
            $attr .= ' class="' . esc_attr( $row_class ) . '"';
        }
        if ( $row_style ) {
            $attr .= ' style="' . esc_attr( $row_style ) . '"';
        }
        if ( $search_text ) {
            $attr .= ' data-search="' . esc_attr( $search_text ) . '"';
        }
        if ( $setting_id ) {
            $attr .= ' id="' . esc_attr( 'setting-row-' . sanitize_html_class( $setting_id ) ) . '"';
            $attr .= ' data-setting-id="' . esc_attr( $setting_id ) . '"';
        }
        return $attr;
    }

    private function get_user_favorites() {
        $favorites = get_user_meta( get_current_user_id(), 'developer_starter_favorite_settings', true );
        if ( ! is_array( $favorites ) ) {
            $favorites = array();
        }
        $favorites = array_map( 'sanitize_key', $favorites );
        $favorites = array_values( array_unique( array_filter( $favorites ) ) );
        return $favorites;
    }

    private function is_favorite( $setting_id ) {
        if ( ! $setting_id ) {
            return false;
        }
        return in_array( $setting_id, $this->favorite_settings, true );
    }

    private function get_field_label_html( $setting_id, $label, $input_id = '' ) {
        $label_text = esc_html( $label );
        if ( ! $setting_id ) {
            return $label_text;
        }
        $is_favorite = $this->is_favorite( $setting_id );
        $title = $is_favorite ? __( '取消收藏', 'developer-starter' ) : __( '收藏此设置', 'developer-starter' );
        $button = '<button type="button" class="ds-favorite-toggle" data-field="' . esc_attr( $setting_id ) . '" aria-pressed="' . ( $is_favorite ? 'true' : 'false' ) . '" title="' . esc_attr( $title ) . '" aria-label="' . esc_attr( $title ) . '"></button>';
        if ( $input_id ) {
            $label_html = '<label for="' . esc_attr( $input_id ) . '" class="ds-field-label-text">' . $label_text . '</label>';
        } else {
            $label_html = '<span class="ds-field-label-text">' . $label_text . '</span>';
        }
        return '<div class="ds-field-label-wrap">' . $button . $label_html . '</div>';
    }
    
    // ===== Field Renderers =====
    private function field_text( $id, $label, $options, $desc = '', $default = '', $input_type = 'text', $attrs = array(), $row_attr = '' ) {
        $value = isset( $options[ $id ] ) ? $options[ $id ] : $default;
        $attrs = is_array( $attrs ) ? $attrs : array();
        if ( ! isset( $attrs['class'] ) ) {
            $attrs['class'] = 'regular-text';
        }
        $attrs = array_merge( array(
            'type'  => $input_type,
            'id'    => $id,
            'name'  => $this->option_name . '[' . $id . ']',
            'value' => $value,
        ), $attrs );
        echo '<tr' . $row_attr . '><th scope="row">' . $this->get_field_label_html( $id, $label, $id ) . '</th>';
        echo '<td><input' . $this->build_attrs( $attrs ) . ' />';
        $this->render_design_quick_values( $id );
        if ( $desc ) {
            echo '<p class="description">' . esc_html( $desc ) . '</p>';
        }
        echo '</td></tr>';
    }

    /**
     * 给全局设计里的高频数值字段提供点选式快捷值，减少用户手写 CSS。
     *
     * @param string $id Field ID.
     * @return void
     */
    private function render_design_quick_values( $id ) {
        $items = $this->get_design_quick_value_items( $id );
        if ( empty( $items ) ) {
            return;
        }

        $this->render_design_quick_value_assets_once();

        echo '<div class="ds-design-quick-values" data-ds-design-quick-values="' . esc_attr( $id ) . '">';
        echo '<span class="ds-design-quick-values__label">' . esc_html__( '快捷选择', 'developer-starter' ) . '</span>';
        foreach ( $items as $item ) {
            if ( empty( $item['label'] ) || ! array_key_exists( 'value', $item ) ) {
                continue;
            }
            echo '<button type="button" class="button-link ds-design-quick-value" data-target="' . esc_attr( $id ) . '" data-value="' . esc_attr( (string) $item['value'] ) . '">' . esc_html( (string) $item['label'] ) . '</button>';
        }
        echo '</div>';
    }

    /**
     * @param string $id Field ID.
     * @return array<int,array<string,string>>
     */
    private function get_design_quick_value_items( $id ) {
        $id = sanitize_key( (string) $id );
        if ( 0 !== strpos( $id, 'design_' ) ) {
            return array();
        }

        if ( false !== strpos( $id, 'shadow' ) ) {
            return array(
                array( 'label' => __( '无', 'developer-starter' ), 'value' => 'none' ),
                array( 'label' => __( '轻', 'developer-starter' ), 'value' => '0 8px 24px rgba(15, 23, 42, 0.08)' ),
                array( 'label' => __( '中', 'developer-starter' ), 'value' => '0 16px 40px rgba(15, 23, 42, 0.12)' ),
                array( 'label' => __( '重', 'developer-starter' ), 'value' => '0 24px 60px rgba(15, 23, 42, 0.18)' ),
            );
        }

        if ( false !== strpos( $id, 'radius' ) ) {
            return array(
                array( 'label' => __( '直角', 'developer-starter' ), 'value' => '0px' ),
                array( 'label' => __( '小', 'developer-starter' ), 'value' => '6px' ),
                array( 'label' => __( '圆润', 'developer-starter' ), 'value' => '14px' ),
                array( 'label' => __( '胶囊', 'developer-starter' ), 'value' => '999px' ),
            );
        }

        if ( false !== strpos( $id, 'padding' ) ) {
            return array(
                array( 'label' => __( '紧凑', 'developer-starter' ), 'value' => '8px 16px' ),
                array( 'label' => __( '标准', 'developer-starter' ), 'value' => '12px 24px' ),
                array( 'label' => __( '舒展', 'developer-starter' ), 'value' => '14px 28px' ),
            );
        }

        if ( 'design_animation_speed' === $id ) {
            return array(
                array( 'label' => __( '快', 'developer-starter' ), 'value' => '0.18s' ),
                array( 'label' => __( '标准', 'developer-starter' ), 'value' => '0.25s' ),
                array( 'label' => __( '柔和', 'developer-starter' ), 'value' => '0.35s' ),
            );
        }

        if ( false !== strpos( $id, 'letter_spacing' ) ) {
            return array(
                array( 'label' => __( '默认', 'developer-starter' ), 'value' => '0em' ),
                array( 'label' => __( '紧凑', 'developer-starter' ), 'value' => '-0.02em' ),
                array( 'label' => __( '疏朗', 'developer-starter' ), 'value' => '0.04em' ),
            );
        }

        if ( 'design_component_module_title_size' === $id ) {
            return array(
                array( 'label' => __( '小', 'developer-starter' ), 'value' => '1.75rem' ),
                array( 'label' => __( '标准', 'developer-starter' ), 'value' => '2rem' ),
                array( 'label' => __( '大', 'developer-starter' ), 'value' => '2.5rem' ),
            );
        }

        if ( 'design_component_footer_heading_size' === $id ) {
            return array(
                array( 'label' => __( '小', 'developer-starter' ), 'value' => '16px' ),
                array( 'label' => __( '标准', 'developer-starter' ), 'value' => '18px' ),
                array( 'label' => __( '大', 'developer-starter' ), 'value' => '22px' ),
            );
        }

        return array();
    }

    /**
     * @return void
     */
    private function render_design_quick_value_assets_once() {
        static $printed = false;
        if ( $printed ) {
            return;
        }
        $printed = true;
        ?>
        <style>
            .ds-design-quick-values {
                display: flex;
                flex-wrap: wrap;
                align-items: center;
                gap: 8px;
                margin-top: 8px;
            }
            .ds-design-quick-values__label {
                color: #64748b;
                font-size: 12px;
            }
            .ds-design-quick-value {
                display: inline-flex;
                align-items: center;
                min-height: 26px;
                padding: 0 10px;
                border: 1px solid #dbe3f0;
                border-radius: 999px;
                background: #f8fafc;
                color: #334155;
                font-size: 12px;
                font-weight: 600;
                text-decoration: none;
                cursor: pointer;
            }
            .ds-design-quick-value:hover,
            .ds-design-quick-value:focus {
                border-color: #2563eb;
                background: #eff6ff;
                color: #1d4ed8;
                outline: none;
            }
        </style>
        <script>
        document.addEventListener('click', function(event) {
            var button = event.target && event.target.closest ? event.target.closest('.ds-design-quick-value') : null;
            if (!button) {
                return;
            }
            var targetId = button.getAttribute('data-target') || '';
            var value = button.getAttribute('data-value') || '';
            if (!targetId) {
                return;
            }
            var input = document.getElementById(targetId);
            if (!input) {
                return;
            }
            input.value = value;
            input.dispatchEvent(new Event('input', { bubbles: true }));
            input.dispatchEvent(new Event('change', { bubbles: true }));
            input.focus();
        });
        </script>
        <?php
    }

    private function field_number( $id, $label, $options, $desc = '', $default = '', $attrs = array(), $suffix = '', $row_attr = '' ) {
        $value = isset( $options[ $id ] ) ? $options[ $id ] : $default;
        $attrs = is_array( $attrs ) ? $attrs : array();
        if ( ! isset( $attrs['class'] ) ) {
            $attrs['class'] = 'small-text';
        }
        $attrs = array_merge( array(
            'type'  => 'number',
            'id'    => $id,
            'name'  => $this->option_name . '[' . $id . ']',
            'value' => $value,
        ), $attrs );
        echo '<tr' . $row_attr . '><th scope="row">' . $this->get_field_label_html( $id, $label, $id ) . '</th>';
        echo '<td><input' . $this->build_attrs( $attrs ) . ' />';
        if ( $suffix ) {
            echo ' ' . esc_html( $suffix );
        }
        if ( $desc ) {
            echo '<p class="description">' . esc_html( $desc ) . '</p>';
        }
        echo '</td></tr>';
    }

    private function field_textarea( $id, $label, $options, $desc = '', $default = '', $attrs = array(), $row_attr = '' ) {
        $value = isset( $options[ $id ] ) ? $options[ $id ] : $default;
        $attrs = is_array( $attrs ) ? $attrs : array();
        if ( ! isset( $attrs['class'] ) ) {
            $attrs['class'] = 'large-text';
        }
        if ( ! isset( $attrs['rows'] ) ) {
            $attrs['rows'] = 4;
        }
        $attrs = array_merge( array(
            'id'   => $id,
            'name' => $this->option_name . '[' . $id . ']',
        ), $attrs );
        echo '<tr' . $row_attr . '><th scope="row">' . $this->get_field_label_html( $id, $label, $id ) . '</th>';
        echo '<td><textarea' . $this->build_attrs( $attrs ) . '>' . esc_textarea( $value ) . '</textarea>';
        if ( $desc ) {
            echo '<p class="description">' . esc_html( $desc ) . '</p>';
        }
        echo '</td></tr>';
    }

    private function field_image( $id, $label, $options, $desc = '', $default = '', $preview_style = '', $row_attr = '' ) {
        $value = isset( $options[ $id ] ) ? $options[ $id ] : $default;
        $preview_style = $preview_style ? $preview_style : 'display:block;max-width:200px;margin-top:10px;';
        $preview_style_hidden = $preview_style;
        if ( strpos( $preview_style_hidden, 'display:' ) === false ) {
            $preview_style_hidden = 'display:none;' . $preview_style_hidden;
        } else {
            $preview_style_hidden = preg_replace( '/display\\s*:\\s*[^;]+;?/', 'display:none;', $preview_style_hidden );
        }
        echo '<tr' . $row_attr . '><th scope="row">' . $this->get_field_label_html( $id, $label, $id ) . '</th><td>';
        echo '<div class="ds-image-field">';
        echo '<input type="text" id="' . esc_attr( $id ) . '" name="' . $this->option_name . '[' . $id . ']" value="' . esc_attr( $value ) . '" class="ds-image-url regular-text" placeholder="输入图片URL或点击选择" />';
        echo '<button type="button" class="button ds-upload-image-btn">选择图片</button> ';
        echo '<button type="button" class="button ds-remove-image-btn">移除</button>';
        echo $value ? '<img src="' . esc_url( $value ) . '" class="ds-image-preview" style="' . esc_attr( $preview_style ) . '" />' : '<img class="ds-image-preview" style="' . esc_attr( $preview_style_hidden ) . '" />';
        echo '</div>';
        if ( $desc ) {
            echo '<p class="description">' . esc_html( $desc ) . '</p>';
        }
        echo '</td></tr>';
    }

    private function field_file( $id, $label, $options, $desc = '', $default = '', $attrs = array(), $button_label = '', $row_attr = '' ) {
        $value = isset( $options[ $id ] ) ? $options[ $id ] : $default;
        $attrs = is_array( $attrs ) ? $attrs : array();
        if ( ! isset( $attrs['class'] ) ) {
            $attrs['class'] = 'ds-file-url regular-text';
        } elseif ( false === strpos( (string) $attrs['class'], 'ds-file-url' ) ) {
            $attrs['class'] = trim( (string) $attrs['class'] . ' ds-file-url' );
        }
        $attrs = array_merge( array(
            'type'  => 'text',
            'id'    => $id,
            'name'  => $this->option_name . '[' . $id . ']',
            'value' => $value,
        ), $attrs );

        $button_label = '' !== (string) $button_label ? (string) $button_label : __( '选择文件', 'developer-starter' );

        echo '<tr' . $row_attr . '><th scope="row">' . $this->get_field_label_html( $id, $label, $id ) . '</th><td>';
        echo '<div class="ds-file-field">';
        echo '<input' . $this->build_attrs( $attrs ) . ' /> ';
        echo '<button type="button" class="button ds-upload-file-btn" data-title="' . esc_attr( $button_label ) . '">' . esc_html( $button_label ) . '</button> ';
        echo '<button type="button" class="button ds-remove-file-btn">' . esc_html__( '移除', 'developer-starter' ) . '</button>';
        echo '</div>';
        if ( $desc ) {
            echo '<p class="description">' . esc_html( $desc ) . '</p>';
        }
        echo '</td></tr>';
    }

    private function field_color( $id, $label, $options, $default = '#2563eb', $row_attr = '', $desc = '' ) {
        $value = isset( $options[ $id ] ) ? $options[ $id ] : $default;
        echo '<tr' . $row_attr . '><th scope="row">' . $this->get_field_label_html( $id, $label, $id ) . '</th>';
        echo '<td><input type="text" id="' . $id . '" name="' . $this->option_name . '[' . $id . ']" value="' . esc_attr( $value ) . '" class="ds-color-picker" data-default-color="' . esc_attr( $default ) . '" />';
        if ( $desc ) {
            echo '<p class="description">' . esc_html( $desc ) . '</p>';
        }
        echo '</td></tr>';
    }

    private function field_password( $id, $label, $options, $desc = '', $row_attr = '' ) {
        // 密码字段显示时解密，但不显示实际值，只显示占位符
        $value = isset( $options[ $id ] ) ? $options[ $id ] : '';
        $has_value = ! empty( $value );
        $placeholder = $has_value ? '••••••••（已设置，留空保持不变）' : '请输入密码';
        echo '<tr' . $row_attr . '><th scope="row">' . $this->get_field_label_html( $id, $label, $id ) . '</th>';
        echo '<td><input type="password" id="' . $id . '" name="' . $this->option_name . '[' . $id . ']" value="" class="regular-text" placeholder="' . esc_attr( $placeholder ) . '" autocomplete="new-password" />';
        if ( $has_value ) {
            echo '<input type="hidden" name="' . $this->option_name . '[' . $id . '_existing]" value="1" />';
        }
        if ( $desc ) {
            echo '<p class="description">' . esc_html( $desc ) . '</p>';
        }
        echo '</td></tr>';
    }

    private function field_checkbox( $id, $label, $options, $desc = '', $default = null, $row_attr = '' ) {
        // 使用 array_key_exists 区分「从未保存过」和「主动保存为空」
        // 当用户取消勾选 checkbox 后，保存的值为 ''（空字符串）
        // 此时不应该用 default 覆盖，否则 UI 会错误地显示为勾选状态
        if ( array_key_exists( $id, $options ) ) {
            $value = $options[ $id ];
        } elseif ( $default !== null ) {
            $value = $default;
        } else {
            $value = '';
        }
        echo '<tr' . $row_attr . '><th scope="row">' . $this->get_field_label_html( $id, $label, $id ) . '</th>';
        echo '<td><label>';
        echo '<input type="hidden" name="' . $this->option_name . '[' . $id . ']" value="" />';
        echo '<input type="checkbox" id="' . esc_attr( $id ) . '" name="' . $this->option_name . '[' . $id . ']" value="1"' . checked( $value, '1', false ) . ' /> ';
        if ( $desc ) {
            echo esc_html( $desc );
        }
        echo '</label></td></tr>';
    }

    private function field_select( $id, $label, $options, $choices, $desc = '', $default = '', $attrs = array(), $row_attr = '' ) {
        $value = isset( $options[ $id ] ) ? $options[ $id ] : $default;
        $attrs = is_array( $attrs ) ? $attrs : array();
        if ( ! isset( $attrs['id'] ) ) {
            $attrs['id'] = $id;
        }
        $attrs = array_merge( array(
            'name' => $this->option_name . '[' . $id . ']',
        ), $attrs );
        $input_id = $attrs['id'] ?? $id;
        echo '<tr' . $row_attr . '><th scope="row">' . $this->get_field_label_html( $id, $label, $input_id ) . '</th><td>';
        echo '<select' . $this->build_attrs( $attrs ) . '>';
        foreach ( $choices as $k => $v ) {
            echo '<option value="' . esc_attr( $k ) . '"' . selected( $value, $k, false ) . '>' . esc_html( $v ) . '</option>';
        }
        echo '</select>';
        if ( $desc ) {
            echo '<p class="description">' . esc_html( $desc ) . '</p>';
        }
        echo '</td></tr>';
    }

    private function field_repeater( $id, $label, $options, $fields, $desc = '', $default_items = array(), $row_attr = '' ) {
        $items = isset( $options[ $id ] ) && is_array( $options[ $id ] ) ? $options[ $id ] : array();
        if ( empty( $items ) && ! isset( $options[ $id ] ) && is_array( $default_items ) ) {
            $items = $default_items;
        }
        echo '<tr' . $row_attr . '><th scope="row">' . $this->get_field_label_html( $id, $label ) . '</th><td>';
        echo '<div class="ds-repeater-wrap">';
        echo '<div class="ds-repeater-list" style="margin-bottom: 10px;">';
        
        foreach ( $items as $idx => $item ) {
            echo '<div class="ds-repeater-item" style="background: #f9f9f9; padding: 15px; margin-bottom: 10px; border-radius: 5px; position: relative; border: 1px solid #ddd;">';
            echo '<a href="#" class="ds-repeater-remove" style="position: absolute; top: 5px; right: 10px; color: #a00; text-decoration: none;">删除</a>';
            foreach ( $fields as $f ) {
                $fval = isset( $item[ $f['id'] ] ) ? $item[ $f['id'] ] : '';
                $fname = $this->option_name . '[' . $id . '][' . $idx . '][' . $f['id'] . ']';
                echo '<div style="margin-bottom: 8px;"><label><strong>' . esc_html( $f['label'] ) . '</strong></label><br>';
                if ( $f['type'] === 'textarea' ) {
                    echo '<textarea name="' . esc_attr( $fname ) . '" rows="2" style="width:100%;">' . esc_textarea( $fval ) . '</textarea>';
                } else {
                    echo '<input type="text" name="' . esc_attr( $fname ) . '" value="' . esc_attr( $fval ) . '" style="width:100%;" />';
                }
                echo '</div>';
            }
            echo '</div>';
        }
        
        echo '</div>';
        
        $tpl = '<div class="ds-repeater-item" style="background: #f9f9f9; padding: 15px; margin-bottom: 10px; border-radius: 5px; position: relative; border: 1px solid #ddd;">';
        $tpl .= '<a href="#" class="ds-repeater-remove" style="position: absolute; top: 5px; right: 10px; color: #a00; text-decoration: none;">删除</a>';
        foreach ( $fields as $f ) {
            $fname = $this->option_name . '[' . $id . '][__IDX__][' . $f['id'] . ']';
            $tpl .= '<div style="margin-bottom: 8px;"><label><strong>' . esc_html( $f['label'] ) . '</strong></label><br>';
            if ( $f['type'] === 'textarea' ) {
                $tpl .= '<textarea name="' . esc_attr( $fname ) . '" rows="2" style="width:100%;"></textarea>';
            } else {
                $tpl .= '<input type="text" name="' . esc_attr( $fname ) . '" value="" style="width:100%;" />';
            }
            $tpl .= '</div>';
        }
        $tpl .= '</div>';
        
        echo '<div class="ds-repeater-tpl" data-template="' . esc_attr( $tpl ) . '" style="display:none;"></div>';
        echo '<button type="button" class="button ds-repeater-add">+ 添加</button>';
        if ( $desc ) {
            echo '<p class="description" style="margin-top: 8px;">' . esc_html( $desc ) . '</p>';
        }
        echo '</div></td></tr>';
    }

    private function field_checkbox_group( $id, $label, $options, $choices, $desc = '', $args = array(), $row_attr = '', $default = array() ) {
        $values = isset( $options[ $id ] ) ? (array) $options[ $id ] : ( is_array( $default ) ? $default : array() );
        $values = array_map( 'strval', $values );
        $args = is_array( $args ) ? $args : array();
        $wrapper_style = $args['wrapper_style'] ?? '';
        $wrapper_class = $args['wrapper_class'] ?? '';
        $label_style = $args['label_style'] ?? 'display:block;margin-bottom:8px;';
        echo '<tr' . $row_attr . '><th scope="row">' . $this->get_field_label_html( $id, $label ) . '</th><td>';
        echo '<input type="hidden" name="' . $this->option_name . '[' . $id . ']" value="" />';
        if ( $wrapper_style || $wrapper_class ) {
            echo '<div' . ( $wrapper_class ? ' class="' . esc_attr( $wrapper_class ) . '"' : '' ) . ( $wrapper_style ? ' style="' . esc_attr( $wrapper_style ) . '"' : '' ) . '>';
        }
        foreach ( $choices as $key => $text ) {
            $checked = in_array( (string) $key, $values, true ) ? 'checked' : '';
            echo '<label style="' . esc_attr( $label_style ) . '">';
            echo '<input type="checkbox" name="' . $this->option_name . '[' . $id . '][]" value="' . esc_attr( $key ) . '" ' . $checked . ' /> ';
            echo esc_html( $text );
            echo '</label>';
        }
        if ( $wrapper_style || $wrapper_class ) {
            echo '</div>';
        }
        if ( $desc ) {
            echo '<p class="description">' . esc_html( $desc ) . '</p>';
        }
        echo '</td></tr>';
    }

    private function field_page_id( $id, $label, $options, $desc = '', $row_attr = '' ) {
        $value = isset( $options[ $id ] ) ? $options[ $id ] : '';
        echo '<tr' . $row_attr . '><th scope="row">' . $this->get_field_label_html( $id, $label, $id ) . '</th>';
        echo '<td><input type="number" id="' . esc_attr( $id ) . '" name="' . $this->option_name . '[' . $id . ']" value="' . esc_attr( $value ) . '" class="small-text" />';
        if ( $value ) {
            echo ' <a href="' . esc_url( get_permalink( $value ) ) . '" target="_blank">' . __( '查看页面', 'developer-starter' ) . '</a>';
        }
        if ( $desc ) {
            echo '<p class="description">' . esc_html( $desc ) . '</p>';
        }
        echo '</td></tr>';
    }

    private function field_note( $content, $style = '', $row_attr = '' ) {
        if ( ! $content ) {
            return;
        }
        echo '<tr' . $row_attr . '><th scope="row"></th><td>';
        echo '<p class="description"' . ( $style ? ' style="' . esc_attr( $style ) . '"' : '' ) . '>' . wp_kses_post( $content ) . '</p>';
        echo '</td></tr>';
    }
}
