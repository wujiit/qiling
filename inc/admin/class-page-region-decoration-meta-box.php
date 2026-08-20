<?php
/**
 * 页面级区域装修设置框。
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Admin;

use Developer_Starter\Core\Page_Region_Decoration;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Page_Region_Decoration_Meta_Box {

    const NONCE_ACTION = 'qiling_page_region_decoration_save';
    const NONCE_NAME   = 'qiling_page_region_decoration_nonce';

    public function __construct() {
        add_action( 'add_meta_boxes_page', array( $this, 'register_meta_box' ) );
        add_action( 'save_post_page', array( $this, 'save_meta_box' ), 20, 2 );
    }

    /**
     * 注册独立设置框，避免与页面视觉风格和全局页脚设置混在一起。
     *
     * @return void
     */
    public function register_meta_box() {
        add_meta_box(
            'qiling_page_region_decoration',
            __( '当前页顶部与底部装修', 'developer-starter' ),
            array( $this, 'render_meta_box' ),
            'page',
            'normal',
            'default'
        );
    }

    /**
     * 输出用户可理解的区域来源设置。
     *
     * @param \WP_Post $post 当前页面。
     * @return void
     */
    public function render_meta_box( $post ) {
        wp_nonce_field( self::NONCE_ACTION, self::NONCE_NAME );
        $settings = Page_Region_Decoration::get_post_settings( $post->ID );
        $pages = get_pages(
            array(
                'post_status' => 'publish',
                'sort_column' => 'post_title',
                'sort_order'  => 'ASC',
            )
        );
        ?>
        <div class="qiling-page-region-decoration" data-qiling-page-region-decoration>
            <p><strong><?php esc_html_e( '使用说明', 'developer-starter' ); ?></strong></p>
            <p class="description"><?php esc_html_e( '每个区域都可以跟随全站设置、使用一个独立装修页面，或者仅在当前页隐藏。只想改背景和文字颜色时，不需要挂载装修页面，请直接使用“页面视觉风格”中的对应区域配色。', 'developer-starter' ); ?></p>
            <table class="widefat striped" style="margin-top: 14px;">
                <thead>
                    <tr>
                        <th><?php esc_html_e( '页面区域', 'developer-starter' ); ?></th>
                        <th><?php esc_html_e( '当前页处理方式', 'developer-starter' ); ?></th>
                        <th><?php esc_html_e( '装修内容来源', 'developer-starter' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( Page_Region_Decoration::get_regions() as $region => $label ) : ?>
                        <?php $region_settings = $settings[ $region ]; ?>
                        <tr data-qiling-page-region-row>
                            <th scope="row"><?php echo esc_html( $label ); ?></th>
                            <td>
                                <select name="qiling_page_region_decoration[<?php echo esc_attr( $region ); ?>][mode]" data-qiling-page-region-mode>
                                    <option value="inherit" <?php selected( $region_settings['mode'], 'inherit' ); ?>><?php esc_html_e( '跟随全站设置', 'developer-starter' ); ?></option>
                                    <option value="custom" <?php selected( $region_settings['mode'], 'custom' ); ?>><?php esc_html_e( '使用指定装修页面', 'developer-starter' ); ?></option>
                                    <option value="hidden" <?php selected( $region_settings['mode'], 'hidden' ); ?>><?php esc_html_e( '仅在当前页隐藏', 'developer-starter' ); ?></option>
                                </select>
                            </td>
                            <td>
                                <select name="qiling_page_region_decoration[<?php echo esc_attr( $region ); ?>][page_id]" data-qiling-page-region-source>
                                    <option value="0"><?php esc_html_e( '请选择已完成装修的页面', 'developer-starter' ); ?></option>
                                    <?php foreach ( $pages as $source_page ) : ?>
                                        <?php if ( absint( $source_page->ID ) === absint( $post->ID ) ) { continue; } ?>
                                        <option value="<?php echo esc_attr( (string) $source_page->ID ); ?>" <?php selected( $region_settings['page_id'], $source_page->ID ); ?>><?php echo esc_html( $source_page->post_title . ' (#' . $source_page->ID . ')' ); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <p class="description" style="margin-top: 12px;"><?php esc_html_e( '优先级：当前页指定装修 > 当前页隐藏 > 全站装修 > 主题默认结构。装修源页面不能选择当前页面自身，以免循环加载。', 'developer-starter' ); ?></p>
        </div>
        <script>
        (function() {
            var root = document.querySelector('[data-qiling-page-region-decoration]');
            if (!root) return;
            root.querySelectorAll('[data-qiling-page-region-row]').forEach(function(row) {
                var mode = row.querySelector('[data-qiling-page-region-mode]');
                var source = row.querySelector('[data-qiling-page-region-source]');
                function sync() {
                    if (source) source.disabled = !mode || mode.value !== 'custom';
                }
                if (mode) mode.addEventListener('change', sync);
                sync();
            });
        })();
        </script>
        <?php
    }

    /**
     * 保存页面级区域装修设置。
     *
     * @param int      $post_id 页面 ID。
     * @param \WP_Post $post    页面对象。
     * @return void
     */
    public function save_meta_box( $post_id, $post ) {
        if ( ! $post instanceof \WP_Post || 'page' !== $post->post_type || wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
            return;
        }
        if ( ! current_user_can( 'edit_page', $post_id ) ) {
            return;
        }
        if ( ! isset( $_POST[ self::NONCE_NAME ] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ self::NONCE_NAME ] ) ), self::NONCE_ACTION ) ) {
            return;
        }

        $settings = isset( $_POST['qiling_page_region_decoration'] ) && is_array( $_POST['qiling_page_region_decoration'] )
            ? wp_unslash( $_POST['qiling_page_region_decoration'] )
            : array();
        Page_Region_Decoration::persist_post_settings( $post_id, $settings );
    }
}
