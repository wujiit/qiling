<?php
/**
 * Certificate Honors Module - 独立资质荣誉模块
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Modules\Modules;

use Developer_Starter\Modules\Module_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Certificate_Honors_Module extends Module_Base {

    public function __construct() {
        $this->category    = 'general';
        $this->icon        = 'dashicons-awards';
        $this->description = __( '独立展示企业资质、认证证书与荣誉奖项', 'developer-starter' );
    }

    public function get_id() {
        return 'certificate_honors';
    }

    public function get_name() {
        return __( '资质荣誉(独立模块)', 'developer-starter' );
    }

    public function get_fields() {
        return array(
            array(
                'id'      => 'ch_title',
                'type'    => 'text',
                'label'   => __( '标题', 'developer-starter' ),
                'default' => __( '资质荣誉', 'developer-starter' ),
            ),
            array(
                'id'      => 'ch_subtitle',
                'type'    => 'text',
                'label'   => __( '副标题', 'developer-starter' ),
                'default' => __( '覆盖核心资质、行业认证与企业荣誉', 'developer-starter' ),
            ),
            array(
                'id'      => 'ch_columns',
                'type'    => 'select',
                'label'   => __( '每行列数', 'developer-starter' ),
                'options' => array(
                    '2' => __( '2列', 'developer-starter' ),
                    '3' => __( '3列', 'developer-starter' ),
                    '4' => __( '4列', 'developer-starter' ),
                ),
                'default' => '3',
            ),
            array(
                'id'      => 'ch_enable_filter',
                'type'    => 'select',
                'label'   => __( '启用分类筛选', 'developer-starter' ),
                'options' => array(
                    'no'  => __( '关闭', 'developer-starter' ),
                    'yes' => __( '开启', 'developer-starter' ),
                ),
                'default' => 'yes',
            ),
            array( 'id' => 'ch_link_text', 'type' => 'text', 'label' => __( '查看证书按钮文案', 'developer-starter' ), 'default' => __( '查看证书', 'developer-starter' ) ),
            array(
                'id'      => 'module_bg_color',
                'type'    => 'text',
                'label'   => __( '背景颜色/渐变', 'developer-starter' ),
                'default' => '',
            ),
            array(
                'id'          => 'ch_badge_bg',
                'type'        => 'color',
                'label'       => __( '标签/徽章背景颜色', 'developer-starter' ),
                'default'     => '',
                'description' => __( '留空时跟随页面预设风格或全局徽章颜色。', 'developer-starter' ),
            ),
            array(
                'id'      => 'module_padding_top',
                'type'    => 'text',
                'label'   => __( '上边距 (如 60px)', 'developer-starter' ),
                'default' => '60px',
            ),
            array(
                'id'      => 'module_padding_bottom',
                'type'    => 'text',
                'label'   => __( '下边距 (如 60px)', 'developer-starter' ),
                'default' => '60px',
            ),
            array(
                'id'          => 'ch_items',
                'type'        => 'repeater',
                'label'       => __( '证书/荣誉列表', 'developer-starter' ),
                'description' => __( '每条可设置证书图片、编号、机构、有效期与附件链接', 'developer-starter' ),
                'fields'      => array(
                    array( 'id' => 'image', 'type' => 'image', 'label' => __( '证书图片', 'developer-starter' ) ),
                    array( 'id' => 'title', 'type' => 'text', 'label' => __( '证书名称', 'developer-starter' ) ),
                    array( 'id' => 'category', 'type' => 'text', 'label' => __( '分类(如 体系认证/专利/奖项)', 'developer-starter' ) ),
                    array( 'id' => 'badge', 'type' => 'text', 'label' => __( '标签(如 国家级/权威)', 'developer-starter' ) ),
                    array( 'id' => 'cert_no', 'type' => 'text', 'label' => __( '证书编号', 'developer-starter' ) ),
                    array( 'id' => 'issuer', 'type' => 'text', 'label' => __( '颁发机构', 'developer-starter' ) ),
                    array( 'id' => 'issue_date', 'type' => 'text', 'label' => __( '发证日期', 'developer-starter' ) ),
                    array( 'id' => 'expiry_date', 'type' => 'text', 'label' => __( '有效期至', 'developer-starter' ) ),
                    array( 'id' => 'file_url', 'type' => 'text', 'label' => __( '附件链接(PDF/原图)', 'developer-starter' ) ),
                ),
            ),
        );
    }

    public function render( $data = array() ) {
        $title         = isset( $data['ch_title'] ) ? $data['ch_title'] : __( '资质荣誉', 'developer-starter' );
        $subtitle      = isset( $data['ch_subtitle'] ) ? $data['ch_subtitle'] : '';
        $columns       = isset( $data['ch_columns'] ) ? max( 2, min( 4, (int) $data['ch_columns'] ) ) : 3;
        $enable_filter = isset( $data['ch_enable_filter'] ) ? $data['ch_enable_filter'] : 'yes';
        $link_text     = isset( $data['ch_link_text'] ) && '' !== trim( (string) $data['ch_link_text'] ) ? (string) $data['ch_link_text'] : __( '查看证书', 'developer-starter' );
        $items         = isset( $data['ch_items'] ) && is_array( $data['ch_items'] ) ? $data['ch_items'] : array();
        $bg_color      = isset( $data['module_bg_color'] ) ? trim( (string) $data['module_bg_color'] ) : '';
        $badge_bg      = isset( $data['ch_badge_bg'] ) ? trim( wp_strip_all_tags( (string) $data['ch_badge_bg'] ) ) : '';
        $badge_bg      = str_replace( array( ';', '{', '}' ), '', $badge_bg );
        $pt            = isset( $data['module_padding_top'] ) && $data['module_padding_top'] !== '' ? $data['module_padding_top'] : '60px';
        $pb            = isset( $data['module_padding_bottom'] ) && $data['module_padding_bottom'] !== '' ? $data['module_padding_bottom'] : '60px';
        $module_id     = 'ch-module-' . wp_rand( 1000, 999999 );

        if ( empty( $items ) ) {
            $items = array(
                array(
                    'title'      => __( 'ISO9001质量管理体系认证', 'developer-starter' ),
                    'category'   => __( '体系认证', 'developer-starter' ),
                    'badge'      => __( '权威认证', 'developer-starter' ),
                    'cert_no'    => 'ISO-9001-2025-001',
                    'issuer'     => __( '中国质量认证中心', 'developer-starter' ),
                    'issue_date' => '2025-01-18',
                    'expiry_date'=> '2028-01-17',
                ),
                array(
                    'title'      => __( '高新技术企业认定', 'developer-starter' ),
                    'category'   => __( '企业资质', 'developer-starter' ),
                    'badge'      => __( '国家级', 'developer-starter' ),
                    'cert_no'    => 'GR2025-88991',
                    'issuer'     => __( '科技主管部门', 'developer-starter' ),
                    'issue_date' => '2025-03-05',
                    'expiry_date'=> '2028-03-04',
                ),
                array(
                    'title'      => __( '行业创新奖', 'developer-starter' ),
                    'category'   => __( '荣誉奖项', 'developer-starter' ),
                    'badge'      => __( '年度奖项', 'developer-starter' ),
                    'cert_no'    => 'AWARD-2025-021',
                    'issuer'     => __( '行业协会', 'developer-starter' ),
                    'issue_date' => '2025-09-21',
                    'expiry_date'=> '',
                ),
            );
        }

        $section_style = "padding-top: {$pt}; padding-bottom: {$pb};";
        if ( $bg_color !== '' ) {
            $section_style .= strpos( $bg_color, 'gradient' ) !== false ? "background: {$bg_color};" : "background-color: {$bg_color};";
        }
        if ( $badge_bg !== '' ) {
            $section_style .= "--qiling-component-badge-bg: {$badge_bg};";
        }

        $categories = array();
        foreach ( $items as $item ) {
            $cat = isset( $item['category'] ) ? trim( (string) $item['category'] ) : '';
            if ( $cat !== '' && ! in_array( $cat, $categories, true ) ) {
                $categories[] = $cat;
            }
        }

        $get_media_url = function( $value ) {
            if ( empty( $value ) ) {
                return '';
            }
            if ( function_exists( 'developer_starter_get_media_url' ) ) {
                $resolved = developer_starter_get_media_url( $value );
                if ( ! empty( $resolved ) ) {
                    return $resolved;
                }
            }
            if ( is_numeric( $value ) ) {
                $url = wp_get_attachment_url( (int) $value );
                if ( $url ) {
                    return $url;
                }
            }
            if ( is_array( $value ) && ! empty( $value['url'] ) ) {
                return (string) $value['url'];
            }
            return (string) $value;
        };
        ?>
        <section class="module module-certificate-honors" id="<?php echo esc_attr( $module_id ); ?>" style="<?php echo esc_attr( $section_style ); ?>" data-cols="<?php echo esc_attr( $columns ); ?>">
            <div class="container">
                <div class="section-header text-center">
                    <?php if ( $title ) : ?>
                        <h2 class="section-title"><?php echo esc_html( $title ); ?></h2>
                    <?php endif; ?>
                    <?php if ( $subtitle ) : ?>
                        <p class="section-subtitle"><?php echo esc_html( $subtitle ); ?></p>
                    <?php endif; ?>
                </div>

                <?php if ( $enable_filter === 'yes' && count( $categories ) > 1 ) : ?>
                    <div class="ch-filter" data-target="<?php echo esc_attr( $module_id ); ?>">
                        <button type="button" class="ch-filter-btn is-active" data-cat="all"><?php esc_html_e( '全部', 'developer-starter' ); ?></button>
                        <?php foreach ( $categories as $cat ) : ?>
                            <button type="button" class="ch-filter-btn" data-cat="<?php echo esc_attr( sanitize_title( $cat ) ); ?>"><?php echo esc_html( $cat ); ?></button>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <div class="ch-grid">
                    <?php foreach ( $items as $index => $item ) : ?>
                        <?php
                        $item_title   = isset( $item['title'] ) ? $item['title'] : '';
                        $item_cat     = isset( $item['category'] ) ? trim( (string) $item['category'] ) : '';
                        $item_badge   = isset( $item['badge'] ) ? trim( (string) $item['badge'] ) : '';
                        $cert_no      = isset( $item['cert_no'] ) ? $item['cert_no'] : '';
                        $issuer       = isset( $item['issuer'] ) ? $item['issuer'] : '';
                        $issue_date   = isset( $item['issue_date'] ) ? $item['issue_date'] : '';
                        $expiry_date  = isset( $item['expiry_date'] ) ? $item['expiry_date'] : '';
                        $file_url     = isset( $item['file_url'] ) ? $item['file_url'] : '';
                        $image        = $get_media_url( isset( $item['image'] ) ? $item['image'] : '' );
                        $cat_slug     = $item_cat !== '' ? sanitize_title( $item_cat ) : 'uncategorized';
                        $anim_attr    = $this->get_staggered_animation_attr( $index, 80, 80 );
                        ?>
                        <article class="ch-card" data-cat="<?php echo esc_attr( $cat_slug ); ?>" <?php echo $anim_attr; ?>>
                            <?php if ( $image ) : ?>
                                <div class="ch-media">
                                    <img src="<?php echo esc_url( $image ); ?>" alt="<?php echo esc_attr( $item_title ); ?>" loading="lazy" />
                                    <?php if ( $item_badge ) : ?>
                                        <span class="ch-badge"><?php echo esc_html( $item_badge ); ?></span>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>

                            <div class="ch-body">
                                <?php if ( $item_cat ) : ?>
                                    <div class="ch-cat"><?php echo esc_html( $item_cat ); ?></div>
                                <?php endif; ?>
                                <?php if ( $item_title ) : ?>
                                    <h3 class="ch-title"><?php echo esc_html( $item_title ); ?></h3>
                                <?php endif; ?>

                                <div class="ch-meta">
                                    <?php if ( $cert_no ) : ?><div><strong><?php esc_html_e( '编号', 'developer-starter' ); ?>：</strong><?php echo esc_html( $cert_no ); ?></div><?php endif; ?>
                                    <?php if ( $issuer ) : ?><div><strong><?php esc_html_e( '机构', 'developer-starter' ); ?>：</strong><?php echo esc_html( $issuer ); ?></div><?php endif; ?>
                                    <?php if ( $issue_date ) : ?><div><strong><?php esc_html_e( '发证', 'developer-starter' ); ?>：</strong><?php echo esc_html( $issue_date ); ?></div><?php endif; ?>
                                    <?php if ( $expiry_date ) : ?><div><strong><?php esc_html_e( '有效期', 'developer-starter' ); ?>：</strong><?php echo esc_html( $expiry_date ); ?></div><?php endif; ?>
                                </div>

                                <?php if ( $file_url ) : ?>
                                    <a href="<?php echo esc_url( $file_url ); ?>" class="ch-link" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $link_text ); ?></a>
                                <?php endif; ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>

            <script>
            (function () {
                var root = document.getElementById('<?php echo esc_js( $module_id ); ?>');
                if (!root) return;
                var filterWrap = root.querySelector('.ch-filter');
                if (!filterWrap) return;

                filterWrap.addEventListener('click', function (e) {
                    var btn = e.target.closest('.ch-filter-btn');
                    if (!btn) return;

                    var cat = btn.getAttribute('data-cat') || 'all';
                    var cards = root.querySelectorAll('.ch-card');
                    var btns = filterWrap.querySelectorAll('.ch-filter-btn');

                    btns.forEach(function (b) { b.classList.remove('is-active'); });
                    btn.classList.add('is-active');

                    cards.forEach(function (card) {
                        var hit = (cat === 'all') || (card.getAttribute('data-cat') === cat);
                        card.style.display = hit ? '' : 'none';
                    });
                });
            })();
            </script>
        </section>
        <?php
    }
}
