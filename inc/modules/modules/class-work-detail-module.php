<?php
/**
 * Work Detail Module - 作品详情模块
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Modules\Modules;

use Developer_Starter\Modules\Module_Base;
use Developer_Starter\Modules\Module_Manager;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Work_Detail_Module extends Module_Base {

    public function __construct() {
        $this->category    = 'content';
        $this->icon        = 'dashicons-media-document';
        $this->description = __( '读取 URL 参数中的作品并展示完整详情，支持版权、出版物、EXIF 与授权表单。', 'developer-starter' );
    }

    public function get_id() {
        return 'work_detail';
    }

    public function get_name() {
        return __( '作品详情', 'developer-starter' );
    }

    public function get_fields() {
        return array(
            array(
                'id'      => 'wd_empty_text',
                'type'    => 'text',
                'label'   => __( '未匹配到作品时提示', 'developer-starter' ),
                'default' => __( '请选择一个作品进入详情页。', 'developer-starter' ),
            ),
            array(
                'id'      => 'wd_show_back',
                'type'    => 'select',
                'label'   => __( '显示返回作品库按钮', 'developer-starter' ),
                'options' => array(
                    '1' => __( '是', 'developer-starter' ),
                    '0' => __( '否', 'developer-starter' ),
                ),
                'default' => '1',
            ),
            array(
                'id'      => 'wd_back_label',
                'type'    => 'text',
                'label'   => __( '返回按钮文案', 'developer-starter' ),
                'default' => __( '返回作品库', 'developer-starter' ),
            ),
            array(
                'id'      => 'wd_library_url',
                'type'    => 'text',
                'label'   => __( '作品库页面 URL（可选）', 'developer-starter' ),
                'default' => '',
                'desc'    => __( '留空则返回当前页面并移除作品参数。', 'developer-starter' ),
            ),
            array(
                'id'      => 'wd_show_cover',
                'type'    => 'select',
                'label'   => __( '显示封面图', 'developer-starter' ),
                'options' => array(
                    '1' => __( '是', 'developer-starter' ),
                    '0' => __( '否', 'developer-starter' ),
                ),
                'default' => '1',
            ),
            array(
                'id'      => 'wd_show_gallery',
                'type'    => 'select',
                'label'   => __( '显示图集', 'developer-starter' ),
                'options' => array(
                    '1' => __( '是', 'developer-starter' ),
                    '0' => __( '否', 'developer-starter' ),
                ),
                'default' => '1',
            ),
            array(
                'id'      => 'wd_show_content',
                'type'    => 'select',
                'label'   => __( '显示正文', 'developer-starter' ),
                'options' => array(
                    '1' => __( '是', 'developer-starter' ),
                    '0' => __( '否', 'developer-starter' ),
                ),
                'default' => '1',
            ),
            array(
                'id'      => 'wd_show_terms',
                'type'    => 'select',
                'label'   => __( '显示分类标签', 'developer-starter' ),
                'options' => array(
                    '1' => __( '是', 'developer-starter' ),
                    '0' => __( '否', 'developer-starter' ),
                ),
                'default' => '1',
            ),
            array(
                'id'      => 'wd_show_copyright',
                'type'    => 'select',
                'label'   => __( '显示版权信息', 'developer-starter' ),
                'options' => array(
                    '1' => __( '是', 'developer-starter' ),
                    '0' => __( '否', 'developer-starter' ),
                ),
                'default' => '1',
            ),
            array(
                'id'      => 'wd_show_book',
                'type'    => 'select',
                'label'   => __( '显示出版信息', 'developer-starter' ),
                'options' => array(
                    '1' => __( '是', 'developer-starter' ),
                    '0' => __( '否', 'developer-starter' ),
                ),
                'default' => '1',
            ),
            array(
                'id'      => 'wd_show_exif',
                'type'    => 'select',
                'label'   => __( '显示 EXIF / 器材信息', 'developer-starter' ),
                'options' => array(
                    '1' => __( '是', 'developer-starter' ),
                    '0' => __( '否', 'developer-starter' ),
                ),
                'default' => '1',
            ),
            array(
                'id'      => 'wd_show_external',
                'type'    => 'select',
                'label'   => __( '显示外链按钮', 'developer-starter' ),
                'options' => array(
                    '1' => __( '是', 'developer-starter' ),
                    '0' => __( '否', 'developer-starter' ),
                ),
                'default' => '1',
            ),
            array( 'id' => 'wd_external_label', 'type' => 'text', 'label' => __( '外链按钮默认文案', 'developer-starter' ), 'default' => __( '立即查看', 'developer-starter' ), 'desc' => __( '作品数据没有单独按钮文案时使用。', 'developer-starter' ) ),
            array(
                'id'      => 'wd_bg_color',
                'type'    => 'color',
                'label'   => __( '背景颜色', 'developer-starter' ),
                'default' => 'var(--color-neutral-0)',
            ),
            array(
                'id'      => 'wd_padding_top',
                'type'    => 'text',
                'label'   => __( '顶部间距', 'developer-starter' ),
                'default' => '52px',
            ),
            array(
                'id'      => 'wd_padding_bottom',
                'type'    => 'text',
                'label'   => __( '底部间距', 'developer-starter' ),
                'default' => '64px',
            ),
        );
    }

    public function render( $data = array() ) {
        if ( ! class_exists( 'QilingWork_Repository' ) ) {
            $this->render_missing_plugin_notice();
            return;
        }

        $empty_text = isset( $data['wd_empty_text'] ) ? sanitize_text_field( (string) $data['wd_empty_text'] ) : __( '请选择一个作品进入详情页。', 'developer-starter' );
        $show_back  = isset( $data['wd_show_back'] ) ? '1' === (string) $data['wd_show_back'] : true;
        $back_label = isset( $data['wd_back_label'] ) ? sanitize_text_field( (string) $data['wd_back_label'] ) : __( '返回作品库', 'developer-starter' );
        $library_url = isset( $data['wd_library_url'] ) ? esc_url_raw( (string) $data['wd_library_url'] ) : '';

        $show_cover        = isset( $data['wd_show_cover'] ) ? '1' === (string) $data['wd_show_cover'] : true;
        $show_gallery      = isset( $data['wd_show_gallery'] ) ? '1' === (string) $data['wd_show_gallery'] : true;
        $show_content      = isset( $data['wd_show_content'] ) ? '1' === (string) $data['wd_show_content'] : true;
        $show_terms        = isset( $data['wd_show_terms'] ) ? '1' === (string) $data['wd_show_terms'] : true;
        $show_copyright    = isset( $data['wd_show_copyright'] ) ? '1' === (string) $data['wd_show_copyright'] : true;
        $show_book         = isset( $data['wd_show_book'] ) ? '1' === (string) $data['wd_show_book'] : true;
        $show_exif         = isset( $data['wd_show_exif'] ) ? '1' === (string) $data['wd_show_exif'] : true;
        $show_external_btn = isset( $data['wd_show_external'] ) ? '1' === (string) $data['wd_show_external'] : true;
        $external_label    = isset( $data['wd_external_label'] ) && '' !== trim( (string) $data['wd_external_label'] ) ? sanitize_text_field( (string) $data['wd_external_label'] ) : __( '立即查看', 'developer-starter' );

        $bg_color       = isset( $data['wd_bg_color'] ) ? sanitize_text_field( (string) $data['wd_bg_color'] ) : 'var(--color-neutral-0)';
        $padding_top    = isset( $data['wd_padding_top'] ) ? Module_Manager::sanitize_spacing_value( $data['wd_padding_top'] ) : '52px';
        $padding_bottom = isset( $data['wd_padding_bottom'] ) ? Module_Manager::sanitize_spacing_value( $data['wd_padding_bottom'] ) : '64px';

        if ( '' === $padding_top ) {
            $padding_top = '52px';
        }
        if ( '' === $padding_bottom ) {
            $padding_bottom = '64px';
        }

        $item_id   = isset( $_GET['qilingwork_id'] ) ? absint( wp_unslash( $_GET['qilingwork_id'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $item_slug = isset( $_GET['qilingwork_item'] ) ? sanitize_title( wp_unslash( (string) $_GET['qilingwork_item'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $item_type = isset( $_GET['qilingwork_item_type'] ) ? sanitize_key( wp_unslash( (string) $_GET['qilingwork_item_type'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

        $item = null;
        if ( $item_id > 0 ) {
            $item = \QilingWork_Repository::get_item( $item_id, true, true );
        } elseif ( '' !== $item_slug ) {
            $item = \QilingWork_Repository::get_item_by_slug( $item_slug, true, true, $item_type );
        }

        $module_id = 'work-detail-' . uniqid();

        if ( ! is_array( $item ) || ( ! current_user_can( 'manage_options' ) && 'published' !== (string) $item['status'] ) ) {
            $this->render_empty_state( $module_id, $bg_color, $padding_top, $padding_bottom, $empty_text, $show_back, $library_url, $back_label );
            return;
        }

        $meta = isset( $item['meta'] ) && is_array( $item['meta'] ) ? $item['meta'] : array();
        $exif = \QilingWork_Repository::get_item_exif( (int) $item['id'] );
        if ( ! is_array( $exif ) ) {
            $exif = array();
        }

        $terms_map = isset( $item['terms'] ) && is_array( $item['terms'] ) ? $item['terms'] : array();
        $gallery   = array();

        if ( isset( $meta['gallery_urls'] ) && is_array( $meta['gallery_urls'] ) ) {
            foreach ( $meta['gallery_urls'] as $url ) {
                $san = esc_url( (string) $url );
                if ( '' !== $san ) {
                    $gallery[] = $san;
                }
            }
        }

        $back_url = '' !== $library_url ? $library_url : remove_query_arg(
            array( 'qilingwork_item', 'qilingwork_item_type', 'qilingwork_id' ),
            $this->current_page_url()
        );
        $external_url = isset( $meta['external_url'] ) ? esc_url( (string) $meta['external_url'] ) : '';
        $external_cta = isset( $meta['cta_label'] ) && '' !== (string) $meta['cta_label'] ? sanitize_text_field( (string) $meta['cta_label'] ) : $external_label;
        $section_style = $this->build_section_style( $bg_color, $padding_top, $padding_bottom );
        ?>
        <section class="module module-work-detail" id="<?php echo esc_attr( $module_id ); ?>" style="<?php echo esc_attr( $section_style ); ?>">
            <div class="container">
                <?php if ( $show_back ) : ?>
                    <div class="qw-detail-topbar">
                        <a href="<?php echo esc_url( $back_url ); ?>" class="qw-detail-back"><?php echo esc_html( $back_label ); ?></a>
                    </div>
                <?php endif; ?>

                <header class="qw-detail-header">
                    <h1 class="qw-detail-title"><?php echo esc_html( (string) $item['title'] ); ?></h1>
                    <?php if ( ! empty( $item['excerpt'] ) ) : ?>
                        <p class="qw-detail-excerpt"><?php echo esc_html( (string) $item['excerpt'] ); ?></p>
                    <?php endif; ?>
                    <div class="qw-detail-meta-line">
                        <?php if ( ! empty( $item['item_type'] ) ) : ?>
                            <span><?php echo esc_html( (string) $item['item_type'] ); ?></span>
                        <?php endif; ?>
                        <?php if ( ! empty( $item['published_at'] ) ) : ?>
                            <span><?php echo esc_html( function_exists( 'developer_starter_format_date_value' ) ? developer_starter_format_date_value( (string) $item['published_at'], true ) : mysql2date( 'Y-m-d H:i', (string) $item['published_at'], true ) ); ?></span>
                        <?php endif; ?>
                        <?php if ( '' !== (string) $item['price'] ) : ?>
                            <span><?php echo esc_html( $this->format_price( $item['price'], $item['currency'] ) ); ?></span>
                        <?php endif; ?>
                    </div>
                </header>

                <?php if ( $show_cover && ! empty( $item['cover_url'] ) ) : ?>
                    <figure class="qw-detail-cover">
                        <img src="<?php echo esc_url( (string) $item['cover_url'] ); ?>" alt="<?php echo esc_attr( (string) $item['title'] ); ?>" loading="lazy" />
                    </figure>
                <?php endif; ?>

                <div class="qw-detail-layout">
                    <main class="qw-detail-main">
                        <?php if ( $show_content && ! empty( $item['content'] ) ) : ?>
                            <div class="qw-detail-content">
                                <?php echo wp_kses_post( wpautop( (string) $item['content'] ) ); ?>
                            </div>
                        <?php endif; ?>

                        <?php if ( $show_gallery && ! empty( $gallery ) ) : ?>
                            <section class="qw-detail-section">
                                <h3><?php echo esc_html__( '作品图集', 'developer-starter' ); ?></h3>
                                <div class="qw-detail-gallery">
                                    <?php foreach ( $gallery as $img_url ) : ?>
                                        <img src="<?php echo esc_url( $img_url ); ?>" alt="<?php echo esc_attr( (string) $item['title'] ); ?>" loading="lazy" />
                                    <?php endforeach; ?>
                                </div>
                            </section>
                        <?php endif; ?>

                    </main>

                    <aside class="qw-detail-side">
                        <?php if ( $show_external_btn && '' !== $external_url ) : ?>
                            <div class="qw-detail-side-card">
                                <a class="qw-detail-cta" href="<?php echo esc_url( $external_url ); ?>" target="_blank" rel="noopener noreferrer">
                                    <?php echo esc_html( $external_cta ); ?>
                                </a>
                            </div>
                        <?php endif; ?>

                        <?php if ( $show_terms && ! empty( $terms_map ) ) : ?>
                            <div class="qw-detail-side-card">
                                <h3><?php echo esc_html__( '分类标签', 'developer-starter' ); ?></h3>
                                <div class="qw-detail-term-groups">
                                    <?php foreach ( $terms_map as $taxonomy => $rows ) : ?>
                                        <?php if ( empty( $rows ) ) : ?>
                                            <?php continue; ?>
                                        <?php endif; ?>
                                        <div class="qw-detail-term-group">
                                            <strong><?php echo esc_html( (string) $taxonomy ); ?></strong>
                                            <div class="qw-detail-term-list">
                                                <?php foreach ( $rows as $term ) : ?>
                                                    <?php if ( empty( $term['name'] ) ) : ?>
                                                        <?php continue; ?>
                                                    <?php endif; ?>
                                                    <span><?php echo esc_html( (string) $term['name'] ); ?></span>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if ( $show_copyright ) : ?>
                            <?php
                            $copyright_rows = array(
                                __( '版权归属', 'developer-starter' ) => isset( $meta['copyright_owner'] ) ? (string) $meta['copyright_owner'] : '',
                                __( '授权类型', 'developer-starter' ) => isset( $meta['copyright_license'] ) ? (string) $meta['copyright_license'] : '',
                                __( '版权说明', 'developer-starter' ) => isset( $meta['copyright_notice'] ) ? (string) $meta['copyright_notice'] : '',
                            );
                            $this->render_meta_card( __( '版权与授权', 'developer-starter' ), $copyright_rows );
                            ?>
                        <?php endif; ?>

                        <?php if ( $show_book ) : ?>
                            <?php
                            $book_rows = array(
                                __( '书名', 'developer-starter' )   => isset( $meta['book_title'] ) ? (string) $meta['book_title'] : '',
                                __( '作者', 'developer-starter' )   => isset( $meta['book_author'] ) ? (string) $meta['book_author'] : '',
                                __( '出版社', 'developer-starter' ) => isset( $meta['book_publisher'] ) ? (string) $meta['book_publisher'] : '',
                                __( 'ISBN', 'developer-starter' ) => isset( $meta['book_isbn'] ) ? (string) $meta['book_isbn'] : '',
                                __( '出版日期', 'developer-starter' ) => isset( $meta['book_publish_date'] ) ? (string) $meta['book_publish_date'] : '',
                            );
                            $this->render_meta_card( __( '书籍 / 出版物', 'developer-starter' ), $book_rows );
                            ?>
                        <?php endif; ?>

                        <?php if ( $show_exif ) : ?>
                            <?php
                            $exif_rows = array(
                                __( '机身品牌', 'developer-starter' ) => isset( $exif['camera_make'] ) ? (string) $exif['camera_make'] : '',
                                __( '机身型号', 'developer-starter' ) => isset( $exif['camera_model'] ) ? (string) $exif['camera_model'] : '',
                                __( '镜头型号', 'developer-starter' ) => isset( $exif['lens_model'] ) ? (string) $exif['lens_model'] : '',
                                __( '焦段', 'developer-starter' ) => isset( $exif['focal_length'] ) ? (string) $exif['focal_length'] : '',
                                __( '光圈', 'developer-starter' ) => isset( $exif['aperture'] ) ? (string) $exif['aperture'] : '',
                                __( '快门', 'developer-starter' ) => isset( $exif['shutter_speed'] ) ? (string) $exif['shutter_speed'] : '',
                                __( 'ISO', 'developer-starter' ) => isset( $exif['iso_value'] ) ? (string) $exif['iso_value'] : '',
                                __( '拍摄时间', 'developer-starter' ) => isset( $exif['taken_at'] ) ? (string) $exif['taken_at'] : '',
                            );
                            $this->render_meta_card( __( 'EXIF / 器材', 'developer-starter' ), $exif_rows );
                            ?>
                        <?php endif; ?>
                    </aside>
                </div>
            </div>
        </section>
        <?php
    }

    /**
     * Render empty state.
     *
     * @param string $module_id Module id.
     * @param string $bg_color Background.
     * @param string $padding_top Top padding.
     * @param string $padding_bottom Bottom padding.
     * @param string $message Message.
     * @param bool   $show_back Show back.
     * @param string $library_url Library URL.
     * @param string $back_label Back label.
     * @return void
     */
    private function render_empty_state( $module_id, $bg_color, $padding_top, $padding_bottom, $message, $show_back, $library_url, $back_label ) {
        $back_url = '' !== $library_url ? $library_url : remove_query_arg(
            array( 'qilingwork_item', 'qilingwork_item_type', 'qilingwork_id' ),
            $this->current_page_url()
        );
        $section_style = $this->build_section_style( $bg_color, $padding_top, $padding_bottom );
        ?>
        <section class="module module-work-detail" id="<?php echo esc_attr( $module_id ); ?>" style="<?php echo esc_attr( $section_style ); ?>">
            <div class="container">
                <div class="qw-detail-empty">
                    <p><?php echo esc_html( $message ); ?></p>
                    <?php if ( $show_back ) : ?>
                        <a href="<?php echo esc_url( $back_url ); ?>"><?php echo esc_html( $back_label ); ?></a>
                    <?php endif; ?>
                </div>
            </div>
        </section>
        <?php
    }

    /**
     * Render metadata card.
     *
     * @param string               $title Title.
     * @param array<string,string> $rows Rows.
     * @return void
     */
    private function render_meta_card( $title, $rows ) {
        $has_content = false;
        foreach ( $rows as $value ) {
            if ( '' !== trim( (string) $value ) ) {
                $has_content = true;
                break;
            }
        }

        if ( ! $has_content ) {
            return;
        }
        ?>
        <div class="qw-detail-side-card qw-detail-meta-card">
            <h3><?php echo esc_html( $title ); ?></h3>
            <dl>
                <?php foreach ( $rows as $label => $value ) : ?>
                    <?php if ( '' === trim( (string) $value ) ) : ?>
                        <?php continue; ?>
                    <?php endif; ?>
                    <dt><?php echo esc_html( $label ); ?></dt>
                    <dd><?php echo esc_html( (string) $value ); ?></dd>
                <?php endforeach; ?>
            </dl>
        </div>
        <?php
    }

    /**
     * Format price text.
     *
     * @param mixed $price Price.
     * @param mixed $currency Currency.
     * @return string
     */
    private function format_price( $price, $currency ) {
        $currency = sanitize_text_field( (string) $currency );
        $price    = is_scalar( $price ) ? (string) $price : '';

        if ( is_numeric( $price ) ) {
            if ( function_exists( 'developer_starter_format_currency_amount' ) ) {
                return developer_starter_format_currency_amount( $price, $currency, 2 );
            }

            return number_format_i18n( (float) $price, 2 ) . ' ' . $currency;
        }

        return trim( $price . ' ' . $currency );
    }

    /**
     * Build root section style variables.
     *
     * @param string $bg_color Background color.
     * @param string $padding_top Top padding.
     * @param string $padding_bottom Bottom padding.
     * @return string
     */
    private function build_section_style( $bg_color, $padding_top, $padding_bottom ) {
        return sprintf(
            '--qw-detail-bg:%1$s;--qw-detail-padding-top:%2$s;--qw-detail-padding-bottom:%3$s;',
            (string) $bg_color,
            (string) $padding_top,
            (string) $padding_bottom
        );
    }

    /**
     * Current URL helper.
     *
     * @return string
     */
    private function current_page_url() {
        if ( function_exists( 'get_permalink' ) && is_singular() ) {
            $url = get_permalink();
            if ( is_string( $url ) && '' !== $url ) {
                return $url;
            }
        }

        $request_uri = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( (string) $_SERVER['REQUEST_URI'] ) ) : '/';
        return home_url( $request_uri );
    }

    /**
     * Render plugin missing notice for admins.
     *
     * @return void
     */
    private function render_missing_plugin_notice() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        ?>
        <div class="notice notice-warning qiling-module-admin-notice">
            <?php echo esc_html__( '作品详情模块需要先启用「启灵作品库（qilingwork）」插件。', 'developer-starter' ); ?>
        </div>
        <?php
    }
}
