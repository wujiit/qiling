<?php
/**
 * Pet Profile Module - 宠物档案/领养
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Modules\Modules;

use Developer_Starter\Modules\Module_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Pet_Profile_Module extends Module_Base {

    public function __construct() {
        $this->category = 'industry'; // 行业专属
        $this->icon = 'dashicons-pets'; // 宠物图标
        $this->description = __( '展示宠物档案、领养信息或种猫种犬库', 'developer-starter' );
    }

    public function get_id() {
        return 'pet_profile';
    }

    public function get_name() {
        return __( '宠物档案/领养', 'developer-starter' );
    }

    public function get_fields() {
        return array(
            // 基础设置
            array(
                'id' => 'pet_title',
                'label' => __( '模块标题 (支持HTML)', 'developer-starter' ),
                'type' => 'text',
                'default' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '寻找你的<span style="color:var(--pet-primary, var(--color-accent))">灵魂伴侣</span>', 'Find your <span style="color:var(--pet-primary, var(--color-accent))">perfect companion</span>' ) : __( '寻找你的<span style="color:var(--pet-primary, var(--color-accent))">灵魂伴侣</span>', 'developer-starter' ),
            ),
            array(
                'id' => 'pet_subtitle',
                'label' => __( '副标题', 'developer-starter' ),
                'type' => 'textarea',
                'rows' => 2,
                'default' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '每一只都经过严格健康检查与性格评估', 'Each profile is reviewed for health, temperament, and care needs.' ) : __( '每一只都经过严格健康检查与性格评估', 'developer-starter' ),
            ),
            array(
                'id' => 'pet_columns',
                'label' => __( '每行显示数量', 'developer-starter' ),
                'type' => 'select',
                'options' => array( '3' => __( '3列', 'developer-starter' ), '4' => __( '4列', 'developer-starter' ) ),
                'default' => '4',
            ),
            
            // 样式设置
            array(
                'id' => 'pet_bg_color',
                'label' => __( '背景颜色', 'developer-starter' ),
                'type' => 'color',
                'default' => 'var(--qiling-color-error-alpha-01)', // 默认淡粉色背景
            ),
            array(
                'id' => 'pet_card_bg',
                'label' => __( '卡片背景色', 'developer-starter' ),
                'type' => 'color',
                'default' => 'var(--color-neutral-0)',
            ),
            array(
                'id' => 'pet_primary_color',
                'label' => __( '主色调 (标签/按钮)', 'developer-starter' ),
                'type' => 'color',
                'default' => '',
            ),
            array(
                'id'          => 'pet_badge_bg',
                'label'       => __( '标签/徽章背景颜色', 'developer-starter' ),
                'type'        => 'color',
                'default'     => '',
                'description' => __( '控制状态徽章与特点标签背景；留空时保留状态语义色并跟随全局标签风格。', 'developer-starter' ),
            ),

            // 内容列表
            array(
                'id' => 'pet_items',
                'label' => __( '宠物列表', 'developer-starter' ),
                'type' => 'repeater',
                'fields' => array(
                    array( 'id' => 'image', 'label' => __( '封面照片', 'developer-starter' ), 'type' => 'image' ),
                    array( 'id' => 'name', 'label' => __( '昵称/编号', 'developer-starter' ), 'type' => 'text' ),
                    array( 'id' => 'breed', 'label' => __( '品种', 'developer-starter' ), 'type' => 'text', 'placeholder' => __( '如：布偶猫', 'developer-starter' ) ),
                    array( 
                        'id' => 'gender', 
                        'label' => __( '性别', 'developer-starter' ), 
                        'type' => 'select', 
                        'options' => array( 'male' => __( '弟弟 (♂)', 'developer-starter' ), 'female' => __( '妹妹 (♀)', 'developer-starter' ), 'unknown' => __( '保密', 'developer-starter' ) ) 
                    ),
                    array( 'id' => 'age', 'label' => __( '年龄/生日', 'developer-starter' ), 'type' => 'text', 'placeholder' => __( '3个月', 'developer-starter' ) ),
                    array( 
                        'id' => 'status', 
                        'label' => __( '当前状态', 'developer-starter' ), 
                        'type' => 'select', 
                        'options' => array( 'available' => __( '待领养/待售', 'developer-starter' ), 'reserved' => __( '已预订', 'developer-starter' ), 'adopted' => __( '已回家', 'developer-starter' ) ) 
                    ),
                    array( 'id' => 'tags', 'label' => __( '性格/特点标签 (逗号分隔)', 'developer-starter' ), 'type' => 'text', 'placeholder' => __( '粘人, 疫苗全, 会用猫砂', 'developer-starter' ) ),
                    array( 'id' => 'price', 'label' => __( '领养费/价格 (可选)', 'developer-starter' ), 'type' => 'text' ),
                    array( 'id' => 'link', 'label' => __( '详情链接 (可选)', 'developer-starter' ), 'type' => 'text' ),
                ),
                'default_items' => array(
                    array( 'name' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '汤圆', 'Mochi' ) : __( '汤圆', 'developer-starter' ), 'breed' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '布偶猫', 'Ragdoll Cat' ) : __( '布偶猫', 'developer-starter' ), 'gender' => 'female', 'age' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '4个月', '4 months' ) : __( '4个月', 'developer-starter' ), 'status' => 'available', 'tags' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '双色, 爆毛, 粘人精', 'Bicolor, fluffy coat, affectionate' ) : __( '双色, 爆毛, 粘人精', 'developer-starter' ), 'price' => function_exists( 'developer_starter_get_demo_price_text' ) ? developer_starter_get_demo_price_text( 5000 ) : '¥5000' ),
                    array( 'name' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '奥利奥', 'Oreo' ) : __( '奥利奥', 'developer-starter' ), 'breed' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '边境牧羊犬', 'Border Collie' ) : __( '边境牧羊犬', 'developer-starter' ), 'gender' => 'male', 'age' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '2个月', '2 months' ) : __( '2个月', 'developer-starter' ), 'status' => 'reserved', 'tags' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '高智商, 飞盘狗预备役', 'Smart, active, frisbee-ready' ) : __( '高智商, 飞盘狗预备役', 'developer-starter' ), 'price' => '' ),
                    array( 'name' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '咪咪', 'Sunny' ) : __( '咪咪', 'developer-starter' ), 'breed' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '中华田园橘', 'Domestic Orange Tabby' ) : __( '中华田园橘', 'developer-starter' ), 'gender' => 'male', 'age' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '1岁', '1 year old' ) : __( '1岁', 'developer-starter' ), 'status' => 'available', 'tags' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '免费领养, 已绝育, 胖嘟嘟', 'Adoption free, neutered, cuddly' ) : __( '免费领养, 已绝育, 胖嘟嘟', 'developer-starter' ), 'price' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '免费', 'Free' ) : __( '免费', 'developer-starter' ) ),
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
                'description' => __( '开启后，宠物卡片将依次延迟显示，形成阶梯视觉效果', 'developer-starter' ),
            ),
        );
    }

    public function render( $data = array() ) {
        $title = isset( $data['pet_title'] ) ? $data['pet_title'] : ( function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '寻找你的<span style="color:var(--pet-primary, var(--color-accent))">灵魂伴侣</span>', 'Find your <span style="color:var(--pet-primary, var(--color-accent))">perfect companion</span>' ) : __( '寻找你的<span style="color:var(--pet-primary, var(--color-accent))">灵魂伴侣</span>', 'developer-starter' ) );
        $subtitle = isset( $data['pet_subtitle'] ) ? $data['pet_subtitle'] : ( function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '每一只都经过严格健康检查与性格评估', 'Each profile is reviewed for health, temperament, and care needs.' ) : __( '每一只都经过严格健康检查与性格评估', 'developer-starter' ) );
        $bg_color = isset( $data['pet_bg_color'] ) ? $data['pet_bg_color'] : 'var(--qiling-color-error-alpha-01)';
        $card_bg = isset( $data['pet_card_bg'] ) ? $data['pet_card_bg'] : 'var(--color-neutral-0)';
        $primary_color = ! empty( $data['pet_primary_color'] ) ? $data['pet_primary_color'] : '';
        $badge_bg = ! empty( $data['pet_badge_bg'] ) ? trim( wp_strip_all_tags( (string) $data['pet_badge_bg'] ) ) : '';
        $badge_bg = str_replace( array( ';', '{', '}' ), '', $badge_bg );
        $columns = isset( $data['pet_columns'] ) ? intval( $data['pet_columns'] ) : 4;
        $items = isset( $data['pet_items'] ) ? $data['pet_items'] : array();

        $style_vars = "background-color: {$bg_color};";
        $style_vars .= "--pet-card-bg: {$card_bg};";
        if ( '' !== $primary_color ) {
            $style_vars .= "--pet-primary: {$primary_color};";
        }
        if ( '' !== $badge_bg ) {
            $style_vars .= "--qiling-component-badge-bg: {$badge_bg};";
            $style_vars .= "--pet-status-available-bg: {$badge_bg};";
            $style_vars .= "--pet-status-reserved-bg: {$badge_bg};";
            $style_vars .= "--pet-status-adopted-bg: {$badge_bg};";
        }
        
        // Animation Setting
        $enable_anim = isset( $data['enable_staggered_animation'] ) ? $data['enable_staggered_animation'] : 'yes';

        // Grid Class
        $grid_class = "qiling-pet-grid grid-cols-{$columns}";
        ?>
        <section class="module module-pet-profile" style="<?php echo esc_attr( $style_vars ); ?>">
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
                    <div class="<?php echo esc_attr( $grid_class ); ?>">
                        <?php foreach ( $items as $index => $item ) : 
                            $img = isset( $item['image'] ) ? $item['image'] : '';
                            $name = isset( $item['name'] ) ? $item['name'] : __( '未命名', 'developer-starter' );
                            $breed = isset( $item['breed'] ) ? $item['breed'] : '';
                            $gender = isset( $item['gender'] ) ? $item['gender'] : 'unknown';
                            $age = isset( $item['age'] ) ? $item['age'] : '';
                            $status = isset( $item['status'] ) ? $item['status'] : 'available';
                            $tags_str = isset( $item['tags'] ) ? $item['tags'] : '';
                            $price = isset( $item['price'] ) ? $item['price'] : '';
                            $link = isset( $item['link'] ) ? $item['link'] : '';

                            // Process Tags
                            $tags = array_filter( array_map( 'trim', explode( ',', $tags_str ) ) );

                            // Status Label
                            $status_labels = array(
                                'available' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '待领养', 'Available' ) : __( '待领养', 'developer-starter' ),
                                'reserved' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '已预订', 'Reserved' ) : __( '已预订', 'developer-starter' ),
                                'adopted' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '已回家', 'Adopted' ) : __( '已回家', 'developer-starter' )
                            );
                            $status_label = isset( $status_labels[$status] ) ? $status_labels[$status] : ( function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '查看详情', 'View Details' ) : __( '查看详情', 'developer-starter' ) );
                            
                            $tag_el = $link ? 'a' : 'div';
                            $href = $link ? ' href="' . esc_url( $link ) . '"' : '';
                            
                            // Calculate staggered animation.
                            $anim_attr = '';
                            if ( $enable_anim === 'yes' ) {
                                $anim_attr = $this->get_staggered_animation_attr( $index );
                            }
                        ?>
                            <<?php echo $tag_el . $href; ?> class="qiling-pet-card status-<?php echo esc_attr( $status ); ?>" <?php echo $anim_attr; ?>>
                                <div class="qiling-pet-image">
                                    <?php if ( $img ) : ?>
                                        <img src="<?php echo esc_url( $img ); ?>" alt="<?php echo esc_attr( $name ); ?>" loading="lazy">
                                    <?php else : ?>
                                        <div class="qiling-pet-placeholder">
                                            <span class="dashicons dashicons-pets"></span>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <!-- Status Badge -->
                                    <span class="qiling-pet-status"><?php echo esc_html( $status_label ); ?></span>
                                </div>

                                <div class="qiling-pet-content">
                                    <div class="qiling-pet-header">
                                        <h3 class="qiling-pet-name">
                                            <?php echo esc_html( $name ); ?>
                                            <?php if ( $gender === 'male' ) : ?>
                                                <span class="qiling-pet-gender male" title="<?php esc_attr_e( '男孩', 'developer-starter' ); ?>">♂</span>
                                            <?php elseif ( $gender === 'female' ) : ?>
                                                <span class="qiling-pet-gender female" title="<?php esc_attr_e( '女孩', 'developer-starter' ); ?>">♀</span>
                                            <?php endif; ?>
                                        </h3>
                                        <?php if ( $price ) : ?>
                                            <span class="qiling-pet-price"><?php echo esc_html( $price ); ?></span>
                                        <?php endif; ?>
                                    </div>

                                    <div class="qiling-pet-meta">
                                        <?php if ( $breed ) : ?>
                                            <span class="meta-item"><i class="dashicons dashicons-tag"></i> <?php echo esc_html( $breed ); ?></span>
                                        <?php endif; ?>
                                        <?php if ( $age ) : ?>
                                            <span class="meta-item"><i class="dashicons dashicons-clock"></i> <?php echo esc_html( $age ); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <?php if ( ! empty( $tags ) ) : ?>
                                        <div class="qiling-pet-tags">
                                            <?php foreach ( $tags as $tag_text ) : ?>
                                                <span class="qiling-pet-tag"><?php echo esc_html( $tag_text ); ?></span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </<?php echo $tag_el; ?>>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>
        <?php
    }
}
