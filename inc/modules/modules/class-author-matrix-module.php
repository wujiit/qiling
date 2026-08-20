<?php
/**
 * Author Matrix Module - 作者矩阵/专栏页
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Modules\Modules;

use Developer_Starter\Modules\Module_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Author_Matrix_Module extends Module_Base {

    public function __construct() {
        $this->category = 'content';
        $this->icon = 'dashicons-admin-users';
        $this->description = __( '按作者聚合与排名展示专栏作者', 'developer-starter' );
    }

    public function get_id() {
        return 'author_matrix';
    }

    public function get_name() {
        return __( '作者矩阵/专栏页', 'developer-starter' );
    }

    public function get_fields() {
        return array(
            array(
                'id'      => 'am_title',
                'type'    => 'text',
                'label'   => __( '模块标题', 'developer-starter' ),
                'default' => __( '作者矩阵', 'developer-starter' ),
            ),
            array(
                'id'      => 'am_subtitle',
                'type'    => 'text',
                'label'   => __( '模块副标题', 'developer-starter' ),
                'default' => __( '精选专栏作者与内容创作者', 'developer-starter' ),
            ),
            array(
                'id'      => 'am_source_mode',
                'type'    => 'select',
                'label'   => __( '来源模式', 'developer-starter' ),
                'options' => array(
                    'auto' => __( '自动聚合', 'developer-starter' ),
                    'manual' => __( '手动作者ID', 'developer-starter' ),
                ),
                'default' => 'auto',
            ),
            array(
                'id'    => 'am_user_ids',
                'type'  => 'text',
                'label' => __( '作者ID (逗号分隔，手动模式)', 'developer-starter' ),
            ),
            array(
                'id'    => 'am_roles',
                'type'  => 'text',
                'label' => __( '角色白名单 (逗号分隔，可选)', 'developer-starter' ),
            ),
            array(
                'id'      => 'am_limit',
                'type'    => 'number',
                'label'   => __( '显示数量', 'developer-starter' ),
                'default' => '8',
            ),
            array(
                'id'      => 'am_columns',
                'type'    => 'select',
                'label'   => __( '每行列数', 'developer-starter' ),
                'options' => array(
                    '2' => __( '2列', 'developer-starter' ),
                    '3' => __( '3列', 'developer-starter' ),
                    '4' => __( '4列', 'developer-starter' ),
                ),
                'default' => '4',
            ),
            array(
                'id'      => 'am_rank_by',
                'type'    => 'select',
                'label'   => __( '排名依据', 'developer-starter' ),
                'options' => array(
                    'posts' => __( '发文量', 'developer-starter' ),
                    'views' => __( '总阅读量', 'developer-starter' ),
                    'comments' => __( '总评论量', 'developer-starter' ),
                    'latest' => __( '最近活跃', 'developer-starter' ),
                ),
                'default' => 'posts',
            ),
            array(
                'id'      => 'am_order',
                'type'    => 'select',
                'label'   => __( '排序方向', 'developer-starter' ),
                'options' => array(
                    'DESC' => __( '降序', 'developer-starter' ),
                    'ASC' => __( '升序', 'developer-starter' ),
                ),
                'default' => 'DESC',
            ),
            array(
                'id'      => 'am_min_posts',
                'type'    => 'number',
                'label'   => __( '最小发文数过滤', 'developer-starter' ),
                'default' => '1',
            ),
            array(
                'id'      => 'am_show_bio',
                'type'    => 'select',
                'label'   => __( '显示作者简介', 'developer-starter' ),
                'options' => array(
                    'yes' => __( '显示', 'developer-starter' ),
                    'no' => __( '隐藏', 'developer-starter' ),
                ),
                'default' => 'yes',
            ),
            array(
                'id'      => 'am_show_stats',
                'type'    => 'select',
                'label'   => __( '显示统计信息', 'developer-starter' ),
                'options' => array(
                    'yes' => __( '显示', 'developer-starter' ),
                    'no' => __( '隐藏', 'developer-starter' ),
                ),
                'default' => 'yes',
            ),
            array(
                'id'      => 'am_show_latest',
                'type'    => 'select',
                'label'   => __( '显示最近活跃时间', 'developer-starter' ),
                'options' => array(
                    'yes' => __( '显示', 'developer-starter' ),
                    'no' => __( '隐藏', 'developer-starter' ),
                ),
                'default' => 'yes',
            ),
            array(
                'id'      => 'am_button_text',
                'type'    => 'text',
                'label'   => __( '按钮文案', 'developer-starter' ),
                'default' => __( '查看专栏', 'developer-starter' ),
            ),
            array(
                'id'      => 'am_button_bg_color',
                'type'    => 'color',
                'label'   => __( '按钮背景颜色', 'developer-starter' ),
                'default' => '',
                'description' => __( '留空时跟随全局设计里的按钮样式', 'developer-starter' ),
            ),
            array(
                'id'      => 'am_button_text_color',
                'type'    => 'color',
                'label'   => __( '按钮文字颜色', 'developer-starter' ),
                'default' => '',
                'description' => __( '留空时跟随全局设计里的按钮样式', 'developer-starter' ),
            ),
            $this->get_button_border_color_field( 'am_button_border_color' ),
            array(
                'id'      => 'am_button_hover_bg_color',
                'type'    => 'color',
                'label'   => __( '按钮悬停背景颜色', 'developer-starter' ),
                'default' => '',
                'description' => __( '留空时跟随全局设计里的按钮悬停样式', 'developer-starter' ),
            ),
            array(
                'id'      => 'am_button_hover_text_color',
                'type'    => 'color',
                'label'   => __( '按钮悬停文字颜色', 'developer-starter' ),
                'default' => '',
                'description' => __( '留空时跟随全局设计里的按钮悬停样式', 'developer-starter' ),
            ),
            $this->get_button_border_color_field( 'am_button_hover_border_color', __( '按钮悬停边框颜色', 'developer-starter' ), __( '留空时跟随按钮悬停背景色。', 'developer-starter' ) ),
            array(
                'id'      => 'am_card_bg',
                'type'    => 'color',
                'label'   => __( '卡片背景色', 'developer-starter' ),
                'default' => 'var(--color-neutral-0)',
            ),
            array(
                'id'          => 'am_badge_bg',
                'type'        => 'color',
                'label'       => __( '标签/徽章背景颜色', 'developer-starter' ),
                'default'     => '',
                'description' => __( '控制排名徽章背景，留空时跟随页面预设风格或全局徽章颜色。', 'developer-starter' ),
            ),
            array(
                'id'      => 'am_bg_color',
                'type'    => 'color',
                'label'   => __( '模块背景色', 'developer-starter' ),
                'default' => 'var(--color-neutral-50)',
            ),
            array(
                'id'      => 'module_padding_top',
                'type'    => 'text',
                'label'   => __( '上边距', 'developer-starter' ),
                'default' => '60px',
            ),
            array(
                'id'      => 'module_padding_bottom',
                'type'    => 'text',
                'label'   => __( '下边距', 'developer-starter' ),
                'default' => '60px',
            ),
        );
    }

    public function render( $data = array() ) {
        $clean_css_value = static function( $value ) {
            $value = trim( wp_strip_all_tags( (string) $value ) );
            return str_replace( array( ';', '{', '}' ), '', $value );
        };
        $title = isset( $data['am_title'] ) && $data['am_title'] !== '' ? $data['am_title'] : __( '作者矩阵', 'developer-starter' );
        $subtitle = isset( $data['am_subtitle'] ) ? $data['am_subtitle'] : '';
        $columns = isset( $data['am_columns'] ) ? max( 2, min( 4, intval( $data['am_columns'] ) ) ) : 4;
        $show_bio = ! isset( $data['am_show_bio'] ) || $data['am_show_bio'] === 'yes';
        $show_stats = ! isset( $data['am_show_stats'] ) || $data['am_show_stats'] === 'yes';
        $show_latest = ! isset( $data['am_show_latest'] ) || $data['am_show_latest'] === 'yes';
        $button_text = isset( $data['am_button_text'] ) && $data['am_button_text'] !== '' ? $data['am_button_text'] : __( '查看专栏', 'developer-starter' );
        $button_bg_color = isset( $data['am_button_bg_color'] ) ? $clean_css_value( $data['am_button_bg_color'] ) : '';
        $button_text_color = isset( $data['am_button_text_color'] ) ? $clean_css_value( $data['am_button_text_color'] ) : '';
        $button_border_color = isset( $data['am_button_border_color'] ) ? $clean_css_value( $data['am_button_border_color'] ) : '';
        $button_hover_bg_color = isset( $data['am_button_hover_bg_color'] ) ? $clean_css_value( $data['am_button_hover_bg_color'] ) : '';
        $button_hover_text_color = isset( $data['am_button_hover_text_color'] ) ? $clean_css_value( $data['am_button_hover_text_color'] ) : '';
        $button_hover_border_color = isset( $data['am_button_hover_border_color'] ) ? $clean_css_value( $data['am_button_hover_border_color'] ) : '';
        $comments_feature_enabled = function_exists( '\developer_starter_comments_feature_enabled' ) ? \developer_starter_comments_feature_enabled() : true;

        $card_bg = isset( $data['am_card_bg'] ) ? $data['am_card_bg'] : 'var(--color-neutral-0)';
        $badge_bg = isset( $data['am_badge_bg'] ) ? $clean_css_value( $data['am_badge_bg'] ) : '';
        $bg_color = isset( $data['am_bg_color'] ) ? $data['am_bg_color'] : 'var(--color-neutral-50)';
        $pt = isset( $data['module_padding_top'] ) && $data['module_padding_top'] !== '' ? $data['module_padding_top'] : '60px';
        $pb = isset( $data['module_padding_bottom'] ) && $data['module_padding_bottom'] !== '' ? $data['module_padding_bottom'] : '60px';

        $authors = $this->get_ranked_authors( $data );
        if ( empty( $authors ) ) {
            return;
        }

        $module_id = 'author-matrix-' . uniqid();
        $section_style = 'background: ' . $bg_color . '; padding-top: ' . $pt . '; padding-bottom: ' . $pb . ';';

        if ( '' !== $button_bg_color ) {
            $section_style .= '--am-btn-bg:' . $button_bg_color . ';';
            $section_style .= '--am-btn-border:' . $button_bg_color . ';';
        }

        if ( '' !== $button_text_color ) {
            $section_style .= '--am-btn-text:' . $button_text_color . ';';
        }

        if ( '' !== $button_border_color ) {
            $section_style .= '--am-btn-border:' . $button_border_color . ';';
        }

        if ( '' !== $button_hover_bg_color ) {
            $section_style .= '--am-btn-hover-bg:' . $button_hover_bg_color . ';';
            $section_style .= '--am-btn-hover-border:' . $button_hover_bg_color . ';';
        }

        if ( '' !== $button_hover_text_color ) {
            $section_style .= '--am-btn-hover-text:' . $button_hover_text_color . ';';
        }

        if ( '' !== $button_hover_border_color ) {
            $section_style .= '--am-btn-hover-border:' . $button_hover_border_color . ';';
        }

        if ( '' !== $badge_bg ) {
            $section_style .= '--qiling-component-badge-bg:' . $badge_bg . ';';
        }
        ?>
        <section id="<?php echo esc_attr( $module_id ); ?>" class="module module-author-matrix" style="<?php echo esc_attr( $section_style ); ?>">
            <div class="container">
                <?php if ( $title || $subtitle ) : ?>
                    <div class="section-header text-center">
                        <?php if ( $title ) : ?>
                            <h2 class="section-title"><?php echo esc_html( $title ); ?></h2>
                        <?php endif; ?>
                        <?php if ( $subtitle ) : ?>
                            <p class="section-subtitle"><?php echo esc_html( $subtitle ); ?></p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <div class="am-grid am-cols-<?php echo esc_attr( (string) $columns ); ?>">
                    <?php foreach ( $authors as $index => $author_data ) : ?>
                        <?php
                        $author_id = intval( $author_data['author_id'] );
                        $display_name = $author_data['display_name'];
                        $bio = $author_data['description'];
                        $avatar = get_avatar_url( $author_id, array( 'size' => 160 ) );
                        $author_url = get_author_posts_url( $author_id );
                        $post_count = intval( $author_data['post_count'] );
                        $views = intval( $author_data['views'] );
                        $comments = intval( $author_data['comments'] );
                        $latest_time = intval( $author_data['latest_ts'] );
                        $latest_text = $latest_time > 0 ? date_i18n( get_option( 'date_format' ), $latest_time ) : '';
                        $rank = $index + 1;
                        ?>
                        <article class="am-card" style="background: <?php echo esc_attr( $card_bg ); ?>;">
                            <div class="am-rank">#<?php echo esc_html( (string) $rank ); ?></div>
                            <a class="am-avatar-link" href="<?php echo esc_url( $author_url ); ?>">
                                <?php if ( $avatar ) : ?>
                                    <img class="am-avatar" src="<?php echo esc_url( $avatar ); ?>" alt="<?php echo esc_attr( $display_name ); ?>" />
                                <?php endif; ?>
                            </a>
                            <h3 class="am-name"><a href="<?php echo esc_url( $author_url ); ?>"><?php echo esc_html( $display_name ); ?></a></h3>

                            <?php if ( $show_bio && $bio !== '' ) : ?>
                                <p class="am-bio"><?php echo esc_html( wp_trim_words( $bio, 26, '...' ) ); ?></p>
                            <?php endif; ?>

                            <?php if ( $show_stats ) : ?>
                                <div class="am-stats">
                                    <span><?php printf( esc_html__( '%d 篇文章', 'developer-starter' ), $post_count ); ?></span>
                                    <span><?php printf( esc_html__( '%d 阅读', 'developer-starter' ), $views ); ?></span>
                                    <?php if ( $comments_feature_enabled ) : ?>
                                        <span><?php printf( esc_html__( '%d 评论', 'developer-starter' ), $comments ); ?></span>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>

                            <?php if ( $show_latest && $latest_text !== '' ) : ?>
                                <div class="am-latest"><?php echo esc_html__( '最近活跃：', 'developer-starter' ) . esc_html( $latest_text ); ?></div>
                            <?php endif; ?>

                            <a class="am-btn" href="<?php echo esc_url( $author_url ); ?>"><?php echo esc_html( $button_text ); ?></a>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <?php
    }

    private function get_ranked_authors( $data ) {
        $source_mode = isset( $data['am_source_mode'] ) ? $data['am_source_mode'] : 'auto';
        $limit = isset( $data['am_limit'] ) ? max( 1, min( 50, intval( $data['am_limit'] ) ) ) : 8;
        $min_posts = isset( $data['am_min_posts'] ) ? max( 0, intval( $data['am_min_posts'] ) ) : 1;
        $rank_by = isset( $data['am_rank_by'] ) ? $data['am_rank_by'] : 'posts';
        $order = isset( $data['am_order'] ) && strtoupper( $data['am_order'] ) === 'ASC' ? 'ASC' : 'DESC';
        $rank_metric = $this->map_rank_metric( $rank_by );

        $cache_key = 'ds_am_' . md5( wp_json_encode( array(
            'source_mode' => $source_mode,
            'user_ids' => isset( $data['am_user_ids'] ) ? (string) $data['am_user_ids'] : '',
            'roles' => isset( $data['am_roles'] ) ? (string) $data['am_roles'] : '',
            'limit' => $limit,
            'min_posts' => $min_posts,
            'rank_by' => $rank_by,
            'order' => $order,
        ) ) );

        $cache_enabled = true;

        if ( $cache_enabled ) {
            if ( function_exists( 'developer_starter_cache_fetch' ) ) {
                $cached = \developer_starter_cache_fetch( $cache_key, 'developer_starter_module' );
            } else {
                $cached = get_transient( $cache_key );
            }
            if ( is_array( $cached ) ) {
                return $cached;
            }
        }

        $users = $this->load_candidate_users( $data, $source_mode );
        if ( empty( $users ) ) {
            return array();
        }

        $author_ids = array_map(
            static function( $u ) {
                return intval( $u->ID );
            },
            $users
        );
        $metrics_map = $this->build_author_metrics( $author_ids );

        $rows = array();
        foreach ( $users as $user ) {
            $author_id = intval( $user->ID );
            $metrics = isset( $metrics_map[ $author_id ] ) ? $metrics_map[ $author_id ] : array(
                'post_count' => 0,
                'views' => 0,
                'comments' => 0,
                'latest_ts' => 0,
            );

            if ( $metrics['post_count'] < $min_posts ) {
                continue;
            }

            $rows[] = array(
                'author_id' => $author_id,
                'display_name' => $user->display_name,
                'description' => (string) get_user_meta( $author_id, 'description', true ),
                'post_count' => intval( $metrics['post_count'] ),
                'views' => intval( $metrics['views'] ),
                'comments' => intval( $metrics['comments'] ),
                'latest_ts' => intval( $metrics['latest_ts'] ),
            );
        }

        if ( empty( $rows ) ) {
            return array();
        }

        usort(
            $rows,
            static function( $a, $b ) use ( $rank_metric, $order ) {
                $a_val = isset( $a[ $rank_metric ] ) ? intval( $a[ $rank_metric ] ) : 0;
                $b_val = isset( $b[ $rank_metric ] ) ? intval( $b[ $rank_metric ] ) : 0;
                if ( $a_val === $b_val ) {
                    if ( $a['post_count'] === $b['post_count'] ) {
                        return strcasecmp( $a['display_name'], $b['display_name'] );
                    }
                    return $b['post_count'] <=> $a['post_count'];
                }
                $cmp = $b_val <=> $a_val;
                return $order === 'ASC' ? -1 * $cmp : $cmp;
            }
        );

        $rows = array_slice( $rows, 0, $limit );
        if ( $cache_enabled ) {
            if ( function_exists( 'developer_starter_cache_store' ) ) {
                \developer_starter_cache_store( $cache_key, $rows, 10 * MINUTE_IN_SECONDS, 'developer_starter_module' );
            } else {
                set_transient( $cache_key, $rows, 10 * MINUTE_IN_SECONDS );
            }
        }
        return $rows;
    }

    private function load_candidate_users( $data, $source_mode ) {
        $args = array(
            'fields' => array( 'ID', 'display_name' ),
            'number' => 300,
        );

        $roles_raw = isset( $data['am_roles'] ) ? (string) $data['am_roles'] : '';
        $roles = array_filter( array_map( 'trim', explode( ',', $roles_raw ) ) );
        if ( ! empty( $roles ) ) {
            $args['role__in'] = $roles;
        }

        if ( $source_mode === 'manual' ) {
            $ids_raw = isset( $data['am_user_ids'] ) ? (string) $data['am_user_ids'] : '';
            $ids = array_map( 'intval', array_filter( array_map( 'trim', explode( ',', $ids_raw ) ) ) );
            if ( empty( $ids ) ) {
                return array();
            }
            $args['include'] = $ids;
            $args['number'] = count( $ids );
            $args['orderby'] = 'include';
        } else {
            $args['has_published_posts'] = array( 'post' );
            $args['orderby'] = 'registered';
            $args['order'] = 'DESC';
        }

        $users = get_users( $args );
        return is_array( $users ) ? $users : array();
    }

    private function build_author_metrics( $author_ids ) {
        $metrics = array();
        if ( empty( $author_ids ) ) {
            return $metrics;
        }

        foreach ( $author_ids as $author_id ) {
            $metrics[ $author_id ] = array(
                'post_count' => 0,
                'views' => 0,
                'comments' => 0,
                'latest_ts' => 0,
            );
        }

        global $wpdb;
        $in_placeholders = implode( ',', array_fill( 0, count( $author_ids ), '%d' ) );
        $sql_where = " WHERE p.post_type = 'post' AND p.post_status = 'publish' AND p.post_author IN ({$in_placeholders})";

        $sql_posts = "SELECT p.post_author AS author_id, COUNT(*) AS total_posts FROM {$wpdb->posts} p {$sql_where} GROUP BY p.post_author";
        $rows_posts = $wpdb->get_results( $wpdb->prepare( $sql_posts, $author_ids ), ARRAY_A );
        foreach ( (array) $rows_posts as $row ) {
            $author_id = intval( $row['author_id'] );
            if ( isset( $metrics[ $author_id ] ) ) {
                $metrics[ $author_id ]['post_count'] = intval( $row['total_posts'] );
            }
        }

        $sql_comments = "SELECT p.post_author AS author_id, SUM(p.comment_count) AS total_comments FROM {$wpdb->posts} p {$sql_where} GROUP BY p.post_author";
        $rows_comments = $wpdb->get_results( $wpdb->prepare( $sql_comments, $author_ids ), ARRAY_A );
        foreach ( (array) $rows_comments as $row ) {
            $author_id = intval( $row['author_id'] );
            if ( isset( $metrics[ $author_id ] ) ) {
                $metrics[ $author_id ]['comments'] = intval( $row['total_comments'] );
            }
        }

        $sql_latest = "SELECT p.post_author AS author_id, MAX(p.post_date_gmt) AS latest_date FROM {$wpdb->posts} p {$sql_where} GROUP BY p.post_author";
        $rows_latest = $wpdb->get_results( $wpdb->prepare( $sql_latest, $author_ids ), ARRAY_A );
        foreach ( (array) $rows_latest as $row ) {
            $author_id = intval( $row['author_id'] );
            if ( isset( $metrics[ $author_id ] ) && ! empty( $row['latest_date'] ) && $row['latest_date'] !== '0000-00-00 00:00:00' ) {
                $metrics[ $author_id ]['latest_ts'] = strtotime( $row['latest_date'] . ' GMT' );
            }
        }

        $sql_views = "SELECT p.post_author AS author_id, SUM(CAST(pm.meta_value AS UNSIGNED)) AS total_views
            FROM {$wpdb->posts} p
            LEFT JOIN {$wpdb->postmeta} pm ON pm.post_id = p.ID AND pm.meta_key = 'ds_post_views_count'
            {$sql_where}
            GROUP BY p.post_author";
        $rows_views = $wpdb->get_results( $wpdb->prepare( $sql_views, $author_ids ), ARRAY_A );
        foreach ( (array) $rows_views as $row ) {
            $author_id = intval( $row['author_id'] );
            if ( isset( $metrics[ $author_id ] ) ) {
                $metrics[ $author_id ]['views'] = intval( $row['total_views'] );
            }
        }

        return $metrics;
    }

    private function map_rank_metric( $rank_by ) {
        $map = array(
            'posts' => 'post_count',
            'views' => 'views',
            'comments' => 'comments',
            'latest' => 'latest_ts',
        );

        return isset( $map[ $rank_by ] ) ? $map[ $rank_by ] : 'post_count';
    }
}
