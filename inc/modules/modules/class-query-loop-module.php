<?php
/**
 * Query Loop Module.
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Modules\Modules;

use Developer_Starter\Modules\Module_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Query_Loop_Module extends Module_Base {

    public function __construct() {
        $this->category    = 'content';
        $this->icon        = 'dashicons-list-view';
        $this->description = __( '输出当前归档、文章列表、分类标签列表、文章类型或启灵内容模型。', 'developer-starter' );
    }

    public function get_id() {
        return 'query_loop';
    }

    public function get_name() {
        return __( 'Query Loop 查询循环', 'developer-starter' );
    }

    public function get_fields() {
        return array(
            array( 'id' => 'ql_title', 'type' => 'text', 'label' => __( '标题', 'developer-starter' ) ),
            array( 'id' => 'ql_subtitle', 'type' => 'textarea', 'label' => __( '副标题', 'developer-starter' ) ),
            array(
                'id'      => 'ql_source',
                'type'    => 'select',
                'label'   => __( '数据来源', 'developer-starter' ),
                'options' => array(
                    'current_query' => __( '当前归档查询', 'developer-starter' ),
                    'latest'        => __( '最新文章', 'developer-starter' ),
                    'category'      => __( '指定分类文章', 'developer-starter' ),
                    'tag'           => __( '指定标签文章', 'developer-starter' ),
                    'post_type'     => __( '指定文章类型', 'developer-starter' ),
                    'content_model' => __( '启灵内容模型', 'developer-starter' ),
                ),
                'default' => 'latest',
            ),
            array( 'id' => 'ql_category_terms', 'type' => 'text', 'label' => __( '分类 ID 或别名', 'developer-starter' ), 'dependency' => array( 'ql_source', '==', 'category' ) ),
            array( 'id' => 'ql_tag_terms', 'type' => 'text', 'label' => __( '标签 ID 或别名', 'developer-starter' ), 'dependency' => array( 'ql_source', '==', 'tag' ) ),
            array(
                'id'         => 'ql_post_type',
                'type'       => 'select',
                'label'      => __( '文章类型', 'developer-starter' ),
                'options'    => $this->get_post_type_options(),
                'default'    => 'post',
                'dependency' => array( 'ql_source', '==', 'post_type' ),
            ),
            array(
                'id'         => 'ql_content_model',
                'type'       => 'select',
                'label'      => __( '内容模型', 'developer-starter' ),
                'options'    => $this->get_content_model_options(),
                'default'    => 'post',
                'dependency' => array( 'ql_source', '==', 'content_model' ),
            ),
            array( 'id' => 'ql_count', 'type' => 'number', 'label' => __( '数量', 'developer-starter' ), 'default' => '6' ),
            array(
                'id'      => 'ql_columns',
                'type'    => 'select',
                'label'   => __( '列数', 'developer-starter' ),
                'options' => array(
                    '1' => __( '1列', 'developer-starter' ),
                    '2' => __( '2列', 'developer-starter' ),
                    '3' => __( '3列', 'developer-starter' ),
                    '4' => __( '4列', 'developer-starter' ),
                ),
                'default' => '3',
            ),
            array(
                'id'      => 'ql_orderby',
                'type'    => 'select',
                'label'   => __( '排序方式', 'developer-starter' ),
                'options' => array(
                    'date'          => __( '发布日期', 'developer-starter' ),
                    'modified'      => __( '更新时间', 'developer-starter' ),
                    'title'         => __( '标题', 'developer-starter' ),
                    'menu_order'    => __( '菜单顺序', 'developer-starter' ),
                    'sort_order'    => __( '内容模型排序权重', 'developer-starter' ),
                    'comment_count' => __( '评论数', 'developer-starter' ),
                    'rand'          => __( '随机', 'developer-starter' ),
                ),
                'default' => 'date',
            ),
            array(
                'id'      => 'ql_order',
                'type'    => 'select',
                'label'   => __( '排序方向', 'developer-starter' ),
                'options' => array(
                    'DESC' => __( '降序', 'developer-starter' ),
                    'ASC'  => __( '升序', 'developer-starter' ),
                ),
                'default' => 'DESC',
            ),
            array( 'id' => 'ql_enable_pagination', 'type' => 'select', 'label' => __( '启用分页', 'developer-starter' ), 'options' => array( 'yes' => __( '是', 'developer-starter' ), 'no' => __( '否', 'developer-starter' ) ), 'default' => 'no' ),
            array( 'id' => 'ql_show_image', 'type' => 'select', 'label' => __( '显示图片', 'developer-starter' ), 'options' => array( 'yes' => __( '是', 'developer-starter' ), 'no' => __( '否', 'developer-starter' ) ), 'default' => 'yes' ),
            array( 'id' => 'ql_show_excerpt', 'type' => 'select', 'label' => __( '显示摘要', 'developer-starter' ), 'options' => array( 'yes' => __( '是', 'developer-starter' ), 'no' => __( '否', 'developer-starter' ) ), 'default' => 'yes' ),
            array( 'id' => 'ql_excerpt_length', 'type' => 'number', 'label' => __( '摘要字数', 'developer-starter' ), 'default' => '28' ),
            array( 'id' => 'ql_show_meta', 'type' => 'select', 'label' => __( '显示分类和日期', 'developer-starter' ), 'options' => array( 'yes' => __( '是', 'developer-starter' ), 'no' => __( '否', 'developer-starter' ) ), 'default' => 'yes' ),
            array( 'id' => 'ql_show_read_more', 'type' => 'select', 'label' => __( '显示按钮', 'developer-starter' ), 'options' => array( 'yes' => __( '是', 'developer-starter' ), 'no' => __( '否', 'developer-starter' ) ), 'default' => 'yes' ),
            array( 'id' => 'ql_read_more_text', 'type' => 'text', 'label' => __( '按钮文案', 'developer-starter' ), 'default' => __( '查看详情', 'developer-starter' ) ),
            array( 'id' => 'ql_empty_text', 'type' => 'text', 'label' => __( '空状态文案', 'developer-starter' ), 'default' => __( '暂无内容', 'developer-starter' ) ),
        );
    }

    public function render( $data = array() ) {
        $settings = $this->normalize_settings( $data );
        list( $query, $uses_main_query ) = $this->build_query( $settings );

        if ( ! $query instanceof \WP_Query ) {
            return;
        }

        $posts      = is_array( $query->posts ) ? $query->posts : array();
        $module_id  = function_exists( 'wp_unique_id' ) ? wp_unique_id( 'query-loop-' ) : 'query-loop-' . uniqid();
        $title      = isset( $settings['title'] ) ? (string) $settings['title'] : '';
        $subtitle   = isset( $settings['subtitle'] ) ? (string) $settings['subtitle'] : '';
        $columns    = isset( $settings['columns'] ) ? (int) $settings['columns'] : 3;
        $section_classes = array(
            'module',
            'module-query-loop',
            'qiling-query-loop',
            'qiling-query-loop--cols-' . max( 1, min( 4, $columns ) ),
            'qiling-query-loop--source-' . sanitize_html_class( (string) $settings['source'] ),
        );
        ?>
        <section id="<?php echo esc_attr( $module_id ); ?>" class="<?php echo esc_attr( implode( ' ', $section_classes ) ); ?>">
            <div class="container qiling-query-loop__container">
                <?php if ( '' !== $title || '' !== $subtitle ) : ?>
                    <header class="qiling-query-loop__header">
                        <?php if ( '' !== $title ) : ?>
                            <h2 class="qiling-query-loop__title"><?php echo esc_html( $title ); ?></h2>
                        <?php endif; ?>
                        <?php if ( '' !== $subtitle ) : ?>
                            <p class="qiling-query-loop__subtitle"><?php echo esc_html( $subtitle ); ?></p>
                        <?php endif; ?>
                    </header>
                <?php endif; ?>

                <?php if ( ! empty( $posts ) ) : ?>
                    <div class="qiling-query-loop__grid">
                        <?php foreach ( $posts as $post_item ) : ?>
                            <?php
                            $post = $post_item instanceof \WP_Post ? $post_item : get_post( $post_item );
                            if ( ! $post instanceof \WP_Post ) {
                                continue;
                            }
                            setup_postdata( $post );
                            $this->push_loop_context( $post->ID );
                            try {
                                $this->render_post_card( $post, $settings );
                            } finally {
                                $this->pop_loop_context();
                            }
                            ?>
                        <?php endforeach; ?>
                    </div>
                    <?php wp_reset_postdata(); ?>

                    <?php if ( ! empty( $settings['pagination'] ) ) : ?>
                        <?php $this->render_pagination( $query, (int) $settings['paged'], $uses_main_query ); ?>
                    <?php endif; ?>
                <?php else : ?>
                    <div class="qiling-query-loop__empty">
                        <?php echo esc_html( (string) $settings['empty_text'] ); ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>
        <?php
    }

    private function normalize_settings( $data ) {
        $data = is_array( $data ) ? $data : array();

        $source = isset( $data['ql_source'] ) ? sanitize_key( (string) $data['ql_source'] ) : 'latest';
        if ( ! in_array( $source, array( 'current_query', 'latest', 'category', 'tag', 'post_type', 'content_model' ), true ) ) {
            $source = 'latest';
        }

        $count = isset( $data['ql_count'] ) ? absint( $data['ql_count'] ) : 6;
        if ( $count < 1 ) {
            $count = 6;
        }
        $count = min( 50, $count );

        $columns = isset( $data['ql_columns'] ) ? absint( $data['ql_columns'] ) : 3;
        $columns = max( 1, min( 4, $columns ) );

        $orderby = isset( $data['ql_orderby'] ) ? sanitize_key( (string) $data['ql_orderby'] ) : 'date';
        if ( ! in_array( $orderby, array( 'date', 'modified', 'title', 'menu_order', 'sort_order', 'comment_count', 'rand' ), true ) ) {
            $orderby = 'date';
        }

        $order = isset( $data['ql_order'] ) ? strtoupper( sanitize_key( (string) $data['ql_order'] ) ) : 'DESC';
        $order = 'ASC' === $order ? 'ASC' : 'DESC';

        return array(
            'title'             => isset( $data['ql_title'] ) ? (string) $data['ql_title'] : '',
            'subtitle'          => isset( $data['ql_subtitle'] ) ? (string) $data['ql_subtitle'] : '',
            'source'            => $source,
            'category_terms'    => isset( $data['ql_category_terms'] ) ? (string) $data['ql_category_terms'] : '',
            'tag_terms'         => isset( $data['ql_tag_terms'] ) ? (string) $data['ql_tag_terms'] : '',
            'post_type'         => isset( $data['ql_post_type'] ) ? sanitize_key( (string) $data['ql_post_type'] ) : 'post',
            'content_model'     => isset( $data['ql_content_model'] ) ? sanitize_key( (string) $data['ql_content_model'] ) : 'post',
            'count'             => $count,
            'columns'           => $columns,
            'orderby'           => $orderby,
            'order'             => $order,
            'pagination'        => isset( $data['ql_enable_pagination'] ) && 'yes' === $data['ql_enable_pagination'],
            'show_image'        => ! isset( $data['ql_show_image'] ) || 'no' !== $data['ql_show_image'],
            'show_excerpt'      => ! isset( $data['ql_show_excerpt'] ) || 'no' !== $data['ql_show_excerpt'],
            'show_meta'         => ! isset( $data['ql_show_meta'] ) || 'no' !== $data['ql_show_meta'],
            'show_read_more'    => ! isset( $data['ql_show_read_more'] ) || 'no' !== $data['ql_show_read_more'],
            'excerpt_length'    => max( 5, min( 120, isset( $data['ql_excerpt_length'] ) ? absint( $data['ql_excerpt_length'] ) : 28 ) ),
            'read_more_text'    => isset( $data['ql_read_more_text'] ) && '' !== (string) $data['ql_read_more_text'] ? (string) $data['ql_read_more_text'] : __( '查看详情', 'developer-starter' ),
            'empty_text'        => isset( $data['ql_empty_text'] ) && '' !== (string) $data['ql_empty_text'] ? (string) $data['ql_empty_text'] : __( '暂无内容', 'developer-starter' ),
            'paged'             => $this->get_current_paged(),
        );
    }

    private function build_query( $settings ) {
        global $wp_query;

        if (
            'current_query' === $settings['source']
            && $wp_query instanceof \WP_Query
            && ! is_singular()
            && ( is_archive() || is_home() || is_search() )
        ) {
            return array( $wp_query, true );
        }

        $args = $this->build_query_args( $settings );
        if ( function_exists( 'developer_starter_run_cached_query' ) ) {
            return array(
                \developer_starter_run_cached_query(
                    $args,
                    'module_query_loop',
                    array(
                        'needs_pagination' => ! empty( $settings['pagination'] ),
                    )
                ),
                false,
            );
        }

        return array( new \WP_Query( $args ), false );
    }

    private function build_query_args( $settings ) {
        $post_type = 'post';

        if ( 'post_type' === $settings['source'] ) {
            $post_type = $this->sanitize_public_post_type( (string) $settings['post_type'] );
        } elseif ( 'content_model' === $settings['source'] ) {
            $post_type = $this->get_content_model_post_type( (string) $settings['content_model'] );
            if ( '' === $post_type ) {
                $post_type = 'post';
            }
        }

        $args = array(
            'post_type'              => $post_type,
            'post_status'            => 'publish',
            'posts_per_page'         => (int) $settings['count'],
            'paged'                  => ! empty( $settings['pagination'] ) ? (int) $settings['paged'] : 1,
            'ignore_sticky_posts'    => true,
            'update_post_meta_cache' => true,
            'update_post_term_cache' => true,
        );

        if ( empty( $settings['pagination'] ) ) {
            $args['no_found_rows'] = true;
        }

        if ( 'category' === $settings['source'] ) {
            $this->add_tax_query( $args, 'category', (string) $settings['category_terms'] );
        } elseif ( 'tag' === $settings['source'] ) {
            $this->add_tax_query( $args, 'post_tag', (string) $settings['tag_terms'] );
        }

        $orderby = (string) $settings['orderby'];
        if ( 'sort_order' === $orderby ) {
            $args['meta_key'] = class_exists( '\Developer_Starter\Core\Content_Model_Center' )
                ? \Developer_Starter\Core\Content_Model_Center::get_model_meta_key( 'sort_order' )
                : '_qiling_model_sort_order';
            $args['orderby'] = array(
                'meta_value_num' => (string) $settings['order'],
                'date'           => 'DESC',
            );
        } elseif ( 'rand' === $orderby ) {
            $args['orderby'] = 'rand';
        } else {
            $args['orderby'] = $orderby;
            $args['order']   = (string) $settings['order'];
        }

        return (array) apply_filters( 'developer_starter_query_loop_query_args', $args, $settings );
    }

    private function render_post_card( \WP_Post $post, $settings ) {
        $post_id = (int) $post->ID;
        $item    = $this->get_loop_item_data( $post, $settings );
        $title   = isset( $item['title'] ) ? (string) $item['title'] : get_the_title( $post );
        $link    = isset( $item['link'] ) ? (string) $item['link'] : (string) get_permalink( $post );
        $image   = isset( $item['image'] ) ? (string) $item['image'] : '';
        $excerpt = isset( $item['excerpt'] ) ? (string) $item['excerpt'] : '';
        $terms   = isset( $item['categories'] ) ? (string) $item['categories'] : '';
        $date    = isset( $item['date'] ) ? (string) $item['date'] : '';
        $date_iso = isset( $item['date_iso'] ) ? (string) $item['date_iso'] : get_the_date( 'c', $post );
        if ( '' === $link ) {
            $link = (string) get_permalink( $post );
        }
        ?>
        <article class="qiling-query-loop-card post-<?php echo esc_attr( (string) $post_id ); ?>">
            <?php if ( ! empty( $settings['show_image'] ) && '' !== $image ) : ?>
                <a class="qiling-query-loop-card__media" href="<?php echo esc_url( $link ); ?>" aria-label="<?php echo esc_attr( $title ); ?>">
                    <img src="<?php echo esc_url( $image ); ?>" alt="<?php echo esc_attr( $title ); ?>" loading="lazy">
                </a>
            <?php endif; ?>

            <div class="qiling-query-loop-card__body">
                <?php if ( ! empty( $settings['show_meta'] ) && ( '' !== $terms || '' !== $date ) ) : ?>
                    <div class="qiling-query-loop-card__meta">
                        <?php if ( '' !== $terms ) : ?>
                            <span class="qiling-query-loop-card__term"><?php echo esc_html( $terms ); ?></span>
                        <?php endif; ?>
                        <?php if ( '' !== $date ) : ?>
                            <time datetime="<?php echo esc_attr( $date_iso ); ?>"><?php echo esc_html( $date ); ?></time>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <h3 class="qiling-query-loop-card__title">
                    <a href="<?php echo esc_url( $link ); ?>"><?php echo esc_html( $title ); ?></a>
                </h3>

                <?php if ( ! empty( $settings['show_excerpt'] ) && '' !== $excerpt ) : ?>
                    <div class="qiling-query-loop-card__excerpt">
                        <?php echo esc_html( $excerpt ); ?>
                    </div>
                <?php endif; ?>

                <?php if ( ! empty( $settings['show_read_more'] ) ) : ?>
                    <a class="qiling-query-loop-card__read-more" href="<?php echo esc_url( $link ); ?>">
                        <?php echo esc_html( (string) $settings['read_more_text'] ); ?>
                    </a>
                <?php endif; ?>
            </div>
        </article>
        <?php
    }

    /**
     * Resolve current loop item fields through Dynamic Data when available.
     *
     * @param \WP_Post            $post     Current post.
     * @param array<string,mixed> $settings Module settings.
     * @return array<string,string>
     */
    private function get_loop_item_data( \WP_Post $post, $settings ) {
        $post_id = (int) $post->ID;
        $data = array(
            'title'      => (string) get_the_title( $post ),
            'image'      => $this->get_post_image_url( $post_id ),
            'excerpt'    => $this->get_post_excerpt( $post, isset( $settings['excerpt_length'] ) ? (int) $settings['excerpt_length'] : 28 ),
            'link'       => (string) get_permalink( $post ),
            'categories' => $this->get_primary_term_label( $post_id ),
            'date'       => (string) get_the_date( '', $post ),
            'date_iso'   => (string) get_the_date( 'c', $post ),
        );

        if ( class_exists( '\Developer_Starter\Core\Dynamic_Data_Manager' ) ) {
            $dynamic_manager = \Developer_Starter\Core\Dynamic_Data_Manager::get_instance();
            $dynamic_context = method_exists( $dynamic_manager, 'get_current_context' )
                ? $dynamic_manager->get_current_context()
                : array();
            $source_map = array(
                'title'      => 'loop.title',
                'image'      => 'loop.featured_image',
                'excerpt'    => 'loop.excerpt',
                'link'       => 'loop.link',
                'categories' => 'loop.categories',
                'date'       => 'loop.date',
            );

            foreach ( $source_map as $key => $source ) {
                $value = $dynamic_manager->resolve_source( $source, $dynamic_context );
                if ( is_scalar( $value ) && '' !== trim( (string) $value ) ) {
                    $data[ $key ] = (string) $value;
                }
            }
        }

        $data = array_map( 'strval', $data );

        return (array) apply_filters( 'developer_starter_query_loop_item_data', $data, $post, $settings );
    }

    private function render_pagination( \WP_Query $query, $paged, $uses_main_query ) {
        unset( $uses_main_query );

        $total = isset( $query->max_num_pages ) ? (int) $query->max_num_pages : 1;
        if ( $total <= 1 ) {
            return;
        }

        $paged = max( 1, (int) $paged );
        $big   = 999999999;
        $links = paginate_links(
            array(
                'base'      => str_replace( (string) $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
                'format'    => '',
                'current'   => $paged,
                'total'     => $total,
                'type'      => 'array',
                'prev_text' => __( '上一页', 'developer-starter' ),
                'next_text' => __( '下一页', 'developer-starter' ),
            )
        );

        if ( empty( $links ) || ! is_array( $links ) ) {
            return;
        }
        ?>
        <nav class="qiling-query-loop__pagination" role="navigation" aria-label="<?php echo esc_attr__( '查询循环分页', 'developer-starter' ); ?>">
            <?php foreach ( $links as $link ) : ?>
                <?php echo wp_kses_post( $link ); ?>
            <?php endforeach; ?>
        </nav>
        <?php
    }

    private function push_loop_context( $post_id ) {
        if ( ! class_exists( '\Developer_Starter\Core\Dynamic_Data_Manager' ) ) {
            return;
        }

        $manager = \Developer_Starter\Core\Dynamic_Data_Manager::get_instance();
        if ( method_exists( $manager, 'push_loop_item_context' ) ) {
            $manager->push_loop_item_context( $post_id );
        } else {
            $manager->push_post_context( $post_id );
        }
    }

    private function pop_loop_context() {
        if ( ! class_exists( '\Developer_Starter\Core\Dynamic_Data_Manager' ) ) {
            return;
        }

        \Developer_Starter\Core\Dynamic_Data_Manager::get_instance()->pop_context();
    }

    private function get_current_paged() {
        $paged = function_exists( 'get_query_var' ) ? absint( get_query_var( 'paged' ) ) : 0;
        if ( $paged < 1 && function_exists( 'get_query_var' ) ) {
            $paged = absint( get_query_var( 'page' ) );
        }

        return max( 1, $paged );
    }

    private function get_post_type_options() {
        $options = array( 'post' => __( '文章', 'developer-starter' ) );
        if ( ! function_exists( 'get_post_types' ) ) {
            return $options;
        }

        $post_types = get_post_types( array( 'public' => true ), 'objects' );
        foreach ( $post_types as $post_type => $object ) {
            if ( 'attachment' === $post_type ) {
                continue;
            }
            $options[ sanitize_key( $post_type ) ] = isset( $object->labels->singular_name ) && '' !== $object->labels->singular_name
                ? (string) $object->labels->singular_name
                : (string) $object->label;
        }

        return $options;
    }

    private function get_content_model_options() {
        $options = array( 'post' => __( '文章', 'developer-starter' ) );
        $models  = $this->get_content_model_map();

        foreach ( $models as $model_id => $model ) {
            if ( ! is_array( $model ) ) {
                continue;
            }
            $label = isset( $model['label'] ) ? (string) $model['label'] : (string) $model_id;
            if ( '' !== $label ) {
                $options[ sanitize_key( (string) $model_id ) ] = $label;
            }
        }

        return $options;
    }

    private function get_content_model_post_type( $model_id ) {
        $model_id = sanitize_key( (string) $model_id );
        if ( '' === $model_id ) {
            return '';
        }

        $models = $this->get_content_model_map();
        if ( isset( $models[ $model_id ] ) && is_array( $models[ $model_id ] ) ) {
            $post_type = '';
            if ( isset( $models[ $model_id ]['postType'] ) ) {
                $post_type = sanitize_key( (string) $models[ $model_id ]['postType'] );
            } elseif ( isset( $models[ $model_id ]['post_type'] ) ) {
                $post_type = sanitize_key( (string) $models[ $model_id ]['post_type'] );
            }

            if ( '' !== $post_type ) {
                return $post_type;
            }
        }

        return $this->sanitize_public_post_type( $model_id );
    }

    private function get_content_model_map() {
        $models = array();

        if ( function_exists( 'developer_starter_get_content_model_client_payload' ) ) {
            $payload = developer_starter_get_content_model_client_payload();
            foreach ( array( 'models', 'allModels' ) as $bucket ) {
                if ( empty( $payload[ $bucket ] ) || ! is_array( $payload[ $bucket ] ) ) {
                    continue;
                }
                foreach ( $payload[ $bucket ] as $model_id => $model ) {
                    if ( is_array( $model ) ) {
                        $models[ sanitize_key( (string) $model_id ) ] = $model;
                    }
                }
            }
        }

        if ( function_exists( 'developer_starter_get_content_model_definitions' ) ) {
            foreach ( developer_starter_get_content_model_definitions() as $model_id => $model ) {
                if ( is_array( $model ) && ! isset( $models[ sanitize_key( (string) $model_id ) ] ) ) {
                    $models[ sanitize_key( (string) $model_id ) ] = $model;
                }
            }
        }

        return (array) apply_filters( 'developer_starter_query_loop_content_models', $models );
    }

    private function sanitize_public_post_type( $post_type ) {
        $post_type = sanitize_key( (string) $post_type );
        if ( '' === $post_type ) {
            return 'post';
        }

        if ( function_exists( 'post_type_exists' ) && post_type_exists( $post_type ) ) {
            $object = get_post_type_object( $post_type );
            if ( $object && ! empty( $object->public ) ) {
                return $post_type;
            }
        }

        return 'post';
    }

    private function add_tax_query( &$args, $taxonomy, $raw_terms ) {
        $taxonomy = sanitize_key( (string) $taxonomy );
        if ( '' === $taxonomy || ! taxonomy_exists( $taxonomy ) ) {
            return;
        }

        $parsed = $this->parse_identifier_list( $raw_terms );
        $queries = array();
        if ( ! empty( $parsed['ids'] ) ) {
            $queries[] = array(
                'taxonomy' => $taxonomy,
                'field'    => 'term_id',
                'terms'    => $parsed['ids'],
            );
        }
        if ( ! empty( $parsed['slugs'] ) ) {
            $queries[] = array(
                'taxonomy' => $taxonomy,
                'field'    => 'slug',
                'terms'    => $parsed['slugs'],
            );
        }

        if ( empty( $queries ) ) {
            return;
        }

        $args['tax_query'] = count( $queries ) > 1
            ? array_merge( array( 'relation' => 'OR' ), $queries )
            : $queries;
    }

    private function parse_identifier_list( $raw_value ) {
        $ids   = array();
        $slugs = array();
        $parts = preg_split( '/[\s,，]+/', (string) $raw_value );

        foreach ( $parts as $part ) {
            $part = trim( (string) $part );
            if ( '' === $part ) {
                continue;
            }
            if ( ctype_digit( $part ) ) {
                $ids[] = absint( $part );
            } else {
                $slug = sanitize_title( $part );
                if ( '' !== $slug ) {
                    $slugs[] = $slug;
                }
            }
        }

        return array(
            'ids'   => array_values( array_unique( array_filter( $ids ) ) ),
            'slugs' => array_values( array_unique( array_filter( $slugs ) ) ),
        );
    }

    private function get_post_image_url( $post_id ) {
        if ( function_exists( 'developer_starter_get_thumbnail_url' ) ) {
            $url = developer_starter_get_thumbnail_url( $post_id, 'medium_large' );
            if ( '' !== $url ) {
                return (string) $url;
            }
        }

        if ( function_exists( 'developer_starter_get_featured_image_url' ) ) {
            $url = developer_starter_get_featured_image_url( $post_id, 'medium_large' );
            if ( '' !== $url ) {
                return (string) $url;
            }
        }

        if ( has_post_thumbnail( $post_id ) ) {
            $url = get_the_post_thumbnail_url( $post_id, 'medium_large' );
            if ( $url ) {
                return (string) $url;
            }
        }

        return '';
    }

    private function get_post_excerpt( \WP_Post $post, $length ) {
        if ( has_excerpt( $post ) ) {
            $excerpt = get_the_excerpt( $post );
        } else {
            $excerpt = wp_strip_all_tags( strip_shortcodes( (string) $post->post_content ) );
        }

        return wp_trim_words( $excerpt, max( 5, (int) $length ), '...' );
    }

    private function get_primary_term_html( $post_id ) {
        $taxonomy = $this->get_primary_taxonomy_for_post( $post_id );
        if ( '' === $taxonomy ) {
            return '';
        }

        $terms = get_the_terms( $post_id, $taxonomy );
        if ( empty( $terms ) || is_wp_error( $terms ) ) {
            return '';
        }

        $term = reset( $terms );
        if ( ! $term instanceof \WP_Term ) {
            return '';
        }

        $link = get_term_link( $term );
        if ( is_wp_error( $link ) || '' === $link ) {
            return esc_html( $term->name );
        }

        return '<a href="' . esc_url( $link ) . '">' . esc_html( $term->name ) . '</a>';
    }

    private function get_primary_term_label( $post_id ) {
        return wp_strip_all_tags( $this->get_primary_term_html( $post_id ) );
    }

    private function get_primary_taxonomy_for_post( $post_id ) {
        $post_type = get_post_type( $post_id );
        if ( ! $post_type ) {
            return '';
        }

        $preferred = array( 'category', sanitize_key( $post_type . '_category' ), 'post_tag', sanitize_key( $post_type . '_tag' ) );
        foreach ( $preferred as $taxonomy ) {
            if ( taxonomy_exists( $taxonomy ) && is_object_in_taxonomy( $post_type, $taxonomy ) ) {
                return $taxonomy;
            }
        }

        $taxonomies = get_object_taxonomies( $post_type, 'objects' );
        foreach ( $taxonomies as $taxonomy => $object ) {
            if ( ! empty( $object->public ) && ! empty( $object->hierarchical ) ) {
                return (string) $taxonomy;
            }
        }

        foreach ( $taxonomies as $taxonomy => $object ) {
            if ( ! empty( $object->public ) ) {
                return (string) $taxonomy;
            }
        }

        return '';
    }
}
