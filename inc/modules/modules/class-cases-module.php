<?php
/**
 * Cases Module - 成功案例模块
 *
 * @package Developer_Starter
 * @since 1.0.0
 */

namespace Developer_Starter\Modules\Modules;

use Developer_Starter\Modules\Module_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Cases_Module extends Module_Base {

    public function __construct() {
        $this->category = 'content';
        $this->icon = 'dashicons-portfolio';
        $this->description = __( '案例展示', 'developer-starter' );
    }

    public function get_id() {
        return 'cases';
    }

    public function get_name() {
        return __( '案例展示', 'developer-starter' );
    }

    public function get_fields() {
        return array(
            array( 'id' => 'cases_title', 'type' => 'text', 'label' => __( '标题', 'developer-starter' ), 'default' => __( '成功案例', 'developer-starter' ) ),
            array( 'id' => 'module_bg_color', 'type' => 'color', 'label' => __( '背景颜色', 'developer-starter' ), 'default' => '' ),
            array( 'id' => 'cases_padding_top', 'type' => 'text', 'label' => __( '上边距', 'developer-starter' ), 'default' => '80px' ),
            array( 'id' => 'cases_padding_bottom', 'type' => 'text', 'label' => __( '下边距', 'developer-starter' ), 'default' => '80px' ),
            array( 'id' => 'cases_count', 'type' => 'number', 'label' => __( '显示数量', 'developer-starter' ), 'default' => '6' ),
            array( 'id' => 'cases_columns', 'type' => 'select', 'label' => __( '列数', 'developer-starter' ), 'options' => array(
                '2' => __( '2列', 'developer-starter' ),
                '3' => __( '3列', 'developer-starter' ),
                '4' => __( '4列', 'developer-starter' ),
            ), 'default' => '3' ),
            array( 'id' => 'cases_categories', 'type' => 'text', 'label' => __( '分类ID (逗号分隔)', 'developer-starter' ) ),
            array( 'id' => 'cases_show_image', 'type' => 'select', 'label' => __( '显示图片', 'developer-starter' ), 'options' => array( '1' => __( '是', 'developer-starter' ), '0' => __( '否', 'developer-starter' ) ), 'default' => '1' ),
            array( 'id' => 'cases_image_height', 'type' => 'text', 'label' => __( '图片高度', 'developer-starter' ), 'default' => '200px' ),
            array( 'id' => 'cases_detail_text', 'type' => 'text', 'label' => __( '卡片查看详情文案', 'developer-starter' ), 'default' => __( '查看详情', 'developer-starter' ) ),
            array(
                'id' => 'enable_staggered_animation',
                'label' => __( '开启列表逐个显示动画', 'developer-starter' ),
                'type' => 'select',
                'options' => array(
                    'yes' => __( '开启', 'developer-starter' ),
                    'no' => __( '关闭', 'developer-starter' ),
                ),
                'default' => 'yes',
                'description' => __( '开启后，案例卡片将依次延迟显示，形成阶梯视觉效果', 'developer-starter' ),
            ),
        );
    }

    public function render( $data = array() ) {
        $title = isset( $data['cases_title'] ) && $data['cases_title'] !== ''
            ? $data['cases_title']
            : ( function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '成功案例', 'Case Studies' ) : __( '成功案例', 'developer-starter' ) );
        $count = isset( $data['cases_count'] ) && $data['cases_count'] !== '' ? intval( $data['cases_count'] ) : 6;
        $columns = isset( $data['cases_columns'] ) && $data['cases_columns'] !== '' ? $data['cases_columns'] : '3';
        $categories = isset( $data['cases_categories'] ) ? $data['cases_categories'] : '';
        
        // 显示开关 - 默认显示图片，只有明确设置为0时才隐藏
        $show_image = ! isset( $data['cases_show_image'] ) || $data['cases_show_image'] !== '0';
        $image_height = isset( $data['cases_image_height'] ) && $data['cases_image_height'] !== '' ? $data['cases_image_height'] : '200px';
        $detail_text = isset( $data['cases_detail_text'] ) && '' !== trim( (string) $data['cases_detail_text'] ) ? (string) $data['cases_detail_text'] : __( '查看详情', 'developer-starter' );
        $padding_top = isset( $data['cases_padding_top'] ) && '' !== trim( (string) $data['cases_padding_top'] ) ? (string) $data['cases_padding_top'] : '80px';
        $padding_bottom = isset( $data['cases_padding_bottom'] ) && '' !== trim( (string) $data['cases_padding_bottom'] ) ? (string) $data['cases_padding_bottom'] : '80px';
        $bg_color = isset( $data['module_bg_color'] ) ? trim( (string) $data['module_bg_color'] ) : '';
        $section_style = "padding-top:{$padding_top};padding-bottom:{$padding_bottom};";
        if ( '' !== $bg_color ) {
            $section_style .= false !== strpos( $bg_color, 'gradient' ) ? "background:{$bg_color};" : "background-color:{$bg_color};";
        }
        
        // 解析分类
        $cat_ids = array();
        if ( ! empty( $categories ) ) {
            $cat_ids = array_map( 'intval', array_filter( explode( ',', $categories ) ) );
        }
        
        // 获取分类信息
        $category_list = array();
        if ( ! empty( $cat_ids ) ) {
            foreach ( $cat_ids as $cat_id ) {
                $cat = get_category( $cat_id );
                if ( $cat && ! is_wp_error( $cat ) ) {
                    $category_list[] = array(
                        'id'   => $cat_id,
                        'name' => $cat->name,
                    );
                }
            }
        }
        
        $args = array(
            'post_type'      => 'post',
            'posts_per_page' => $count,
            'post_status'    => 'publish',
        );
        
        if ( ! empty( $cat_ids ) ) {
            $args['cat'] = $cat_ids[0];
        } else {
            // 尝试获取 'case' slug 的分类
            $case_cat = get_category_by_slug( 'case' );
            if ( $case_cat ) {
                $args['cat'] = $case_cat->term_id;
            }
        }
        
        if ( function_exists( 'developer_starter_run_cached_query' ) ) {
            $query = \developer_starter_run_cached_query(
                $args,
                'module_cases',
                array(
                    'needs_pagination' => false,
                )
            );
        } else {
            $query = new \WP_Query( $args );
        }
        $module_id = 'cases-module-' . uniqid();
        
        // Animation Setting
        $enable_anim = isset( $data['enable_staggered_animation'] ) ? $data['enable_staggered_animation'] : 'yes';
        ?>
        <section class="module module-cases" id="<?php echo esc_attr( $module_id ); ?>" style="<?php echo esc_attr( $section_style ); ?>">
            <div class="container">
                <div class="section-header text-center">
                    <h2 class="section-title"><?php echo esc_html( $title ); ?></h2>
                </div>
                
                <?php if ( count( $category_list ) > 1 ) : ?>
                    <div class="category-tabs" style="text-align: center; margin-bottom: var(--qiling-space-30);">
                        <?php foreach ( $category_list as $index => $cat ) : ?>
                            <button type="button" 
                                    class="tab-btn <?php echo $index === 0 ? 'active' : ''; ?>" 
                                    data-category="<?php echo esc_attr( $cat['id'] ); ?>"
                                    style="padding: var(--qiling-space-8) var(--qiling-space-20); margin: var(--qiling-space-5); border: 1px solid var(--color-primary); background: <?php echo $index === 0 ? 'var(--color-primary)' : 'transparent'; ?>; color: <?php echo $index === 0 ? 'var(--color-text-inverse)' : 'var(--color-primary)'; ?>; border-radius: 20px; cursor: pointer;">
                                <?php echo esc_html( $cat['name'] ); ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if ( $query->have_posts() ) : ?>
                    <div class="cases-grid grid-cols-<?php echo esc_attr( $columns ); ?>">
                        <?php while ( $query->have_posts() ) : $query->the_post(); 
                            // 获取封面图片 - 优先特色图片，其次文章第一张图片
                            $image_url = '';
                            if ( function_exists( 'developer_starter_get_thumbnail_url' ) ) {
                                $image_url = developer_starter_get_thumbnail_url( get_the_ID(), 'large' );
                            } elseif ( function_exists( 'developer_starter_get_featured_image_url' ) ) {
                                $image_url = developer_starter_get_featured_image_url( get_the_ID(), 'large' );
                            } elseif ( has_post_thumbnail() ) {
                                $image_url = get_the_post_thumbnail_url( get_the_ID(), 'large' );
                            }
                            if ( empty( $image_url ) ) {
                                // 从文章内容中获取第一张图片
                                $post_content = get_the_content();
                                if ( preg_match( '/<img[^>]+src=[\'"]([^\'"]+)[\'"][^>]*>/i', $post_content, $matches ) ) {
                                    $image_url = $matches[1];
                                }
                            }
                            if ( empty( $image_url ) && function_exists( 'developer_starter_get_first_image' ) ) {
                                $image_url = developer_starter_get_first_image( get_the_ID() );
                            }
                            
                            // Calculate Staggered Animation
                            $anim_attr = '';
                            if ( $enable_anim === 'yes' ) {
                                $anim_attr = $this->get_staggered_animation_attr( $query->current_post );
                            }
                        ?>
                            <div class="case-card" style="background: var(--color-neutral-0); border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px var(--qiling-color-rgba-0-0-0-005);" <?php echo $anim_attr; ?>>
                                <?php if ( $show_image ) : ?>
                                    <a href="<?php echo esc_url( get_permalink() ); ?>" class="case-thumb" style="display: block; height: <?php echo esc_attr( $image_height ); ?>; overflow: hidden; position: relative;">
                                        <?php if ( $image_url ) : ?>
                                            <img src="<?php echo esc_url( $image_url ); ?>" alt="<?php the_title_attribute(); ?>" style="width: 100%; height: 100%; object-fit: cover;" />
                                        <?php else : ?>
                                            <div style="width: 100%; height: 100%; background: linear-gradient(135deg, var(--color-neutral-50) 0%, var(--color-neutral-200) 100%); display: flex; align-items: center; justify-content: center; color: var(--color-text-muted);">
                                                <span class="dashicons dashicons-format-image" style="font-size: var(--qiling-text-rem-3);"></span>
                                            </div>
                                        <?php endif; ?>
                                        <div class="case-overlay" style="position: absolute; inset: 0; background: var(--qiling-color-rgba-0-0-0-04); opacity: 0; transition: opacity 0.3s; display: flex; align-items: center; justify-content: center;">
                                            <span style="color: var(--color-text-inverse); font-size: var(--qiling-text-rem-0p9);"><?php echo esc_html( $detail_text ); ?></span>
                                        </div>
                                    </a>
                                <?php endif; ?>
                                
                                <div class="case-info" style="padding: var(--qiling-space-15);">
                                    <h3 class="case-title" style="margin: 0; font-size: var(--qiling-text-rem-1);">
                                        <a href="<?php echo esc_url( get_permalink() ); ?>" style="color: var(--qiling-component-post-card-title-color); text-decoration: none;"><?php echo esc_html( get_the_title() ); ?></a>
                                    </h3>
                                </div>
                            </div>
                        <?php endwhile; wp_reset_postdata(); ?>
                    </div>
                <?php else : ?>
                    <p class="text-center"><?php esc_html_e( '暂无案例', 'developer-starter' ); ?></p>
                <?php endif; ?>
            </div>
        </section>
<?php
    }
}
