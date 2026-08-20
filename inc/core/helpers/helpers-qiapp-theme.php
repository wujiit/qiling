<?php
/**
 * 启灵App 主题适配 helper
 *
 * @package Developer_Starter
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! function_exists( 'developer_starter_qiapp_try_bootstrap_bundled_plugin' ) ) {
    /**
     * 尝试加载主题内置的启灵App插件代码（当站点未以常规插件方式激活时兜底）。
     *
     * @return bool
     */
    function developer_starter_qiapp_try_bootstrap_bundled_plugin() {
        static $attempted = false;
        static $loaded    = false;

        if ( $attempted ) {
            return $loaded;
        }

        $attempted = true;

        if ( class_exists( 'QiApp_Post_Type' )
            || class_exists( 'QiApp_Software' )
            || class_exists( 'QiApp_Database' )
            || class_exists( 'QilingApp' )
            || function_exists( 'qilingapp' )
            || function_exists( 'qiapp' )
            || function_exists( 'qlapp' ) ) {
            $loaded = true;
            return true;
        }

        $candidate_files = array();
        if ( function_exists( 'get_template_directory' ) ) {
            $candidate_files[] = untrailingslashit( (string) get_template_directory() ) . '/plugins/qilingapp/qilingapp.php';
            $candidate_files[] = untrailingslashit( (string) get_template_directory() ) . '/plugins/qiapp/qiapp.php';
        }
        if ( function_exists( 'get_stylesheet_directory' ) ) {
            $candidate_files[] = untrailingslashit( (string) get_stylesheet_directory() ) . '/plugins/qilingapp/qilingapp.php';
            $candidate_files[] = untrailingslashit( (string) get_stylesheet_directory() ) . '/plugins/qiapp/qiapp.php';
        }

        foreach ( array_values( array_unique( array_filter( array_map( 'strval', $candidate_files ) ) ) ) as $plugin_file ) {
            if ( '' === $plugin_file || ! file_exists( $plugin_file ) ) {
                continue;
            }

            require_once $plugin_file;

            if ( class_exists( 'QiApp_Post_Type' )
                || class_exists( 'QiApp_Software' )
                || class_exists( 'QiApp_Database' )
                || class_exists( 'QilingApp' )
                || function_exists( 'qilingapp' )
                || function_exists( 'qiapp' )
                || function_exists( 'qlapp' ) ) {
                $loaded = true;
                break;
            }
        }

        return $loaded;
    }
}

if ( ! function_exists( 'developer_starter_qiapp_has_strong_indicator' ) ) {
    /**
     * 判断标识符是否包含启灵App强特征关键词。
     *
     * @param string $value 标识符。
     * @return bool
     */
    function developer_starter_qiapp_has_strong_indicator( $value ) {
        $value = strtolower( sanitize_key( (string) $value ) );
        if ( '' === $value ) {
            return false;
        }

        return false !== strpos( $value, 'qiapp' )
            || false !== strpos( $value, 'qilingapp' )
            || false !== strpos( $value, 'qlapp' );
    }
}

if ( ! function_exists( 'developer_starter_qiapp_is_available' ) ) {
    /**
     * 启灵App 是否可用。
     *
     * @return bool
     */
    function developer_starter_qiapp_is_available() {
        if ( function_exists( 'developer_starter_qiapp_try_bootstrap_bundled_plugin' ) ) {
            developer_starter_qiapp_try_bootstrap_bundled_plugin();
        }

        $post_type = function_exists( 'developer_starter_qiapp_get_post_type' ) ? developer_starter_qiapp_get_post_type() : 'qiapp_software';
        $taxonomy  = function_exists( 'developer_starter_qiapp_get_category_taxonomy' ) ? developer_starter_qiapp_get_category_taxonomy() : 'qiapp_software_category';

        $has_indicator_function = false;
        foreach ( array( 'qilingapp', 'qiapp', 'qlapp', 'qilingapp_get_post_type', 'qiapp_get_post_type', 'qlapp_get_post_type' ) as $indicator_function ) {
            if ( function_exists( $indicator_function ) ) {
                $has_indicator_function = true;
                break;
            }
        }

        $service_instance = function_exists( 'developer_starter_qiapp_get_service_instance' )
            ? developer_starter_qiapp_get_service_instance()
            : null;

        $has_provider = class_exists( 'QiApp_Post_Type' )
            || class_exists( 'QiApp_Software' )
            || class_exists( 'QiApp_Database' )
            || class_exists( 'QilingApp' );

        $has_runtime_entry = $has_indicator_function || is_object( $service_instance );

        $has_content_type = ( is_string( $post_type ) && '' !== $post_type && post_type_exists( $post_type ) && developer_starter_qiapp_has_strong_indicator( $post_type ) )
            || ( is_string( $taxonomy ) && '' !== $taxonomy && taxonomy_exists( $taxonomy ) && developer_starter_qiapp_has_strong_indicator( $taxonomy ) );

        return $has_content_type && ( $has_provider || $has_runtime_entry );
    }
}

if ( ! function_exists( 'developer_starter_qiapp_get_provider_class' ) ) {
    /**
     * 根据方法名解析启灵App数据提供类（兼容重构后的类名/命名空间）。
     *
     * @param string $method_name 方法名。
     * @param array  $candidates  候选类名。
     * @return string
     */
    function developer_starter_qiapp_get_provider_class( $method_name, $candidates = array() ) {
        $method_name = trim( (string) $method_name );
        if ( '' === $method_name ) {
            return '';
        }

        foreach ( (array) $candidates as $class_name ) {
            $class_name = ltrim( (string) $class_name, '\\' );
            if ( '' === $class_name ) {
                continue;
            }
            if ( class_exists( $class_name ) && method_exists( $class_name, $method_name ) ) {
                return $class_name;
            }
        }

        $declared = get_declared_classes();
        foreach ( $declared as $class_name ) {
            if ( ! is_string( $class_name ) || '' === $class_name ) {
                continue;
            }

            $class_lc = strtolower( $class_name );
            if ( false === strpos( $class_lc, 'qiapp' ) && false === strpos( $class_lc, 'qilingapp' ) && false === strpos( $class_lc, 'qlapp' ) ) {
                continue;
            }

            if ( method_exists( $class_name, $method_name ) ) {
                return $class_name;
            }
        }

        return '';
    }
}

if ( ! function_exists( 'developer_starter_qiapp_call_first_function' ) ) {
    /**
     * 调用第一个存在的函数并返回结果。
     *
     * @param array $function_names 函数名候选。
     * @param array $arguments      调用参数。
     * @return mixed|null
     */
    function developer_starter_qiapp_call_first_function( $function_names, $arguments = array() ) {
        foreach ( (array) $function_names as $function_name ) {
            $function_name = (string) $function_name;
            if ( '' === $function_name || ! function_exists( $function_name ) ) {
                continue;
            }

            return call_user_func_array( $function_name, (array) $arguments );
        }

        return null;
    }
}

if ( ! function_exists( 'developer_starter_qiapp_get_service_instance' ) ) {
    /**
     * 获取启灵App主服务对象（若插件提供入口函数）。
     *
     * @return object|null
     */
    function developer_starter_qiapp_get_service_instance() {
        static $instance = false;

        if ( false === $instance ) {
            $instance = null;

            $resolved = function_exists( 'developer_starter_qiapp_call_first_function' )
                ? developer_starter_qiapp_call_first_function( array( 'qilingapp', 'qiapp', 'qlapp' ) )
                : null;

            if ( is_object( $resolved ) ) {
                $instance = $resolved;
            }
        }

        return is_object( $instance ) ? $instance : null;
    }
}

if ( ! function_exists( 'developer_starter_qiapp_guess_post_type_from_registered' ) ) {
    /**
     * 从已注册 post type 中推断启灵App软件 post type。
     *
     * @return string
     */
    function developer_starter_qiapp_guess_post_type_from_registered() {
        static $resolved = null;

        if ( null !== $resolved ) {
            return $resolved;
        }

        $resolved = '';
        $objects  = get_post_types( array(), 'objects' );
        if ( ! is_array( $objects ) ) {
            return $resolved;
        }

        $built_in = array(
            'post',
            'page',
            'attachment',
            'revision',
            'nav_menu_item',
            'custom_css',
            'customize_changeset',
            'oembed_cache',
            'user_request',
            'wp_block',
            'wp_template',
            'wp_template_part',
            'wp_global_styles',
            'wp_navigation',
            'wp_font_family',
            'wp_font_face',
            'wp_pattern',
        );

        $best_post_type = '';
        $best_score     = 0;

        foreach ( $objects as $post_type => $object ) {
            $post_type = sanitize_key( (string) $post_type );
            if ( '' === $post_type || in_array( $post_type, $built_in, true ) ) {
                continue;
            }

            $score = 0;
            $name  = strtolower( $post_type );

            if ( false !== strpos( $name, 'qiapp' ) || false !== strpos( $name, 'qilingapp' ) || false !== strpos( $name, 'qlapp' ) ) {
                $score += 8;
            }

            $label_source = '';
            if ( is_object( $object ) ) {
                $label_source = (string) ( $object->label ?? '' );
                if ( isset( $object->labels ) && is_object( $object->labels ) ) {
                    $label_source .= ' ' . (string) ( $object->labels->name ?? '' );
                    $label_source .= ' ' . (string) ( $object->labels->singular_name ?? '' );
                }
                if ( ! empty( $object->has_archive ) ) {
                    $score += 1;
                }
                if ( isset( $object->rewrite ) ) {
                    $rewrite_slug = '';
                    if ( is_array( $object->rewrite ) && isset( $object->rewrite['slug'] ) ) {
                        $rewrite_slug = (string) $object->rewrite['slug'];
                    } elseif ( is_string( $object->rewrite ) ) {
                        $rewrite_slug = (string) $object->rewrite;
                    }
                    $rewrite_slug = strtolower( $rewrite_slug );
                    if ( '' !== $rewrite_slug && ( false !== strpos( $rewrite_slug, 'qiapp' ) || false !== strpos( $rewrite_slug, 'qilingapp' ) || false !== strpos( $rewrite_slug, 'qlapp' ) ) ) {
                        $score += 2;
                    }
                }
            }

            $label_source = strtolower( $label_source );
            if ( false !== strpos( $label_source, 'qiapp' )
                || false !== strpos( $label_source, 'qilingapp' )
                || false !== strpos( $label_source, 'qlapp' )
                || false !== strpos( $label_source, '启灵app' ) ) {
                $score += 3;
            }

            $taxonomies = get_object_taxonomies( $post_type, 'names' );
            if ( is_array( $taxonomies ) ) {
                foreach ( $taxonomies as $taxonomy_name ) {
                    $taxonomy_name = strtolower( (string) $taxonomy_name );
                    if ( false !== strpos( $taxonomy_name, 'qiapp' ) || false !== strpos( $taxonomy_name, 'qilingapp' ) || false !== strpos( $taxonomy_name, 'qlapp' ) ) {
                        $score += 2;
                    }
                    if ( false !== strpos( $taxonomy_name, 'category' ) || false !== strpos( $taxonomy_name, 'tag' ) ) {
                        $score += 1;
                    }
                }
            }

            if ( $score > $best_score ) {
                $best_score     = $score;
                $best_post_type = $post_type;
            }
        }

        if ( $best_score >= 8 ) {
            $resolved = $best_post_type;
        }

        return $resolved;
    }
}

if ( ! function_exists( 'developer_starter_qiapp_guess_taxonomy_for_post_type' ) ) {
    /**
     * 基于 post type 推断分类/标签 taxonomy。
     *
     * @param string $post_type post type。
     * @param string $kind      category|tag。
     * @return string
     */
    function developer_starter_qiapp_guess_taxonomy_for_post_type( $post_type, $kind = 'category' ) {
        $post_type = sanitize_key( (string) $post_type );
        $kind      = 'tag' === sanitize_key( (string) $kind ) ? 'tag' : 'category';

        if ( '' === $post_type || ! post_type_exists( $post_type ) ) {
            return '';
        }

        $taxonomies = get_object_taxonomies( $post_type, 'objects' );
        if ( ! is_array( $taxonomies ) ) {
            return '';
        }

        $best_taxonomy = '';
        $best_score    = 0;

        foreach ( $taxonomies as $taxonomy ) {
            if ( ! is_object( $taxonomy ) || empty( $taxonomy->name ) ) {
                continue;
            }

            $taxonomy_name = sanitize_key( (string) $taxonomy->name );
            if ( '' === $taxonomy_name ) {
                continue;
            }

            $score = 0;
            $name  = strtolower( $taxonomy_name );
            if ( false !== strpos( $name, 'qiapp' ) || false !== strpos( $name, 'qilingapp' ) || false !== strpos( $name, 'qlapp' ) ) {
                $score += 5;
            }
            if ( false !== strpos( $name, 'software' ) || false !== strpos( $name, 'app' ) ) {
                $score += 2;
            }

            $label_source = (string) ( $taxonomy->label ?? '' );
            if ( isset( $taxonomy->labels ) && is_object( $taxonomy->labels ) ) {
                $label_source .= ' ' . (string) ( $taxonomy->labels->name ?? '' );
                $label_source .= ' ' . (string) ( $taxonomy->labels->singular_name ?? '' );
            }
            $label_source = strtolower( $label_source );

            if ( 'category' === $kind ) {
                if ( ! empty( $taxonomy->hierarchical ) ) {
                    $score += 1;
                }
                if ( false !== strpos( $name, 'category' ) || false !== strpos( $name, '_cat' ) || false !== strpos( $label_source, 'category' ) || false !== strpos( $label_source, '分类' ) ) {
                    $score += 4;
                }
                if ( 'post_tag' === $taxonomy_name ) {
                    $score = 0;
                }
            } else {
                if ( empty( $taxonomy->hierarchical ) ) {
                    $score += 1;
                }
                if ( false !== strpos( $name, 'tag' ) || false !== strpos( $label_source, 'tag' ) || false !== strpos( $label_source, '标签' ) ) {
                    $score += 4;
                }
                if ( 'category' === $taxonomy_name ) {
                    $score = 0;
                }
            }

            if ( $score > $best_score ) {
                $best_score    = $score;
                $best_taxonomy = $taxonomy_name;
            }
        }

        return $best_score > 0 ? $best_taxonomy : '';
    }
}

if ( ! function_exists( 'developer_starter_qiapp_get_table_columns' ) ) {
    /**
     * 获取指定表的列信息。
     *
     * @param string $table_name 表名。
     * @return array
     */
    function developer_starter_qiapp_get_table_columns( $table_name ) {
        static $cache = array();

        $table_name = trim( (string) $table_name );
        if ( '' === $table_name ) {
            return array();
        }

        if ( isset( $cache[ $table_name ] ) ) {
            return $cache[ $table_name ];
        }

        global $wpdb;
        $columns = array();

        $rows = $wpdb->get_results( "SHOW COLUMNS FROM `{$table_name}`", ARRAY_A );
        if ( is_array( $rows ) ) {
            foreach ( $rows as $row ) {
                $column_name = isset( $row['Field'] ) ? trim( (string) $row['Field'] ) : '';
                if ( '' === $column_name ) {
                    continue;
                }
                $columns[ strtolower( $column_name ) ] = $column_name;
            }
        }

        $cache[ $table_name ] = $columns;
        return $columns;
    }
}

if ( ! function_exists( 'developer_starter_qiapp_pick_first_existing_column' ) ) {
    /**
     * 从候选列中返回第一个存在的列名（保留原始大小写）。
     *
     * @param string $table_name 表名。
     * @param array  $candidates 候选列。
     * @return string
     */
    function developer_starter_qiapp_pick_first_existing_column( $table_name, $candidates ) {
        $columns = function_exists( 'developer_starter_qiapp_get_table_columns' )
            ? developer_starter_qiapp_get_table_columns( $table_name )
            : array();

        if ( empty( $columns ) ) {
            return '';
        }

        foreach ( (array) $candidates as $candidate ) {
            $candidate = strtolower( trim( (string) $candidate ) );
            if ( '' === $candidate ) {
                continue;
            }
            if ( isset( $columns[ $candidate ] ) ) {
                return (string) $columns[ $candidate ];
            }
        }

        return '';
    }
}

if ( ! function_exists( 'developer_starter_qiapp_get_post_type_provider_class' ) ) {
    /**
     * 获取 post type 提供类。
     *
     * @return string
     */
    function developer_starter_qiapp_get_post_type_provider_class() {
        static $class_name = null;

        if ( null === $class_name ) {
            $class_name = developer_starter_qiapp_get_provider_class(
                'get_post_type',
                array(
                    'QiApp_Post_Type',
                    'QilingApp_Post_Type',
                    'QilingApp\\Post_Type',
                    'QilingApp\\PostType',
                )
            );
        }

        return $class_name ? (string) $class_name : '';
    }
}

if ( ! function_exists( 'developer_starter_qiapp_get_software_provider_class' ) ) {
    /**
     * 获取软件数据模型类。
     *
     * @return string
     */
    function developer_starter_qiapp_get_software_provider_class() {
        static $class_name = null;

        if ( null === $class_name ) {
            $class_name = developer_starter_qiapp_get_provider_class(
                'get_by_post_id',
                array(
                    'QiApp_Software',
                    'QilingApp_Software',
                    'QilingApp\\Software',
                    'QilingApp\\Models\\Software',
                )
            );
        }

        return $class_name ? (string) $class_name : '';
    }
}

if ( ! function_exists( 'developer_starter_qiapp_get_database_provider_class' ) ) {
    /**
     * 获取数据库表名提供类。
     *
     * @return string
     */
    function developer_starter_qiapp_get_database_provider_class() {
        static $class_name = null;

        if ( null === $class_name ) {
            $class_name = developer_starter_qiapp_get_provider_class(
                'table',
                array(
                    'QiApp_Database',
                    'QilingApp_Database',
                    'QilingApp\\Database',
                    'QilingApp\\Models\\Database',
                )
            );
        }

        return $class_name ? (string) $class_name : '';
    }
}

if ( ! function_exists( 'developer_starter_qiapp_resolve_table_name' ) ) {
    /**
     * 解析启灵App数据表名（优先走插件提供类，失败再尝试常见前缀）。
     *
     * @param string $table 简表名，如 software / versions。
     * @return string
     */
    function developer_starter_qiapp_resolve_table_name( $table ) {
        static $cache = array();

        $table = sanitize_key( (string) $table );
        if ( '' === $table ) {
            return '';
        }

        if ( isset( $cache[ $table ] ) ) {
            return $cache[ $table ];
        }

        $db_class = developer_starter_qiapp_get_database_provider_class();
        if ( '' !== $db_class && method_exists( $db_class, 'table' ) ) {
            $resolved = call_user_func( array( $db_class, 'table' ), $table );
            $resolved = is_string( $resolved ) ? trim( $resolved, "` \t\n\r\0\x0B" ) : '';
            if ( '' !== $resolved ) {
                $cache[ $table ] = $resolved;
                return $resolved;
            }
        }

        if ( function_exists( 'developer_starter_qiapp_call_first_function' ) ) {
            $resolved = developer_starter_qiapp_call_first_function(
                array( 'qilingapp_table', 'qiapp_table', 'qlapp_table' ),
                array( $table )
            );
            $resolved = is_string( $resolved ) ? trim( $resolved, "` \t\n\r\0\x0B" ) : '';
            if ( '' !== $resolved ) {
                $cache[ $table ] = $resolved;
                return $resolved;
            }
        }

        global $wpdb;
        if ( ! isset( $wpdb ) || ! is_object( $wpdb ) ) {
            $cache[ $table ] = '';
            return '';
        }

        $prefixes = array( 'qiapp_', 'qilingapp_', 'qlapp_' );
        if ( defined( 'QIAPP_TABLE_PREFIX' ) ) {
            array_unshift( $prefixes, (string) QIAPP_TABLE_PREFIX );
        }
        if ( defined( 'QILINGAPP_TABLE_PREFIX' ) ) {
            array_unshift( $prefixes, (string) QILINGAPP_TABLE_PREFIX );
        }
        $prefixes = array_values( array_filter( array_unique( array_map( 'strval', $prefixes ) ) ) );

        foreach ( $prefixes as $prefix ) {
            $full_table = $wpdb->prefix . trim( $prefix ) . $table;
            $exists = $wpdb->get_var(
                $wpdb->prepare(
                    'SHOW TABLES LIKE %s',
                    $full_table
                )
            );
            if ( is_string( $exists ) && '' !== $exists ) {
                $cache[ $table ] = $full_table;
                return $full_table;
            }
        }

        $discovery_patterns = array(
            $wpdb->prefix . '%qiapp%_' . $table,
            $wpdb->prefix . '%qilingapp%_' . $table,
            $wpdb->prefix . '%qlapp%_' . $table,
        );
        foreach ( array_values( array_unique( $discovery_patterns ) ) as $pattern ) {
            $matches = (array) $wpdb->get_col(
                $wpdb->prepare(
                    'SHOW TABLES LIKE %s',
                    $pattern
                )
            );

            foreach ( $matches as $matched_table ) {
                $matched_table = trim( (string) $matched_table );
                if ( '' === $matched_table ) {
                    continue;
                }

                $matched_lc = strtolower( $matched_table );
                if ( false === strpos( $matched_lc, $table ) ) {
                    continue;
                }

                if ( false === strpos( $matched_lc, 'qiapp' )
                    && false === strpos( $matched_lc, 'qilingapp' )
                    && false === strpos( $matched_lc, 'qlapp' ) ) {
                    continue;
                }

                $cache[ $table ] = $matched_table;
                return $matched_table;
            }
        }

        $cache[ $table ] = '';
        return '';
    }
}

if ( ! function_exists( 'developer_starter_qiapp_get_value_from_source' ) ) {
    /**
     * 从对象/数组按候选键获取首个非空值。
     *
     * @param mixed $source 数据源。
     * @param array $keys   候选键。
     * @param mixed $default 默认值。
     * @return mixed
     */
    function developer_starter_qiapp_get_value_from_source( $source, $keys, $default = '' ) {
        $keys = (array) $keys;
        foreach ( $keys as $key ) {
            $key = (string) $key;
            if ( '' === $key ) {
                continue;
            }

            $exists = false;
            $value  = null;

            if ( is_array( $source ) && array_key_exists( $key, $source ) ) {
                $exists = true;
                $value  = $source[ $key ];
            } elseif ( is_object( $source ) && property_exists( $source, $key ) ) {
                $exists = true;
                $value  = $source->{$key};
            } elseif ( is_object( $source ) && isset( $source->{$key} ) ) {
                $exists = true;
                $value  = $source->{$key};
            }

            if ( ! $exists || null === $value || '' === $value ) {
                continue;
            }

            return $value;
        }

        return $default;
    }
}

if ( ! function_exists( 'developer_starter_qiapp_get_post_meta_first_value' ) ) {
    /**
     * 获取首个存在且非空的 post meta 值。
     *
     * @param int   $post_id 文章ID。
     * @param array $keys    候选meta key。
     * @param mixed $default 默认值。
     * @return mixed
     */
    function developer_starter_qiapp_get_post_meta_first_value( $post_id, $keys, $default = '' ) {
        $post_id = absint( $post_id );
        if ( ! $post_id ) {
            return $default;
        }

        foreach ( (array) $keys as $meta_key ) {
            $meta_key = (string) $meta_key;
            if ( '' === $meta_key ) {
                continue;
            }

            $value = get_post_meta( $post_id, $meta_key, true );
            if ( null === $value || '' === $value ) {
                continue;
            }

            return $value;
        }

        return $default;
    }
}

if ( ! function_exists( 'developer_starter_qiapp_build_meta_software' ) ) {
    /**
     * 基于 post meta 构建软件对象兜底数据。
     *
     * @param int $post_id 文章 ID。
     * @return object|null
     */
    function developer_starter_qiapp_build_meta_software( $post_id ) {
        $post_id = absint( $post_id );
        if ( ! $post_id ) {
            return null;
        }

        $software_name = developer_starter_qiapp_get_post_meta_first_value(
            $post_id,
            array(
                'qiapp_software_name',
                '_qiapp_software_name',
                'qilingapp_software_name',
                '_qilingapp_software_name',
                'software_name',
                '_software_name',
                'app_name',
                '_app_name',
                'name',
            ),
            ''
        );
        $software_icon = developer_starter_qiapp_get_post_meta_first_value(
            $post_id,
            array(
                'qiapp_software_icon',
                '_qiapp_software_icon',
                'qilingapp_software_icon',
                '_qilingapp_software_icon',
                'software_icon',
                '_software_icon',
                'app_icon',
                '_app_icon',
                'icon',
            ),
            ''
        );
        $software_screenshot = developer_starter_qiapp_get_post_meta_first_value(
            $post_id,
            array(
                'qiapp_software_screenshot',
                '_qiapp_software_screenshot',
                'qilingapp_software_screenshot',
                '_qilingapp_software_screenshot',
                'software_screenshot',
                '_software_screenshot',
                'screenshot',
                'cover_image',
            ),
            ''
        );

        if ( '' === (string) $software_name && '' === (string) $software_icon && '' === (string) $software_screenshot ) {
            return null;
        }

        return (object) array(
            'id'                 => 0,
            'post_id'            => $post_id,
            'software_name'      => (string) $software_name,
            'software_icon'      => (string) $software_icon,
            'software_screenshot'=> (string) $software_screenshot,
            'short_description'  => (string) developer_starter_qiapp_get_post_meta_first_value( $post_id, array( 'qiapp_short_description', '_qiapp_short_description', 'qilingapp_short_description', 'short_description', 'software_summary', 'summary', 'description' ), '' ),
            'developer_name'     => (string) developer_starter_qiapp_get_post_meta_first_value( $post_id, array( 'qiapp_developer_name', '_qiapp_developer_name', 'qilingapp_developer_name', 'developer_name', 'developer', 'author' ), '' ),
            'license_type'       => (string) developer_starter_qiapp_get_post_meta_first_value( $post_id, array( 'qiapp_license_type', '_qiapp_license_type', 'qilingapp_license_type', 'license_type', 'license' ), 'free' ),
            'rating'             => (float) developer_starter_qiapp_get_post_meta_first_value( $post_id, array( 'qiapp_rating', '_qiapp_rating', 'qilingapp_rating', 'rating', 'score' ), 5 ),
            'total_downloads'    => (int) developer_starter_qiapp_get_post_meta_first_value( $post_id, array( 'qiapp_total_downloads', '_qiapp_total_downloads', 'qilingapp_total_downloads', 'download_count', 'downloads', 'real_downloads' ), 0 ),
            'view_count'         => (int) developer_starter_qiapp_get_post_meta_first_value( $post_id, array( 'qiapp_view_count', '_qiapp_view_count', 'qilingapp_view_count', 'views', 'view_count' ), 0 ),
            'updated_at'         => (string) get_post_modified_time( 'Y-m-d H:i:s', false, $post_id ),
        );
    }
}

if ( ! function_exists( 'developer_starter_qiapp_build_meta_latest_version' ) ) {
    /**
     * 基于 post meta 构建最新版本兜底数据。
     *
     * @param int $post_id 文章 ID。
     * @return object|null
     */
    function developer_starter_qiapp_build_meta_latest_version( $post_id ) {
        $post_id = absint( $post_id );
        if ( ! $post_id ) {
            return null;
        }

        $version = developer_starter_qiapp_get_post_meta_first_value(
            $post_id,
            array( 'qiapp_version', '_qiapp_version', 'qilingapp_version', '_qilingapp_version', 'software_version', 'app_version', 'latest_version', 'version' ),
            ''
        );

        if ( '' === (string) $version ) {
            return null;
        }

        return (object) array(
            'id'           => 0,
            'software_id'  => 0,
            'version'      => (string) $version,
            'release_date' => (string) developer_starter_qiapp_get_post_meta_first_value( $post_id, array( 'qiapp_release_date', '_qiapp_release_date', 'qilingapp_release_date', 'release_date', 'released_at', 'updated_at' ), '' ),
            'platforms'    => (string) developer_starter_qiapp_get_post_meta_first_value( $post_id, array( 'qiapp_platforms', '_qiapp_platforms', 'qilingapp_platforms', 'platforms', 'platform', 'support_platforms' ), '' ),
            'file_size'    => (string) developer_starter_qiapp_get_post_meta_first_value( $post_id, array( 'qiapp_file_size', '_qiapp_file_size', 'qilingapp_file_size', 'file_size', 'size' ), '' ),
        );
    }
}

if ( ! function_exists( 'developer_starter_qiapp_get_software_row_by_post_id' ) ) {
    /**
     * 获取文章对应软件行数据（新版/旧版兼容）。
     *
     * @param int $post_id 文章 ID。
     * @return object|null
     */
    function developer_starter_qiapp_get_software_row_by_post_id( $post_id ) {
        $post_id = absint( $post_id );
        if ( ! $post_id ) {
            return null;
        }

        $software_class = developer_starter_qiapp_get_software_provider_class();
        if ( '' !== $software_class && method_exists( $software_class, 'get_by_post_id' ) ) {
            $software = call_user_func( array( $software_class, 'get_by_post_id' ), $post_id );
            if ( $software ) {
                return $software;
            }
        }

        global $wpdb;
        $software_table = developer_starter_qiapp_resolve_table_name( 'software' );
        if ( '' !== $software_table ) {
            $post_id_column = function_exists( 'developer_starter_qiapp_pick_first_existing_column' )
                ? developer_starter_qiapp_pick_first_existing_column(
                    $software_table,
                    array( 'post_id', 'postid', 'postId', 'wp_post_id', 'article_id', 'object_id', 'content_id' )
                )
                : 'post_id';

            if ( '' !== $post_id_column ) {
                $software = $wpdb->get_row(
                    $wpdb->prepare(
                        "SELECT * FROM `{$software_table}` WHERE `{$post_id_column}` = %d LIMIT 1",
                        $post_id
                    )
                );
                if ( $software ) {
                    return $software;
                }
            }
        }

        return developer_starter_qiapp_build_meta_software( $post_id );
    }
}

if ( ! function_exists( 'developer_starter_qiapp_get_latest_version_row' ) ) {
    /**
     * 获取软件最新版本（新版/旧版兼容）。
     *
     * @param int $software_id 软件 ID。
     * @param int $post_id     软件文章 ID。
     * @return object|null
     */
    function developer_starter_qiapp_get_latest_version_row( $software_id, $post_id = 0 ) {
        $software_id = absint( $software_id );
        $post_id     = absint( $post_id );

        $software_class = developer_starter_qiapp_get_software_provider_class();
        if ( '' !== $software_class && $software_id > 0 && method_exists( $software_class, 'get_latest_version' ) ) {
            $latest = call_user_func( array( $software_class, 'get_latest_version' ), $software_id );
            if ( $latest ) {
                return $latest;
            }
        }

        global $wpdb;
        $versions_table = developer_starter_qiapp_resolve_table_name( 'versions' );
        if ( '' !== $versions_table && $software_id > 0 ) {
            $software_id_column = function_exists( 'developer_starter_qiapp_pick_first_existing_column' )
                ? developer_starter_qiapp_pick_first_existing_column(
                    $versions_table,
                    array( 'software_id', 'softwareid', 'softwareId', 'app_id', 'appId', 'item_id' )
                )
                : 'software_id';

            if ( '' !== $software_id_column ) {
                $id_column = function_exists( 'developer_starter_qiapp_pick_first_existing_column' )
                    ? developer_starter_qiapp_pick_first_existing_column( $versions_table, array( 'id', 'version_id', 'versionId' ) )
                    : 'id';

                $sort_column = function_exists( 'developer_starter_qiapp_pick_first_existing_column' )
                    ? developer_starter_qiapp_pick_first_existing_column( $versions_table, array( 'sort_order', 'sort', 'display_order' ) )
                    : '';
                $date_column = function_exists( 'developer_starter_qiapp_pick_first_existing_column' )
                    ? developer_starter_qiapp_pick_first_existing_column( $versions_table, array( 'release_date', 'released_at', 'updated_at', 'created_at' ) )
                    : '';

                $order_parts = array();
                if ( '' !== $sort_column ) {
                    $order_parts[] = "`{$sort_column}` ASC";
                    if ( '' !== $id_column ) {
                        $order_parts[] = "`{$id_column}` ASC";
                    }
                } else {
                    if ( '' !== $date_column ) {
                        $order_parts[] = "`{$date_column}` DESC";
                    }
                    if ( '' !== $id_column ) {
                        $order_parts[] = "`{$id_column}` DESC";
                    }
                }
                if ( empty( $order_parts ) ) {
                    $order_parts[] = '`' . $software_id_column . '` DESC';
                }

                $latest = $wpdb->get_row(
                    $wpdb->prepare(
                        "SELECT * FROM `{$versions_table}` WHERE `{$software_id_column}` = %d ORDER BY " . implode( ', ', array_unique( $order_parts ) ) . ' LIMIT 1',
                        $software_id
                    )
                );
                if ( $latest ) {
                    return $latest;
                }
            }
        }

        if ( $post_id > 0 ) {
            return developer_starter_qiapp_build_meta_latest_version( $post_id );
        }

        return null;
    }
}

if ( ! function_exists( 'developer_starter_qiapp_get_post_ids_via_wp_query' ) ) {
    /**
     * 通过 WP_Query 获取软件文章 ID（数据库模型不可用时兜底）。
     *
     * @param string $post_type 软件 post type。
     * @param string $taxonomy  软件分类 taxonomy。
     * @param array  $term_ids  分类ID列表。
     * @param int    $limit     数量。
     * @param string $orderby   排序字段。
     * @return array
     */
    function developer_starter_qiapp_get_post_ids_via_wp_query( $post_type, $taxonomy, $term_ids, $limit, $orderby ) {
        $post_type = sanitize_key( (string) $post_type );
        if ( '' === $post_type || ! post_type_exists( $post_type ) ) {
            $post_type = function_exists( 'developer_starter_qiapp_guess_post_type_from_registered' )
                ? sanitize_key( (string) developer_starter_qiapp_guess_post_type_from_registered() )
                : '';
        }
        if ( '' === $post_type || ! post_type_exists( $post_type ) ) {
            return array();
        }

        $query_args = array(
            'post_type'              => $post_type,
            'post_status'            => 'publish',
            'posts_per_page'         => max( 1, min( 50, (int) $limit ) ),
            'fields'                 => 'ids',
            'ignore_sticky_posts'    => true,
            'no_found_rows'          => true,
            'update_post_meta_cache' => false,
            'update_post_term_cache' => false,
        );

        if ( ! empty( $term_ids ) && is_string( $taxonomy ) && '' !== $taxonomy ) {
            $query_args['tax_query'] = array(
                array(
                    'taxonomy' => $taxonomy,
                    'field'    => 'term_id',
                    'terms'    => array_map( 'absint', (array) $term_ids ),
                ),
            );
        }

        switch ( sanitize_key( (string) $orderby ) ) {
            case 'name':
                $query_args['orderby'] = 'title';
                $query_args['order']   = 'ASC';
                break;
            case 'random':
                $query_args['orderby'] = 'rand';
                break;
            case 'oldest':
                $query_args['orderby'] = 'date';
                $query_args['order']   = 'ASC';
                break;
            case 'updated':
            case 'modified':
            case 'latest':
                $query_args['orderby'] = 'modified';
                $query_args['order']   = 'DESC';
                break;
            case 'downloads':
            case 'views':
            case 'newest':
            case 'date':
            default:
                $query_args['orderby'] = 'date';
                $query_args['order']   = 'DESC';
                break;
        }

        return array_values( array_filter( array_map( 'intval', get_posts( $query_args ) ) ) );
    }
}

if ( ! function_exists( 'developer_starter_qiapp_get_post_type' ) ) {
    /**
     * 获取软件 CPT。
     *
     * @return string
     */
    function developer_starter_qiapp_get_post_type() {
        static $post_type = null;

        if ( null === $post_type ) {
            $post_type = '';

            $provider_class = function_exists( 'developer_starter_qiapp_get_post_type_provider_class' )
                ? developer_starter_qiapp_get_post_type_provider_class()
                : '';

            if ( '' !== $provider_class && method_exists( $provider_class, 'get_post_type' ) ) {
                $resolved = call_user_func( array( $provider_class, 'get_post_type' ) );
                if ( is_string( $resolved ) && '' !== trim( $resolved ) ) {
                    $post_type = sanitize_key( $resolved );
                }
            }

            if ( '' === $post_type && function_exists( 'developer_starter_qiapp_call_first_function' ) ) {
                $resolved = developer_starter_qiapp_call_first_function(
                    array(
                        'qilingapp_get_post_type',
                        'qiapp_get_post_type',
                        'qlapp_get_post_type',
                    )
                );
                if ( is_string( $resolved ) && '' !== trim( $resolved ) ) {
                    $post_type = sanitize_key( $resolved );
                }
            }

            if ( '' === $post_type && function_exists( 'developer_starter_qiapp_get_service_instance' ) ) {
                $service = developer_starter_qiapp_get_service_instance();
                if ( is_object( $service ) ) {
                    foreach ( array( 'get_post_type', 'post_type', 'software_post_type' ) as $method_name ) {
                        if ( ! method_exists( $service, $method_name ) ) {
                            continue;
                        }
                        $resolved = call_user_func( array( $service, $method_name ) );
                        if ( is_string( $resolved ) && '' !== trim( $resolved ) ) {
                            $post_type = sanitize_key( $resolved );
                            break;
                        }
                    }
                }
            }

            if ( '' === $post_type && function_exists( 'developer_starter_qiapp_guess_post_type_from_registered' ) ) {
                $post_type = sanitize_key( (string) developer_starter_qiapp_guess_post_type_from_registered() );
            }

            $candidates = array(
                $post_type,
                'qiapp_software',
                'qilingapp_software',
                'qlapp_software',
                'qiling_app_software',
                'qiling_app',
            );

            foreach ( array_values( array_unique( $candidates ) ) as $candidate ) {
                $candidate = sanitize_key( (string) $candidate );
                if ( '' !== $candidate && post_type_exists( $candidate ) ) {
                    $post_type = $candidate;
                    break;
                }
            }

            if ( '' === $post_type ) {
                $post_type = 'qiapp_software';
            }
        }

        return $post_type;
    }
}

if ( ! function_exists( 'developer_starter_qiapp_get_category_taxonomy' ) ) {
    /**
     * 获取软件分类 taxonomy。
     *
     * @return string
     */
    function developer_starter_qiapp_get_category_taxonomy() {
        static $taxonomy = null;

        if ( null === $taxonomy ) {
            $taxonomy = '';

            $provider_class = function_exists( 'developer_starter_qiapp_get_post_type_provider_class' )
                ? developer_starter_qiapp_get_post_type_provider_class()
                : '';

            if ( '' !== $provider_class && method_exists( $provider_class, 'get_category_taxonomy' ) ) {
                $resolved = call_user_func( array( $provider_class, 'get_category_taxonomy' ) );
                if ( is_string( $resolved ) && '' !== trim( $resolved ) ) {
                    $taxonomy = sanitize_key( $resolved );
                }
            }

            if ( '' === $taxonomy && function_exists( 'developer_starter_qiapp_call_first_function' ) ) {
                $resolved = developer_starter_qiapp_call_first_function(
                    array(
                        'qilingapp_get_category_taxonomy',
                        'qiapp_get_category_taxonomy',
                        'qlapp_get_category_taxonomy',
                    )
                );
                if ( is_string( $resolved ) && '' !== trim( $resolved ) ) {
                    $taxonomy = sanitize_key( $resolved );
                }
            }

            if ( '' === $taxonomy && function_exists( 'developer_starter_qiapp_get_service_instance' ) ) {
                $service = developer_starter_qiapp_get_service_instance();
                if ( is_object( $service ) ) {
                    foreach ( array( 'get_category_taxonomy', 'category_taxonomy', 'software_category_taxonomy' ) as $method_name ) {
                        if ( ! method_exists( $service, $method_name ) ) {
                            continue;
                        }
                        $resolved = call_user_func( array( $service, $method_name ) );
                        if ( is_string( $resolved ) && '' !== trim( $resolved ) ) {
                            $taxonomy = sanitize_key( $resolved );
                            break;
                        }
                    }
                }
            }

            if ( '' === $taxonomy && function_exists( 'developer_starter_qiapp_guess_taxonomy_for_post_type' ) ) {
                $taxonomy = sanitize_key(
                    (string) developer_starter_qiapp_guess_taxonomy_for_post_type(
                        developer_starter_qiapp_get_post_type(),
                        'category'
                    )
                );
            }

            $candidates = array(
                $taxonomy,
                'qiapp_software_category',
                'qilingapp_software_category',
                'qlapp_software_category',
                'qiling_app_software_category',
                'software_category',
                'app_category',
            );

            foreach ( array_values( array_unique( $candidates ) ) as $candidate ) {
                $candidate = sanitize_key( (string) $candidate );
                if ( '' !== $candidate && taxonomy_exists( $candidate ) ) {
                    $taxonomy = $candidate;
                    break;
                }
            }

            if ( '' === $taxonomy ) {
                $taxonomy = 'qiapp_software_category';
            }
        }

        return $taxonomy;
    }
}

if ( ! function_exists( 'developer_starter_qiapp_get_tag_taxonomy' ) ) {
    /**
     * 获取软件标签 taxonomy。
     *
     * @return string
     */
    function developer_starter_qiapp_get_tag_taxonomy() {
        static $taxonomy = null;

        if ( null === $taxonomy ) {
            $taxonomy = '';

            $provider_class = function_exists( 'developer_starter_qiapp_get_post_type_provider_class' )
                ? developer_starter_qiapp_get_post_type_provider_class()
                : '';

            if ( '' !== $provider_class && method_exists( $provider_class, 'get_tag_taxonomy' ) ) {
                $resolved = call_user_func( array( $provider_class, 'get_tag_taxonomy' ) );
                if ( is_string( $resolved ) && '' !== trim( $resolved ) ) {
                    $taxonomy = sanitize_key( $resolved );
                }
            }

            if ( '' === $taxonomy && function_exists( 'developer_starter_qiapp_call_first_function' ) ) {
                $resolved = developer_starter_qiapp_call_first_function(
                    array(
                        'qilingapp_get_tag_taxonomy',
                        'qiapp_get_tag_taxonomy',
                        'qlapp_get_tag_taxonomy',
                    )
                );
                if ( is_string( $resolved ) && '' !== trim( $resolved ) ) {
                    $taxonomy = sanitize_key( $resolved );
                }
            }

            if ( '' === $taxonomy && function_exists( 'developer_starter_qiapp_get_service_instance' ) ) {
                $service = developer_starter_qiapp_get_service_instance();
                if ( is_object( $service ) ) {
                    foreach ( array( 'get_tag_taxonomy', 'tag_taxonomy', 'software_tag_taxonomy' ) as $method_name ) {
                        if ( ! method_exists( $service, $method_name ) ) {
                            continue;
                        }
                        $resolved = call_user_func( array( $service, $method_name ) );
                        if ( is_string( $resolved ) && '' !== trim( $resolved ) ) {
                            $taxonomy = sanitize_key( $resolved );
                            break;
                        }
                    }
                }
            }

            if ( '' === $taxonomy && function_exists( 'developer_starter_qiapp_guess_taxonomy_for_post_type' ) ) {
                $taxonomy = sanitize_key(
                    (string) developer_starter_qiapp_guess_taxonomy_for_post_type(
                        developer_starter_qiapp_get_post_type(),
                        'tag'
                    )
                );
            }

            $candidates = array(
                $taxonomy,
                'qiapp_software_tag',
                'qilingapp_software_tag',
                'qlapp_software_tag',
                'qiling_app_software_tag',
                'software_tag',
                'app_tag',
            );

            foreach ( array_values( array_unique( $candidates ) ) as $candidate ) {
                $candidate = sanitize_key( (string) $candidate );
                if ( '' !== $candidate && taxonomy_exists( $candidate ) ) {
                    $taxonomy = $candidate;
                    break;
                }
            }

            if ( '' === $taxonomy ) {
                $taxonomy = 'qiapp_software_tag';
            }
        }

        return $taxonomy;
    }
}

if ( ! function_exists( 'developer_starter_qiapp_get_archive_link' ) ) {
    /**
     * 获取软件归档链接。
     *
     * @return string
     */
    function developer_starter_qiapp_get_archive_link() {
        $archive_link = get_post_type_archive_link( developer_starter_qiapp_get_post_type() );
        if ( $archive_link ) {
            return $archive_link;
        }

        $fallback_slug = 'software-library';
        $provider_class = function_exists( 'developer_starter_qiapp_get_post_type_provider_class' )
            ? developer_starter_qiapp_get_post_type_provider_class()
            : '';
        if ( '' !== $provider_class && method_exists( $provider_class, 'get_archive_slug' ) ) {
            $resolved_slug = call_user_func( array( $provider_class, 'get_archive_slug' ) );
            if ( is_string( $resolved_slug ) && '' !== trim( $resolved_slug ) ) {
                $fallback_slug = trim( $resolved_slug, '/' );
            }
        }

        return home_url( '/' . trim( (string) $fallback_slug, '/' ) . '/' );
    }
}

if ( ! function_exists( 'developer_starter_qiapp_get_total_software_count' ) ) {
    /**
     * 获取软件总数。
     *
     * @return int
     */
    function developer_starter_qiapp_get_total_software_count() {
        $software_class = function_exists( 'developer_starter_qiapp_get_software_provider_class' )
            ? developer_starter_qiapp_get_software_provider_class()
            : '';
        if ( '' !== $software_class && method_exists( $software_class, 'get_total_count' ) ) {
            return (int) call_user_func( array( $software_class, 'get_total_count' ) );
        }

        $post_type = developer_starter_qiapp_get_post_type();
        if ( ! post_type_exists( $post_type ) && function_exists( 'developer_starter_qiapp_guess_post_type_from_registered' ) ) {
            $post_type = (string) developer_starter_qiapp_guess_post_type_from_registered();
        }
        if ( ! post_type_exists( $post_type ) ) {
            return 0;
        }
        $counts    = wp_count_posts( $post_type );
        if ( is_object( $counts ) && isset( $counts->publish ) ) {
            return max( 0, (int) $counts->publish );
        }

        return 0;
    }
}

if ( ! function_exists( 'developer_starter_qiapp_get_total_downloads_count' ) ) {
    /**
     * 获取总下载量。
     *
     * @return int
     */
    function developer_starter_qiapp_get_total_downloads_count() {
        $software_class = function_exists( 'developer_starter_qiapp_get_software_provider_class' )
            ? developer_starter_qiapp_get_software_provider_class()
            : '';
        if ( '' !== $software_class && method_exists( $software_class, 'get_total_downloads' ) ) {
            return (int) call_user_func( array( $software_class, 'get_total_downloads' ) );
        }

        global $wpdb;
        $software_table = function_exists( 'developer_starter_qiapp_resolve_table_name' )
            ? developer_starter_qiapp_resolve_table_name( 'software' )
            : '';
        if ( '' !== $software_table ) {
            $download_column = function_exists( 'developer_starter_qiapp_pick_first_existing_column' )
                ? developer_starter_qiapp_pick_first_existing_column(
                    $software_table,
                    array( 'total_downloads', 'download_count', 'downloads', 'real_downloads' )
                )
                : 'total_downloads';

            if ( '' !== $download_column ) {
                $sum = $wpdb->get_var( "SELECT SUM(CAST(COALESCE(`{$download_column}`, 0) AS UNSIGNED)) FROM `{$software_table}`" );
                if ( null !== $sum ) {
                    return max( 0, (int) $sum );
                }
            }
        }

        return 0;
    }
}

if ( ! function_exists( 'developer_starter_qiapp_get_software_for_post' ) ) {
    /**
     * 获取指定文章对应的软件对象。
     *
     * @param int $post_id 文章 ID。
     * @return object|null
     */
    function developer_starter_qiapp_get_software_for_post( $post_id = 0 ) {
        static $request_cache = array();

        if ( ! developer_starter_qiapp_is_available() ) {
            return null;
        }

        $post_id = $post_id ? absint( $post_id ) : get_the_ID();
        if ( ! $post_id ) {
            return null;
        }

        if ( array_key_exists( $post_id, $request_cache ) ) {
            return $request_cache[ $post_id ];
        }

        $software_post_type = developer_starter_qiapp_get_post_type();
        if ( $software_post_type && post_type_exists( $software_post_type ) && get_post_type( $post_id ) !== $software_post_type ) {
            $request_cache[ $post_id ] = null;
            return null;
        }

        $request_cache[ $post_id ] = function_exists( 'developer_starter_qiapp_get_software_row_by_post_id' )
            ? developer_starter_qiapp_get_software_row_by_post_id( $post_id )
            : null;

        return $request_cache[ $post_id ];
    }
}

if ( ! function_exists( 'developer_starter_qiapp_has_software_data' ) ) {
    /**
     * 当前文章是否存在启灵App 软件数据。
     *
     * @param int $post_id 文章 ID。
     * @return bool
     */
    function developer_starter_qiapp_has_software_data( $post_id = 0 ) {
        return (bool) developer_starter_qiapp_get_software_for_post( $post_id );
    }
}

if ( ! function_exists( 'developer_starter_qiapp_get_screenshot_url' ) ) {
    /**
     * 获取软件截图 URL。
     *
     * @param int $post_id 文章 ID。
     * @return string
     */
    function developer_starter_qiapp_get_screenshot_url( $post_id = 0 ) {
        $software = developer_starter_qiapp_get_software_for_post( $post_id );
        $screenshot = function_exists( 'developer_starter_qiapp_get_value_from_source' )
            ? developer_starter_qiapp_get_value_from_source( $software, array( 'software_screenshot', 'screenshot', 'cover_image' ), '' )
            : '';
        if ( '' === (string) $screenshot ) {
            return '';
        }

        $screenshot = (string) $screenshot;
        if ( function_exists( 'developer_starter_normalize_local_media_url' ) ) {
            $normalized = developer_starter_normalize_local_media_url( $screenshot );
            // ===== 终极兜底 =====
            // 如果 normalize 把原本有的链接过滤成了空（通常是因为改域名/CDN导致物理文件检查失败）
            // 我们直接返回原始链接，强行显示
            if ( empty( $normalized ) && ! empty( $screenshot ) ) {
                return $screenshot;
            }
            $screenshot = $normalized;
        }

        return $screenshot;
    }
}

if ( ! function_exists( 'developer_starter_qiapp_parse_term_ids' ) ) {
    /**
     * 解析逗号分隔 term ID。
     *
     * @param string|array $raw 原始输入。
     * @return array
     */
    function developer_starter_qiapp_parse_term_ids( $raw ) {
        if ( is_array( $raw ) ) {
            return array_values( array_filter( array_unique( array_map( 'absint', $raw ) ) ) );
        }

        $parts = preg_split( '/[\s,，]+/', (string) $raw );

        if ( ! is_array( $parts ) ) {
            return array();
        }

        return array_values( array_filter( array_unique( array_map( 'absint', $parts ) ) ) );
    }
}

if ( ! function_exists( 'developer_starter_qiapp_get_license_map' ) ) {
    /**
     * 获取授权类型映射。
     *
     * @return array
     */
    function developer_starter_qiapp_get_license_map() {
        return array(
            'free'         => array( 'text' => __( '免费', 'developer-starter' ), 'class' => 'qiapp-badge-free' ),
            'opensource'   => array( 'text' => __( '开源', 'developer-starter' ), 'class' => 'qiapp-badge-opensource' ),
            'paid'         => array( 'text' => __( '收费', 'developer-starter' ), 'class' => 'qiapp-badge-paid' ),
            'trial'        => array( 'text' => __( '试用', 'developer-starter' ), 'class' => 'qiapp-badge-trial' ),
            'subscription' => array( 'text' => __( '订阅', 'developer-starter' ), 'class' => 'qiapp-badge-subscription' ),
            'ad'           => array( 'text' => __( '广告支持', 'developer-starter' ), 'class' => 'qiapp-badge-ad' ),
            'freemium'     => array( 'text' => __( '免费增值', 'developer-starter' ), 'class' => 'qiapp-badge-freemium' ),
        );
    }
}

if ( ! function_exists( 'developer_starter_qiapp_get_platform_map' ) ) {
    /**
     * 获取平台映射。
     *
     * @return array
     */
    function developer_starter_qiapp_get_platform_map() {
        return array(
            'windows' => 'Windows',
            'macos'   => 'macOS',
            'linux'   => 'Linux',
            'android' => 'Android',
            'ios'     => 'iOS',
        );
    }
}

if ( ! function_exists( 'developer_starter_qiapp_format_count_short' ) ) {
    /**
     * 紧凑数字格式。
     *
     * @param int $count 数值。
     * @return string
     */
    function developer_starter_qiapp_format_count_short( $count ) {
        $count = (int) $count;

        if ( $count >= 100000000 ) {
            return number_format_i18n( round( $count / 100000000, 1 ), 1 ) . __( '亿', 'developer-starter' );
        }

        if ( $count >= 10000 ) {
            return number_format_i18n( round( $count / 10000, 1 ), 1 ) . __( '万', 'developer-starter' );
        }

        return number_format_i18n( $count );
    }
}

if ( ! function_exists( 'developer_starter_qiapp_format_date' ) ) {
    /**
     * 统一格式化日期。
     *
     * @param string $value 原始日期。
     * @return string
     */
    function developer_starter_qiapp_format_date( $value ) {
        $value = trim( (string) $value );
        if ( '' === $value ) {
            return '';
        }

        if ( function_exists( 'developer_starter_format_date_value' ) ) {
            return developer_starter_format_date_value( $value );
        }

        $timestamp = strtotime( $value );
        if ( false === $timestamp ) {
            return $value;
        }

        return date_i18n( 'Y-m-d', $timestamp );
    }
}

if ( ! function_exists( 'developer_starter_qiapp_get_post_ids' ) ) {
    /**
     * 按启灵App 自定义表读取软件文章 ID。
     *
     * @param array $args 查询参数。
     * @return array
     */
    function developer_starter_qiapp_get_post_ids( $args = array() ) {
        static $request_cache = array();

        if ( ! developer_starter_qiapp_is_available() ) {
            return array();
        }

        $defaults = array(
            'term_ids' => array(),
            'limit'    => 10,
            'orderby'  => 'date',
        );
        $args = wp_parse_args( $args, $defaults );

        $term_ids = developer_starter_qiapp_parse_term_ids( $args['term_ids'] );
        $limit    = max( 1, min( 50, (int) $args['limit'] ) );
        $orderby  = sanitize_key( (string) $args['orderby'] );

        $request_key = md5( wp_json_encode( array( $term_ids, $limit, $orderby ) ) );
        if ( isset( $request_cache[ $request_key ] ) ) {
            return $request_cache[ $request_key ];
        }

        $use_persistent_cache = ! is_user_logged_in() && 'random' !== $orderby;
        if ( $use_persistent_cache && function_exists( 'developer_starter_has_logged_in_cookie_hint' ) ) {
            $use_persistent_cache = ! developer_starter_has_logged_in_cookie_hint();
        }

        $cache_key = 'qiapp_post_ids_v2_' . $request_key;
        if ( $use_persistent_cache ) {
            if ( function_exists( 'developer_starter_cache_fetch' ) ) {
                $cached = developer_starter_cache_fetch( $cache_key, 'developer_starter_module' );
            } else {
                $cached = get_transient( $cache_key );
            }

            if ( is_array( $cached ) ) {
                $request_cache[ $request_key ] = array_values( array_filter( array_map( 'intval', $cached ) ) );
                return $request_cache[ $request_key ];
            }
        }

        global $wpdb;
        $post_type = developer_starter_qiapp_get_post_type();
        $taxonomy  = developer_starter_qiapp_get_category_taxonomy();
        $post_ids  = array();

        $software_table = function_exists( 'developer_starter_qiapp_resolve_table_name' )
            ? developer_starter_qiapp_resolve_table_name( 'software' )
            : '';

        if ( '' !== $software_table ) {
            $post_id_column = function_exists( 'developer_starter_qiapp_pick_first_existing_column' )
                ? developer_starter_qiapp_pick_first_existing_column(
                    $software_table,
                    array( 'post_id', 'postid', 'postId', 'wp_post_id', 'article_id', 'object_id', 'content_id' )
                )
                : 'post_id';

            if ( '' !== $post_id_column ) {
                $download_column = function_exists( 'developer_starter_qiapp_pick_first_existing_column' )
                    ? developer_starter_qiapp_pick_first_existing_column( $software_table, array( 'total_downloads', 'download_count', 'downloads', 'real_downloads' ) )
                    : 'total_downloads';
                $view_column = function_exists( 'developer_starter_qiapp_pick_first_existing_column' )
                    ? developer_starter_qiapp_pick_first_existing_column( $software_table, array( 'view_count', 'views', 'total_views' ) )
                    : 'view_count';
                $updated_column = function_exists( 'developer_starter_qiapp_pick_first_existing_column' )
                    ? developer_starter_qiapp_pick_first_existing_column( $software_table, array( 'updated_at', 'modified_at', 'last_updated', 'updated_time', 'updatedAt' ) )
                    : 'updated_at';

                $joins = " INNER JOIN {$wpdb->posts} p ON p.ID = s.`{$post_id_column}`";
                $where = array(
                    "s.`{$post_id_column}` IS NOT NULL",
                    "s.`{$post_id_column}` > 0",
                    "p.post_status = 'publish'",
                );
                if ( is_string( $post_type ) && '' !== $post_type && post_type_exists( $post_type ) ) {
                    $where[] = $wpdb->prepare( 'p.post_type = %s', $post_type );
                }
                $prepare_args = array();

                if ( ! empty( $term_ids ) && is_string( $taxonomy ) && '' !== $taxonomy && taxonomy_exists( $taxonomy ) ) {
                    $joins .= " INNER JOIN {$wpdb->term_relationships} tr ON tr.object_id = p.ID";
                    $joins .= $wpdb->prepare(
                        " INNER JOIN {$wpdb->term_taxonomy} tt ON tt.term_taxonomy_id = tr.term_taxonomy_id AND tt.taxonomy = %s",
                        $taxonomy
                    );
                    $where[] = 'tt.term_id IN (' . implode( ',', array_fill( 0, count( $term_ids ), '%d' ) ) . ')';
                    $prepare_args = array_merge( $prepare_args, $term_ids );
                }

                switch ( $orderby ) {
                    case 'downloads':
                        if ( '' !== $download_column ) {
                            $order_sql = 'CAST(COALESCE(s.`' . $download_column . '`, 0) AS UNSIGNED) DESC, p.ID DESC';
                        } else {
                            $order_sql = 'p.post_date DESC, p.ID DESC';
                        }
                        break;
                    case 'views':
                        if ( '' !== $view_column ) {
                            $order_sql = 'CAST(COALESCE(s.`' . $view_column . '`, 0) AS UNSIGNED) DESC, p.ID DESC';
                        } else {
                            $order_sql = 'p.post_date DESC, p.ID DESC';
                        }
                        break;
                    case 'updated':
                    case 'modified':
                    case 'latest':
                        if ( '' !== $updated_column ) {
                            $order_sql = 'COALESCE(s.`' . $updated_column . '`, p.post_modified) DESC, p.post_modified DESC';
                        } else {
                            $order_sql = 'p.post_modified DESC, p.ID DESC';
                        }
                        break;
                    case 'name':
                        $order_sql = 'p.post_title ASC, p.ID DESC';
                        break;
                    case 'random':
                        $order_sql = 'RAND()';
                        break;
                    case 'oldest':
                        $order_sql = 'p.post_date ASC, p.ID ASC';
                        break;
                    case 'newest':
                    case 'date':
                    default:
                        $order_sql = 'p.post_date DESC, p.ID DESC';
                        break;
                }

                $sql = "SELECT DISTINCT p.ID
                    FROM `{$software_table}` s
                    {$joins}
                    WHERE " . implode( ' AND ', $where ) . "
                    ORDER BY {$order_sql}
                    LIMIT %d";

                $prepare_args[] = $limit;
                $prepared_sql = $wpdb->prepare( $sql, $prepare_args );
                $post_ids = array_values( array_filter( array_map( 'intval', (array) $wpdb->get_col( $prepared_sql ) ) ) );
            }
        }

        if ( empty( $post_ids ) && function_exists( 'developer_starter_qiapp_get_post_ids_via_wp_query' ) ) {
            $post_ids = developer_starter_qiapp_get_post_ids_via_wp_query(
                $post_type,
                $taxonomy,
                $term_ids,
                $limit,
                $orderby
            );
        }

        $request_cache[ $request_key ] = $post_ids;

        if ( $use_persistent_cache ) {
            if ( function_exists( 'developer_starter_cache_store' ) ) {
                developer_starter_cache_store( $cache_key, $post_ids, 10 * MINUTE_IN_SECONDS, 'developer_starter_module' );
            } else {
                set_transient( $cache_key, $post_ids, 10 * MINUTE_IN_SECONDS );
            }
        }

        return $post_ids;
    }
}

if ( ! function_exists( 'developer_starter_qiapp_preload_entries' ) ) {
    /**
     * 批量预取软件与最新版本。
     *
     * @param array $post_ids 文章 ID 列表。
     * @return array
     */
    function developer_starter_qiapp_preload_entries( $post_ids ) {
        static $request_cache = array();

        $post_ids = array_values( array_filter( array_unique( array_map( 'intval', (array) $post_ids ) ) ) );
        if ( empty( $post_ids ) || ! developer_starter_qiapp_is_available() ) {
            return array(
                'software'        => array(),
                'latest_versions' => array(),
            );
        }

        $request_key = md5( wp_json_encode( $post_ids ) );
        if ( isset( $request_cache[ $request_key ] ) ) {
            return $request_cache[ $request_key ];
        }

        $software_map = array();
        $latest_versions = array();

        foreach ( $post_ids as $post_id ) {
            $software_row = function_exists( 'developer_starter_qiapp_get_software_row_by_post_id' )
                ? developer_starter_qiapp_get_software_row_by_post_id( $post_id )
                : null;

            if ( ! $software_row ) {
                continue;
            }

            $software_map[ (int) $post_id ] = $software_row;

            $software_id = function_exists( 'developer_starter_qiapp_get_value_from_source' )
                ? (int) developer_starter_qiapp_get_value_from_source( $software_row, array( 'id', 'software_id' ), 0 )
                : 0;

            $latest_row = function_exists( 'developer_starter_qiapp_get_latest_version_row' )
                ? developer_starter_qiapp_get_latest_version_row( $software_id, $post_id )
                : null;

            if ( ! $latest_row ) {
                continue;
            }

            if ( $software_id > 0 ) {
                $latest_versions[ $software_id ] = $latest_row;
            } else {
                $latest_versions[ 'post_' . (int) $post_id ] = $latest_row;
            }
        }

        $request_cache[ $request_key ] = array(
            'software'        => $software_map,
            'latest_versions' => $latest_versions,
        );

        return $request_cache[ $request_key ];
    }
}

if ( ! function_exists( 'developer_starter_qiapp_get_entry_data' ) ) {
    /**
     * 获取单个软件的统一展示数据。
     *
     * @param int   $post_id   软件文章 ID。
     * @param array $preloaded 预加载数据。
     * @return array|null
     */
    function developer_starter_qiapp_get_entry_data( $post_id, $preloaded = array() ) {
        if ( ! developer_starter_qiapp_is_available() ) {
            return null;
        }

        $post_id = absint( $post_id );
        if ( ! $post_id ) {
            return null;
        }

        $software_map = isset( $preloaded['software'] ) && is_array( $preloaded['software'] ) ? $preloaded['software'] : array();
        $version_map = isset( $preloaded['latest_versions'] ) && is_array( $preloaded['latest_versions'] ) ? $preloaded['latest_versions'] : array();

        $software = isset( $software_map[ $post_id ] )
            ? $software_map[ $post_id ]
            : ( function_exists( 'developer_starter_qiapp_get_software_row_by_post_id' ) ? developer_starter_qiapp_get_software_row_by_post_id( $post_id ) : null );
        if ( ! $software ) {
            return null;
        }

        $software_id = function_exists( 'developer_starter_qiapp_get_value_from_source' )
            ? (int) developer_starter_qiapp_get_value_from_source( $software, array( 'id', 'software_id' ), 0 )
            : 0;
        $latest_version = null;
        if ( $software_id > 0 && isset( $version_map[ $software_id ] ) ) {
            $latest_version = $version_map[ $software_id ];
        } elseif ( isset( $version_map[ 'post_' . $post_id ] ) ) {
            $latest_version = $version_map[ 'post_' . $post_id ];
        } elseif ( function_exists( 'developer_starter_qiapp_get_latest_version_row' ) ) {
            $latest_version = developer_starter_qiapp_get_latest_version_row( $software_id, $post_id );
        }

        $license_map = developer_starter_qiapp_get_license_map();
        $platform_map = developer_starter_qiapp_get_platform_map();
        $license_types = array();
        $license_source = function_exists( 'developer_starter_qiapp_get_value_from_source' )
            ? developer_starter_qiapp_get_value_from_source( $software, array( 'license_type', 'license', 'license_types' ), '' )
            : '';
        if ( ! empty( $license_source ) ) {
            $decoded = json_decode( (string) $license_source, true );
            if ( is_array( $decoded ) ) {
                $license_types = $decoded;
            } else {
                $license_types = array( (string) $license_source );
            }
        }
        if ( empty( $license_types ) ) {
            $license_types = array( 'free' );
        }

        $license_items = array();
        foreach ( array_unique( array_map( 'sanitize_key', $license_types ) ) as $license_key ) {
            if ( isset( $license_map[ $license_key ] ) ) {
                $license_items[] = $license_map[ $license_key ] + array( 'key' => $license_key );
            }
        }
        if ( empty( $license_items ) ) {
            $license_items[] = $license_map['free'] + array( 'key' => 'free' );
        }

        $platform_keys = array();
        $platforms_source = function_exists( 'developer_starter_qiapp_get_value_from_source' )
            ? developer_starter_qiapp_get_value_from_source( $latest_version, array( 'platforms', 'platform', 'support_platforms' ), '' )
            : '';
        if ( ! empty( $platforms_source ) ) {
            $decoded_platforms = json_decode( (string) $platforms_source, true );
            if ( is_array( $decoded_platforms ) ) {
                $platform_keys = array_values( array_filter( array_unique( array_map( 'sanitize_key', $decoded_platforms ) ) ) );
            } else {
                $platform_keys = array_values( array_filter( array_unique( array_map( 'sanitize_key', preg_split( '/[\s,，\/|]+/', (string) $platforms_source ) ) ) ) );
            }
        }
        $platform_names = array();
        foreach ( $platform_keys as $platform_key ) {
            if ( isset( $platform_map[ $platform_key ] ) ) {
                $platform_names[] = $platform_map[ $platform_key ];
            }
        }

        $taxonomy = developer_starter_qiapp_get_category_taxonomy();
        $categories = get_the_terms( $post_id, $taxonomy );
        if ( ! is_array( $categories ) ) {
            $categories = array();
        }
        $category_names = wp_list_pluck( $categories, 'name' );

        $summary = '';
        $summary_source = function_exists( 'developer_starter_qiapp_get_value_from_source' )
            ? developer_starter_qiapp_get_value_from_source( $software, array( 'short_description', 'summary', 'description' ), '' )
            : '';
        if ( '' !== (string) $summary_source ) {
            $summary = (string) $summary_source;
        } elseif ( has_excerpt( $post_id ) ) {
            $summary = (string) get_the_excerpt( $post_id );
        } else {
            $summary = wp_trim_words( wp_strip_all_tags( (string) get_post_field( 'post_content', $post_id ) ), 26, '...' );
        }

        $update_source = '';
        $release_date_source = function_exists( 'developer_starter_qiapp_get_value_from_source' )
            ? developer_starter_qiapp_get_value_from_source( $latest_version, array( 'release_date', 'released_at', 'updated_at' ), '' )
            : '';
        $updated_at_source = function_exists( 'developer_starter_qiapp_get_value_from_source' )
            ? developer_starter_qiapp_get_value_from_source( $software, array( 'updated_at', 'modified_at' ), '' )
            : '';
        if ( '' !== (string) $release_date_source ) {
            $update_source = (string) $release_date_source;
        } elseif ( '' !== (string) $updated_at_source ) {
            $update_source = (string) $updated_at_source;
        } else {
            $update_source = get_post_modified_time( 'Y-m-d H:i:s', false, $post_id );
        }

        $download_count = function_exists( 'developer_starter_qiapp_get_value_from_source' )
            ? (int) developer_starter_qiapp_get_value_from_source( $software, array( 'total_downloads', 'download_count', 'downloads', 'real_downloads' ), 0 )
            : 0;
        $view_count = function_exists( 'developer_starter_qiapp_get_value_from_source' )
            ? (int) developer_starter_qiapp_get_value_from_source( $software, array( 'view_count', 'views' ), 0 )
            : 0;
        $version = function_exists( 'developer_starter_qiapp_get_value_from_source' )
            ? (string) developer_starter_qiapp_get_value_from_source( $latest_version, array( 'version', 'version_name', 'latest_version' ), '' )
            : '';
        $update_timestamp = $update_source ? strtotime( $update_source ) : false;

        $title = function_exists( 'developer_starter_qiapp_get_value_from_source' )
            ? (string) developer_starter_qiapp_get_value_from_source( $software, array( 'software_name', 'name', 'app_name', 'title' ), '' )
            : '';
        if ( '' === $title ) {
            $title = get_the_title( $post_id );
        }

        $icon = function_exists( 'developer_starter_qiapp_get_value_from_source' )
            ? (string) developer_starter_qiapp_get_value_from_source( $software, array( 'software_icon', 'icon', 'app_icon' ), '' )
            : '';
        $developer = function_exists( 'developer_starter_qiapp_get_value_from_source' )
            ? (string) developer_starter_qiapp_get_value_from_source( $software, array( 'developer_name', 'developer', 'author' ), '' )
            : '';
        $rating = function_exists( 'developer_starter_qiapp_get_value_from_source' )
            ? (float) developer_starter_qiapp_get_value_from_source( $software, array( 'rating', 'score' ), 5 )
            : 5.0;
        $file_size = function_exists( 'developer_starter_qiapp_get_value_from_source' )
            ? (string) developer_starter_qiapp_get_value_from_source( $latest_version, array( 'file_size', 'size' ), '' )
            : '';

        return array(
            'software'              => $software,
            'latest_version'        => $latest_version,
            'post_id'               => $post_id,
            'permalink'             => get_permalink( $post_id ),
            'title'                 => $title,
            'icon'                  => $icon,
            'summary'               => $summary,
            'developer'             => $developer,
            'rating'                => $rating,
            'license_items'         => $license_items,
            'category_names'        => $category_names,
            'primary_category'      => ! empty( $category_names ) ? (string) reset( $category_names ) : '',
            'platform_names'        => $platform_names,
            'platform_text'         => implode( ' / ', $platform_names ),
            'version'               => $version,
            'version_label'         => '' !== $version ? 'v' . $version : '',
            'update_source'         => $update_source,
            'update_timestamp'      => $update_timestamp ? (int) $update_timestamp : 0,
            'release_date'          => developer_starter_qiapp_format_date( (string) $release_date_source ),
            'update_date'           => developer_starter_qiapp_format_date( $update_source ),
            'file_size'             => $file_size,
            'download_count'        => $download_count,
            'download_text'         => developer_starter_qiapp_format_count_short( $download_count ),
            'view_count'            => $view_count,
            'view_text'             => developer_starter_qiapp_format_count_short( $view_count ),
            'official_website'      => function_exists( 'developer_starter_qiapp_get_value_from_source' ) ? (string) developer_starter_qiapp_get_value_from_source( $software, array( 'official_website', 'website', 'site_url' ), '' ) : '',
            'docs_url'              => function_exists( 'developer_starter_qiapp_get_value_from_source' ) ? (string) developer_starter_qiapp_get_value_from_source( $software, array( 'docs_url', 'documentation_url', 'doc_url' ), '' ) : '',
            'support_url'           => function_exists( 'developer_starter_qiapp_get_value_from_source' ) ? (string) developer_starter_qiapp_get_value_from_source( $software, array( 'support_url', 'help_url' ), '' ) : '',
            'system_requirements'   => function_exists( 'developer_starter_qiapp_get_value_from_source' ) ? (string) developer_starter_qiapp_get_value_from_source( $software, array( 'system_requirements', 'requirements' ), '' ) : '',
            'installation_guide'    => function_exists( 'developer_starter_qiapp_get_value_from_source' ) ? (string) developer_starter_qiapp_get_value_from_source( $software, array( 'installation_guide', 'install_guide' ), '' ) : '',
            'primary_article_id'    => function_exists( 'developer_starter_qiapp_get_value_from_source' ) ? absint( developer_starter_qiapp_get_value_from_source( $software, array( 'primary_article_id', 'main_post_id' ), 0 ) ) : 0,
        );
    }
}
