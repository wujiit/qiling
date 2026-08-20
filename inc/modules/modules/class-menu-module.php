<?php
/**
 * Menu Module - 菜单/价目表
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Modules\Modules;

use Developer_Starter\Modules\Module_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Menu_Module extends Module_Base {

    public function __construct() {
        $this->category = 'business'; // 归类为商业
        $this->icon = 'dashicons-list-view';
        $this->description = __( '展示餐厅菜单、服务价目表或产品清单', 'developer-starter' );
    }

    public function get_id() {
        return 'menu';
    }

    public function get_name() {
        return __( '菜单/价目表', 'developer-starter' );
    }

    public function get_fields() {
        return array(
            array(
                'id' => 'menu_title',
                'label' => __( '模块标题 (支持HTML)', 'developer-starter' ),
                'type' => 'text',
                'default' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '特色<span style="color:var(--qiling-color-eab308)">菜单</span>', 'Featured <span style="color:var(--qiling-color-eab308)">Menu</span>' ) : __( '特色<span style="color:var(--qiling-color-eab308)">菜单</span>', 'developer-starter' ),
                'description' => __( '如：我们的&lt;b&gt;服务&lt;/b&gt;', 'developer-starter' ),
            ),
            array(
                'id' => 'menu_subtitle',
                'label' => __( '副标题', 'developer-starter' ),
                'type' => 'textarea',
                'rows' => 2,
                'default' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '精心挑选的优质服务与产品', 'A curated selection of signature services and products.' ) : __( '精心挑选的优质服务与产品', 'developer-starter' ),
            ),
            array(
                'id' => 'menu_layout',
                'label' => __( '布局模式', 'developer-starter' ),
                'type' => 'select',
                'options' => array(
                    'list' => __( '单栏列表 (适合详细介绍)', 'developer-starter' ),
                    'grid' => __( '双栏网格 (适合紧凑展示)', 'developer-starter' ),
                ),
                'default' => 'grid',
            ),
            array(
                'id' => 'menu_bg_color',
                'label' => __( '背景颜色', 'developer-starter' ),
                'type' => 'color',
                'default' => 'var(--color-neutral-0)',
            ),
            array(
                'id' => 'menu_accent_color',
                'label' => __( '强调色 (价格/角标)', 'developer-starter' ),
                'type' => 'color',
                'default' => 'var(--qiling-color-eab308)',
            ),
            
            // Items Repeater
            array(
                'id' => 'menu_items',
                'label' => __( '菜单/价目列表', 'developer-starter' ),
                'type' => 'repeater',
                'fields' => array(
                    array( 'id' => 'image', 'label' => __( '图片 (可选)', 'developer-starter' ), 'type' => 'image' ),
                    array( 'id' => 'title', 'label' => __( '名称', 'developer-starter' ), 'type' => 'text' ),
                    array( 'id' => 'desc', 'label' => __( '描述/配料', 'developer-starter' ), 'type' => 'textarea', 'rows' => 2 ),
                    array( 'id' => 'price', 'label' => __( '价格', 'developer-starter' ), 'type' => 'text', 'description' => function_exists( 'developer_starter_get_demo_price_hint' ) ? developer_starter_get_demo_price_hint( 68 ) : __( '如 ¥68', 'developer-starter' ) ),
                    array( 'id' => 'badge', 'label' => __( '推荐角标', 'developer-starter' ), 'type' => 'text', 'description' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '如 推荐, Hot, 新品 (留空不显示)', 'Examples: Featured, Hot, New (leave blank to hide)' ) : __( '如 推荐, Hot, 新品 (留空不显示)', 'developer-starter' ) ),
                    array( 'id' => 'link', 'label' => __( '链接 (可选)', 'developer-starter' ), 'type' => 'text' ),
                ),
                'default_items' => array(
                    array(
                        'title' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '经典商务套餐', 'Business Lunch Set' ) : __( '经典商务套餐', 'developer-starter' ),
                        'desc' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '包含主食、汤品、时令蔬菜与水果', 'Includes a main dish, soup, seasonal vegetables, and fruit.' ) : __( '包含主食、汤品、时令蔬菜与水果', 'developer-starter' ),
                        'price' => function_exists( 'developer_starter_get_demo_price_text' ) ? developer_starter_get_demo_price_text( 48 ) : '¥48',
                        'badge' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '推荐', 'Featured' ) : __( '推荐', 'developer-starter' ),
                    ),
                    array(
                        'title' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '高级定制服务', 'Premium Concierge Service' ) : __( '高级定制服务', 'developer-starter' ),
                        'desc' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '一对一专属顾问，24小时响应', 'A dedicated consultant with around-the-clock support.' ) : __( '一对一专属顾问，24小时响应', 'developer-starter' ),
                        'price' => function_exists( 'developer_starter_get_demo_price_text' ) ? developer_starter_get_demo_price_text( 2999 ) : '¥2999',
                        'badge' => 'Hot',
                    ),
                    array(
                        'title' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '基础保养套餐', 'Essential Maintenance Package' ) : __( '基础保养套餐', 'developer-starter' ),
                        'desc' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '机油更换、滤芯更换、全车检查', 'Oil change, filter replacement, and a full inspection.' ) : __( '机油更换、滤芯更换、全车检查', 'developer-starter' ),
                        'price' => function_exists( 'developer_starter_get_demo_price_text' ) ? developer_starter_get_demo_price_text( 398 ) : '¥398',
                        'badge' => '',
                    ),
                    array(
                        'title' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '深度清洗服务', 'Detail Cleaning Service' ) : __( '深度清洗服务', 'developer-starter' ),
                        'desc' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '内饰精洗、外观打蜡、轮毂清洁', 'Interior deep clean, exterior wax, and wheel detailing.' ) : __( '内饰精洗、外观打蜡、轮毂清洁', 'developer-starter' ),
                        'price' => function_exists( 'developer_starter_get_demo_price_text' ) ? developer_starter_get_demo_price_text( 580 ) : '¥580',
                        'badge' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '新品', 'New' ) : __( '新品', 'developer-starter' ),
                    ),
                ),
            ),
            
            // Spacing
            array(
                'id' => 'module_margin_top',
                'label' => __( '上间距', 'developer-starter' ),
                'type' => 'text',
                'default' => '80px',
            ),
            array(
                'id' => 'module_margin_bottom',
                'label' => __( '下间距', 'developer-starter' ),
                'type' => 'text',
                'default' => '80px',
            ),
            array(
                'id' => 'enable_staggered_animation',
                'label' => __( '开启列表逐个显示动画', 'developer-starter' ),
                'type' => 'select',
                'options' => array(
                    'yes' => __( '开启', 'developer-starter' ),
                    'no' => __( '关闭', 'developer-starter' ),
                ),
                'default' => 'yes',
                'description' => __( '开启后，菜单项目将依次延迟显示，形成阶梯视觉效果', 'developer-starter' ),
            ),
        );
    }

    public function render( $data = array() ) {
        // Data Extraction
        $title = isset( $data['menu_title'] ) && $data['menu_title'] !== ''
            ? $data['menu_title']
            : ( function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '特色<span style="color:var(--qiling-color-eab308)">菜单</span>', 'Featured <span style="color:var(--qiling-color-eab308)">Menu</span>' ) : __( '特色<span style="color:var(--qiling-color-eab308)">菜单</span>', 'developer-starter' ) );
        $subtitle = isset( $data['menu_subtitle'] ) && $data['menu_subtitle'] !== ''
            ? $data['menu_subtitle']
            : ( function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '精心挑选的优质服务与产品', 'A curated selection of signature services and products.' ) : __( '精心挑选的优质服务与产品', 'developer-starter' ) );
        $layout = isset( $data['menu_layout'] ) ? $data['menu_layout'] : 'grid';
        $bg_color = isset( $data['menu_bg_color'] ) ? $data['menu_bg_color'] : 'var(--color-neutral-0)';
        $accent_color = isset( $data['menu_accent_color'] ) ? $data['menu_accent_color'] : 'var(--qiling-color-eab308)';
        $items = isset( $data['menu_items'] ) ? $data['menu_items'] : array();
        
        // CSS Vars
        $style_vars = "background-color: {$bg_color};";
        $style_vars .= "--menu-accent: {$accent_color};";
        
        // Animation Setting
        $enable_anim = isset( $data['enable_staggered_animation'] ) ? $data['enable_staggered_animation'] : 'yes';
        
        // Check Layout
        $grid_class = $layout === 'grid' ? 'qiling-menu-grid-2' : 'qiling-menu-list';
        ?>
        <section class="module module-menu" style="<?php echo esc_attr( $style_vars ); ?>">
            <div class="container">
                <?php if ( $title || $subtitle ) : ?>
                    <div class="section-header text-center">
                        <?php if ( $title ) : ?>
                            <h2 class="section-title"><?php echo wp_kses_post( $title ); ?></h2>
                        <?php endif; ?>
                        <?php if ( $subtitle ) : ?>
                            <p class="section-subtitle"><?php echo wp_kses_post( $subtitle ); ?></p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <?php if ( ! empty( $items ) ) : ?>
                    <div class="qiling-menu-container <?php echo esc_attr( $grid_class ); ?>">
                        <?php foreach ( $items as $index => $item ) : 
                            $img = isset( $item['image'] ) ? $item['image'] : '';
                            $name = isset( $item['title'] ) ? $item['title'] : '';
                            $desc = isset( $item['desc'] ) ? $item['desc'] : '';
                            $price = isset( $item['price'] ) ? $item['price'] : '';
                            $badge = isset( $item['badge'] ) ? $item['badge'] : '';
                            $link = isset( $item['link'] ) ? $item['link'] : '';
                            
                            $tag = $link ? 'a' : 'div';
                            $href = $link ? ' href="' . esc_url( $link ) . '"' : '';

                            // Calculate Staggered Animation
                            $anim_attr = '';
                            if ( $enable_anim === 'yes' ) {
                                $anim_attr = $this->get_staggered_animation_attr( $index );
                            }
                        ?>
                            <<?php echo $tag . $href; ?> class="qiling-menu-item" <?php echo $anim_attr; ?>>
                                <?php if ( $img ) : ?>
                                    <div class="qiling-menu-img">
                                        <img src="<?php echo esc_url( $img ); ?>" alt="<?php echo esc_attr( $name ); ?>" loading="lazy">
                                    </div>
                                <?php endif; ?>
                                
                                <div class="qiling-menu-content">
                                    <div class="qiling-menu-header">
                                        <h3 class="qiling-menu-name">
                                            <?php echo esc_html( $name ); ?>
                                            <?php if ( $badge ) : ?>
                                                <span class="qiling-menu-badge"><?php echo esc_html( $badge ); ?></span>
                                            <?php endif; ?>
                                        </h3>
                                        <!-- Line Separator only for list view or visual effect -->
                                        <span class="qiling-menu-dots"></span>
                                        <span class="qiling-menu-price"><?php echo esc_html( $price ); ?></span>
                                    </div>
                                    <?php if ( $desc ) : ?>
                                        <div class="qiling-menu-desc"><?php echo esc_html( $desc ); ?></div>
                                    <?php endif; ?>
                                </div>
                            </<?php echo $tag; ?>>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>
        <?php
    }
}
