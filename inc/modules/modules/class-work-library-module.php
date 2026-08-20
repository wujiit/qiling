<?php
/**
 * Work Library Module - 作品库展示模块
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Modules\Modules;

use Developer_Starter\Modules\Module_Base;
use Developer_Starter\Modules\Module_Manager;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Work_Library_Module extends Module_Base {

    public function __construct() {
        $this->category    = 'content';
        $this->icon        = 'dashicons-portfolio';
        $this->description = __( '读取启灵作品库插件数据，支持响应式网格、分页和详情跳转。', 'developer-starter' );
    }

    public function get_id() {
        return 'work_library';
    }

    public function get_name() {
        return __( '作品库展示', 'developer-starter' );
    }

    public function get_fields() {
        return array(
            array(
                'id'      => 'wl_title',
                'type'    => 'text',
                'label'   => __( '模块标题', 'developer-starter' ),
                'default' => __( '作品库(插件)', 'developer-starter' ),
            ),
            array(
                'id'      => 'wl_subtitle',
                'type'    => 'text',
                'label'   => __( '模块副标题', 'developer-starter' ),
                'default' => __( '最新发布的精选作品', 'developer-starter' ),
            ),
            array(
                'id'      => 'wl_item_type',
                'type'    => 'select',
                'label'   => __( '作品类型筛选', 'developer-starter' ),
                'options' => array(
                    'all'     => __( '全部类型', 'developer-starter' ),
                    'work'    => __( '通用作品', 'developer-starter' ),
                    'photo'   => __( '摄影', 'developer-starter' ),
                    'music'   => __( '音乐', 'developer-starter' ),
                    'book'    => __( '书籍 / 出版物', 'developer-starter' ),
                    'product' => __( '产品', 'developer-starter' ),
                ),
                'default' => 'all',
            ),
            array(
                'id'      => 'wl_taxonomy',
                'type'    => 'text',
                'label'   => __( '分类类型 taxonomy（可选）', 'developer-starter' ),
                'default' => '',
                'desc'    => __( '例如 style / series / medium / genre', 'developer-starter' ),
            ),
            array(
                'id'      => 'wl_term_ids',
                'type'    => 'text',
                'label'   => __( '分类 ID（逗号分隔，可选）', 'developer-starter' ),
                'default' => '',
                'desc'    => __( '只显示这些分类下的作品。留空表示不按分类过滤。', 'developer-starter' ),
            ),
            array(
                'id'      => 'wl_per_page',
                'type'    => 'number',
                'label'   => __( '每页数量', 'developer-starter' ),
                'default' => 12,
            ),
            array(
                'id'      => 'wl_columns',
                'type'    => 'select',
                'label'   => __( '桌面端列数', 'developer-starter' ),
                'options' => array(
                    '2' => '2',
                    '3' => '3',
                    '4' => '4',
                    '5' => '5',
                ),
                'default' => '3',
            ),
            array(
                'id'      => 'wl_cover_ratio',
                'type'    => 'select',
                'label'   => __( '封面比例', 'developer-starter' ),
                'options' => array(
                    '1:1'  => '1:1',
                    '4:3'  => '4:3',
                    '3:4'  => '3:4',
                    '16:9' => '16:9',
                ),
                'default' => '4:3',
            ),
            array(
                'id'      => 'wl_show_excerpt',
                'type'    => 'select',
                'label'   => __( '显示简介', 'developer-starter' ),
                'options' => array(
                    '1' => __( '是', 'developer-starter' ),
                    '0' => __( '否', 'developer-starter' ),
                ),
                'default' => '1',
            ),
            array(
                'id'      => 'wl_show_price',
                'type'    => 'select',
                'label'   => __( '显示价格', 'developer-starter' ),
                'options' => array(
                    '1' => __( '是', 'developer-starter' ),
                    '0' => __( '否', 'developer-starter' ),
                ),
                'default' => '1',
            ),
            array(
                'id'      => 'wl_show_type',
                'type'    => 'select',
                'label'   => __( '显示类型标签', 'developer-starter' ),
                'options' => array(
                    '1' => __( '是', 'developer-starter' ),
                    '0' => __( '否', 'developer-starter' ),
                ),
                'default' => '1',
            ),
            array(
                'id'      => 'wl_show_terms',
                'type'    => 'select',
                'label'   => __( '显示分类标签', 'developer-starter' ),
                'options' => array(
                    '1' => __( '是', 'developer-starter' ),
                    '0' => __( '否', 'developer-starter' ),
                ),
                'default' => '1',
            ),
            array(
                'id'      => 'wl_show_pager',
                'type'    => 'select',
                'label'   => __( '显示分页', 'developer-starter' ),
                'options' => array(
                    '1' => __( '是', 'developer-starter' ),
                    '0' => __( '否', 'developer-starter' ),
                ),
                'default' => '1',
            ),
            array(
                'id'      => 'wl_page_query_key',
                'type'    => 'text',
                'label'   => __( '分页参数名', 'developer-starter' ),
                'default' => 'qw_page',
                'desc'    => __( '同一页面有多个作品库模块时，请给每个模块设不同参数名。', 'developer-starter' ),
            ),
            array(
                'id'      => 'wl_detail_page_url',
                'type'    => 'text',
                'label'   => __( '详情页 URL', 'developer-starter' ),
                'default' => '',
                'desc'    => __( '留空则跳当前页面。建议填写独立详情页地址。', 'developer-starter' ),
            ),
            array(
                'id'      => 'wl_bg_color',
                'type'    => 'color',
                'label'   => __( '背景颜色', 'developer-starter' ),
                'default' => 'var(--color-neutral-50)',
            ),
            array(
                'id'      => 'wl_card_bg',
                'type'    => 'color',
                'label'   => __( '卡片背景', 'developer-starter' ),
                'default' => 'var(--color-neutral-0)',
            ),
            array(
                'id'      => 'wl_badge_bg',
                'type'    => 'color',
                'label'   => __( '标签/徽章背景颜色', 'developer-starter' ),
                'default' => '',
                'desc'    => __( '控制作品类型和分类标签，留空时跟随页面预设/全局徽章颜色。', 'developer-starter' ),
            ),
            array(
                'id'      => 'wl_padding_top',
                'type'    => 'text',
                'label'   => __( '顶部间距', 'developer-starter' ),
                'default' => '64px',
            ),
            array(
                'id'      => 'wl_padding_bottom',
                'type'    => 'text',
                'label'   => __( '底部间距', 'developer-starter' ),
                'default' => '64px',
            ),
        );
    }

    public function render( $data = array() ) {
        if ( ! class_exists( 'QilingWork_Repository' ) || ! class_exists( 'QilingWork_DB' ) ) {
            $this->render_missing_plugin_notice();
            return;
        }

        $title       = isset( $data['wl_title'] ) ? sanitize_text_field( (string) $data['wl_title'] ) : __( '作品库', 'developer-starter' );
        $subtitle    = isset( $data['wl_subtitle'] ) ? sanitize_text_field( (string) $data['wl_subtitle'] ) : '';
        $item_type   = isset( $data['wl_item_type'] ) ? sanitize_key( (string) $data['wl_item_type'] ) : 'all';
        $taxonomy    = isset( $data['wl_taxonomy'] ) ? sanitize_key( (string) $data['wl_taxonomy'] ) : '';
        $term_ids    = isset( $data['wl_term_ids'] ) ? $this->parse_term_ids( (string) $data['wl_term_ids'] ) : array();
        $per_page    = isset( $data['wl_per_page'] ) ? max( 1, min( 48, (int) $data['wl_per_page'] ) ) : 12;
        $columns     = isset( $data['wl_columns'] ) ? max( 2, min( 5, (int) $data['wl_columns'] ) ) : 3;
        $cover_ratio = isset( $data['wl_cover_ratio'] ) ? (string) $data['wl_cover_ratio'] : '4:3';

        $show_excerpt = isset( $data['wl_show_excerpt'] ) ? '1' === (string) $data['wl_show_excerpt'] : true;
        $show_price   = isset( $data['wl_show_price'] ) ? '1' === (string) $data['wl_show_price'] : true;
        $show_type    = isset( $data['wl_show_type'] ) ? '1' === (string) $data['wl_show_type'] : true;
        $show_terms   = isset( $data['wl_show_terms'] ) ? '1' === (string) $data['wl_show_terms'] : true;
        $show_pager   = isset( $data['wl_show_pager'] ) ? '1' === (string) $data['wl_show_pager'] : true;

        $page_key = isset( $data['wl_page_query_key'] ) ? sanitize_key( (string) $data['wl_page_query_key'] ) : 'qw_page';
        if ( '' === $page_key ) {
            $page_key = 'qw_page';
        }

        $detail_page_url = isset( $data['wl_detail_page_url'] ) ? esc_url_raw( (string) $data['wl_detail_page_url'] ) : '';
        if ( '' === $detail_page_url ) {
            $detail_page_url = $this->current_page_url();
        }

        $bg_color       = isset( $data['wl_bg_color'] ) ? sanitize_text_field( (string) $data['wl_bg_color'] ) : 'var(--color-neutral-50)';
        $card_bg        = isset( $data['wl_card_bg'] ) ? sanitize_text_field( (string) $data['wl_card_bg'] ) : 'var(--color-neutral-0)';
        $badge_bg       = isset( $data['wl_badge_bg'] ) ? sanitize_text_field( (string) $data['wl_badge_bg'] ) : '';
        $padding_top    = isset( $data['wl_padding_top'] ) ? Module_Manager::sanitize_spacing_value( $data['wl_padding_top'] ) : '64px';
        $padding_bottom = isset( $data['wl_padding_bottom'] ) ? Module_Manager::sanitize_spacing_value( $data['wl_padding_bottom'] ) : '64px';

        if ( '' === $padding_top ) {
            $padding_top = '64px';
        }
        if ( '' === $padding_bottom ) {
            $padding_bottom = '64px';
        }

        $current_page = isset( $_GET[ $page_key ] ) ? max( 1, absint( wp_unslash( $_GET[ $page_key ] ) ) ) : 1; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if ( 'all' === $item_type ) {
            $item_type = '';
        }

        $query = $this->query_items(
            array(
                'page'      => $current_page,
                'per_page'  => $per_page,
                'item_type' => $item_type,
                'taxonomy'  => $taxonomy,
                'term_ids'  => $term_ids,
            )
        );

        $items = isset( $query['rows'] ) && is_array( $query['rows'] ) ? $query['rows'] : array();
        $meta  = isset( $query['meta'] ) && is_array( $query['meta'] ) ? $query['meta'] : array(
            'page'  => 1,
            'pages' => 1,
            'total' => 0,
        );

        $item_ids   = array_map( 'intval', wp_list_pluck( $items, 'id' ) );
        $terms_map  = $show_terms ? $this->get_terms_map_by_item_ids( $item_ids ) : array();
        $ratio_pct  = $this->cover_ratio_to_padding_percent( $cover_ratio );
        $tablet_col = max( 2, min( 3, $columns ) );
        $module_id  = 'work-library-' . uniqid();
        $pager_base = remove_query_arg(
            array( $page_key, 'qilingwork_item', 'qilingwork_item_type' ),
            $this->current_page_url()
        );
        $section_style = $this->build_section_style( $bg_color, $card_bg, $padding_top, $padding_bottom, $columns, $tablet_col, $ratio_pct );
        if ( '' !== $badge_bg && ! preg_match( '/[;{}<>]/', $badge_bg ) ) {
            $section_style .= '--qiling-component-badge-bg:' . $badge_bg . ';';
        }
        ?>
        <section class="module module-work-library" id="<?php echo esc_attr( $module_id ); ?>" style="<?php echo esc_attr( $section_style ); ?>">
            <div class="container">
                <?php if ( '' !== $title || '' !== $subtitle ) : ?>
                    <header class="qw-lib-head">
                        <?php if ( '' !== $title ) : ?>
                            <h2 class="qw-lib-title"><?php echo esc_html( $title ); ?></h2>
                        <?php endif; ?>
                        <?php if ( '' !== $subtitle ) : ?>
                            <p class="qw-lib-subtitle"><?php echo esc_html( $subtitle ); ?></p>
                        <?php endif; ?>
                    </header>
                <?php endif; ?>

                <?php if ( empty( $items ) ) : ?>
                    <div class="qw-lib-empty"><?php echo esc_html__( '暂无作品，请先在启灵作品库插件里发布内容。', 'developer-starter' ); ?></div>
                <?php else : ?>
                    <div class="qw-lib-grid">
                        <?php foreach ( $items as $item ) : ?>
                            <?php
                            $item_slug = isset( $item['slug'] ) ? sanitize_title( (string) $item['slug'] ) : '';
                            $item_type_key = isset( $item['item_type'] ) ? sanitize_key( (string) $item['item_type'] ) : '';
                            $item_url = add_query_arg(
                                array(
                                    'qilingwork_item'      => $item_slug,
                                    'qilingwork_item_type' => $item_type_key,
                                ),
                                $detail_page_url
                            );
                            $item_terms = isset( $terms_map[ (int) $item['id'] ] ) && is_array( $terms_map[ (int) $item['id'] ] ) ? $terms_map[ (int) $item['id'] ] : array();
                            ?>
                            <article class="qw-lib-card">
                                <a class="qw-lib-card-link" href="<?php echo esc_url( $item_url ); ?>">
                                    <div class="qw-lib-cover">
                                        <?php if ( ! empty( $item['cover_url'] ) ) : ?>
                                            <img src="<?php echo esc_url( (string) $item['cover_url'] ); ?>" alt="<?php echo esc_attr( (string) $item['title'] ); ?>" loading="lazy" />
                                        <?php else : ?>
                                            <span class="qw-lib-cover-empty"><?php echo esc_html__( '暂无封面', 'developer-starter' ); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="qw-lib-card-body">
                                        <?php if ( $show_type && '' !== $item_type_key ) : ?>
                                            <span class="qw-lib-type"><?php echo esc_html( $item_type_key ); ?></span>
                                        <?php endif; ?>
                                        <h3 class="qw-lib-item-title"><?php echo esc_html( (string) $item['title'] ); ?></h3>
                                        <?php if ( $show_excerpt && ! empty( $item['excerpt'] ) ) : ?>
                                            <p class="qw-lib-excerpt"><?php echo esc_html( (string) $item['excerpt'] ); ?></p>
                                        <?php endif; ?>
                                        <div class="qw-lib-meta">
                                            <?php if ( ! empty( $item['published_at'] ) ) : ?>
                                                <span><?php echo esc_html( function_exists( 'developer_starter_format_date_value' ) ? developer_starter_format_date_value( (string) $item['published_at'] ) : mysql2date( 'Y-m-d', (string) $item['published_at'], true ) ); ?></span>
                                            <?php endif; ?>
                                            <?php if ( $show_price && '' !== (string) $item['price'] ) : ?>
                                                <span class="qw-lib-price"><?php echo esc_html( $this->format_price( $item['price'], $item['currency'] ) ); ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <?php if ( $show_terms && ! empty( $item_terms ) ) : ?>
                                            <div class="qw-lib-terms">
                                                <?php foreach ( $item_terms as $term_group ) : ?>
                                                    <?php foreach ( $term_group as $term_name ) : ?>
                                                        <span class="qw-lib-term"><?php echo esc_html( (string) $term_name ); ?></span>
                                                    <?php endforeach; ?>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </a>
                            </article>
                        <?php endforeach; ?>
                    </div>

                    <?php if ( $show_pager && isset( $meta['pages'] ) && (int) $meta['pages'] > 1 ) : ?>
                        <nav class="qw-lib-pager" aria-label="<?php echo esc_attr__( '作品分页', 'developer-starter' ); ?>">
                            <?php for ( $i = 1; $i <= (int) $meta['pages']; $i++ ) : ?>
                                <?php $url = add_query_arg( array( $page_key => $i ), $pager_base ); ?>
                                <a href="<?php echo esc_url( $url ); ?>" class="<?php echo esc_attr( (int) $meta['page'] === $i ? 'is-active' : '' ); ?>">
                                    <?php echo esc_html( (string) $i ); ?>
                                </a>
                            <?php endfor; ?>
                        </nav>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </section>
        <?php
    }

    /**
     * Build root section style variables.
     *
     * @param string $bg_color Background color.
     * @param string $card_bg Card background.
     * @param string $padding_top Top padding.
     * @param string $padding_bottom Bottom padding.
     * @param int    $columns Desktop column count.
     * @param int    $tablet_col Tablet column count.
     * @param string $ratio_pct Cover ratio.
     * @return string
     */
    private function build_section_style( $bg_color, $card_bg, $padding_top, $padding_bottom, $columns, $tablet_col, $ratio_pct ) {
        return sprintf(
            '--qw-lib-bg:%1$s;--qw-lib-card-bg:%2$s;--qw-lib-padding-top:%3$s;--qw-lib-padding-bottom:%4$s;--qw-lib-grid-template:repeat(%5$d, minmax(0, 1fr));--qw-lib-grid-template-tablet:repeat(%6$d, minmax(0, 1fr));--qw-lib-cover-ratio:%7$s;',
            (string) $bg_color,
            (string) $card_bg,
            (string) $padding_top,
            (string) $padding_bottom,
            (int) $columns,
            (int) $tablet_col,
            (string) $ratio_pct . '%'
        );
    }

    /**
     * Normalize SQL identifiers returned by the work library plugin.
     *
     * @param string $identifier Table or column identifier.
     * @return string
     */
    private function normalize_sql_identifier( $identifier ) {
        $identifier = trim( (string) $identifier, "` \t\n\r\0\x0B" );
        return preg_match( '/\A[A-Za-z0-9_]+\z/', $identifier ) ? $identifier : '';
    }

    /**
     * Quote a whitelisted SQL identifier.
     *
     * @param string $identifier Table or column identifier.
     * @return string
     */
    private function quote_sql_identifier( $identifier ) {
        $identifier = $this->normalize_sql_identifier( $identifier );
        return '' !== $identifier ? '`' . $identifier . '`' : '';
    }

    /**
     * Query published items with optional taxonomy filters.
     *
     * @param array<string,mixed> $args Query args.
     * @return array<string,mixed>
     */
    private function query_items( $args ) {
        global $wpdb;

        $tables = \QilingWork_DB::tables();
        $items_table_sql = isset( $tables['items'] ) ? $this->quote_sql_identifier( $tables['items'] ) : '';
        if ( '' === $items_table_sql ) {
            return array(
                'rows' => array(),
                'meta' => array( 'page' => 1, 'pages' => 1, 'total' => 0 ),
            );
        }

        $page      = isset( $args['page'] ) ? max( 1, (int) $args['page'] ) : 1;
        $per_page  = isset( $args['per_page'] ) ? max( 1, min( 48, (int) $args['per_page'] ) ) : 12;
        $offset    = ( $page - 1 ) * $per_page;
        $item_type = isset( $args['item_type'] ) ? sanitize_key( (string) $args['item_type'] ) : '';
        $taxonomy  = isset( $args['taxonomy'] ) ? sanitize_key( (string) $args['taxonomy'] ) : '';
        $term_ids  = isset( $args['term_ids'] ) && is_array( $args['term_ids'] ) ? array_values( array_filter( array_map( 'absint', $args['term_ids'] ) ) ) : array();

        $where = array( $wpdb->prepare( 'i.status = %s', 'published' ) );

        if ( '' !== $item_type ) {
            $where[] = $wpdb->prepare( 'i.item_type = %s', $item_type );
        }

        if ( ! empty( $term_ids ) && ! empty( $tables['term_rel'] ) && ! empty( $tables['terms'] ) ) {
            $term_rel_table_sql = $this->quote_sql_identifier( $tables['term_rel'] );
            $terms_table_sql    = $this->quote_sql_identifier( $tables['terms'] );
            if ( '' !== $term_rel_table_sql && '' !== $terms_table_sql ) {
                $term_placeholders = implode( ',', array_fill( 0, count( $term_ids ), '%d' ) );
                $exists_args       = array();
                $exists_sql        = "EXISTS (
                SELECT 1
                FROM {$term_rel_table_sql} tr
                INNER JOIN {$terms_table_sql} t ON t.id = tr.term_id
                WHERE tr.item_id = i.id";

                if ( '' !== $taxonomy ) {
                    $exists_sql   .= ' AND t.taxonomy = %s';
                    $exists_args[] = $taxonomy;
                }

                $exists_sql .= " AND t.id IN ({$term_placeholders}))";
                $exists_args = array_merge( $exists_args, $term_ids );

                $where[] = $wpdb->prepare( $exists_sql, $exists_args );
            }
        }

        $where_sql = implode( ' AND ', $where );
        $total     = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$items_table_sql} i WHERE {$where_sql}" );
        $pages     = max( 1, (int) ceil( $total / $per_page ) );
        if ( $page > $pages ) {
            $page   = $pages;
            $offset = ( $page - 1 ) * $per_page;
        }
        $rows_sql  = $wpdb->prepare(
            "SELECT i.id, i.item_type, i.title, i.slug, i.excerpt, i.cover_url, i.price, i.currency, i.published_at
             FROM {$items_table_sql} i
             WHERE {$where_sql}
             ORDER BY i.sort_order ASC, i.id DESC
             LIMIT %d OFFSET %d",
            $per_page,
            $offset
        );
        $rows      = $wpdb->get_results( $rows_sql, ARRAY_A );

        return array(
            'rows' => is_array( $rows ) ? $rows : array(),
            'meta' => array(
                'page'     => min( $page, $pages ),
                'per_page' => $per_page,
                'total'    => $total,
                'pages'    => $pages,
            ),
        );
    }

    /**
     * Batch query term names by item ids.
     *
     * @param int[] $item_ids Item IDs.
     * @return array<int,array<string,array<int,string>>>
     */
    private function get_terms_map_by_item_ids( $item_ids ) {
        global $wpdb;

        $item_ids = array_values( array_filter( array_map( 'absint', $item_ids ) ) );
        if ( empty( $item_ids ) || ! class_exists( 'QilingWork_DB' ) ) {
            return array();
        }

        $tables = \QilingWork_DB::tables();
        $term_rel_table_sql = isset( $tables['term_rel'] ) ? $this->quote_sql_identifier( $tables['term_rel'] ) : '';
        $terms_table_sql    = isset( $tables['terms'] ) ? $this->quote_sql_identifier( $tables['terms'] ) : '';
        if ( '' === $term_rel_table_sql || '' === $terms_table_sql ) {
            return array();
        }

        $item_placeholders = implode( ',', array_fill( 0, count( $item_ids ), '%d' ) );
        $sql = $wpdb->prepare(
            "SELECT tr.item_id, t.taxonomy, t.name
            FROM {$term_rel_table_sql} tr
            INNER JOIN {$terms_table_sql} t ON t.id = tr.term_id
            WHERE tr.item_id IN ({$item_placeholders})
            ORDER BY tr.item_id ASC, t.taxonomy ASC, t.sort_order ASC, t.name ASC",
            $item_ids
        );

        $rows = $wpdb->get_results( $sql, ARRAY_A );
        $map  = array();

        if ( is_array( $rows ) ) {
            foreach ( $rows as $row ) {
                $item_id = (int) $row['item_id'];
                $tax     = isset( $row['taxonomy'] ) ? (string) $row['taxonomy'] : '';
                $name    = isset( $row['name'] ) ? (string) $row['name'] : '';

                if ( $item_id <= 0 || '' === $tax || '' === $name ) {
                    continue;
                }

                if ( ! isset( $map[ $item_id ] ) ) {
                    $map[ $item_id ] = array();
                }
                if ( ! isset( $map[ $item_id ][ $tax ] ) ) {
                    $map[ $item_id ][ $tax ] = array();
                }

                if ( ! in_array( $name, $map[ $item_id ][ $tax ], true ) ) {
                    $map[ $item_id ][ $tax ][] = $name;
                }
            }
        }

        return $map;
    }

    /**
     * Parse term IDs.
     *
     * @param string $raw Raw csv.
     * @return int[]
     */
    private function parse_term_ids( $raw ) {
        $parts = array_filter( array_map( 'trim', explode( ',', $raw ) ) );
        $ids   = array();

        foreach ( $parts as $part ) {
            $id = absint( $part );
            if ( $id > 0 ) {
                $ids[] = $id;
            }
        }

        return array_values( array_unique( $ids ) );
    }

    /**
     * Ratio to padding-top percent.
     *
     * @param string $ratio Ratio string.
     * @return string
     */
    private function cover_ratio_to_padding_percent( $ratio ) {
        switch ( $ratio ) {
            case '1:1':
                return '100';
            case '3:4':
                return '133.33';
            case '16:9':
                return '56.25';
            case '4:3':
            default:
                return '75';
        }
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
     * Get current page url.
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
     * Render admin-only missing plugin notice.
     *
     * @return void
     */
    private function render_missing_plugin_notice() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        ?>
        <div class="notice notice-warning qiling-module-admin-notice">
            <?php echo esc_html__( '作品库展示模块需要先启用「启灵作品库（qilingwork）」插件。', 'developer-starter' ); ?>
        </div>
        <?php
    }
}
