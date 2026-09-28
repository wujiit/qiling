<?php
/**
 * 动态数据绑定管理器。
 *
 * Enables the `_ds_dynamic` module data protocol and resolves basic WordPress
 * 在模块渲染前注入动态上下文。
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Core;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Dynamic_Data_Manager {

    const DYNAMIC_KEY = '_ds_dynamic';

    /**
     * 单例实例。
     *
     * @var Dynamic_Data_Manager|null
     */
    private static $instance = null;

    /**
     * 供页面构建器 AJAX 预览使用的临时渲染上下文。
     *
     * @var array<int,array<string,mixed>>
     */
    private $context_stack = array();

    /**
     * 获取单例实例。
     *
     * @return Dynamic_Data_Manager
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * 构造函数.
     */
    private function __construct() {
        add_filter( 'developer_starter_module_data', array( $this, 'resolve_module_data' ), 20, 3 );
    }

    /**
     * 模块绑定的动态数据源注册表。
     *
     * @return array<string,array<string,string>>
     */
    public function get_sources() {
        $post_group = __( '当前文章', 'developer-starter' );
        $loop_group = __( '当前循环项', 'developer-starter' );
        $query_group = __( '当前查询', 'developer-starter' );
        $term_group = __( '当前分类/标签', 'developer-starter' );
        $site_group = __( '站点信息', 'developer-starter' );

        $sources = array(
            'post.title' => array(
                'label'      => __( '当前文章标题', 'developer-starter' ),
                'group'      => 'post',
                'groupLabel' => $post_group,
                'valueType'  => 'text',
            ),
            'post.content' => array(
                'label'      => __( '当前文章正文', 'developer-starter' ),
                'group'      => 'post',
                'groupLabel' => $post_group,
                'valueType'  => 'html',
            ),
            'post.excerpt' => array(
                'label'      => __( '当前文章摘要', 'developer-starter' ),
                'group'      => 'post',
                'groupLabel' => $post_group,
                'valueType'  => 'text',
            ),
            'post.featured_image' => array(
                'label'      => __( '当前文章特色图', 'developer-starter' ),
                'group'      => 'post',
                'groupLabel' => $post_group,
                'valueType'  => 'image',
            ),
            'post.link' => array(
                'label'      => __( '当前文章链接', 'developer-starter' ),
                'group'      => 'post',
                'groupLabel' => $post_group,
                'valueType'  => 'url',
            ),
            'post.author' => array(
                'label'      => __( '当前作者', 'developer-starter' ),
                'group'      => 'post',
                'groupLabel' => $post_group,
                'valueType'  => 'text',
            ),
            'post.date' => array(
                'label'      => __( '当前发布时间', 'developer-starter' ),
                'group'      => 'post',
                'groupLabel' => $post_group,
                'valueType'  => 'text',
            ),
            'post.categories' => array(
                'label'      => __( '当前文章分类', 'developer-starter' ),
                'group'      => 'post',
                'groupLabel' => $post_group,
                'valueType'  => 'text',
            ),
            'post.tags' => array(
                'label'      => __( '当前文章标签', 'developer-starter' ),
                'group'      => 'post',
                'groupLabel' => $post_group,
                'valueType'  => 'text',
            ),
            'post.terms' => array(
                'label'      => __( '当前分类/标签', 'developer-starter' ),
                'group'      => 'post',
                'groupLabel' => $post_group,
                'valueType'  => 'text',
            ),
            'loop.title' => array(
                'label'      => __( '循环项标题', 'developer-starter' ),
                'group'      => 'loop',
                'groupLabel' => $loop_group,
                'valueType'  => 'text',
            ),
            'loop.featured_image' => array(
                'label'      => __( '循环项图片', 'developer-starter' ),
                'group'      => 'loop',
                'groupLabel' => $loop_group,
                'valueType'  => 'image',
            ),
            'loop.excerpt' => array(
                'label'      => __( '循环项摘要', 'developer-starter' ),
                'group'      => 'loop',
                'groupLabel' => $loop_group,
                'valueType'  => 'text',
            ),
            'loop.link' => array(
                'label'      => __( '循环项链接', 'developer-starter' ),
                'group'      => 'loop',
                'groupLabel' => $loop_group,
                'valueType'  => 'url',
            ),
            'loop.categories' => array(
                'label'      => __( '循环项分类', 'developer-starter' ),
                'group'      => 'loop',
                'groupLabel' => $loop_group,
                'valueType'  => 'text',
            ),
            'loop.date' => array(
                'label'      => __( '循环项日期', 'developer-starter' ),
                'group'      => 'loop',
                'groupLabel' => $loop_group,
                'valueType'  => 'text',
            ),
            'archive.title' => array(
                'label'      => __( '当前归档标题', 'developer-starter' ),
                'group'      => 'query',
                'groupLabel' => $query_group,
                'valueType'  => 'text',
            ),
            'archive.description' => array(
                'label'      => __( '当前归档描述', 'developer-starter' ),
                'group'      => 'query',
                'groupLabel' => $query_group,
                'valueType'  => 'text',
            ),
            'search.query' => array(
                'label'      => __( '当前搜索词', 'developer-starter' ),
                'group'      => 'query',
                'groupLabel' => $query_group,
                'valueType'  => 'text',
            ),
            'search.results_count' => array(
                'label'      => __( '搜索结果数量', 'developer-starter' ),
                'group'      => 'query',
                'groupLabel' => $query_group,
                'valueType'  => 'text',
            ),
            'author.name' => array(
                'label'      => __( '当前作者名称', 'developer-starter' ),
                'group'      => 'query',
                'groupLabel' => $query_group,
                'valueType'  => 'text',
            ),
            'term.name' => array(
                'label'      => __( '当前分类/标签名称', 'developer-starter' ),
                'group'      => 'term',
                'groupLabel' => $term_group,
                'valueType'  => 'text',
            ),
            'term.description' => array(
                'label'      => __( '当前分类/标签描述', 'developer-starter' ),
                'group'      => 'term',
                'groupLabel' => $term_group,
                'valueType'  => 'text',
            ),
            'category.name' => array(
                'label'      => __( '当前分类名称', 'developer-starter' ),
                'group'      => 'term',
                'groupLabel' => $term_group,
                'valueType'  => 'text',
            ),
            'category.description' => array(
                'label'      => __( '当前分类描述', 'developer-starter' ),
                'group'      => 'term',
                'groupLabel' => $term_group,
                'valueType'  => 'text',
            ),
            'site.title' => array(
                'label'      => __( '站点标题', 'developer-starter' ),
                'group'      => 'site',
                'groupLabel' => $site_group,
                'valueType'  => 'text',
            ),
            'site.tagline' => array(
                'label'      => __( '站点副标题', 'developer-starter' ),
                'group'      => 'site',
                'groupLabel' => $site_group,
                'valueType'  => 'text',
            ),
            'site.home_url' => array(
                'label'      => __( '首页链接', 'developer-starter' ),
                'group'      => 'site',
                'groupLabel' => $site_group,
                'valueType'  => 'url',
            ),
        );

        return (array) apply_filters( 'developer_starter_dynamic_data_sources', $sources );
    }

    /**
     * 暴露给前端页面构建器的数据载荷。
     *
     * @return array<string,mixed>
     */
    public function get_client_payload() {
        $sources = array();
        $groups  = array();

        foreach ( $this->get_sources() as $source_id => $source ) {
            if ( ! is_array( $source ) ) {
                continue;
            }

            $source_id = $this->normalize_source_id( $source_id );
            if ( '' === $source_id ) {
                continue;
            }

            $group_key   = isset( $source['group'] ) ? sanitize_key( (string) $source['group'] ) : 'general';
            $group_label = isset( $source['groupLabel'] ) ? (string) $source['groupLabel'] : $group_key;
            if ( '' === $group_key ) {
                $group_key = 'general';
            }

            if ( ! isset( $groups[ $group_key ] ) ) {
                $groups[ $group_key ] = array(
                    'key'   => $group_key,
                    'label' => $group_label,
                );
            }

            $sources[] = array(
                'id'        => $source_id,
                'label'     => isset( $source['label'] ) ? (string) $source['label'] : $source_id,
                'group'     => $group_key,
                'groupLabel'=> $group_label,
                'valueType' => isset( $source['valueType'] ) ? sanitize_key( (string) $source['valueType'] ) : 'text',
            );
        }

        return array(
            'dynamicKey' => self::DYNAMIC_KEY,
            'groups'     => array_values( $groups ),
            'sources'    => $sources,
        );
    }

    /**
     * 为当前渲染操作添加临时的文章上下文。
     *
     * @param int $post_id Post ID.
     * @return void
     */
    public function push_post_context( $post_id ) {
        $this->context_stack[] = $this->build_context_from_post_id( $post_id );
    }

    /**
     * 为查询循环渲染添加临时的循环项上下文。
     *
     * @param int $post_id Post ID.
     * @return void
     */
    public function push_loop_item_context( $post_id ) {
        $context = $this->build_context_from_post_id( $post_id );
        $context['loopItem'] = true;
        $this->context_stack[] = $context;
    }

    /**
     * 弹出并移除最近添加的临时动态上下文。
     *
     * @return void
     */
    public function pop_context() {
        array_pop( $this->context_stack );
    }

    /**
     * 在渲染前解析模块数据中的动态绑定数据。
     *
     * @param mixed  $data      Module data.
     * @param string $module_id Module ID.
     * @param object $module    Module instance.
     * @return mixed
     */
    public function resolve_module_data( $data, $module_id = '', $module = null ) {
        unset( $module_id, $module );

        if ( ! is_array( $data ) ) {
            return $data;
        }

        $bindings = $this->normalize_bindings(
            isset( $data[ self::DYNAMIC_KEY ] ) ? $data[ self::DYNAMIC_KEY ] : array()
        );
        if ( empty( $bindings ) ) {
            return $data;
        }

        $context = $this->get_current_context();
        foreach ( $bindings as $field_id => $binding ) {
            $source = isset( $binding['source'] ) ? $this->normalize_source_id( $binding['source'] ) : '';
            if ( '' === $field_id || '' === $source ) {
                continue;
            }

            $value = $this->resolve_source( $source, $context );
            if ( null !== $value ) {
                $data[ $field_id ] = $value;
            }
        }

        return $data;
    }

    /**
     * Normalize `_ds_dynamic` bindings.
     *
     * @param mixed $bindings Raw bindings.
     * @return array<string,array<string,string>>
     */
    public function normalize_bindings( $bindings ) {
        $normalized = array();
        if ( ! is_array( $bindings ) ) {
            return $normalized;
        }

        $sources = $this->get_sources();
        foreach ( $bindings as $field_id => $binding ) {
            $field_id = $this->sanitize_field_id( $field_id );
            if ( '' === $field_id ) {
                continue;
            }

            $source = '';
            if ( is_array( $binding ) && isset( $binding['source'] ) && is_scalar( $binding['source'] ) ) {
                $source = $this->normalize_source_id( $binding['source'] );
            } elseif ( is_scalar( $binding ) ) {
                $source = $this->normalize_source_id( $binding );
            }

            if ( '' === $source || ! isset( $sources[ $source ] ) ) {
                continue;
            }

            $normalized[ $field_id ] = array(
                'source' => $source,
            );
        }

        return $normalized;
    }

    /**
     * 规范化数据源 ID 并保留点号命名空间。
     *
     * @param mixed $source Source ID.
     * @return string
     */
    public function normalize_source_id( $source ) {
        if ( ! is_scalar( $source ) ) {
            return '';
        }

        $source = strtolower( trim( (string) $source ) );
        $source = preg_replace( '/[^a-z0-9_\.\-]/', '', $source );

        return is_string( $source ) ? $source : '';
    }

    /**
     * 获取当前请求的动态数据上下文。
     *
     * @return array<string,mixed>
     */
    public function get_current_context() {
        if ( ! empty( $this->context_stack ) ) {
            $context = end( $this->context_stack );
            return is_array( $context ) ? $context : array();
        }

        $context = $this->build_context_from_current_query();

        return (array) apply_filters( 'developer_starter_dynamic_data_context', $context );
    }

    /**
     * 根据当前全局查询构建动态上下文。
     *
     * @return array<string,mixed>
     */
    public function build_context_from_current_query() {
        $queried = function_exists( 'get_queried_object' ) ? get_queried_object() : null;
        $post    = $queried instanceof \WP_Post ? $queried : null;
        $term    = $queried instanceof \WP_Term ? $queried : null;

        if ( ! $post ) {
            global $post;
            if ( $post instanceof \WP_Post ) {
                $post = get_post( $post->ID );
            }
        }

        if ( ! $post && function_exists( 'get_the_ID' ) ) {
            $post_id = (int) get_the_ID();
            if ( $post_id > 0 ) {
                $post = get_post( $post_id );
            }
        }

        $context = $this->build_context( $post, $term );

        if ( is_archive() || is_search() ) {
            $context['archiveTitle'] = function_exists( 'get_the_archive_title' )
                ? wp_strip_all_tags( (string) get_the_archive_title() )
                : '';
            $context['archiveDescription'] = function_exists( 'get_the_archive_description' )
                ? wp_strip_all_tags( (string) get_the_archive_description() )
                : '';
        }

        if ( is_search() ) {
            global $wp_query;
            $context['searchQuery'] = function_exists( 'get_search_query' ) ? rawurldecode( (string) get_search_query( false ) ) : '';
            $context['searchResultsCount'] = $wp_query instanceof \WP_Query ? max( 0, (int) $wp_query->found_posts ) : 0;
            if ( empty( $context['archiveTitle'] ) ) {
                $context['archiveTitle'] = '' !== $context['searchQuery']
                    ? sprintf( __( '搜索：%s', 'developer-starter' ), $context['searchQuery'] )
                    : __( '搜索结果', 'developer-starter' );
            }
        }

        if ( is_author() ) {
            $author = get_queried_object();
            if ( $author instanceof \WP_User ) {
                $context['author'] = $author;
                $context['authorName'] = (string) $author->display_name;
            }
        }

        return $context;
    }

    /**
     * 根据文章 ID 构建动态上下文。
     *
     * @param int $post_id Post ID.
     * @return array<string,mixed>
     */
    public function build_context_from_post_id( $post_id ) {
        $post = get_post( absint( $post_id ) );

        return $this->build_context( $post instanceof \WP_Post ? $post : null, null );
    }

    /**
     * 从当前上下文中解析单个动态数据源。
     *
     * @param string              $source  Source ID.
     * @param array<string,mixed> $context Dynamic context.
     * @return string|null
     */
    public function resolve_source( $source, $context = array() ) {
        $source  = $this->normalize_source_id( $source );
        $context = is_array( $context ) ? $context : array();
        $post    = isset( $context['post'] ) && $context['post'] instanceof \WP_Post ? $context['post'] : null;

        switch ( $source ) {
            case 'post.title':
                return $post ? get_the_title( $post ) : '';

            case 'post.content':
                return $post ? $this->get_post_content( $post ) : '';

            case 'post.excerpt':
                return $post ? $this->get_post_excerpt( $post ) : '';

            case 'post.featured_image':
                return $post ? $this->get_post_featured_image_url( $post->ID ) : '';

            case 'post.link':
                return $post ? (string) get_permalink( $post ) : '';

            case 'post.author':
                return $post ? (string) get_the_author_meta( 'display_name', (int) $post->post_author ) : '';

            case 'post.date':
                return $post ? (string) get_the_date( '', $post ) : '';

            case 'post.categories':
                return $post ? $this->get_post_primary_taxonomy_names( $post->ID, 'category', true ) : '';

            case 'post.tags':
                return $post ? $this->get_post_primary_taxonomy_names( $post->ID, 'post_tag', false ) : '';

            case 'post.terms':
                if ( ! $post ) {
                    return '';
                }
                $terms = array_filter(
                    array(
                        $this->get_post_primary_taxonomy_names( $post->ID, 'category', true ),
                        $this->get_post_primary_taxonomy_names( $post->ID, 'post_tag', false ),
                    )
                );
                return implode( ', ', $terms );

            case 'loop.title':
                return $this->resolve_source( 'post.title', $context );

            case 'loop.featured_image':
                return $this->resolve_source( 'post.featured_image', $context );

            case 'loop.excerpt':
                return $this->resolve_source( 'post.excerpt', $context );

            case 'loop.link':
                return $this->resolve_source( 'post.link', $context );

            case 'loop.categories':
                return $this->resolve_source( 'post.categories', $context );

            case 'loop.date':
                return $this->resolve_source( 'post.date', $context );

            case 'archive.title':
                return isset( $context['archiveTitle'] ) ? (string) $context['archiveTitle'] : '';

            case 'archive.description':
                return isset( $context['archiveDescription'] ) ? (string) $context['archiveDescription'] : '';

            case 'search.query':
                return isset( $context['searchQuery'] ) ? (string) $context['searchQuery'] : '';

            case 'search.results_count':
                return isset( $context['searchResultsCount'] ) ? number_format_i18n( (int) $context['searchResultsCount'] ) : '0';

            case 'author.name':
                if ( isset( $context['authorName'] ) ) {
                    return (string) $context['authorName'];
                }
                $author = isset( $context['author'] ) && $context['author'] instanceof \WP_User ? $context['author'] : null;
                return $author ? (string) $author->display_name : '';

            case 'term.name':
                $term = $this->get_context_term( $context );
                return $term ? $term->name : '';

            case 'term.description':
                $term = $this->get_context_term( $context );
                return $term ? $this->get_term_description( $term ) : '';

            case 'category.name':
                $term = $this->get_context_term( $context, 'category' );
                return $term ? $term->name : '';

            case 'category.description':
                $term = $this->get_context_term( $context, 'category' );
                return $term ? $this->get_term_description( $term ) : '';

            case 'site.title':
                return (string) get_bloginfo( 'name' );

            case 'site.tagline':
                return (string) get_bloginfo( 'description' );

            case 'site.home_url':
                return (string) home_url( '/' );
        }

        return null;
    }

    /**
     * 检查构建器字段类型是否支持动态数据绑定。
     *
     * @param string $field_type Field type.
     * @return bool
     */
    public static function is_supported_field_type( $field_type ) {
        $field_type = sanitize_key( (string) $field_type );
        if ( '' === $field_type ) {
            $field_type = 'text';
        }

        return in_array(
            $field_type,
            array( 'text', 'textarea', 'editor', 'image', 'upload', 'file', 'gallery', 'url', 'link' ),
            true
        );
    }

    /**
     * 构建动态上下文数据载荷。
     *
     * @param \WP_Post|null $post Post object.
     * @param \WP_Term|null $term Term object.
     * @return array<string,mixed>
     */
    private function build_context( $post = null, $term = null ) {
        $post = $post instanceof \WP_Post ? $post : null;
        $term = $term instanceof \WP_Term ? $term : null;

        if ( ! $term && $post ) {
            $term = $this->get_primary_post_term( $post->ID, 'category' );
        }

        return array(
            'post'   => $post,
            'postId' => $post ? (int) $post->ID : 0,
            'term'   => $term,
            'termId' => $term ? (int) $term->term_id : 0,
        );
    }

    /**
     * 获取当前上下文中的 Term 对象（可限制分类法）。
     *
     * @param array<string,mixed> $context  Context.
     * @param string              $taxonomy Taxonomy.
     * @return \WP_Term|null
     */
    private function get_context_term( $context, $taxonomy = '' ) {
        $taxonomy = sanitize_key( (string) $taxonomy );
        $term     = isset( $context['term'] ) && $context['term'] instanceof \WP_Term ? $context['term'] : null;

        if ( $term && ( '' === $taxonomy || $term->taxonomy === $taxonomy ) ) {
            return $term;
        }

        $post = isset( $context['post'] ) && $context['post'] instanceof \WP_Post ? $context['post'] : null;
        if ( ! $post ) {
            return null;
        }

        return $this->get_primary_post_term( $post->ID, '' !== $taxonomy ? $taxonomy : 'category' );
    }

    /**
     * 获取文章所属的首个分类或标签。
     *
     * @param int    $post_id  Post ID.
     * @param string $taxonomy Taxonomy.
     * @return \WP_Term|null
     */
    private function get_primary_post_term( $post_id, $taxonomy ) {
        $terms = get_the_terms( absint( $post_id ), sanitize_key( (string) $taxonomy ) );
        if ( is_wp_error( $terms ) || empty( $terms ) || ! is_array( $terms ) ) {
            return null;
        }

        $first = reset( $terms );
        return $first instanceof \WP_Term ? $first : null;
    }

    /**
     * 获取文章所属分类或标签的逗号分隔名称。
     *
     * @param int    $post_id  Post ID.
     * @param string $taxonomy Taxonomy.
     * @return string
     */
    private function get_post_term_names( $post_id, $taxonomy ) {
        $terms = get_the_terms( absint( $post_id ), sanitize_key( (string) $taxonomy ) );
        if ( is_wp_error( $terms ) || empty( $terms ) || ! is_array( $terms ) ) {
            return '';
        }

        $names = array();
        foreach ( $terms as $term ) {
            if ( $term instanceof \WP_Term && '' !== $term->name ) {
                $names[] = $term->name;
            }
        }

        return implode( ', ', array_values( array_unique( $names ) ) );
    }

    /**
     * 从优先分类法获取分类名称列表，回退至公开分类法。
     *
     * @param int    $post_id              Post ID.
     * @param string $preferred_taxonomy   Preferred taxonomy.
     * @param bool   $prefer_hierarchical  Whether hierarchical taxonomies are preferred.
     * @return string
     */
    private function get_post_primary_taxonomy_names( $post_id, $preferred_taxonomy, $prefer_hierarchical = true ) {
        $post_id = absint( $post_id );
        if ( $post_id <= 0 ) {
            return '';
        }

        $preferred_taxonomy = sanitize_key( (string) $preferred_taxonomy );
        $names = '' !== $preferred_taxonomy ? $this->get_post_term_names( $post_id, $preferred_taxonomy ) : '';
        if ( '' !== $names ) {
            return $names;
        }

        $post_type = get_post_type( $post_id );
        if ( ! $post_type ) {
            return '';
        }

        $candidate_taxonomies = array();
        if ( '' !== $preferred_taxonomy ) {
            $candidate_taxonomies[] = sanitize_key( $post_type . '_' . str_replace( 'post_', '', $preferred_taxonomy ) );
        }

        $taxonomies = get_object_taxonomies( $post_type, 'objects' );
        if ( is_array( $taxonomies ) ) {
            foreach ( $taxonomies as $taxonomy => $object ) {
                if ( empty( $object->public ) ) {
                    continue;
                }
                if ( (bool) $object->hierarchical === (bool) $prefer_hierarchical ) {
                    $candidate_taxonomies[] = (string) $taxonomy;
                }
            }

            foreach ( $taxonomies as $taxonomy => $object ) {
                if ( ! empty( $object->public ) ) {
                    $candidate_taxonomies[] = (string) $taxonomy;
                }
            }
        }

        foreach ( array_values( array_unique( array_filter( $candidate_taxonomies ) ) ) as $taxonomy ) {
            if ( ! taxonomy_exists( $taxonomy ) || ! is_object_in_taxonomy( $post_type, $taxonomy ) ) {
                continue;
            }
            $names = $this->get_post_term_names( $post_id, $taxonomy );
            if ( '' !== $names ) {
                return $names;
            }
        }

        return '';
    }

    /**
     * 获取纯文本摘要内容。
     *
     * @param \WP_Post $post Post.
     * @return string
     */
    private function get_post_excerpt( \WP_Post $post ) {
        if ( has_excerpt( $post ) ) {
            return (string) get_the_excerpt( $post );
        }

        return wp_trim_words( wp_strip_all_tags( $post->post_content ), 55, '...' );
    }

    /**
     * 在保护全局文章状态的同时获取经过滤的正文。
     *
     * @param \WP_Post $post Post.
     * @return string
     */
    private function get_post_content( \WP_Post $post ) {
        $previous_post = isset( $GLOBALS['post'] ) ? $GLOBALS['post'] : null;

        $GLOBALS['post'] = $post;
        setup_postdata( $post );
        $content = apply_filters( 'the_content', $post->post_content );

        if ( $previous_post instanceof \WP_Post ) {
            $GLOBALS['post'] = $previous_post;
            setup_postdata( $previous_post );
        } else {
            wp_reset_postdata();
            unset( $GLOBALS['post'] );
        }

        return is_string( $content ) ? $content : '';
    }

    /**
     * 解析特色图片 URL（支持主题自定义特色图地址）。
     *
     * @param int $post_id Post ID.
     * @return string
     */
    private function get_post_featured_image_url( $post_id ) {
        $post_id = absint( $post_id );
        if ( $post_id <= 0 ) {
            return '';
        }

        if ( function_exists( 'developer_starter_get_featured_image_url' ) ) {
            $url = developer_starter_get_featured_image_url( $post_id, 'full' );
            if ( is_string( $url ) && '' !== $url ) {
                return $url;
            }
        }

        $custom_url = get_post_meta( $post_id, '_developer_starter_featured_image_url', true );
        if ( is_string( $custom_url ) && '' !== trim( $custom_url ) ) {
            return esc_url_raw( $custom_url );
        }

        if ( has_post_thumbnail( $post_id ) ) {
            $url = get_the_post_thumbnail_url( $post_id, 'full' );
            return is_string( $url ) ? $url : '';
        }

        return '';
    }

    /**
     * 获取分类或标签的纯文本描述。
     *
     * @param \WP_Term $term Term.
     * @return string
     */
    private function get_term_description( \WP_Term $term ) {
        $description = term_description( $term->term_id, $term->taxonomy );
        if ( ! is_string( $description ) ) {
            return '';
        }

        return trim( wp_strip_all_tags( $description ) );
    }

    /**
     * 清理动态绑定的目标字段标识 ID。
     *
     * @param mixed $field_id Field ID.
     * @return string
     */
    private function sanitize_field_id( $field_id ) {
        if ( ! is_scalar( $field_id ) ) {
            return '';
        }

        $field_id = trim( (string) $field_id );
        $field_id = preg_replace( '/[^A-Za-z0-9_\-\.]/', '', $field_id );

        return is_string( $field_id ) ? $field_id : '';
    }
}
