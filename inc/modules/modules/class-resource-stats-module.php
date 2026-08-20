<?php
/**
 * Resource Stats Module - 博客资源统计
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Modules\Modules;

use Developer_Starter\Modules\Module_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Resource_Stats_Module extends Module_Base {

    public function __construct() {
        $this->category = 'homepage'; // 放在首页分类下
        $this->icon = 'dashicons-chart-pie'; // 使用饼图图标区分
        $this->description = __( '展示博客资源、用户、运行时间等综合数据统计', 'developer-starter' );
    }

    public function get_id() {
        return 'resource_stats';
    }

    public function get_name() {
        return __( '博客资源统计', 'developer-starter' );
    }

    public function get_fields() {
        return array(
            // 基础设置
            array( 'id' => 'rs_title', 'label' => __( '模块标题', 'developer-starter' ), 'type' => 'text', 'default' => '' ),
            array( 'id' => 'rs_subtitle', 'label' => __( '副标题', 'developer-starter' ), 'type' => 'text', 'default' => '' ),
            
            // 样式设置
            array(
                'id' => 'rs_title_color',
                'label' => __( '标题颜色', 'developer-starter' ),
                'type' => 'color',
                'default' => '',
            ),
            array(
                'id' => 'rs_subtitle_color',
                'label' => __( '副标题颜色', 'developer-starter' ),
                'type' => 'color',
                'default' => '',
            ),

            // 建站日期设置 (供运行天数使用)
            array(
                'id' => 'rs_site_start_date',
                'label' => __( '建站日期', 'developer-starter' ),
                'type' => 'text',
                'default' => '2020-01-01',
                'description' => __( '格式：YYYY-MM-DD，用于计算运行天数', 'developer-starter' )
            ),
            
            // 统计项设置
            array(
                'id' => 'rs_stats_list',
                'label' => __( '统计数据项', 'developer-starter' ),
                'type' => 'repeater',
                'description' => __( '添加要展示的数据项', 'developer-starter' ),
                'fields' => array(
                    array( 'id' => 'stat_label', 'label' => __( '显示名称', 'developer-starter' ), 'type' => 'text', 'default' => __( '总资源数', 'developer-starter' ) ),
                    array( 'id' => 'stat_icon', 'label' => __( '图标 (Dashicons/Emoji)', 'developer-starter' ), 'type' => 'text', 'default' => 'dashicons-chart-area' ),
                    
                    array( 
                        'id' => 'data_source', 
                        'label' => __( '数据来源', 'developer-starter' ), 
                        'type' => 'select', 
                        'options' => array(
                            'total_posts' => __( '全站文章总数', 'developer-starter' ),
                            'category_posts' => __( '指定分类文章数', 'developer-starter' ),
                            'total_users' => __( '全站用户总数', 'developer-starter' ),
                            'vip_users' => __( 'VIP会员总数', 'developer-starter' ),
                            'site_days' => __( '网站运行天数', 'developer-starter' ),
                            'custom' => __( '自定义数字', 'developer-starter' )
                        ),
                        'default' => 'total_posts'
                    ),
                    
                    array( 
                        'id' => 'source_id', 
                        'label' => __( '分类ID / 自定义数字', 'developer-starter' ), 
                        'type' => 'text', 
                        'description' => __( '如果是分类填ID；如果是自定义填数字；其他留空', 'developer-starter' )
                    ),
                    
                    array( 
                        'id' => 'virtual_num', 
                        'label' => __( '虚拟增长数值', 'developer-starter' ), 
                        'type' => 'number', 
                        'default' => '0',
                        'description' => __( '前台显示 = 真实数据 + 此虚拟数值', 'developer-starter' )
                    ),
                    
                    array( 'id' => 'show_plus', 'label' => __( '显示加号(+)', 'developer-starter' ), 'type' => 'select', 'options' => array( 'yes' => __( '显示', 'developer-starter' ), 'no' => __( '不显示', 'developer-starter' ) ), 'default' => 'yes' ),
                ),
            ),
            
            // 外观设置
            array(
                'id' => 'rs_bg_color',
                'label' => __( '背景颜色', 'developer-starter' ),
                'type' => 'color',
                'default' => 'var(--color-neutral-50)',
                'desc' => __( '支持颜色或渐变', 'developer-starter' ),
            ),
            array(
                'id' => 'rs_bg_image',
                'label' => __( '背景图片', 'developer-starter' ),
                'type' => 'image',
            ),
            
            // 间距
            array(
                'id' => 'rs_padding_top',
                'label' => __( '上边距', 'developer-starter' ),
                'type' => 'text',
                'default' => '80px',
            ),
            array(
                'id' => 'rs_padding_bottom',
                'label' => __( '下边距', 'developer-starter' ),
                'type' => 'text',
                'default' => '80px',
            ),
        );
    }

    /**
     * 获取统计数据（带缓存）
     */
    private function get_allowed_qilingshop_user_info_tables( $wpdb ) {
        $prefix = isset( $wpdb->prefix ) ? (string) $wpdb->prefix : '';
        $tables = array(
            $prefix . 'qilingshop_user_info',
        );

        if ( defined( 'QILINGSHOP_TABLE_PREFIX' ) ) {
            $tables[] = $prefix . (string) QILINGSHOP_TABLE_PREFIX . 'user_info';
        }

        $allowed = array();
        foreach ( $tables as $table ) {
            $table = (string) $table;
            if ( '' !== $table && '' !== $this->quote_table_identifier( $table ) ) {
                $allowed[] = $table;
            }
        }

        return array_values( array_unique( $allowed ) );
    }

    private function quote_table_identifier( $table_name ) {
        $table_name = (string) $table_name;
        if ( '' === $table_name || ! preg_match( '/^[A-Za-z0-9_]+$/', $table_name ) ) {
            return '';
        }

        return '`' . $table_name . '`';
    }

    private function resolve_qilingshop_user_info_table( $wpdb ) {
        $allowed = $this->get_allowed_qilingshop_user_info_tables( $wpdb );
        if ( empty( $allowed ) ) {
            return '';
        }

        $candidates = array();
        if ( isset( $wpdb->qilingshop_user_info ) ) {
            $candidates[] = (string) $wpdb->qilingshop_user_info;
        }
        $candidates = array_merge( $candidates, $allowed );

        foreach ( $candidates as $table_name ) {
            if ( in_array( $table_name, $allowed, true ) ) {
                return $table_name;
            }
        }

        return '';
    }

    private function table_exists( $wpdb, $table_name ) {
        if ( '' === $table_name || '' === $this->quote_table_identifier( $table_name ) ) {
            return false;
        }

        $exists = $wpdb->get_var(
            $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $table_name ) )
        );

        return $exists === $table_name;
    }

    private function get_stat_data( $source, $id = '', $start_date = '' ) {
        // 缓存键名构建：前缀 + 来源 + ID(如果有)
        $cache_key = 'ds_rs_stat_' . $source . '_' . md5( $id . $start_date );
        $cache_enabled = true;
        
        // 尝试获取缓存
        if ( $cache_enabled ) {
            if ( function_exists( 'developer_starter_cache_fetch' ) ) {
                $cached_val = \developer_starter_cache_fetch( $cache_key, 'developer_starter_module' );
            } else {
                $cached_val = get_transient( $cache_key );
            }
            if ( false !== $cached_val ) {
                return (int) $cached_val;
            }
        }

        $count = 0;
        
        switch ( $source ) {
            case 'total_posts':
                $count_posts = wp_count_posts();
                $count = isset( $count_posts->publish ) ? $count_posts->publish : 0;
                break;
                
            case 'category_posts':
                if ( $id ) {
                    $term = get_term( intval( $id ), 'category' );
                    if ( $term && ! is_wp_error( $term ) ) {
                        $count = $term->count;
                    }
                }
                break;
                
            case 'total_users':
                $result = count_users();
                $count = isset( $result['total_users'] ) ? $result['total_users'] : 0;
                break;
                
            case 'vip_users':
                global $wpdb;
                // 检查启灵商城表是否存在
                if ( defined( 'QILINGSHOP_TABLE_PREFIX' ) ) {
                    $table_name = $this->resolve_qilingshop_user_info_table( $wpdb );
                    $quoted_table = $this->quote_table_identifier( $table_name );

                    if ( '' !== $quoted_table && $this->table_exists( $wpdb, $table_name ) ) {
                        $today = current_time( 'Y-m-d' );
                        $count = $wpdb->get_var(
                            $wpdb->prepare(
                                "SELECT COUNT(*) FROM {$quoted_table} WHERE `vip_level` > %d AND `vip_expires` > %s",
                                0,
                                $today
                            )
                        );
                    }
                }
                break;
                
            case 'site_days':
                if ( $start_date ) {
                    $start_timestamp = strtotime( $start_date );
                    if ( $start_timestamp ) {
                        $now = current_time( 'timestamp' );
                        $diff = $now - $start_timestamp;
                        $count = floor( $diff / 86400 );
                        if ( $count < 0 ) $count = 0;
                    }
                }
                break;
                
            case 'custom':
                $count = intval( $id );
                break;
        }

        // 设置24小时缓存（仅游客）
        if ( $cache_enabled ) {
            if ( function_exists( 'developer_starter_cache_store' ) ) {
                \developer_starter_cache_store( $cache_key, $count, 24 * HOUR_IN_SECONDS, 'developer_starter_module' );
            } else {
                set_transient( $cache_key, $count, 24 * HOUR_IN_SECONDS );
            }
        }
        
        return (int) $count;
    }

    public function render( $data = array() ) {
        // 基础样式数据
        $title = isset( $data['rs_title'] ) ? $data['rs_title'] : '';
        $subtitle = isset( $data['rs_subtitle'] ) ? $data['rs_subtitle'] : '';
        $title_color = isset( $data['rs_title_color'] ) ? $data['rs_title_color'] : '';
        $subtitle_color = isset( $data['rs_subtitle_color'] ) ? $data['rs_subtitle_color'] : '';
        
        $bg_color = isset( $data['rs_bg_color'] ) ? $data['rs_bg_color'] : '';
        $bg_image = isset( $data['rs_bg_image'] ) ? $data['rs_bg_image'] : '';
        $pt = isset( $data['rs_padding_top'] ) ? $data['rs_padding_top'] : '80px';
        $pb = isset( $data['rs_padding_bottom'] ) ? $data['rs_padding_bottom'] : '80px';
        
        $site_start_date = isset( $data['rs_site_start_date'] ) ? $data['rs_site_start_date'] : '2020-01-01';
        $stats_list = isset( $data['rs_stats_list'] ) ? $data['rs_stats_list'] : array();
        
        // 构建 Section Style
        $section_style = "padding-top: {$pt}; padding-bottom: {$pb};";
        if ( $bg_image ) {
            $section_style .= "background-image: url('" . esc_url( $bg_image ) . "'); background-size: cover; background-position: center;";
        } elseif ( $bg_color ) {
            $section_style .= strpos( $bg_color, 'gradient' ) !== false ? "background: {$bg_color};" : "background-color: {$bg_color};";
        }
        
        // 唯一ID
        $module_id = 'rs-stats-' . uniqid();
        ?>
        <section class="module module-resource-stats <?php echo $bg_image ? 'has-bg-image' : ''; ?>" id="<?php echo esc_attr( $module_id ); ?>" style="<?php echo esc_attr( $section_style ); ?>">
            <div class="container">
                <?php if ( $title || $subtitle ) : ?>
                    <div class="section-header text-center rs-header">
                        <?php if ( $title ) : ?>
                            <h2 class="section-title rs-title" style="<?php echo $title_color ? 'color:' . esc_attr($title_color) : ''; ?>"><?php echo esc_html( $title ); ?></h2>
                        <?php endif; ?>
                        <?php if ( $subtitle ) : ?>
                            <p class="section-subtitle rs-subtitle" style="<?php echo $subtitle_color ? 'color:' . esc_attr($subtitle_color) : ''; ?>"><?php echo esc_html( $subtitle ); ?></p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <div class="rs-stats-grid">
                    <?php 
                    if ( ! empty( $stats_list ) ) :
                        foreach ( $stats_list as $item ) :
                            $source = isset( $item['data_source'] ) ? $item['data_source'] : 'custom';
                            $source_id = isset( $item['source_id'] ) ? $item['source_id'] : '';
                            $virtual = isset( $item['virtual_num'] ) ? intval( $item['virtual_num'] ) : 0;
                            $label = isset( $item['stat_label'] ) ? $item['stat_label'] : '';
                            $icon = isset( $item['stat_icon'] ) ? $item['stat_icon'] : '';
                            $show_plus = isset( $item['show_plus'] ) && $item['show_plus'] === 'yes';
                            
                            // 获取真实数据
                            $real_count = $this->get_stat_data( $source, $source_id, $site_start_date );
                            
                            // 最终显示数据 (真实 + 虚拟)
                            $display_count = $real_count + $virtual;
                    ?>
                        <div class="rs-stat-item">
                            <div class="rs-stat-content">
                                <?php if ( $icon ) : ?>
                                    <div class="rs-stat-icon">
                                        <?php if ( strpos( $icon, 'dashicons' ) === 0 ) : ?>
                                            <span class="dashicons <?php echo esc_attr( $icon ); ?>"></span>
                                        <?php else : ?>
                                            <?php echo esc_html( $icon ); ?>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="rs-stat-number-wrap">
                                    <span class="rs-stat-number"><?php echo esc_html( $display_count ); ?></span>
                                    <?php if ( $show_plus ) : ?>
                                        <span class="rs-stat-plus">+</span>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="rs-stat-label"><?php echo esc_html( $label ); ?></div>
                            </div>
                        </div>
                    <?php 
                        endforeach; 
                    endif;
                    ?>
                </div>
            </div>
        </section>
        <?php
    }
}
