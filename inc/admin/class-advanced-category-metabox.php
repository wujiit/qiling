<?php
/**
 * Advanced Category Metabox - 高级分类选择元框
 * 
 * 在文章编辑器中添加高级分类选择功能
 * 允许为文章分配大分类和小分类标签用于前端筛选
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Admin;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Advanced_Category_Metabox {

    /**
     * 构造函数
     */
    public function __construct() {
        add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
        add_action( 'save_post', array( $this, 'save_meta_box' ) );
    }

    /**
     * 添加元框到文章编辑器
     */
    public function add_meta_box() {
        add_meta_box(
            'ds_advanced_category',
            __( '🔖 高级分类设置', 'developer-starter' ),
            array( $this, 'render_meta_box' ),
            'post',
            'side',
            'default'
        );
    }

    private function get_category_advanced_filter_options( $term_id ) {
        $levels = $this->get_term_levels( $term_id );
        if ( empty( $levels ) ) {
            return array();
        }
        $result = array();
        foreach ( $levels as $index => $level ) {
            $options = array_unique( array_filter( $level['options'] ) );
            if ( empty( $options ) ) {
                continue;
            }
            $label = $level['label'] ? $level['label'] : sprintf( __( '分类层级 %d', 'developer-starter' ), $index + 1 );
            $result[] = array(
                'key' => 'level_' . $index,
                'label' => $label,
                'options' => array_values( $options ),
            );
        }
        return $result;
    }
    
    private function get_term_levels( $term_id ) {
        $levels_raw = get_term_meta( $term_id, 'ds_adv_custom_levels', true );
        if ( ! empty( $levels_raw ) ) {
            $trimmed_raw = ltrim( $levels_raw );
            $decoded = null;
            if ( strpos( $trimmed_raw, '[' ) === 0 || strpos( $trimmed_raw, '{' ) === 0 ) {
                $decoded = json_decode( $levels_raw, true );
            }
            if ( is_array( $decoded ) ) {
                $levels = array();
                foreach ( $decoded as $level ) {
                    $label = isset( $level['label'] ) ? trim( (string) $level['label'] ) : '';
                    $options = isset( $level['options'] ) && is_array( $level['options'] ) ? array_filter( array_map( 'trim', $level['options'] ) ) : array();
                    if ( $label && ! empty( $options ) ) {
                        $levels[] = array( 'label' => $label, 'options' => $options );
                    }
                }
                if ( ! empty( $levels ) ) {
                    return $levels;
                }
            }
            
            $lines = array_filter( array_map( 'trim', preg_split( "/\r\n|\n|\r/", $levels_raw ) ) );
            $levels = array();
            foreach ( $lines as $line ) {
                $parts = explode( ':', $line, 2 );
                $label = isset( $parts[0] ) ? trim( $parts[0] ) : '';
                $opts_text = isset( $parts[1] ) ? trim( $parts[1] ) : '';
                if ( $label && $opts_text ) {
                    $options = array_filter( array_map( 'trim', explode( ',', $opts_text ) ) );
                    if ( ! empty( $options ) ) {
                        $levels[] = array( 'label' => $label, 'options' => $options );
                    }
                }
            }
            if ( ! empty( $levels ) ) {
                return $levels;
            }
        }
        
        $levels = array();
        $major_cats = get_term_meta( $term_id, 'ds_adv_major_cats', true );
        $minor_cats = get_term_meta( $term_id, 'ds_adv_minor_cats', true );
        if ( ! empty( $major_cats ) ) {
            $levels[] = array(
                'label' => __( '大分类', 'developer-starter' ),
                'options' => array_filter( array_map( 'trim', explode( ',', $major_cats ) ) ),
            );
        }
        if ( ! empty( $minor_cats ) ) {
            $levels[] = array(
                'label' => __( '小分类', 'developer-starter' ),
                'options' => array_filter( array_map( 'trim', explode( ',', $minor_cats ) ) ),
            );
        }
        return $levels;
    }

    /**
     * 渲染元框内容
     */
    public function render_meta_box( $post ) {
        wp_nonce_field( 'ds_adv_category_nonce', 'ds_adv_category_nonce' );
        
        // 获取已保存的值
        $saved_levels = get_post_meta( $post->ID, '_ds_adv_levels', true );
        if ( ! is_array( $saved_levels ) ) {
            $saved_levels = array();
            $saved_major = get_post_meta( $post->ID, '_ds_adv_major_cat', true );
            $saved_minor = get_post_meta( $post->ID, '_ds_adv_minor_cat', true );
            if ( $saved_major ) {
                $saved_levels['level_0'] = $saved_major;
            }
            if ( $saved_minor ) {
                $saved_levels['level_1'] = $saved_minor;
            }
        }
        
        $categories = get_categories( array(
            'hide_empty' => false,
        ) );

        $category_levels_map = array();
        foreach ( $categories as $category ) {
            $enabled = get_term_meta( $category->term_id, 'ds_adv_filter_enabled', true );
            if ( $enabled !== '1' ) {
                continue;
            }
            $levels = $this->get_category_advanced_filter_options( $category->term_id );
            if ( empty( $levels ) ) {
                continue;
            }
            $category_levels_map[ (string) $category->term_id ] = array(
                'term_id' => (string) $category->term_id,
                'name' => $category->name,
                'levels' => $levels,
            );
        }

        if ( empty( $category_levels_map ) ) {
            ?>
            <p style="color: #666; font-style: italic;">
                <?php esc_html_e( '暂无可用的高级分类选项。', 'developer-starter' ); ?><br>
                <?php printf( __( '请先在 <a href="%s">分类目录</a> 中启用高级分类筛选并设置筛选层级。', 'developer-starter' ), esc_url( admin_url( 'edit-tags.php?taxonomy=category' ) ) ); ?>
            </p>
            <?php
            return;
        }

        $default_term_id = '';
        foreach ( $category_levels_map as $term_id => $data ) {
            $default_term_id = $term_id;
            break;
        }

        $current_term_id = $default_term_id;
        $post_categories = get_the_category( $post->ID );
        if ( ! empty( $post_categories ) ) {
            foreach ( $post_categories as $post_category ) {
                $post_term_id = (string) $post_category->term_id;
                if ( isset( $category_levels_map[ $post_term_id ] ) ) {
                    $current_term_id = $post_term_id;
                    break;
                }
            }
        }

        $levels = isset( $category_levels_map[ $current_term_id ] ) ? $category_levels_map[ $current_term_id ]['levels'] : array();
        ?>
        <div id="ds-adv-category-levels">
            <?php foreach ( $levels as $level ) : ?>
            <div style="margin-bottom: 12px;">
                <label for="ds_adv_<?php echo esc_attr( $level['key'] ); ?>" style="display: block; margin-bottom: 5px; font-weight: 600;"><?php echo esc_html( $level['label'] ); ?></label>
                <select name="ds_adv_levels[<?php echo esc_attr( $level['key'] ); ?>]" id="ds_adv_<?php echo esc_attr( $level['key'] ); ?>" style="width: 100%;">
                    <option value=""><?php esc_html_e( '-- 不设置 --', 'developer-starter' ); ?></option>
                    <?php foreach ( $level['options'] as $cat ) : ?>
                        <option value="<?php echo esc_attr( $cat ); ?>" <?php selected( isset( $saved_levels[ $level['key'] ] ) ? $saved_levels[ $level['key'] ] : '', $cat ); ?>><?php echo esc_html( $cat ); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endforeach; ?>
        </div>
        
        <p class="description" style="margin-top: 10px; font-size: 11px; color: #888;">
            <?php esc_html_e( '用于前端分类页面的高级筛选功能。', 'developer-starter' ); ?>
        </p>
        <script>
        (function($){
            var levelsMap = <?php echo wp_json_encode( $category_levels_map ); ?>;
            var savedLevels = <?php echo wp_json_encode( $saved_levels ); ?>;
            var defaultTermId = <?php echo wp_json_encode( $default_term_id ); ?>;

            function getSelectedTermId() {
                var selected = null;
                if (window.wp && wp.data && wp.data.select) {
                    var ids = wp.data.select('core/editor').getEditedPostAttribute('categories');
                    if (Array.isArray(ids)) {
                        ids.some(function(id){
                            if (levelsMap[id]) {
                                selected = id;
                                return true;
                            }
                            return false;
                        });
                    }
                }
                if (!selected) {
                    $('#categorychecklist input[type="checkbox"]:checked').each(function(){
                        var id = $(this).val();
                        if (levelsMap[id]) {
                            selected = id;
                            return false;
                        }
                        return true;
                    });
                }
                if (!selected) {
                    selected = defaultTermId;
                }
                return selected;
            }

            function renderLevels(termId) {
                var levels = levelsMap[termId] ? levelsMap[termId].levels : (levelsMap[defaultTermId] ? levelsMap[defaultTermId].levels : []);
                var $container = $('#ds-adv-category-levels');
                $container.empty();
                if (!levels || !levels.length) {
                    $container.append('<p style="color: #666; font-style: italic;"><?php echo esc_js( __( '暂无可用的高级分类选项。', 'developer-starter' ) ); ?></p>');
                    return;
                }
                levels.forEach(function(level){
                    var $wrap = $('<div>').attr('style', 'margin-bottom: 12px;');
                    var $label = $('<label>').attr('for', 'ds_adv_' + level.key).attr('style', 'display: block; margin-bottom: 5px; font-weight: 600;').text(level.label);
                    var $select = $('<select>').attr('name', 'ds_adv_levels[' + level.key + ']').attr('id', 'ds_adv_' + level.key).attr('style', 'width: 100%;');
                    $select.append($('<option>').attr('value', '').text('<?php echo esc_js( __( '-- 不设置 --', 'developer-starter' ) ); ?>'));
                    level.options.forEach(function(option){
                        var $option = $('<option>').attr('value', option).text(option);
                        if (savedLevels && savedLevels[level.key] === option) {
                            $option.prop('selected', true);
                        }
                        $select.append($option);
                    });
                    $wrap.append($label).append($select);
                    $container.append($wrap);
                });
            }

            function refreshLevels() {
                renderLevels(getSelectedTermId());
            }

            $(document).on('change', '#categorychecklist input[type="checkbox"]', refreshLevels);

            if (window.wp && wp.data && wp.data.subscribe) {
                var prevKey = null;
                wp.data.subscribe(function(){
                    var ids = wp.data.select('core/editor').getEditedPostAttribute('categories');
                    if (!Array.isArray(ids)) {
                        ids = [];
                    }
                    var key = ids.join(',');
                    if (key !== prevKey) {
                        prevKey = key;
                        refreshLevels();
                    }
                });
            }

            $(document).ready(function(){
                refreshLevels();
            });
        })(jQuery);
        </script>
        <?php
    }

    /**
     * 保存元框数据
     */
    public function save_meta_box( $post_id ) {
        // 验证
        $nonce = isset( $_POST['ds_adv_category_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['ds_adv_category_nonce'] ) ) : '';
        if ( '' === $nonce || ! wp_verify_nonce( $nonce, 'ds_adv_category_nonce' ) ) {
            return;
        }

        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        $levels = array();
        if ( isset( $_POST['ds_adv_levels'] ) && is_array( $_POST['ds_adv_levels'] ) ) {
            $raw_levels = wp_unslash( $_POST['ds_adv_levels'] );
            foreach ( $raw_levels as $key => $value ) {
                if ( ! is_scalar( $value ) ) {
                    continue;
                }

                $clean_key = sanitize_key( $key );
                $clean_value = sanitize_text_field( $value );
                if ( $clean_value !== '' ) {
                    $levels[ $clean_key ] = $clean_value;
                }
            }
        }
        
        if ( ! empty( $levels ) ) {
            update_post_meta( $post_id, '_ds_adv_levels', $levels );
        } else {
            delete_post_meta( $post_id, '_ds_adv_levels' );
        }
        
        foreach ( $levels as $key => $value ) {
            update_post_meta( $post_id, '_ds_adv_' . $key, $value );
        }

        $meta_prefix = '_ds_adv_';
        $managed_meta_keys = array(
            '_ds_adv_levels',
            '_ds_adv_major_cat',
            '_ds_adv_minor_cat',
        );
        $existing_keys = get_post_meta( $post_id );
        foreach ( $existing_keys as $meta_key => $meta_value ) {
            if ( 0 !== strpos( $meta_key, $meta_prefix ) || in_array( $meta_key, $managed_meta_keys, true ) ) {
                continue;
            }

            $level_key = substr( $meta_key, strlen( $meta_prefix ) );
            if ( '' !== $level_key && ! array_key_exists( $level_key, $levels ) ) {
                delete_post_meta( $post_id, $meta_key );
            }
        }
        
        if ( isset( $levels['level_0'] ) ) {
            update_post_meta( $post_id, '_ds_adv_major_cat', $levels['level_0'] );
        } else {
            delete_post_meta( $post_id, '_ds_adv_major_cat' );
        }
        if ( isset( $levels['level_1'] ) ) {
            update_post_meta( $post_id, '_ds_adv_minor_cat', $levels['level_1'] );
        } else {
            delete_post_meta( $post_id, '_ds_adv_minor_cat' );
        }
    }
}
