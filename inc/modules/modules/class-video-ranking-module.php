<?php
/**
 * Video ranking module.
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Modules\Modules;

use Developer_Starter\Modules\Module_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Video_Ranking_Module extends Module_Base {
    public function __construct() {
        $this->category    = 'content';
        $this->icon        = 'dashicons-awards';
        $this->description = __( '按分类、发布时间、更新时间、评论或浏览量展示影视榜单，播放器不可用时自动降级为普通文章排行。', 'developer-starter' );

        add_filter( 'posts_join', array( $this, 'filter_video_query_join' ), 20, 2 );
        add_filter( 'posts_where', array( $this, 'filter_video_query_where' ), 20, 2 );
    }

    public function get_id() {
        return 'qiling_video_ranking';
    }

    public function get_name() {
        return __( '影视排行榜', 'developer-starter' );
    }

    public function get_fields() {
        $category_options = array( '0' => __( '全部分类', 'developer-starter' ) );
        foreach ( get_categories( array( 'hide_empty' => false ) ) as $category ) {
            $category_options[ (string) $category->term_id ] = $category->name;
        }

        $source_options = array(
            'latest'   => __( '最新发布', 'developer-starter' ),
            'modified' => __( '最近更新', 'developer-starter' ),
            'comments' => __( '评论最多', 'developer-starter' ),
            'category' => __( '指定分类', 'developer-starter' ),
            'manual'   => __( '手工指定', 'developer-starter' ),
        );
        if ( $this->views_available() ) {
            $source_options['views'] = __( '浏览最多', 'developer-starter' );
        }

        return array(
            array( 'id' => 'vr_title', 'type' => 'text', 'label' => __( '模块标题', 'developer-starter' ), 'default' => __( '影视排行榜', 'developer-starter' ) ),
            array( 'id' => 'vr_title_color', 'type' => 'color', 'label' => __( '标题颜色', 'developer-starter' ), 'description' => __( '留空时跟随当前页面主题。', 'developer-starter' ) ),
            array( 'id' => 'vr_subtitle', 'type' => 'text', 'label' => __( '副标题', 'developer-starter' ), 'default' => __( '发现值得观看的热门内容', 'developer-starter' ) ),
            array( 'id' => 'vr_subtitle_color', 'type' => 'color', 'label' => __( '副标题颜色', 'developer-starter' ), 'description' => __( '留空时使用与模块背景匹配的辅助文字颜色。', 'developer-starter' ) ),
            array( 'id' => 'vr_bg_color', 'type' => 'color', 'label' => __( '模块背景颜色', 'developer-starter' ), 'description' => __( '留空时跟随当前页面主题。', 'developer-starter' ) ),
            array( 'id' => 'vr_more_text', 'type' => 'text', 'label' => __( '查看更多文字', 'developer-starter' ), 'default' => __( '查看更多', 'developer-starter' ) ),
            array( 'id' => 'vr_more_url', 'type' => 'text', 'label' => __( '查看更多链接', 'developer-starter' ), 'description' => __( '填写完整排行榜页面或影视分类页地址；留空时不显示“查看更多”按钮。', 'developer-starter' ) ),
            array( 'id' => 'vr_count', 'type' => 'number', 'label' => __( '默认显示数量', 'developer-starter' ), 'default' => '10', 'description' => __( '允许 3-20 条。', 'developer-starter' ) ),
            array( 'id' => 'vr_source', 'type' => 'select', 'label' => __( '默认数据来源', 'developer-starter' ), 'options' => $source_options, 'default' => 'latest' ),
            array( 'id' => 'vr_category', 'type' => 'select', 'label' => __( '默认指定分类', 'developer-starter' ), 'options' => $category_options, 'default' => '0' ),
            array( 'id' => 'vr_manual_ids', 'type' => 'text', 'label' => __( '默认手工文章 ID', 'developer-starter' ), 'description' => __( '使用逗号分隔，手工来源按填写顺序显示。', 'developer-starter' ) ),
            array(
                'id'      => 'vr_layout',
                'type'    => 'select',
                'label'   => __( '布局', 'developer-starter' ),
                'options' => array(
                    'featured'  => __( '前三名突出榜', 'developer-starter' ),
                    'compact'   => __( '紧凑文字榜', 'developer-starter' ),
                    'poster'    => __( '海报榜', 'developer-starter' ),
                    'two_column'=> __( '左右双栏榜', 'developer-starter' ),
                ),
                'default' => 'featured',
            ),
            array(
                'id'            => 'vr_boards',
                'type'          => 'repeater',
                'label'         => __( '排行榜标签', 'developer-starter' ),
                'description'   => __( '最多使用前 5 个榜单；不添加时使用上面的默认数据设置。', 'developer-starter' ),
                'add_button'    => __( '添加榜单', 'developer-starter' ),
                'fields'        => array(
                    array( 'id' => 'label', 'type' => 'text', 'label' => __( '标签名称', 'developer-starter' ), 'default' => __( '总榜', 'developer-starter' ) ),
                    array( 'id' => 'category', 'type' => 'select', 'label' => __( '指定分类', 'developer-starter' ), 'options' => $category_options, 'default' => '0' ),
                    array( 'id' => 'source', 'type' => 'select', 'label' => __( '排序方式', 'developer-starter' ), 'options' => $source_options, 'default' => 'latest' ),
                    array( 'id' => 'count', 'type' => 'number', 'label' => __( '显示数量', 'developer-starter' ), 'default' => '10' ),
                    array( 'id' => 'manual_ids', 'type' => 'text', 'label' => __( '手工文章 ID', 'developer-starter' ), 'description' => __( '仅手工指定来源使用，逗号分隔。', 'developer-starter' ) ),
                ),
                'default_items' => array(),
            ),
            array( 'id' => 'vr_show_rating', 'type' => 'select', 'label' => __( '显示评分', 'developer-starter' ), 'options' => array( 'yes' => __( '显示', 'developer-starter' ), 'no' => __( '隐藏', 'developer-starter' ) ), 'default' => 'yes' ),
            array( 'id' => 'vr_show_quality', 'type' => 'select', 'label' => __( '显示清晰度', 'developer-starter' ), 'options' => array( 'yes' => __( '显示', 'developer-starter' ), 'no' => __( '隐藏', 'developer-starter' ) ), 'default' => 'yes' ),
            array( 'id' => 'vr_show_episodes', 'type' => 'select', 'label' => __( '显示集数', 'developer-starter' ), 'options' => array( 'yes' => __( '显示', 'developer-starter' ), 'no' => __( '隐藏', 'developer-starter' ) ), 'default' => 'yes' ),
            array( 'id' => 'vr_show_category', 'type' => 'select', 'label' => __( '显示分类', 'developer-starter' ), 'options' => array( 'yes' => __( '显示', 'developer-starter' ), 'no' => __( '隐藏', 'developer-starter' ) ), 'default' => 'yes' ),
            array( 'id' => 'vr_show_date', 'type' => 'select', 'label' => __( '显示发布日期', 'developer-starter' ), 'options' => array( 'yes' => __( '显示', 'developer-starter' ), 'no' => __( '隐藏', 'developer-starter' ) ), 'default' => 'no' ),
            array( 'id' => 'vr_show_views', 'type' => 'select', 'label' => __( '显示浏览量', 'developer-starter' ), 'options' => array( 'yes' => __( '显示', 'developer-starter' ), 'no' => __( '隐藏', 'developer-starter' ) ), 'default' => 'no' ),
        );
    }

    public function render( $data = array() ) {
        $title     = isset( $data['vr_title'] ) ? sanitize_text_field( (string) $data['vr_title'] ) : __( '影视排行榜', 'developer-starter' );
        $subtitle  = isset( $data['vr_subtitle'] ) ? sanitize_text_field( (string) $data['vr_subtitle'] ) : '';
        $more_text = isset( $data['vr_more_text'] ) ? sanitize_text_field( (string) $data['vr_more_text'] ) : __( '查看更多', 'developer-starter' );
        $more_url  = isset( $data['vr_more_url'] ) ? esc_url_raw( (string) $data['vr_more_url'] ) : '';
        if ( in_array( trim( $more_url ), array( '#', '#0' ), true ) ) {
            $more_url = '';
        }
        $title_color = isset( $data['vr_title_color'] ) ? sanitize_hex_color( (string) $data['vr_title_color'] ) : '';
        $subtitle_color = isset( $data['vr_subtitle_color'] ) ? sanitize_hex_color( (string) $data['vr_subtitle_color'] ) : '';
        $bg_color = isset( $data['vr_bg_color'] ) ? sanitize_hex_color( (string) $data['vr_bg_color'] ) : '';
        $layout    = isset( $data['vr_layout'] ) ? sanitize_key( (string) $data['vr_layout'] ) : 'featured';
        if ( ! in_array( $layout, array( 'featured', 'compact', 'poster', 'two_column' ), true ) ) {
            $layout = 'featured';
        }

        $boards = $this->normalize_boards( $data );
        foreach ( $boards as $index => $board ) {
            $boards[ $index ]['items'] = $this->get_items( $board );
        }
        $boards = array_values( array_filter( $boards, static function ( $board ) { return ! empty( $board['items'] ); } ) );
        if ( empty( $boards ) ) {
            return;
        }

        $show = array(
            'rating'   => ! isset( $data['vr_show_rating'] ) || 'no' !== $data['vr_show_rating'],
            'quality'  => ! isset( $data['vr_show_quality'] ) || 'no' !== $data['vr_show_quality'],
            'episodes' => ! isset( $data['vr_show_episodes'] ) || 'no' !== $data['vr_show_episodes'],
            'category' => ! isset( $data['vr_show_category'] ) || 'no' !== $data['vr_show_category'],
            'date'     => isset( $data['vr_show_date'] ) && 'yes' === $data['vr_show_date'],
            'views'    => $this->views_available() && isset( $data['vr_show_views'] ) && 'yes' === $data['vr_show_views'],
        );
        $uid = 'qvr-' . uniqid();
        $module_styles = array();
        if ( $title_color ) {
            $module_styles[] = '--qvr-title-color:' . $title_color;
        }
        if ( $subtitle_color ) {
            $module_styles[] = '--qvr-subtitle-color:' . $subtitle_color;
        }
        if ( $bg_color ) {
            $module_styles[] = '--qvr-bg:' . $bg_color;
        }
        ?>
        <section class="module qiling-video-ranking qiling-video-ranking--<?php echo esc_attr( $layout ); ?>" id="<?php echo esc_attr( $uid ); ?>"<?php echo $module_styles ? ' style="' . esc_attr( implode( ';', $module_styles ) ) . '"' : ''; ?>>
            <div class="container">
                <header class="qvr-header">
                    <div class="qvr-heading">
                        <?php if ( '' !== $title ) : ?><h2 class="qvr-title"><?php echo esc_html( $title ); ?></h2><?php endif; ?>
                        <?php if ( '' !== $subtitle ) : ?><p class="qvr-subtitle"><?php echo esc_html( $subtitle ); ?></p><?php endif; ?>
                    </div>
                    <?php if ( '' !== $more_url && '' !== $more_text ) : ?><a class="qvr-more" href="<?php echo esc_url( $more_url ); ?>"><?php echo esc_html( $more_text ); ?><span aria-hidden="true">→</span></a><?php endif; ?>
                </header>

                <?php if ( count( $boards ) > 1 ) : ?>
                    <div class="qvr-tabs" role="tablist" aria-label="<?php esc_attr_e( '选择排行榜', 'developer-starter' ); ?>">
                        <?php foreach ( $boards as $index => $board ) : ?>
                            <button type="button" class="qvr-tab<?php echo 0 === $index ? ' is-active' : ''; ?>" role="tab" aria-selected="<?php echo 0 === $index ? 'true' : 'false'; ?>" aria-controls="<?php echo esc_attr( $uid . '-panel-' . $index ); ?>" data-qvr-tab="<?php echo esc_attr( $index ); ?>"><?php echo esc_html( $board['label'] ); ?></button>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <div class="qvr-panels">
                    <?php foreach ( $boards as $index => $board ) : ?>
                        <div id="<?php echo esc_attr( $uid . '-panel-' . $index ); ?>" class="qvr-panel<?php echo 0 === $index ? ' is-active' : ''; ?>" role="tabpanel"<?php echo 0 === $index ? '' : ' hidden'; ?> data-qvr-panel="<?php echo esc_attr( $index ); ?>">
                            <?php $this->render_board( $board['items'], $layout, $show ); ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php if ( count( $boards ) > 1 ) : ?>
            <script>
            (function(){var root=document.getElementById(<?php echo wp_json_encode( $uid ); ?>);if(!root)return;var tabs=root.querySelectorAll('[data-qvr-tab]');var panels=root.querySelectorAll('[data-qvr-panel]');tabs.forEach(function(tab){tab.addEventListener('click',function(){var key=tab.getAttribute('data-qvr-tab');tabs.forEach(function(item){var active=item===tab;item.classList.toggle('is-active',active);item.setAttribute('aria-selected',active?'true':'false');});panels.forEach(function(panel){var active=panel.getAttribute('data-qvr-panel')===key;panel.classList.toggle('is-active',active);panel.hidden=!active;});});});})();
            </script>
            <?php endif; ?>
        </section>
        <?php
    }

    private function normalize_boards( $data ) {
        $raw_boards = isset( $data['vr_boards'] ) && is_array( $data['vr_boards'] ) ? array_slice( $data['vr_boards'], 0, 5 ) : array();
        if ( empty( $raw_boards ) ) {
            $raw_boards[] = array(
                'label'      => isset( $data['vr_title'] ) ? $data['vr_title'] : __( '总榜', 'developer-starter' ),
                'category'   => isset( $data['vr_category'] ) ? $data['vr_category'] : 0,
                'source'     => isset( $data['vr_source'] ) ? $data['vr_source'] : 'latest',
                'count'      => isset( $data['vr_count'] ) ? $data['vr_count'] : 10,
                'manual_ids' => isset( $data['vr_manual_ids'] ) ? $data['vr_manual_ids'] : '',
            );
        }

        $allowed_sources = array( 'latest', 'modified', 'comments', 'category', 'manual' );
        if ( $this->views_available() ) {
            $allowed_sources[] = 'views';
        }
        $boards = array();
        foreach ( $raw_boards as $index => $raw ) {
            if ( ! is_array( $raw ) ) {
                continue;
            }
            $source = isset( $raw['source'] ) ? sanitize_key( (string) $raw['source'] ) : 'latest';
            if ( ! in_array( $source, $allowed_sources, true ) ) {
                $source = 'latest';
            }
            $label = isset( $raw['label'] ) ? sanitize_text_field( (string) $raw['label'] ) : '';
            $boards[] = array(
                'label'      => '' !== $label ? $label : sprintf( __( '榜单 %d', 'developer-starter' ), $index + 1 ),
                'category'   => isset( $raw['category'] ) ? absint( $raw['category'] ) : 0,
                'source'     => $source,
                'count'      => max( 3, min( 20, isset( $raw['count'] ) ? absint( $raw['count'] ) : 10 ) ),
                'manual_ids' => $this->parse_ids( isset( $raw['manual_ids'] ) ? $raw['manual_ids'] : '' ),
            );
        }
        return $boards;
    }

    private function parse_ids( $value ) {
        $parts = is_array( $value ) ? $value : preg_split( '/[,，\s]+/u', (string) $value );
        return array_values( array_unique( array_filter( array_map( 'absint', (array) $parts ) ) ) );
    }

    private function get_items( $board ) {
        static $request_cache = array();
        $cache_key = md5( wp_json_encode( array( $board, 'video' => $this->video_available(), 'views' => $this->views_available() ) ) );
        if ( isset( $request_cache[ $cache_key ] ) ) {
            return $request_cache[ $cache_key ];
        }

        $args = array(
            'post_type'              => 'post',
            'post_status'            => 'publish',
            'posts_per_page'         => $board['count'],
            'ignore_sticky_posts'    => true,
            'no_found_rows'          => true,
            'update_post_meta_cache' => true,
            'update_post_term_cache' => true,
            'suppress_filters'       => false,
        );
        if ( $board['category'] > 0 ) {
            $args['cat'] = $board['category'];
        }
        switch ( $board['source'] ) {
            case 'manual':
                if ( empty( $board['manual_ids'] ) ) {
                    $request_cache[ $cache_key ] = array();
                    return $request_cache[ $cache_key ];
                }
                $args['post__in']       = array_slice( $board['manual_ids'], 0, $board['count'] );
                $args['orderby']        = 'post__in';
                $args['posts_per_page'] = count( $args['post__in'] );
                break;
            case 'modified':
                $args['orderby'] = 'modified';
                $args['order']   = 'DESC';
                break;
            case 'comments':
                $args['orderby'] = 'comment_count';
                $args['order']   = 'DESC';
                break;
            case 'views':
                if ( $this->views_available() ) {
                    $args['meta_key'] = 'ds_post_views_count';
                    $args['orderby']  = 'meta_value_num';
                    $args['order']    = 'DESC';
                    break;
                }
                // Fall through to latest when statistics are unavailable.
            case 'category':
            case 'latest':
            default:
                $args['orderby'] = 'date';
                $args['order']   = 'DESC';
                break;
        }

        if ( $this->video_available() ) {
            $args['qiling_video_ranking_only'] = 1;
        }
        $query = new \WP_Query( $args );
        $ids   = wp_list_pluck( $query->posts, 'ID' );
        $this->prime_video_meta( $ids );
        $items = array();
        foreach ( $query->posts as $post ) {
            if ( $post instanceof \WP_Post ) {
                $items[] = $this->build_item( $post );
            }
        }
        wp_reset_postdata();
        $request_cache[ $cache_key ] = $items;
        return $request_cache[ $cache_key ];
    }

    private function build_item( $post ) {
        $post_id       = (int) $post->ID;
        $video_meta    = $this->video_available() ? \ArtPlayer_Video_Frontend::get_instance()->get_video_meta_public( $post_id ) : null;
        $poster        = $video_meta && ! empty( $video_meta->cover_image ) ? (string) $video_meta->cover_image : '';
        if ( '' === $poster && function_exists( 'developer_starter_get_featured_image_url' ) ) {
            $poster = (string) developer_starter_get_featured_image_url( $post_id, 'medium_large' );
        } elseif ( '' === $poster && has_post_thumbnail( $post_id ) ) {
            $poster = (string) get_the_post_thumbnail_url( $post_id, 'medium_large' );
        }
        $categories = get_the_category( $post_id );
        $category   = ! empty( $categories ) ? $categories[0]->name : '';
        return array(
            'id'       => $post_id,
            'title'    => get_the_title( $post_id ) ?: __( '（无标题）', 'developer-starter' ),
            'url'      => get_permalink( $post_id ),
            'poster'   => $poster,
            'rating'   => $video_meta && is_numeric( $video_meta->rating ) ? (float) $video_meta->rating : 0,
            'quality'  => $video_meta && ! empty( $video_meta->video_quality ) ? sanitize_text_field( $video_meta->video_quality ) : '',
            'episodes' => function_exists( 'artplayer_get_post_video_urls' ) ? count( (array) artplayer_get_post_video_urls( $post_id ) ) : 0,
            'category' => $category,
            'date'     => get_the_date( '', $post_id ),
            'views'    => $this->views_available() && class_exists( '\Developer_Starter\Core\Post_Enhancer' ) ? \Developer_Starter\Core\Post_Enhancer::get_post_views( $post_id ) : 0,
        );
    }

    private function render_board( $items, $layout, $show ) {
        $top_items  = 'featured' === $layout ? array_slice( $items, 0, 3 ) : array();
        $list_items = 'featured' === $layout ? array_slice( $items, 3 ) : $items;
        if ( ! empty( $top_items ) ) : ?>
            <div class="qvr-top-three">
                <?php foreach ( $top_items as $index => $item ) : $this->render_item( $item, $index + 1, 'top', $show ); endforeach; ?>
            </div>
        <?php endif; ?>
        <?php if ( ! empty( $list_items ) ) : ?>
            <div class="qvr-list">
                <?php foreach ( $list_items as $index => $item ) : $this->render_item( $item, $index + 1 + count( $top_items ), 'list', $show ); endforeach; ?>
            </div>
        <?php endif;
    }

    private function render_item( $item, $rank, $variant, $show ) {
        $has_poster = ! empty( $item['poster'] );
        ?>
        <article class="qvr-item qvr-item--<?php echo esc_attr( $variant ); ?> qvr-rank-<?php echo esc_attr( min( 4, $rank ) ); ?>">
            <a class="qvr-item-link" href="<?php echo esc_url( $item['url'] ); ?>">
                <span class="qvr-rank"><?php echo esc_html( sprintf( '%02d', $rank ) ); ?></span>
                <span class="qvr-poster<?php echo $has_poster ? '' : ' is-placeholder'; ?>">
                    <?php if ( $has_poster ) : ?><img src="<?php echo esc_url( $item['poster'] ); ?>" alt="<?php echo esc_attr( $item['title'] ); ?>" width="240" height="360" loading="lazy" decoding="async" /><?php else : ?><span><?php esc_html_e( '暂无封面', 'developer-starter' ); ?></span><?php endif; ?>
                </span>
                <span class="qvr-item-body">
                    <span class="qvr-item-title"><?php echo esc_html( $item['title'] ); ?></span>
                    <span class="qvr-meta">
                        <?php if ( $show['category'] && '' !== $item['category'] ) : ?><span><?php echo esc_html( $item['category'] ); ?></span><?php endif; ?>
                        <?php if ( $show['date'] ) : ?><span><?php echo esc_html( $item['date'] ); ?></span><?php endif; ?>
                        <?php if ( $show['quality'] && '' !== $item['quality'] ) : ?><span><?php echo esc_html( $item['quality'] ); ?></span><?php endif; ?>
                        <?php if ( $show['episodes'] && $item['episodes'] > 0 ) : ?><span><?php echo esc_html( sprintf( __( '%d 集', 'developer-starter' ), $item['episodes'] ) ); ?></span><?php endif; ?>
                        <?php if ( $show['views'] && $item['views'] > 0 ) : ?><span><?php echo esc_html( sprintf( __( '%s 浏览', 'developer-starter' ), number_format_i18n( $item['views'] ) ) ); ?></span><?php endif; ?>
                    </span>
                </span>
                <?php if ( $show['rating'] && $item['rating'] > 0 ) : ?><span class="qvr-rating"><strong><?php echo esc_html( number_format_i18n( $item['rating'], 1 ) ); ?></strong><small><?php esc_html_e( '评分', 'developer-starter' ); ?></small></span><?php endif; ?>
            </a>
        </article>
        <?php
    }

    public function filter_video_query_join( $join, $query ) {
        if ( ! ( $query instanceof \WP_Query ) || ! $query->get( 'qiling_video_ranking_only' ) || ! $this->video_available() ) {
            return $join;
        }
        global $wpdb;
        $table = $wpdb->prefix . 'artplayer_video_meta';
        if ( false === strpos( $join, 'qiling_ranking_video_meta' ) ) {
            $join .= " INNER JOIN {$table} qiling_ranking_video_meta ON {$wpdb->posts}.ID = qiling_ranking_video_meta.post_id";
        }
        return $join;
    }

    public function filter_video_query_where( $where, $query ) {
        return $query instanceof \WP_Query && $query->get( 'qiling_video_ranking_only' ) && $this->video_available()
            ? $where . ' AND qiling_ranking_video_meta.is_video_mode = 1'
            : $where;
    }

    private function prime_video_meta( $ids ) {
        if ( ! $this->video_available() || empty( $ids ) ) {
            return;
        }
        global $wpdb;
        $ids          = array_values( array_unique( array_filter( array_map( 'absint', $ids ) ) ) );
        $placeholders = implode( ',', array_fill( 0, count( $ids ), '%d' ) );
        $table        = $wpdb->prefix . 'artplayer_video_meta';
        $rows         = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$table} WHERE post_id IN ({$placeholders})", $ids ) );
        $found        = array();
        foreach ( (array) $rows as $row ) {
            $found[] = (int) $row->post_id;
            wp_cache_set( 'artplayer_video_meta_' . (int) $row->post_id, $row );
        }
        foreach ( array_diff( $ids, $found ) as $post_id ) {
            wp_cache_set( 'artplayer_video_meta_' . $post_id, null );
        }
    }

    private function video_available() {
        return function_exists( 'developer_starter_get_search_mode_manager' ) && developer_starter_get_search_mode_manager()->is_video_mode_available();
    }

    private function views_available() {
        return function_exists( 'developer_starter_get_option' ) && (bool) developer_starter_get_option( 'post_views_enable', '' );
    }
}
