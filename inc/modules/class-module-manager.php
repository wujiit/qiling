<?php
/**
 * 模块管理器类
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Modules;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Module_Manager {

    private static $instance = null;
    private $modules = array();
    private $default_module_registry = null;
    private $module_manifest = array();
    private $render_context = 'page';
    private $local_business_module_ids = array( 'branches' );

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // 按需加载：仅在页面相关请求中初始化模块，避免后台全局开销。
    }

    public function register_default_modules() {
        if ( ! empty( $this->modules ) ) {
            return;
        }

        foreach ( $this->get_default_module_registry() as $manifest_entry ) {
            $this->register_module_from_manifest_entry( $manifest_entry );
        }
    }

    /**
     * 获取默认模块注册表。
     *
     * 支持平铺或分组数组，也支持通过 filter 扩展注册表。
     *
     * @return array<int,array<string,mixed>>
     */
    private function get_default_module_registry() {
        if ( null !== $this->default_module_registry ) {
            return $this->default_module_registry;
        }

        $registry_file = __DIR__ . '/module-registry.php';
        $registry = file_exists( $registry_file ) ? require $registry_file : array();
        if ( ! is_array( $registry ) ) {
            $registry = array();
        }

        $registry = apply_filters( 'developer_starter_module_registry', $registry );

        $normalized = array();
        $this->flatten_module_registry( $registry, $normalized );
        $this->default_module_registry = array_values( $normalized );

        return $this->default_module_registry;
    }

    /**
     * 递归扁平化模块注册表。
     *
     * @param mixed                        $entries    注册表原始数据。
     * @param array<string,array<string,mixed>> $normalized 归一化后的注册表索引。
     * @param string                       $group      当前分组键。
     * @return void
     */
    private function flatten_module_registry( $entries, array &$normalized, $group = 'general' ) {
        if ( is_string( $entries ) ) {
            $this->add_module_manifest_to_registry(
                array(
                    'class' => $entries,
                    'group' => $group,
                ),
                $normalized
            );
            return;
        }

        if ( ! is_array( $entries ) ) {
            return;
        }

        if ( isset( $entries['class'] ) && is_string( $entries['class'] ) ) {
            if ( empty( $entries['group'] ) ) {
                $entries['group'] = $group;
            }
            $this->add_module_manifest_to_registry( $entries, $normalized );
            return;
        }

        foreach ( $entries as $entry_key => $entry ) {
            $next_group = $group;
            if ( is_string( $entry_key ) && $entry_key !== '' ) {
                $next_group = sanitize_key( $entry_key );
            }

            $this->flatten_module_registry( $entry, $normalized, $next_group );
        }
    }

    /**
     * 将模块 manifest 条目加入归一化注册表。
     *
     * @param array<string,mixed>              $entry      模块 manifest 条目。
     * @param array<string,array<string,mixed>> $normalized 归一化后的 manifest 索引。
     * @return void
     */
    private function add_module_manifest_to_registry( $entry, array &$normalized ) {
        $entry = $this->normalize_module_manifest_entry( $entry );
        if ( empty( $entry['class'] ) ) {
            return;
        }

        $normalized[ $entry['class'] ] = $entry;
    }

    /**
     * 规范化模块 manifest 条目。
     *
     * @param array<string,mixed> $entry 原始条目。
     * @return array<string,mixed>
     */
    private function normalize_module_manifest_entry( $entry ) {
        $entry = is_array( $entry ) ? $entry : array();
        $module_class = isset( $entry['class'] ) ? ltrim( trim( (string) $entry['class'] ), '\\' ) : '';
        $group = isset( $entry['group'] ) ? sanitize_key( (string) $entry['group'] ) : 'general';
        $group_label = isset( $entry['group_label'] ) ? sanitize_text_field( (string) $entry['group_label'] ) : $this->get_module_group_label( $group );
        $keywords = array();

        if ( isset( $entry['keywords'] ) ) {
            $raw_keywords = is_array( $entry['keywords'] ) ? $entry['keywords'] : array( $entry['keywords'] );
            foreach ( $raw_keywords as $keyword ) {
                if ( ! is_scalar( $keyword ) ) {
                    continue;
                }
                $keyword = trim( wp_strip_all_tags( (string) $keyword ) );
                if ( $keyword !== '' ) {
                    $keywords[] = $keyword;
                }
            }
        }

        $normalized_entry = array(
            'class'      => $module_class,
            'group'      => $group !== '' ? $group : 'general',
            'groupLabel' => $group_label !== '' ? $group_label : $this->get_module_group_label( 'general' ),
            'aiEnabled'  => ! isset( $entry['ai_enabled'] ) || (bool) $entry['ai_enabled'],
            'keywords'   => array_values( array_unique( $keywords ) ),
        );

        if ( class_exists( Module_Standards::class ) ) {
            $normalized_entry = array_merge(
                $normalized_entry,
                Module_Standards::normalize_manifest_metadata( $entry )
            );
        }

        return $normalized_entry;
    }

    /**
     * 按 manifest 条目实例化并注册默认模块。
     *
     * @param array<string,mixed> $entry 模块 manifest 条目。
     * @return void
     */
    private function register_module_from_manifest_entry( $entry ) {
        $entry = $this->normalize_module_manifest_entry( $entry );
        $module_class = isset( $entry['class'] ) ? (string) $entry['class'] : '';
        if ( '' === $module_class || ! class_exists( $module_class ) ) {
            return;
        }

        $module = new $module_class();
        if ( $module instanceof Module_Base ) {
            $this->register_module( $module, $entry );
        }
    }

    /**
     * 获取模块分组标签。
     *
     * @return array<string,string>
     */
    private function get_module_group_labels() {
        $labels = array(
            'core'      => __( '基础通用', 'developer-starter' ),
            'software'  => __( '软件应用', 'developer-starter' ),
            'resume'    => __( '个人履历', 'developer-starter' ),
            'qiling'    => __( '启灵特色', 'developer-starter' ),
            'footer'    => __( '页脚模块', 'developer-starter' ),
            'industry'  => __( '行业场景', 'developer-starter' ),
            'general'   => __( '通用模块', 'developer-starter' ),
            'homepage'  => __( '首页模块', 'developer-starter' ),
            'content'   => __( '内容展示', 'developer-starter' ),
            'hero'      => __( '首屏模块', 'developer-starter' ),
            'business'  => __( '商业模块', 'developer-starter' ),
            'marketing' => __( '营销活动', 'developer-starter' ),
            'education' => __( '教育培训', 'developer-starter' ),
            'component' => __( '组件模块', 'developer-starter' ),
            'media'     => __( '媒体模块', 'developer-starter' ),
            'header'    => __( '头部模块', 'developer-starter' ),
        );

        return (array) apply_filters( 'developer_starter_module_group_labels', $labels );
    }

    /**
     * 获取单个分组标签。
     *
     * @param string $group 分组键。
     * @return string
     */
    private function get_module_group_label( $group ) {
        $group = sanitize_key( (string) $group );
        $labels = $this->get_module_group_labels();
        if ( isset( $labels[ $group ] ) ) {
            return (string) $labels[ $group ];
        }

        return $group !== '' ? ucfirst( str_replace( array( '-', '_' ), ' ', $group ) ) : __( '通用模块', 'developer-starter' );
    }

    /**
     * 为已注册模块构建 manifest 元数据。
     *
     * @param Module_Base          $module   模块实例。
     * @param array<string,mixed>  $manifest 注册表元数据。
     * @return array<string,mixed>
     */
    private function build_registered_module_manifest( Module_Base $module, $manifest = array() ) {
        $manifest = is_array( $manifest ) ? $manifest : array();
        $module_id = (string) $module->get_id();
        $category = method_exists( $module, 'get_category' ) ? (string) $module->get_category() : 'general';
        $group = isset( $manifest['group'] ) ? sanitize_key( (string) $manifest['group'] ) : sanitize_key( $category );
        if ( '' === $group ) {
            $group = 'general';
        }

        $group_label = isset( $manifest['groupLabel'] ) ? (string) $manifest['groupLabel'] : $this->get_module_group_label( $group );
        $keywords = isset( $manifest['keywords'] ) && is_array( $manifest['keywords'] ) ? array_values( array_unique( $manifest['keywords'] ) ) : array();
        $keywords[] = $module_id;
        $keywords[] = (string) $module->get_name();
        $keywords = array_values(
            array_unique(
                array_filter(
                    array_map(
                        function( $keyword ) {
                            return is_scalar( $keyword ) ? trim( (string) $keyword ) : '';
                        },
                        $keywords
                    )
                )
            )
        );

        $metadata = array();
        if ( class_exists( Module_Standards::class ) ) {
            $metadata = Module_Standards::merge_module_metadata(
                Module_Standards::infer_module_metadata( $module_id, $module, $manifest ),
                Module_Standards::normalize_manifest_metadata( $manifest )
            );
        }

        $catalog_item = array(
            'id'          => $module_id,
            'name'        => (string) $module->get_name(),
            'category'    => $category !== '' ? $category : 'general',
            'group'       => $group,
            'groupLabel'  => $group_label,
            'keywords'    => $keywords,
            'aiEnabled'   => isset( $manifest['aiEnabled'] ) ? (bool) $manifest['aiEnabled'] : strpos( $module_id, 'qls_fb_' ) !== 0,
            'icon'        => method_exists( $module, 'get_icon' ) ? (string) $module->get_icon() : 'dashicons-layout',
            'description' => method_exists( $module, 'get_description' ) ? (string) $module->get_description() : '',
            'className'   => get_class( $module ),
        );

        if ( ! empty( $metadata ) ) {
            $catalog_item = array_merge( $catalog_item, $metadata );
        }

        return $catalog_item;
    }

    /**
     * 获取模块元数据标准词表。
     *
     * @return array<string,array<string,string>>
     */
    public function get_module_metadata_taxonomy() {
        if ( class_exists( Module_Standards::class ) ) {
            return Module_Standards::get_metadata_taxonomy();
        }

        return array();
    }

    /**
     * 仅在需要时加载全部模块实例。
     *
     * @param bool $force 强制加载（绕过请求上下文判断）。
     * @return void
     */
    private function ensure_modules_loaded( $force = false ) {
        if ( ! empty( $this->modules ) ) {
            return;
        }

        if ( ! $force && ! $this->should_load_modules_for_request() ) {
            return;
        }

        $this->register_default_modules();
    }

    /**
     * 当前请求是否应该加载模块系统。
     *
     * @return bool
     */
    private function should_load_modules_for_request() {
        if ( function_exists( 'wp_doing_ajax' ) && wp_doing_ajax() ) {
            $should_load = $this->is_module_related_ajax_request();
        } elseif ( is_admin() ) {
            $should_load = $this->is_page_editor_request();
        } else {
            $should_load = $this->is_frontend_page_request();
        }

        return (bool) apply_filters( 'developer_starter_should_load_modules_for_request', $should_load );
    }

    /**
     * 前台仅在 page 请求中加载模块。
     *
     * @return bool
     */
    private function is_frontend_page_request() {
        if ( function_exists( 'is_page' ) && is_page() ) {
            return true;
        }

        global $post;
        if ( $post instanceof \WP_Post && isset( $post->post_type ) && $post->post_type === 'page' ) {
            return true;
        }

        return false;
    }

    /**
     * 后台仅在页面编辑器中加载模块。
     *
     * @return bool
     */
    private function is_page_editor_request() {
        global $pagenow;

        if ( ! in_array( $pagenow, array( 'post.php', 'post-new.php' ), true ) ) {
            return false;
        }

        if ( function_exists( 'get_current_screen' ) ) {
            $screen = get_current_screen();
            if ( $screen && isset( $screen->post_type ) ) {
                return $screen->post_type === 'page';
            }
        }

        $request_post_type = isset( $_REQUEST['post_type'] ) ? sanitize_key( wp_unslash( (string) $_REQUEST['post_type'] ) ) : '';
        if ( $request_post_type !== '' ) {
            return $request_post_type === 'page';
        }

        $request_post_id = isset( $_REQUEST['post'] ) ? absint( wp_unslash( $_REQUEST['post'] ) ) : 0;
        if ( $request_post_id > 0 ) {
            return get_post_type( $request_post_id ) === 'page';
        }

        return false;
    }

    /**
     * 与模块系统相关的 AJAX 请求。
     *
     * @return bool
     */
    private function is_module_related_ajax_request() {
        $action = isset( $_REQUEST['action'] ) ? sanitize_key( wp_unslash( (string) $_REQUEST['action'] ) ) : '';
        if ( $action === '' ) {
            return false;
        }

        $allowed_actions = array(
            'qiling_load_modules_editor_ui',
            'qiling_render_module_item',
            'qiling_save_modules_editor_state',
            'qiling_load_template_html',
            'qiling_save_template',
            'qiling_get_templates',
            'qiling_import_page_json_preview',
            'qiling_export_page_json',
            'qiling_frontend_builder_get_schema',
            'qiling_frontend_builder_get_library_template',
            'qiling_frontend_builder_render_module_preview',
            'qiling_frontend_builder_render_preview',
            'qiling_frontend_builder_save_modules',
            'qiling_ai_generate_page_package',
            'qiling_ai_plan_page_package',
            'qiling_ai_generate_page_module',
        );

        return in_array( $action, $allowed_actions, true );
    }

    public function register_module( Module_Base $module, $manifest = array() ) {
        $module_id = (string) $module->get_id();
        $this->modules[ $module_id ] = $module;
        $this->module_manifest[ $module_id ] = $this->build_registered_module_manifest( $module, $manifest );
    }

    /**
     * 获取已注册模块目录信息。
     *
     * @param bool $force 是否强制加载全部模块。
     * @return array<int,array<string,mixed>>
     */
    public function get_module_catalog( $force = false ) {
        $this->ensure_modules_loaded( (bool) $force );
        return array_values(
            array_filter(
                $this->module_manifest,
                function( $item ) {
                    $module_id = isset( $item['id'] ) ? (string) $item['id'] : '';
                    return $this->is_module_visible_in_catalog( $module_id );
                }
            )
        );
    }

    /**
     * Whether a module should be visible in builder catalogs.
     *
     * @param string $module_id Module id.
     * @return bool
     */
    public function is_module_visible_in_catalog( $module_id ) {
        $module_id = sanitize_key( (string) $module_id );
        $visible = true;

        if ( in_array( $module_id, $this->local_business_module_ids, true ) ) {
            $visible = class_exists( '\Developer_Starter\Core\Content_Model_Center' )
                && \Developer_Starter\Core\Content_Model_Center::is_local_business_enabled();
        }

        return (bool) apply_filters( 'developer_starter_module_catalog_item_visible', $visible, $module_id );
    }

    /**
     * 获取模块目录审计报告。
     *
     * @param bool $force 是否强制加载全部模块。
     * @return array<string,mixed>
     */
    public function get_module_catalog_audit( $force = false ) {
        $catalog = $this->get_module_catalog( (bool) $force );
        if ( class_exists( Module_Standards::class ) ) {
            return Module_Standards::build_catalog_audit( $catalog );
        }

        return array(
            'total'       => count( $catalog ),
            'needsReview' => array(),
        );
    }

    /**
     * 获取单个模块的 manifest 元数据。
     *
     * @param string $module_id 模块 ID。
     * @return array<string,mixed>|null
     */
    public function get_module_manifest_item( $module_id ) {
        $this->ensure_modules_loaded();
        $module_id = (string) $module_id;
        return isset( $this->module_manifest[ $module_id ] ) ? $this->module_manifest[ $module_id ] : null;
    }

    /**
     * 清洗模块间距值，仅允许安全的 CSS 长度表达式。
     *
     * @param mixed $value 原始值
     * @return string
     */
    public static function sanitize_spacing_value( $value ) {
        if ( ! is_scalar( $value ) ) {
            return '';
        }

        $value = trim( wp_strip_all_tags( (string) $value ) );
        if ( $value === '' ) {
            return '';
        }

        // 阻断 style 注入链路。
        if ( preg_match( '/[;{}<>]/', $value ) ) {
            return '';
        }

        $lower = strtolower( $value );
        if ( in_array( $lower, array( '0', 'auto' ), true ) ) {
            return $lower;
        }

        // 常规长度：80px / 2rem / 10% / -12px 等
        if ( preg_match( '/^-?(?:\d+|\d*\.\d+)(?:px|rem|em|%|vh|vw|vmin|vmax|ch|ex|cm|mm|in|pt|pc)$/i', $value ) ) {
            return $value;
        }

        // 允许函数表达式：calc()/clamp()/min()/max()/var()
        if ( preg_match( '/^(?:calc|clamp|min|max|var)\([a-z0-9\-\+\*\/%\.,\s\(\)]*\)$/i', $value ) ) {
            return $value;
        }

        return '';
    }

    public function get_module( $id ) {
        $this->ensure_modules_loaded();
        return isset( $this->modules[ $id ] ) ? $this->modules[ $id ] : null;
    }

    public function get_all_modules() {
        $this->ensure_modules_loaded();
        return $this->modules;
    }

    /**
     * 首屏模块 ID 列表（用于透明头部、样式加载、间距和图片优先级策略）。
     *
     * @param string $context 使用场景。
     * @return array<int, string>
     */
    public static function get_hero_module_ids( $context = 'default' ) {
        $context = sanitize_key( (string) $context );
        if ( '' === $context ) {
            $context = 'default';
        }

        $hero_module_ids = array(
            'banner',
            'product_showcase',
            'hero_search',
            'double_column_carousel',
            'dynamic_banner',
            'resource_hero_pro',
            'brand_banner_pro',
            'app_hero',
            'qiling_shop_showcase',
            'fullscreen_video',
            'qiling_video_portal_hero',
            'interact_hero',
            'resume_hero',
        );

        $hero_module_ids = apply_filters( 'developer_starter_hero_module_types', $hero_module_ids, $context );
        if ( 'transparent_header' === $context ) {
            $hero_module_ids = apply_filters( 'developer_starter_transparent_header_hero_module_ids', $hero_module_ids );
        }

        if ( ! is_array( $hero_module_ids ) ) {
            return array();
        }

        return array_values( array_unique( array_filter( array_map( 'sanitize_key', $hero_module_ids ), 'strlen' ) ) );
    }

    /**
     * 清洗模块 wrapper 的额外属性片段。
     *
     * @param string $attr 属性片段。
     * @return string
     */
    public static function sanitize_wrapper_attr_fragment( $attr ) {
        $attr = is_string( $attr ) ? trim( $attr ) : '';
        if ( '' === $attr ) {
            return '';
        }

        $allowed_attrs = apply_filters(
            'developer_starter_module_wrapper_allowed_attrs',
            array( 'id', 'role', 'tabindex' )
        );
        $allowed_prefixes = apply_filters(
            'developer_starter_module_wrapper_allowed_attr_prefixes',
            array( 'data-', 'aria-' )
        );

        $allowed_attrs = is_array( $allowed_attrs )
            ? array_values( array_filter( array_map( 'strtolower', array_map( 'sanitize_key', $allowed_attrs ) ) ) )
            : array();
        $allowed_prefixes = is_array( $allowed_prefixes )
            ? array_values( array_filter( array_map( 'strtolower', array_map( 'sanitize_key', $allowed_prefixes ) ) ) )
            : array();

        if ( ! preg_match_all( '/\s*([a-zA-Z][a-zA-Z0-9_:\-\.]*)(?:\s*=\s*("([^"]*)"|\'([^\']*)\'|([^\s"\'=<>`]+)))?/', $attr, $matches, PREG_SET_ORDER ) ) {
            return '';
        }

        $safe_attrs = array();
        $seen_attrs = array();

        foreach ( $matches as $match ) {
            $name = strtolower( (string) $match[1] );
            if ( '' === $name || isset( $seen_attrs[ $name ] ) || 0 === strpos( $name, 'on' ) ) {
                continue;
            }

            $is_allowed = in_array( $name, $allowed_attrs, true );
            if ( ! $is_allowed ) {
                foreach ( $allowed_prefixes as $prefix ) {
                    if ( '' !== $prefix && 0 === strpos( $name, $prefix ) ) {
                        $is_allowed = true;
                        break;
                    }
                }
            }

            if ( ! $is_allowed ) {
                continue;
            }

            $seen_attrs[ $name ] = true;
            $value = '';
            if ( array_key_exists( 3, $match ) && '' !== $match[3] ) {
                $value = (string) $match[3];
            } elseif ( array_key_exists( 4, $match ) && '' !== $match[4] ) {
                $value = (string) $match[4];
            } elseif ( array_key_exists( 5, $match ) && '' !== $match[5] ) {
                $value = (string) $match[5];
            }

            $safe_attrs[] = '' === $value
                ? ' ' . esc_attr( $name )
                : ' ' . esc_attr( $name ) . '="' . esc_attr( $value ) . '"';
        }

        return implode( '', $safe_attrs );
    }

    /**
     * 在模块渲染结果中统一补齐 img 属性，减少重复模板维护成本。
     *
     * 规则：
     * - 首屏模块默认 eager，且首张图补 fetchpriority=high。
     * - 其他模块默认 lazy。
     * - 全部补 decoding=async（若未手动设置）。
     *
     * @param string $html 模块渲染 HTML
     * @param string $module_id 模块 ID
     * @param array  $data 模块数据
     * @return string
     */
    private function normalize_module_images_markup( $html, $module_id, $data = array() ) {
        if ( ! is_string( $html ) || $html === '' || stripos( $html, '<img' ) === false ) {
            return $html;
        }

        $is_hero_module = 'footer' !== $this->render_context && in_array( $module_id, self::get_hero_module_ids( 'render' ), true );
        $hero_lcp_url = '';
        if ( $is_hero_module && function_exists( 'developer_starter_get_module_lcp_image_candidate' ) ) {
            $hero_lcp_url = developer_starter_get_module_lcp_image_candidate( $module_id, is_array( $data ) ? $data : array() );
        }
        $hero_image_index = 0;
        $hero_priority_added = false;

        $normalized = preg_replace_callback(
            '/<img\b[^>]*>/i',
            function( $matches ) use ( $is_hero_module, $hero_lcp_url, &$hero_image_index, &$hero_priority_added ) {
                $tag = $matches[0];

                if ( ! preg_match( '/\bsrc\s*=\s*([\'"])(.*?)\1/i', $tag, $src_matches ) ) {
                    return $tag;
                }

                $src = trim( html_entity_decode( (string) $src_matches[2], ENT_QUOTES, 'UTF-8' ) );
                if ( $src === '' || strpos( $src, '${' ) !== false || strpos( $src, 'data:' ) === 0 ) {
                    return $tag;
                }

                $default_loading = $is_hero_module ? 'eager' : 'lazy';
                $has_loading_attr = $this->img_tag_has_attr( $tag, 'loading' );
                $matches_lcp_candidate = $is_hero_module && '' !== $hero_lcp_url && $this->image_src_matches_lcp_candidate( $src, $hero_lcp_url );
                $should_prioritize = $is_hero_module
                    && ! $hero_priority_added
                    && ( $matches_lcp_candidate || ( '' === $hero_lcp_url && 0 === $hero_image_index && ! $has_loading_attr ) );

                if ( $should_prioritize ) {
                    $tag = $this->set_img_attr( $tag, 'loading', 'eager' );
                } elseif ( ! $has_loading_attr ) {
                    $tag = $this->append_img_attr( $tag, 'loading="' . $default_loading . '"' );
                }

                if ( stripos( $tag, ' decoding=' ) === false ) {
                    $tag = $this->append_img_attr( $tag, 'decoding="async"' );
                }

                $has_width  = $this->img_tag_has_attr( $tag, 'width' );
                $has_height = $this->img_tag_has_attr( $tag, 'height' );
                if ( ( ! $has_width || ! $has_height ) && ! $this->is_dimensionless_image_src( $src ) ) {
                    $dimensions = $this->get_attachment_image_dimensions_for_src( $src );
                    if ( ! empty( $dimensions['width'] ) && ! empty( $dimensions['height'] ) ) {
                        if ( ! $has_width ) {
                            $tag = $this->append_img_attr( $tag, 'width="' . absint( $dimensions['width'] ) . '"' );
                        }
                        if ( ! $has_height ) {
                            $tag = $this->append_img_attr( $tag, 'height="' . absint( $dimensions['height'] ) . '"' );
                        }
                    }
                }

                if ( $should_prioritize ) {
                    $tag = $this->set_img_attr( $tag, 'fetchpriority', 'high' );
                    $hero_priority_added = true;
                }

                $hero_image_index++;
                return $tag;
            },
            $html
        );

        return is_string( $normalized ) ? $normalized : $html;
    }

    /**
     * 在 img 标签闭合前插入属性。
     *
     * @param string $img_tag img 标签字符串
     * @param string $attribute 形如 key="value" 的属性
     * @return string
     */
    private function append_img_attr( $img_tag, $attribute ) {
        $updated = preg_replace( '/\s*(\/?)>$/', ' ' . $attribute . ' $1>', $img_tag, 1 );
        return is_string( $updated ) ? $updated : $img_tag;
    }

    /**
     * 设置 img 标签属性；已有属性时替换值，没有时追加。
     *
     * @param string $img_tag img 标签字符串
     * @param string $attribute 属性名
     * @param string $value 属性值
     * @return string
     */
    private function set_img_attr( $img_tag, $attribute, $value ) {
        $attribute = strtolower( (string) $attribute );
        if ( '' === $attribute ) {
            return $img_tag;
        }

        $replacement = ' ' . $attribute . '="' . esc_attr( (string) $value ) . '"';
        if ( $this->img_tag_has_attr( $img_tag, $attribute ) ) {
            $updated = preg_replace(
                '/\s' . preg_quote( $attribute, '/' ) . '\s*=\s*(?:"[^"]*"|\'[^\']*\'|[^\s>]+)/i',
                $replacement,
                $img_tag,
                1
            );
            return is_string( $updated ) ? $updated : $img_tag;
        }

        return $this->append_img_attr( $img_tag, $attribute . '="' . esc_attr( (string) $value ) . '"' );
    }

    /**
     * 判断 img 标签是否已有某个属性。
     *
     * @param string $img_tag img 标签字符串
     * @param string $attribute 属性名
     * @return bool
     */
    private function img_tag_has_attr( $img_tag, $attribute ) {
        return (bool) preg_match( '/\s' . preg_quote( $attribute, '/' ) . '\s*=/i', (string) $img_tag );
    }

    /**
     * 判断 img src 是否对应当前模块推断出的 LCP 图片。
     *
     * @param string $src img src
     * @param string $candidate_url LCP 候选 URL
     * @return bool
     */
    private function image_src_matches_lcp_candidate( $src, $candidate_url ) {
        $src = $this->normalize_image_src_for_attachment_lookup( $src );
        $candidate_url = $this->normalize_image_src_for_attachment_lookup( $candidate_url );
        if ( '' === $src || '' === $candidate_url ) {
            return false;
        }

        if ( $src === $candidate_url ) {
            return true;
        }

        $src_parts = function_exists( 'wp_parse_url' ) ? wp_parse_url( $src ) : parse_url( $src );
        $candidate_parts = function_exists( 'wp_parse_url' ) ? wp_parse_url( $candidate_url ) : parse_url( $candidate_url );
        if ( ! is_array( $src_parts ) || ! is_array( $candidate_parts ) ) {
            return false;
        }

        $src_host = isset( $src_parts['host'] ) ? strtolower( (string) $src_parts['host'] ) : '';
        $candidate_host = isset( $candidate_parts['host'] ) ? strtolower( (string) $candidate_parts['host'] ) : '';
        $src_path = isset( $src_parts['path'] ) ? (string) $src_parts['path'] : '';
        $candidate_path = isset( $candidate_parts['path'] ) ? (string) $candidate_parts['path'] : '';

        return '' !== $src_path && $src_host === $candidate_host && rawurldecode( $src_path ) === rawurldecode( $candidate_path );
    }

    /**
     * 判断图片是否不适合补尺寸。
     *
     * @param string $src 图片地址
     * @return bool
     */
    private function is_dimensionless_image_src( $src ) {
        $src = trim( (string) $src );
        if ( '' === $src || 0 === strpos( $src, 'data:' ) || false !== strpos( $src, '${' ) ) {
            return true;
        }

        $path = function_exists( 'wp_parse_url' ) ? wp_parse_url( $src, PHP_URL_PATH ) : parse_url( $src, PHP_URL_PATH );
        $extension = is_string( $path ) ? strtolower( pathinfo( $path, PATHINFO_EXTENSION ) ) : '';

        return in_array( $extension, array( 'svg', 'ico' ), true );
    }

    /**
     * 根据图片 URL 尝试获取媒体库附件尺寸。
     *
     * 只读取本地媒体库附件，不做远程探测，避免前台渲染产生额外网络开销。
     *
     * @param string $src 图片地址
     * @return array{width:int,height:int}
     */
    private function get_attachment_image_dimensions_for_src( $src ) {
        $empty = array(
            'width'  => 0,
            'height' => 0,
        );

        if ( ! function_exists( 'attachment_url_to_postid' ) || ! function_exists( 'wp_get_attachment_image_src' ) ) {
            return $empty;
        }

        $lookup_src = $this->normalize_image_src_for_attachment_lookup( $src );
        if ( '' === $lookup_src ) {
            return $empty;
        }

        $attachment_id = attachment_url_to_postid( $lookup_src );
        if ( $attachment_id <= 0 ) {
            return $empty;
        }

        $image = wp_get_attachment_image_src( $attachment_id, 'full' );
        if ( ! is_array( $image ) || empty( $image[1] ) || empty( $image[2] ) ) {
            return $empty;
        }

        return array(
            'width'  => absint( $image[1] ),
            'height' => absint( $image[2] ),
        );
    }

    /**
     * 规范化图片 URL，便于 attachment_url_to_postid 匹配。
     *
     * @param string $src 图片地址
     * @return string
     */
    private function normalize_image_src_for_attachment_lookup( $src ) {
        $src = trim( html_entity_decode( (string) $src, ENT_QUOTES, 'UTF-8' ) );
        if ( '' === $src || 0 === strpos( $src, 'data:' ) ) {
            return '';
        }

        if ( 0 === strpos( $src, '//' ) ) {
            $src = ( is_ssl() ? 'https:' : 'http:' ) . $src;
        } elseif ( 0 === strpos( $src, '/' ) && function_exists( 'home_url' ) ) {
            $src = home_url( $src );
        }

        return esc_url_raw( $src );
    }

    public function render_module( $module_id, $data = array() ) {
        $this->ensure_modules_loaded();
        $module = $this->get_module( $module_id );
        if ( $module ) {
            $buffer_level = ob_get_level();
            try {
                $data = wp_parse_args( $data, $module->get_default_data() );
                $data = apply_filters( 'developer_starter_module_data', $data, $module_id, $module );

                $should_render = apply_filters( 'developer_starter_should_render_module', true, $module_id, $data, $module );
                if ( ! $should_render ) {
                    return;
                }

                do_action( 'developer_starter_before_render_module', $module_id, $data, $module );
                ob_start();
                $module->render( $data );
                $module_html = ob_get_clean();
                $module_html = $this->normalize_module_images_markup( (string) $module_html, (string) $module_id, is_array( $data ) ? $data : array() );
                echo '<div class="qiling-module-scope qiling-module-scope-' . esc_attr( sanitize_html_class( (string) $module_id ) ) . '" data-qiling-module-scope="' . esc_attr( (string) $module_id ) . '">';
                echo $module_html;
                echo '</div>';
                do_action( 'developer_starter_after_render_module', $module_id, $data, $module );
            } catch ( \Exception $e ) {
                while ( ob_get_level() > $buffer_level ) {
                    ob_end_clean();
                }

                if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
                    echo '<!-- Module Error (' . esc_attr( $module_id ) . '): ' . esc_html( $e->getMessage() ) . ' -->';
                }
                developer_starter_log(
                    'module',
                    'Module render exception.',
                    array(
                        'module_id' => $module_id,
                        'exception' => $e,
                    ),
                    'error'
                );
            } catch ( \Error $e ) {
                while ( ob_get_level() > $buffer_level ) {
                    ob_end_clean();
                }

                if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
                    echo '<!-- Module Fatal Error (' . esc_attr( $module_id ) . '): ' . esc_html( $e->getMessage() ) . ' -->';
                }
                developer_starter_log(
                    'module',
                    'Module render fatal error.',
                    array(
                        'module_id' => $module_id,
                        'error'     => $e,
                    ),
                    'critical'
                );
            }
        }
    }

    public function render_page_modules( $post_id = null, $args = array() ) {
        if ( ! $post_id ) {
            $post_id = get_the_ID();
        }

        $args = wp_parse_args(
            is_array( $args ) ? $args : array(),
            array(
                'builder_mode' => null,
                'context'      => 'page',
            )
        );

        $post_type = $post_id ? get_post_type( $post_id ) : '';
        $allowed_post_types = array( 'page' );
        $allowed_post_types = (array) apply_filters( 'developer_starter_module_render_post_types', $allowed_post_types, $post_id, $args );
        $allowed_post_types = array_values( array_filter( array_unique( array_map( 'sanitize_key', $allowed_post_types ) ) ) );

        if ( $post_id && ! in_array( sanitize_key( (string) $post_type ), $allowed_post_types, true ) ) {
            return;
        }

        $this->ensure_modules_loaded( true );

        $previous_render_context = $this->render_context;
        $this->render_context = sanitize_key( (string) $args['context'] );
        if ( '' === $this->render_context ) {
            $this->render_context = 'page';
        }
        $builder_mode = null === $args['builder_mode']
            ? ( class_exists( '\Developer_Starter\Core\Frontend_Builder' ) && \Developer_Starter\Core\Frontend_Builder::is_builder_mode() )
            : (bool) $args['builder_mode'];
        $modules = function_exists( 'developer_starter_get_page_modules_data' )
            ? developer_starter_get_page_modules_data( $post_id )
            : get_post_meta( $post_id, '_developer_starter_modules', true );
        $modules = apply_filters( 'developer_starter_page_modules_data', $modules, $post_id );
        if ( $builder_mode ) {
            echo '<div id="qiling-builder-start" class="qiling-builder-anchor" data-post-id="' . esc_attr( (string) $post_id ) . '"></div>';
        }

        if ( empty( $modules ) || ! is_array( $modules ) ) {
            if ( $builder_mode ) {
                echo '<div id="qiling-builder-end" class="qiling-builder-anchor" data-post-id="' . esc_attr( (string) $post_id ) . '"></div>';
            }
            $this->render_context = $previous_render_context;
            return;
        }
        
        // 不需要间距设置的首屏模块
        $hero_modules = self::get_hero_module_ids( 'render' );

        // 获取滚动视差设置
        $enable_scroll_reveal = get_post_meta( $post_id, '_developer_starter_enable_scroll_reveal', true );
        $base_scroll_attr = ( $enable_scroll_reveal === '1' ) ? ' data-aos="fade-up" data-aos-duration="800"' : '';
        $builder_index = 0;

        foreach ( $modules as $module_data ) {
            $module_id = isset( $module_data['type'] ) ? sanitize_key( (string) $module_data['type'] ) : '';
            $data = isset( $module_data['data'] ) ? $module_data['data'] : array();
            if ( empty( $module_id ) ) {
                continue;
            }

            $module_is_hidden = class_exists( '\Developer_Starter\Core\Module_Advanced_Style_Service' )
                && \Developer_Starter\Core\Module_Advanced_Style_Service::get_instance()->module_is_hidden( $data );
            if ( $module_is_hidden && ! $builder_mode ) {
                continue;
            }
            if ( $module_is_hidden && $builder_mode ) {
                echo '<div class="module-wrapper qiling-builder-module qfb-hidden-module-placeholder" data-builder-index="' . esc_attr( (string) $builder_index ) . '" data-module-id="' . esc_attr( $module_id ) . '">';
                echo '<div class="qfb-hidden-module-placeholder__inner"><strong>' . esc_html( $this->get_module( $module_id ) ? $this->get_module( $module_id )->get_name() : $module_id ) . '</strong><span>' . esc_html__( '当前模块已暂时隐藏，点击可编辑并恢复显示。', 'developer-starter' ) . '</span></div>';
                echo '</div>';
                $builder_index++;
                continue;
            }

            // 获取间距设置（非首屏模块）
            $margin_top = '';
            $margin_bottom = '';
            $scroll_attr = $base_scroll_attr;
            if ( ! in_array( $module_id, $hero_modules, true ) ) {
                $margin_top = isset( $data['module_margin_top'] ) ? self::sanitize_spacing_value( $data['module_margin_top'] ) : '';
                $margin_bottom = isset( $data['module_margin_bottom'] ) ? self::sanitize_spacing_value( $data['module_margin_bottom'] ) : '';
            }
            
            // 构建Wrapper样式
            $wrapper_style = '';
            $has_wrapper = false;
            $requires_advanced_wrapper = false;

            if ( $margin_top !== '' ) {
                $wrapper_style .= 'margin-top: ' . esc_attr( $margin_top ) . ';';
                $has_wrapper = true;
            }
            if ( $margin_bottom !== '' ) {
                $wrapper_style .= 'margin-bottom: ' . esc_attr( $margin_bottom ) . ';';
                $has_wrapper = true;
            }

            if ( class_exists( '\Developer_Starter\Core\Module_Advanced_Style_Service' ) ) {
                $requires_advanced_wrapper = \Developer_Starter\Core\Module_Advanced_Style_Service::get_instance()->module_requires_wrapper( $data );
            }
            if ( class_exists( '\Developer_Starter\Core\Module_Visual_Style_Service' ) && \Developer_Starter\Core\Module_Visual_Style_Service::get_instance()->module_requires_wrapper( $data ) ) {
                $requires_advanced_wrapper = true;
            }
            
            // 如果开启了滚动视差，或者有自定义间距，则添加wrapper
            if ( $has_wrapper || $requires_advanced_wrapper || $enable_scroll_reveal === '1' || $builder_mode ) {
                // 如果有间距设置，添加module-spacing-wrapper类
                $classes = 'module-wrapper';
                if ( $has_wrapper ) {
                    $classes .= ' module-spacing-wrapper';
                }

                if ( $builder_mode ) {
                    $classes .= ' qiling-builder-module';
                }

                $classes = apply_filters( 'developer_starter_module_wrapper_class', $classes, $module_id, $data, $post_id );
                $wrapper_style = apply_filters( 'developer_starter_module_wrapper_style', $wrapper_style, $module_id, $data, $post_id );
                $scroll_attr = apply_filters( 'developer_starter_module_wrapper_attr', $scroll_attr, $module_id, $data, $post_id );
                $scroll_attr = self::sanitize_wrapper_attr_fragment( $scroll_attr );
                
                $builder_data_attr = '';
                if ( $builder_mode ) {
                    $builder_data_attr = ' data-builder-index="' . esc_attr( (string) $builder_index ) . '" data-module-id="' . esc_attr( $module_id ) . '"';
                }

                echo '<div class="' . esc_attr( $classes ) . '" style="' . esc_attr( $wrapper_style ) . '"' . $scroll_attr . $builder_data_attr . '>';
                $this->render_module( $module_id, $data );
                echo '</div>';
            } else {
                $this->render_module( $module_id, $data );
            }

            if ( $builder_mode ) {
                $builder_index++;
            }
        }

        if ( $builder_mode ) {
            echo '<div id="qiling-builder-end" class="qiling-builder-anchor" data-post-id="' . esc_attr( (string) $post_id ) . '"></div>';
        }
        $this->render_context = $previous_render_context;
    }
}
