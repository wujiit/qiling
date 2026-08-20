<?php
/**
 * Interact Hero Module - 交互首屏Banner
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Modules\Modules;

use Developer_Starter\Modules\Module_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Interact_Hero_Module extends Module_Base {

    public function __construct() {
        $this->category = 'homepage'; // 分类：首页
        $this->icon = 'dashicons-desktop'; // 图标
        $this->description = __( '高度自定义的交互式首屏Banner，支持视频/图片切换，底部带特性卡片。', 'developer-starter' );
    }

    public function get_id() {
        return 'interact_hero';
    }

    public function get_name() {
        return __( '交互首屏Banner', 'developer-starter' );
    }

    /**
     * 定义模块字段
     */
    public function get_fields() {
        return array(
            // --- 左侧内容设置 ---
            array(
                'id' => 'badge_text',
                'type' => 'text',
                'label' => __( '顶部小标签 (Badge)', 'developer-starter' ),
                'default' => 'NEW 2.0',
                'desc' => __( '标题上方的小黑标，留空不显示', 'developer-starter' ),
            ),
            array(
                'id' => 'hero_title_content',   // Renamed for HTML support
                'type' => 'textarea',
                'label' => __( '主标题', 'developer-starter' ),
                'default' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '启灵主题 · 全新一代', 'Qiling Theme, a new generation' ) : __( '启灵主题 · 全新一代', 'developer-starter' ),
                'desc' => __( '支持HTML标签，如 &lt;span style="color:var(--color-primary)"&gt;高亮文字&lt;/span&gt;', 'developer-starter' ),
            ),
            array(
                'id' => 'hero_subtitle_content', // Helper for consistency, though 'subtitle' keyword works too
                'type' => 'textarea',
                'label' => __( '副标题/描述', 'developer-starter' ),
                'default' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '不仅是好看，更是强大的生产力工具。', 'More than beautiful, it is built to help teams ship faster.' ) : __( '不仅是好看，更是强大的生产力工具。', 'developer-starter' ),
                'desc' => __( '支持HTML标签', 'developer-starter' ),
            ),
            array(
                'id' => 'btn_primary_content',  // Renamed for HTML support
                'type' => 'textarea',
                'label' => __( '主按钮文字', 'developer-starter' ),
                'default' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '立即体验', 'Start Now' ) : __( '立即体验', 'developer-starter' ),
                'desc' => __( '支持HTML标签 (如 &lt;span style="color:yellow"&gt;TEXT&lt;/span&gt;)', 'developer-starter' ),
            ),
            array(
                'id' => 'btn_primary_icon',
                'type' => 'text',
                'label' => __( '主按钮图标 (可选)', 'developer-starter' ),
                'default' => '',
                'desc' => __( '支持Emoji(💡)或Symbol类名(如 icon-download)', 'developer-starter' ),
            ),
            array(
                'id' => 'btn_primary_link',
                'type' => 'text',
                'label' => __( '主按钮链接', 'developer-starter' ),
                'default' => '#',
            ),
            array(
                'id' => 'btn_primary_bg_color',
                'type' => 'color',
                'label' => __( '主按钮背景色', 'developer-starter' ),
                'desc' => __( '留空则使用默认强调色', 'developer-starter' ),
            ),
            array(
                'id' => 'btn_primary_text_color',
                'type' => 'color',
                'label' => __( '主按钮文本色', 'developer-starter' ),
                'desc' => __( '留空则使用主按钮默认白色', 'developer-starter' ),
            ),
            $this->get_button_border_color_field( 'btn_primary_border_color', __( '主按钮边框颜色', 'developer-starter' ) ),

            array(
                'id' => 'btn_secondary_content', // Renamed for HTML support
                'type' => 'textarea',
                'label' => __( '次要按钮文字', 'developer-starter' ),
                'default' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '了解更多', 'Learn More' ) : __( '了解更多', 'developer-starter' ),
                'desc' => __( '支持HTML标签', 'developer-starter' ),
            ),
            array(
                'id' => 'btn_secondary_icon',
                'type' => 'text',
                'label' => __( '次要按钮图标 (可选)', 'developer-starter' ),
                'default' => '',
                'desc' => __( '支持Emoji或Symbol类名', 'developer-starter' ),
            ),
            array(
                'id' => 'btn_secondary_link',
                'type' => 'text',
                'label' => __( '次要按钮链接', 'developer-starter' ),
                'default' => '#',
            ),
            array(
                'id' => 'btn_secondary_bg_color',
                'type' => 'color',
                'label' => __( '次要按钮背景色', 'developer-starter' ),
                'desc' => __( '留空则使用默认淡灰色', 'developer-starter' ),
            ),
            array(
                'id' => 'btn_secondary_text_color',
                'type' => 'color',
                'label' => __( '次要按钮文本色', 'developer-starter' ),
                'desc' => __( '留空则使用默认深色', 'developer-starter' ),
            ),
            $this->get_button_border_color_field( 'btn_secondary_border_color', __( '次要按钮边框颜色', 'developer-starter' ) ),

            // --- 右侧媒体设置 ---
            array(
                'id' => 'media_type',
                'type' => 'select',
                'label' => __( '媒体类型', 'developer-starter' ),
                'options' => array(
                    'image' => __( '图片模式', 'developer-starter' ),
                    'video' => __( '视频模式', 'developer-starter' ),
                ),
                'default' => 'image',
            ),
            array(
                'id' => 'hero_image',
                'type' => 'image',
                'label' => __( '上传图片', 'developer-starter' ),
                'dependency' => array( 'media_type', '==', 'image' ),
                'desc' => __( '建议尺寸：800x600px 透明PNG效果最佳', 'developer-starter' ),
            ),
            array(
                'id' => 'hero_video',
                'type' => 'upload',
                'label' => __( '上传视频 (MP4)', 'developer-starter' ),
                'dependency' => array( 'media_type', '==', 'video' ),
                'desc' => __( '建议使用无背景或融合背景的WebM/MP4视频', 'developer-starter' ),
            ),
            array(
                'id' => 'enable_float_anim',
                'type' => 'switcher',
                'label' => __( '开启悬浮呼吸动画', 'developer-starter' ),
                'default' => 'yes',
                'desc' => __( '右侧媒体是否上下浮动', 'developer-starter' ),
            ),

            // --- 底部卡片设置 ---
            array(
                'id' => 'feature_items',
                'type' => 'repeater',
                'label' => __( '特性卡片列表 (建议4-5项)', 'developer-starter' ),
                'fields' => array(
                    array( 
                        'id' => 'f_icon_type', 
                        'type' => 'select', 
                        'label' => __( '图标类型', 'developer-starter' ),
                        'options' => array(
                            'image' => __( '图片/SVG', 'developer-starter' ),
                            'class' => __( 'Symbol/Emoji', 'developer-starter' ),
                        ),
                        'default' => 'image',
                    ),
                    array( 
                        'id' => 'f_icon', 
                        'type' => 'image', 
                        'label' => __( '上传图标', 'developer-starter' ),
                        'dependency' => array( 'f_icon_type', '==', 'image' )
                    ),
                    array( 
                        'id' => 'f_icon_class', 
                        'type' => 'text', 
                        'label' => __( '图标类名或Emoji', 'developer-starter' ), 
                        'desc' => __( '例如: icon-weibo 或 🚀', 'developer-starter' ),
                        'dependency' => array( 'f_icon_type', '==', 'class' )
                    ),
                    array( 'id' => 'f_title_content', 'type' => 'textarea', 'label' => __( '标题 (支持HTML)', 'developer-starter' ), 'default' => __( '特性标题', 'developer-starter' ) ),
                    array( 'id' => 'f_desc', 'type' => 'textarea', 'label' => __( '描述 (支持HTML)', 'developer-starter' ), 'default' => __( '简短的描述文本', 'developer-starter' ) ),
                    array( 'id' => 'f_link', 'type' => 'text', 'label' => __( '链接 (可选)', 'developer-starter' ) ),
                    array( 'id' => 'f_bg_color', 'type' => 'color', 'label' => __( '卡片自定义背景', 'developer-starter' ), 'desc' => __( '留空则使用默认半透明磨砂白', 'developer-starter' ) ),
                ),
            ),

            // --- 样式自定义 ---
            array(
                'id' => 'bg_color',
                'type' => 'color',
                'label' => __( '背景颜色', 'developer-starter' ),
                'default' => 'var(--qiling-color-ebf7f8)', // 类似360的淡蓝灰
            ),
            array(
                'id' => 'text_color',
                'type' => 'color',
                'label' => __( '文本颜色', 'developer-starter' ),
                'default' => 'var(--qiling-color-333333)',
            ),
            array(
                'id' => 'highlight_color',
                'type' => 'color',
                'label' => __( '强调色 (按钮/Hover)', 'developer-starter' ),
                'default' => 'var(--qiling-color-00e09f)', // 360 Green
            ),
            array(
                'id' => 'padding_top',
                'type' => 'text',
                'label' => __( '上边距', 'developer-starter' ),
                'default' => '100px',
            ),
            array(
                'id' => 'padding_bottom',
                'type' => 'text',
                'label' => __( '下边距', 'developer-starter' ),
                'default' => '80px',
            ),
        );
    }

    public function get_demo_data() {
        return array(
            'hero_title_content' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '启灵主题<br>为专业开发者打造', 'Qiling Theme<br>built for professional teams' ) : __( '启灵主题<br>为专业开发者打造', 'developer-starter' ),
            'hero_subtitle_content' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '集成了强大的模块化系统，让开发更简单，让界面更出众。', 'A modular system that keeps building simple and presentation polished.' ) : __( '集成了强大的模块化系统，让开发更简单，让界面更出众。', 'developer-starter' ),
            'badge_text' => 'V3.0 Pro',
            'media_type' => 'image',
            'bg_color' => 'var(--qiling-color-ebf7f8)',
            'highlight_color' => 'var(--qiling-color-00e09f)',
            'feature_items' => array(
                array( 'f_title_content' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '极速开发', 'Fast Setup' ) : __( '极速开发', 'developer-starter' ), 'f_desc' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '内置多种开发工具', 'Built-in tools for a quicker launch workflow.' ) : __( '内置多种开发工具', 'developer-starter' ), 'f_icon_type' => 'class', 'f_icon_class' => '🚀' ),
                array( 'f_title_content' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '美观设计', 'Refined Design' ) : __( '美观设计', 'developer-starter' ), 'f_desc' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '遵循现代设计规范', 'Crafted with a clean and modern visual system.' ) : __( '遵循现代设计规范', 'developer-starter' ), 'f_icon_type' => 'class', 'f_icon_class' => '💎' ),
                array( 'f_title_content' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '响应式布局', 'Responsive Layouts' ) : __( '响应式布局', 'developer-starter' ), 'f_desc' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '完美适配所有终端', 'Ready for desktop, tablet, and mobile screens.' ) : __( '完美适配所有终端', 'developer-starter' ), 'f_icon_type' => 'class', 'f_icon_class' => '📱' ),
                array( 'f_title_content' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '持续更新', 'Ongoing Updates' ) : __( '持续更新', 'developer-starter' ), 'f_desc' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '每周功能迭代优化', 'Continuous improvements to keep the product current.' ) : __( '每周功能迭代优化', 'developer-starter' ), 'f_icon_type' => 'class', 'f_icon_class' => '🔄' ),
            ),
        );
    }

    /**
     * 渲染前端界面
     */
    public function render( $data = array() ) {
        // 数据提取
        $badge = isset($data['badge_text']) ? $data['badge_text'] : '';
        $title = isset($data['hero_title_content']) ? $data['hero_title_content'] : ( function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '启灵主题 · 全新一代', 'Qiling Theme, a new generation' ) : __( '启灵主题 · 全新一代', 'developer-starter' ) );
        $subtitle = isset($data['hero_subtitle_content']) ? $data['hero_subtitle_content'] : ( function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '不仅是好看，更是强大的生产力工具。', 'More than beautiful, it is built to help teams ship faster.' ) : __( '不仅是好看，更是强大的生产力工具。', 'developer-starter' ) );
        // Fallback for old data key
        if(empty($title) && isset($data['hero_title'])) $title = $data['hero_title'];
        if(empty($subtitle) && isset($data['hero_subtitle'])) $subtitle = $data['hero_subtitle'];
        
        $btn1_text = isset($data['btn_primary_content']) ? $data['btn_primary_content'] : ( function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '立即体验', 'Start Now' ) : __( '立即体验', 'developer-starter' ) );
        if(empty($btn1_text) && isset($data['btn_primary_text'])) $btn1_text = $data['btn_primary_text'];

        $btn1_icon = isset($data['btn_primary_icon']) ? $data['btn_primary_icon'] : '';
        $btn1_link = isset($data['btn_primary_link']) ? $data['btn_primary_link'] : '#';
        $btn1_bg = isset($data['btn_primary_bg_color']) ? $data['btn_primary_bg_color'] : '';
        $btn1_color = isset($data['btn_primary_text_color']) ? $data['btn_primary_text_color'] : '';
        $btn1_border = isset($data['btn_primary_border_color']) ? $data['btn_primary_border_color'] : '';
        
        $btn2_text = isset($data['btn_secondary_content']) ? $data['btn_secondary_content'] : ( function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '了解更多', 'Learn More' ) : __( '了解更多', 'developer-starter' ) );
        if(empty($btn2_text) && isset($data['btn_secondary_text'])) $btn2_text = $data['btn_secondary_text'];

        $btn2_icon = isset($data['btn_secondary_icon']) ? $data['btn_secondary_icon'] : '';
        $btn2_link = isset($data['btn_secondary_link']) ? $data['btn_secondary_link'] : '#';
        $btn2_bg = isset($data['btn_secondary_bg_color']) ? $data['btn_secondary_bg_color'] : '';
        $btn2_color = isset($data['btn_secondary_text_color']) ? $data['btn_secondary_text_color'] : '';
        $btn2_border = isset($data['btn_secondary_border_color']) ? $data['btn_secondary_border_color'] : '';

        $media_type = isset($data['media_type']) ? $data['media_type'] : 'image';
        $image = isset($data['hero_image']) ? $data['hero_image'] : '';
        $video = isset($data['hero_video']) ? $data['hero_video'] : '';
        $anim = isset($data['enable_float_anim']) ? $data['enable_float_anim'] : 'yes';

        $features = isset($data['feature_items']) && is_array($data['feature_items']) ? $data['feature_items'] : array();

        // 样式提取
        $bg_color = isset($data['bg_color']) ? $data['bg_color'] : 'var(--qiling-color-ebf7f8)';
        $text_color = isset($data['text_color']) ? $data['text_color'] : 'var(--qiling-color-333333)';
        $main_color = isset($data['highlight_color']) ? $data['highlight_color'] : 'var(--qiling-color-00e09f)';
        $pt = isset($data['padding_top']) ? $data['padding_top'] : '100px';
        $pb = isset($data['padding_bottom']) ? $data['padding_bottom'] : '80px';
        
        // Button Styles
        $btn1_style = '';
        if($btn1_bg) $btn1_style .= "background: {$btn1_bg}; border-color: {$btn1_bg};";
        if($btn1_color) $btn1_style .= "color: {$btn1_color};";
        if($btn1_border) $btn1_style .= "border-color: {$btn1_border};";

        $btn2_style = '';
        if($btn2_bg) $btn2_style .= "background: {$btn2_bg}; border-color: {$btn2_bg};";
        if($btn2_color) $btn2_style .= "color: {$btn2_color};";
        if($btn2_border) $btn2_style .= "border-color: {$btn2_border};";

        $uid = 'ql_ih_' . uniqid();
        ?>

        <!-- 模块样式 (Scoped via CSS Vars) -->
        <style>
            #<?php echo $uid; ?> {
                --ih-bg-color: <?php echo $bg_color; ?>;
                --ih-text-color: <?php echo $text_color; ?>;
                --ih-main-color: <?php echo $main_color; ?>;
                --ih-pt: <?php echo $pt; ?>;
                --ih-pb: <?php echo $pb; ?>;
            }
        </style>

        <section id="<?php echo esc_attr($uid); ?>" class="module-interact-hero">
            <div class="ql-ih-container">
                
                <!-- 上部分：内容与媒体 -->
                <div class="ql-ih-top-row">
                    <!-- 左侧：文字 -->
                    <div class="ql-ih-content-col">
                        <?php if ( $badge ) : ?>
                            <div class="ql-ih-badge"><?php echo esc_html( $badge ); ?></div>
                        <?php endif; ?>
                        
                        <?php if ( $title ) : ?>
                            <h2 class="ql-ih-title"><?php echo wp_kses_post( $title ); ?></h2>
                        <?php endif; ?>
                        
                        <?php if ( $subtitle ) : ?>
                            <div class="ql-ih-subtitle"><?php echo wp_kses_post( $subtitle ); ?></div>
                        <?php endif; ?>

                        <div class="ql-ih-actions">
                            <?php if ( $btn1_text ) : ?>
                                <a href="<?php echo esc_url( $btn1_link ); ?>" class="ql-ih-btn ql-ih-btn-primary" style="<?php echo esc_attr($btn1_style); ?>">
                                    <?php if($btn1_icon): ?>
                                        <?php echo developer_starter_get_icon_html( $btn1_icon, 'btn-icon' ); ?>
                                    <?php endif; ?>
                                    <?php echo wp_kses_post( $btn1_text ); ?>
                                </a>
                            <?php endif; ?>
                            
                            <?php if ( $btn2_text ) : ?>
                                <a href="<?php echo esc_url( $btn2_link ); ?>" class="ql-ih-btn ql-ih-btn-secondary" style="<?php echo esc_attr($btn2_style); ?>">
                                    <?php if($btn2_icon): ?>
                                        <?php echo developer_starter_get_icon_html( $btn2_icon, 'btn-icon' ); ?>
                                    <?php endif; ?>
                                    <?php echo wp_kses_post( $btn2_text ); ?>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- 右侧：媒体 (图片/视频) -->
                    <div class="ql-ih-media-col">
                        <div class="ql-ih-media-wrapper <?php echo $anim === 'yes' ? 'anim-float' : ''; ?>">
                            <?php if ( $media_type === 'video' && $video ) : ?>
                                <video class="ql-ih-media-video" autoplay muted loop playsinline>
                                    <source src="<?php echo esc_url( $video ); ?>" type="video/mp4">
                                </video>
                            <?php elseif ( $image ) : ?>
                                <img src="<?php echo esc_url( $image ); ?>" alt="<?php echo esc_attr( strip_tags($title) ); ?>" class="ql-ih-media-img">
                            <?php else : ?>
                                <!-- 占位符 -->
                                <div style="width: 400px; height: 300px; background: var(--qiling-color-rgba-0-0-0-01); border-radius: 20px; display: flex; align-items: center; justify-content: center;">
                                    <span><?php esc_html_e( '未设置媒体', 'developer-starter' ); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- 下部分：特性卡片 -->
                <?php if ( ! empty( $features ) ) : ?>
                <div class="ql-ih-features-grid">
                    <?php foreach ( $features as $f ) : 
                        $f_icon_type = isset( $f['f_icon_type'] ) ? $f['f_icon_type'] : 'image';
                        $f_icon = isset( $f['f_icon'] ) ? $f['f_icon'] : ''; // Image URL
                        $f_icon_class = isset( $f['f_icon_class'] ) ? $f['f_icon_class'] : ''; // Class or Emoji
                        
                        $f_title = isset( $f['f_title_content'] ) ? $f['f_title_content'] : '';
                        if(empty($f_title) && isset($f['f_title'])) $f_title = $f['f_title'];

                        $f_desc = isset( $f['f_desc'] ) ? $f['f_desc'] : '';
                        $f_link = isset( $f['f_link'] ) ? $f['f_link'] : '';
                        $f_bg = isset( $f['f_bg_color'] ) && $f['f_bg_color'] ? $f['f_bg_color'] : '';
                        
                        $tag = $f_link ? 'a' : 'div';
                        $href = $f_link ? 'href="' . esc_url( $f_link ) . '"' : '';
                        
                        $card_style = $f_bg ? "style='background-color: {$f_bg} !important; border-color: var(--qiling-color-rgba-0-0-0-005);'" : "";
                    ?>
                        <<?php echo $tag; ?> class="ql-ih-card" <?php echo $href; ?> <?php echo $card_style; ?>>
                            <div class="ql-ih-card-icon">
                                <?php if ( $f_icon_type === 'class' && $f_icon_class ) : ?>
                                    <!-- Icon Class or Emoji -->
                                    <span style="font-size: var(--qiling-text-rem-1p5); color: var(--ih-main-color); display: inline-flex;">
                                        <?php echo developer_starter_get_icon_html( $f_icon_class ); ?>
                                    </span>
                                <?php elseif ( $f_icon ) : ?>
                                    <!-- Image -->
                                    <img src="<?php echo esc_url( $f_icon ); ?>" alt="icon">
                                <?php endif; ?>
                            </div>
                            
                            <div class="ql-ih-card-info">
                                <h4><?php echo wp_kses_post( $f_title ); ?></h4>
                                <p><?php echo wp_kses_post( $f_desc ); ?></p>
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
