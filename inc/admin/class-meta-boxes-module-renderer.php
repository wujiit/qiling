<?php
/**
 * Meta Boxes Module Renderer
 *
 * 负责后台模块表单项的 HTML 渲染。
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Admin;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Meta_Boxes_Module_Renderer {

    /**
     * 渲染单个模块项。
     *
     * @param int                               $idx           模块索引。
     * @param string                            $type          模块类型。
     * @param array<string,mixed>               $data          模块数据。
     * @param array<string,array<string,mixed>> $module_fields 模块字段定义。
     * @param bool                              $use_defaults  是否使用默认值。
     * @return void
     */
    public function render_item( $idx, $type, $data, $module_fields, $use_defaults = false ) {
        if ( ! isset( $module_fields[ $type ] ) ) {
            return;
        }

        $config = $module_fields[ $type ];
        $fields = isset( $config['fields'] ) && is_array( $config['fields'] ) ? $config['fields'] : array();
        $title  = isset( $config['title'] ) ? (string) $config['title'] : (string) $type;

        $builder_data_service = null;
        $schema               = array();

        if ( class_exists( '\Developer_Starter\Core\Builder_Data_Service' ) ) {
            $builder_data_service = new \Developer_Starter\Core\Builder_Data_Service();
            $schema               = $builder_data_service->build_module_data_schema_map( $fields );
            $data                 = $builder_data_service->prepare_module_data_for_editor( $type, is_array( $data ) ? $data : array(), $schema );
        }

        if ( $use_defaults && empty( $data ) ) {
            $data = $this->get_defaults( $type, $module_fields );
            if ( $builder_data_service instanceof \Developer_Starter\Core\Builder_Data_Service ) {
                $data = $builder_data_service->prepare_module_data_for_editor( $type, $data, $schema );
            }
        }
        ?>
        <div class="dsm-item dsm-item-<?php echo esc_attr( sanitize_html_class( $type ) ); ?> qiling-module-editor-scope-<?php echo esc_attr( sanitize_html_class( $type ) ); ?>" data-type="<?php echo esc_attr( $type ); ?>" data-qiling-module-editor-scope="<?php echo esc_attr( $type ); ?>">
            <div class="dsm-item-header">
                <span class="dsm-handle">::</span>
                <span class="dsm-title"><?php echo esc_html( $title ); ?></span>
                <span class="dsm-toggle">v</span>
                <a href="#" class="dsm-save-template" title="<?php echo esc_attr__( '保存为模版', 'developer-starter' ); ?>">💾</a>
                <a href="#" class="dsm-remove">x</a>
            </div>
            <div class="dsm-content">
                <input type="hidden" name="modules[<?php echo esc_attr( (string) $idx ); ?>][type]" value="<?php echo esc_attr( $type ); ?>"/>
                <?php foreach ( $fields as $field ) : ?>
                    <?php
                    if (
                        $builder_data_service instanceof \Developer_Starter\Core\Builder_Data_Service
                        && ! empty( $field['id'] )
                        && $builder_data_service->is_legacy_common_style_field_id( (string) $field['id'] )
                    ) {
                        continue;
                    }
                    ?>
                    <?php $this->render_field( $idx, $field, $data ); ?>
                <?php endforeach; ?>

                <?php
                if ( class_exists( '\Developer_Starter\Core\Module_Advanced_Style_Service' ) ) {
                    $capabilities = \Developer_Starter\Modules\Module_Standards::get_design_capabilities( $type );
                    \Developer_Starter\Core\Module_Advanced_Style_Service::get_instance()->render_admin_controls( $idx, $data, $capabilities );
                }
                if ( class_exists( '\Developer_Starter\Core\Module_Visual_Style_Service' ) ) {
                    \Developer_Starter\Core\Module_Visual_Style_Service::get_instance()->render_admin_controls( $idx, $data, $type );
                }
                ?>
            </div>
        </div>
        <?php
    }

    /**
     * 获取模块默认数据。
     *
     * @param string                            $type          模块类型。
     * @param array<string,array<string,mixed>> $module_fields 模块字段定义。
     * @return array<string,mixed>
     */
    public function get_defaults( $type, $module_fields ) {
        $data = array();
        if ( ! isset( $module_fields[ $type ] ) || empty( $module_fields[ $type ]['fields'] ) || ! is_array( $module_fields[ $type ]['fields'] ) ) {
            return $data;
        }

        foreach ( $module_fields[ $type ]['fields'] as $field ) {
            if ( ! is_array( $field ) || empty( $field['id'] ) ) {
                continue;
            }

            $fid = (string) $field['id'];
            if ( isset( $field['type'] ) && $field['type'] === 'repeater' && isset( $field['default_items'] ) ) {
                $data[ $fid ] = $field['default_items'];
            } elseif ( isset( $field['default'] ) ) {
                $data[ $fid ] = $field['default'];
            }
        }

        return $data;
    }

    /**
     * 渲染单个字段。
     *
     * @param int                 $idx   模块索引。
     * @param array<string,mixed> $field 字段定义。
     * @param array<string,mixed> $data  模块数据。
     * @return void
     */
    public function render_field( $idx, $field, $data ) {
        if ( ! is_array( $field ) ) {
            return;
        }

        $field_type = isset( $field['type'] ) ? (string) $field['type'] : 'text';
        if ( in_array( $field_type, array( 'heading', 'header', 'separator' ), true ) ) {
            echo '<div class="dsm-field dsm-field-heading"><h4>' . esc_html( isset( $field['label'] ) ? (string) $field['label'] : '' ) . '</h4></div>';
            return;
        }
        if ( empty( $field['id'] ) ) {
            return;
        }

        $fid  = (string) $field['id'];
        $def  = isset( $field['default'] ) ? $field['default'] : '';
        $val  = isset( $data[ $fid ] ) ? $data[ $fid ] : $def;
        $name = "modules[{$idx}][data][{$fid}]";

        $dep_attr = '';
        if ( isset( $field['dependency'] ) ) {
            $dep_attr = " data-dependency='" . esc_attr( json_encode( $field['dependency'] ) ) . "'";
        }
        ?>
        <div class="dsm-field"<?php echo $dep_attr; ?>>
            <label><?php echo esc_html( isset( $field['label'] ) ? (string) $field['label'] : $fid ); ?></label>
            <?php
            switch ( $field_type ) {
                case 'textarea':
                case 'editor':
                    echo '<textarea name="' . esc_attr( $name ) . '" rows="3">' . esc_textarea( is_scalar( $val ) ? (string) $val : '' ) . '</textarea>';
                    break;

                case 'select':
                    $options = isset( $field['options'] ) && is_array( $field['options'] ) ? $field['options'] : array();
                    $has_yes_no = isset( $options['yes'] ) || isset( $options['no'] );
                    if ( $has_yes_no ) {
                        if ( $val === '1' ) {
                            $val = 'yes';
                        } elseif ( $val === '0' || $val === '' ) {
                            $val = 'no';
                        }
                    }
                    echo '<select name="' . esc_attr( $name ) . '" autocomplete="off">';
                    foreach ( $options as $ov => $ol ) {
                        echo '<option value="' . esc_attr( (string) $ov ) . '"' . selected( $val, $ov, false ) . '>' . esc_html( (string) $ol ) . '</option>';
                    }
                    echo '</select>';
                    break;

                case 'switcher':
                case 'checkbox':
                    echo '<label style="display:inline-flex; align-items:center; gap:6px;"><input type="checkbox" name="' . esc_attr( $name ) . '" value="1"' . checked( in_array( (string) $val, array( '1', 'yes', 'true' ), true ), true, false ) . '/> ' . esc_html__( '启用', 'developer-starter' ) . '</label>';
                    break;

                case 'gallery':
                    echo '<input type="hidden" name="' . esc_attr( $name ) . '" value="' . esc_attr( is_scalar( $val ) ? (string) $val : '' ) . '" class="dsm-img-input"/>';
                    echo '<button type="button" class="button dsm-upload dsm-gallery-upload">' . esc_html__( '选择多图', 'developer-starter' ) . '</button>';
                    echo '<div class="dsm-gallery-preview" style="margin-top:10px;">';
                    if ( is_scalar( $val ) && $val ) {
                        $urls = explode( ',', (string) $val );
                        foreach ( $urls as $u ) {
                            if ( empty( $u ) ) {
                                continue;
                            }
                            echo '<span class="dsm-img-wrap gallery-item" style="margin-right:8px; margin-bottom:8px;"><img src="' . esc_url( trim( $u ) ) . '" class="dsm-img-preview"/><button type="button" class="dsm-img-remove">×</button></span>';
                        }
                    }
                    echo '</div>';
                    break;

                case 'image':
                case 'file':
                case 'upload':
                    echo '<input type="text" name="' . esc_attr( $name ) . '" value="' . esc_attr( is_scalar( $val ) ? (string) $val : '' ) . '" class="dsm-img-input" placeholder="' . esc_attr__( '输入图片URL或点击选择', 'developer-starter' ) . '" style="max-width:350px;"/>';
                    echo '<button type="button" class="button dsm-upload" style="margin-left:8px;">' . esc_html__( '选择', 'developer-starter' ) . '</button>';
                    if ( is_scalar( $val ) && $val ) {
                        echo '<span class="dsm-img-wrap"><img src="' . esc_url( (string) $val ) . '" class="dsm-img-preview"/><button type="button" class="dsm-img-remove">×</button></span>';
                    }
                    break;

                case 'number':
                    echo '<input type="number" name="' . esc_attr( $name ) . '" value="' . esc_attr( is_scalar( $val ) ? (string) $val : '' ) . '" min="' . esc_attr( isset( $field['min'] ) ? (string) $field['min'] : '' ) . '" max="' . esc_attr( isset( $field['max'] ) ? (string) $field['max'] : '' ) . '" step="' . esc_attr( isset( $field['step'] ) ? (string) $field['step'] : '' ) . '"/>';
                    break;

                case 'range':
                    echo '<input type="range" name="' . esc_attr( $name ) . '" value="' . esc_attr( is_scalar( $val ) ? (string) $val : '' ) . '" min="' . esc_attr( isset( $field['min'] ) ? (string) $field['min'] : '0' ) . '" max="' . esc_attr( isset( $field['max'] ) ? (string) $field['max'] : '100' ) . '" step="' . esc_attr( isset( $field['step'] ) ? (string) $field['step'] : '1' ) . '"/>';
                    break;

                case 'color':
                    echo '<input type="text" name="' . esc_attr( $name ) . '" value="' . esc_attr( is_scalar( $val ) ? (string) $val : '' ) . '" class="dsm-color-input" placeholder="#ffffff 或 linear-gradient(...)"/>';
                    break;

                case 'date':
                    echo '<input type="date" name="' . esc_attr( $name ) . '" value="' . esc_attr( is_scalar( $val ) ? (string) $val : '' ) . '"/>';
                    break;

                case 'repeater':
                    $this->render_repeater_field( $idx, $fid, $field, $val );
                    break;

                case 'info':
                    if ( isset( $field['description'] ) ) {
                        echo '<p class="description" style="color: #666; font-size: 13px; margin: 0;">' . wp_kses_post( (string) $field['description'] ) . '</p>';
                    }
                    break;

                default:
                    echo '<input type="text" name="' . esc_attr( $name ) . '" value="' . esc_attr( is_scalar( $val ) ? (string) $val : '' ) . '"/>';
            }

            if ( $field_type !== 'info' && isset( $field['description'] ) ) {
                echo '<p class="description" style="color: #666; font-size: 12px; margin-top: 4px;">' . wp_kses_post( (string) $field['description'] ) . '</p>';
            }
            ?>
        </div>
        <?php
    }

    /**
     * 渲染 repeater 字段。
     *
     * @param int                 $idx   模块索引。
     * @param string              $fid   字段 ID。
     * @param array<string,mixed> $field 字段定义。
     * @param mixed               $val   字段值。
     * @return void
     */
    private function render_repeater_field( $idx, $fid, $field, $val ) {
        $items = is_array( $val ) ? $val : array();
        $subs = isset( $field['fields'] ) && is_array( $field['fields'] ) ? $field['fields'] : array();

        if ( empty( $items ) && isset( $field['default_items'] ) && is_array( $field['default_items'] ) ) {
            $items = $field['default_items'];
        }

        echo '<div class="dsm-repeater-list">';
        foreach ( $items as $ri => $item ) {
            echo '<div class="dsm-repeater-item">';
            echo '<a href="#" class="dsm-repeater-remove">x</a>';
            foreach ( $subs as $sf ) {
                if ( isset( $sf['type'] ) && $sf['type'] === 'header' ) {
                    echo '<h4 style="margin: 15px 0 10px; border-bottom: 1px solid #eee; padding-bottom: 5px; color: #2271b1;">' . esc_html( isset( $sf['label'] ) ? (string) $sf['label'] : '' ) . '</h4>';
                    continue;
                }

                $sf_id = isset( $sf['id'] ) ? (string) $sf['id'] : '';
                $sv = ( is_array( $item ) && $sf_id !== '' && isset( $item[ $sf_id ] ) ) ? $item[ $sf_id ] : '';
                $sn = "modules[{$idx}][data][{$fid}][{$ri}][{$sf_id}]";
                $s_dep_attr = '';
                if ( isset( $sf['dependency'] ) ) {
                    $s_dep_attr = " data-dependency='" . esc_attr( json_encode( $sf['dependency'] ) ) . "'";
                }

                echo '<div class="dsm-field"' . $s_dep_attr . '><label>' . esc_html( isset( $sf['label'] ) ? (string) $sf['label'] : $sf_id ) . '</label>';
                $sf_type = isset( $sf['type'] ) ? (string) $sf['type'] : 'text';
                if ( $sf_type === 'image' || $sf_type === 'file' ) {
                    echo '<input type="text" name="' . esc_attr( $sn ) . '" value="' . esc_attr( is_scalar( $sv ) ? (string) $sv : '' ) . '" class="dsm-img-input" placeholder="' . esc_attr__( '输入图片URL或点击选择', 'developer-starter' ) . '" style="max-width:250px;"/>';
                    echo '<button type="button" class="button dsm-upload" style="margin-left:8px;">' . esc_html__( '选择', 'developer-starter' ) . '</button>';
                    if ( is_scalar( $sv ) && $sv ) {
                        echo '<span class="dsm-img-wrap"><img src="' . esc_url( (string) $sv ) . '" class="dsm-img-preview"/><button type="button" class="dsm-img-remove">×</button></span>';
                    }
                } elseif ( $sf_type === 'gallery' ) {
                    echo '<input type="hidden" name="' . esc_attr( $sn ) . '" value="' . esc_attr( is_scalar( $sv ) ? (string) $sv : '' ) . '" class="dsm-img-input"/>';
                    echo '<button type="button" class="button dsm-upload dsm-gallery-upload">' . esc_html__( '选择多图', 'developer-starter' ) . '</button>';
                    echo '<div class="dsm-gallery-preview" style="margin-top:5px;">';
                    if ( is_scalar( $sv ) && $sv ) {
                        $urls = explode( ',', (string) $sv );
                        foreach ( $urls as $u ) {
                            if ( empty( $u ) ) {
                                continue;
                            }
                            echo '<span class="dsm-img-wrap gallery-item" style="margin-right:5px; margin-bottom:5px;"><img src="' . esc_url( trim( $u ) ) . '" class="dsm-img-preview" style="max-width:50px;max-height:50px;"/><button type="button" class="dsm-img-remove">×</button></span>';
                        }
                    }
                    echo '</div>';
                } elseif ( $sf_type === 'textarea' ) {
                    echo '<textarea name="' . esc_attr( $sn ) . '" rows="2">' . esc_textarea( is_scalar( $sv ) ? (string) $sv : '' ) . '</textarea>';
                } elseif ( $sf_type === 'number' ) {
                    echo '<input type="number" name="' . esc_attr( $sn ) . '" value="' . esc_attr( is_scalar( $sv ) ? (string) $sv : '' ) . '"/>';
                } elseif ( $sf_type === 'date' ) {
                    echo '<input type="date" name="' . esc_attr( $sn ) . '" value="' . esc_attr( is_scalar( $sv ) ? (string) $sv : '' ) . '"/>';
                } elseif ( $sf_type === 'checkbox' ) {
                    echo '<label style="display:inline-flex; align-items:center; gap:6px;"><input type="checkbox" name="' . esc_attr( $sn ) . '" value="1"' . checked( ! empty( $sv ), true, false ) . '/> ' . esc_html__( '启用', 'developer-starter' ) . '</label>';
                } elseif ( $sf_type === 'select' && isset( $sf['options'] ) && is_array( $sf['options'] ) ) {
                    echo '<select name="' . esc_attr( $sn ) . '" autocomplete="off">';
                    foreach ( $sf['options'] as $opt_val => $opt_label ) {
                        echo '<option value="' . esc_attr( (string) $opt_val ) . '"' . selected( $sv, $opt_val, false ) . '>' . esc_html( (string) $opt_label ) . '</option>';
                    }
                    echo '</select>';
                } else {
                    echo '<input type="text" name="' . esc_attr( $sn ) . '" value="' . esc_attr( is_scalar( $sv ) ? (string) $sv : '' ) . '"/>';
                }
                if ( isset( $sf['description'] ) ) {
                    echo '<p class="description" style="color: #666; font-size: 12px; margin-top: 4px;">' . wp_kses_post( (string) $sf['description'] ) . '</p>';
                }
                echo '</div>';
            }
            echo '</div>';
        }
        echo '</div>';

        $tpl_html = '<div class="dsm-repeater-item"><a href="#" class="dsm-repeater-remove">x</a>';
        foreach ( $subs as $sf ) {
            if ( isset( $sf['type'] ) && $sf['type'] === 'header' ) {
                $tpl_html .= '<h4 style="margin: 15px 0 10px; border-bottom: 1px solid #eee; padding-bottom: 5px; color: #2271b1;">' . esc_html( isset( $sf['label'] ) ? (string) $sf['label'] : '' ) . '</h4>';
                continue;
            }

            $sf_id = isset( $sf['id'] ) ? (string) $sf['id'] : '';
            $sn = "modules[{$idx}][data][{$fid}][__RIDX__][{$sf_id}]";
            $s_dep_attr = '';
            if ( isset( $sf['dependency'] ) ) {
                $s_dep_attr = " data-dependency='" . esc_attr( json_encode( $sf['dependency'] ) ) . "'";
            }

            $tpl_html .= '<div class="dsm-field"' . $s_dep_attr . '><label>' . esc_html( isset( $sf['label'] ) ? (string) $sf['label'] : $sf_id ) . '</label>';
            $sf_type = isset( $sf['type'] ) ? (string) $sf['type'] : 'text';
            if ( $sf_type === 'image' || $sf_type === 'file' ) {
                $tpl_html .= '<input type="text" name="' . esc_attr( $sn ) . '" value="" class="dsm-img-input" placeholder="' . esc_attr__( '输入图片URL或点击选择', 'developer-starter' ) . '" style="max-width:250px;"/>';
                $tpl_html .= '<button type="button" class="button dsm-upload" style="margin-left:8px;">' . esc_html__( '选择', 'developer-starter' ) . '</button>';
            } elseif ( $sf_type === 'textarea' ) {
                $tpl_html .= '<textarea name="' . esc_attr( $sn ) . '" rows="2"></textarea>';
            } elseif ( $sf_type === 'number' ) {
                $tpl_html .= '<input type="number" name="' . esc_attr( $sn ) . '" value=""/>';
            } elseif ( $sf_type === 'date' ) {
                $tpl_html .= '<input type="date" name="' . esc_attr( $sn ) . '" value=""/>';
            } elseif ( $sf_type === 'checkbox' ) {
                $tpl_html .= '<label style="display:inline-flex; align-items:center; gap:6px;"><input type="checkbox" name="' . esc_attr( $sn ) . '" value="1"/> ' . esc_html__( '启用', 'developer-starter' ) . '</label>';
            } elseif ( $sf_type === 'select' && isset( $sf['options'] ) && is_array( $sf['options'] ) ) {
                $tpl_html .= '<select name="' . esc_attr( $sn ) . '" autocomplete="off">';
                foreach ( $sf['options'] as $opt_val => $opt_label ) {
                    $tpl_html .= '<option value="' . esc_attr( (string) $opt_val ) . '">' . esc_html( (string) $opt_label ) . '</option>';
                }
                $tpl_html .= '</select>';
            } else {
                $tpl_html .= '<input type="text" name="' . esc_attr( $sn ) . '" value=""/>';
            }
            if ( isset( $sf['description'] ) ) {
                $tpl_html .= '<p class="description" style="color: #666; font-size: 12px; margin-top: 4px;">' . wp_kses_post( (string) $sf['description'] ) . '</p>';
            }
            $tpl_html .= '</div>';
        }
        $tpl_html .= '</div>';
        echo '<div class="dsm-rep-tpl" data-template="' . esc_attr( $tpl_html ) . '" style="display:none;"></div>';
        echo '<button type="button" class="dsm-btn-add dsm-rep-add">' . __( '+ 添加项目', 'developer-starter' ) . '</button>';
    }
}
