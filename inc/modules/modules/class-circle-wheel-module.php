<?php
/**
 * Circle Wheel Module - 圆形交互式轮盘
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Modules\Modules;

use Developer_Starter\Modules\Module_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Circle_Wheel_Module extends Module_Base {

    public function __construct() {
        $this->category = 'homepage';
        $this->icon = 'dashicons-update'; // 类似旋转的图标
        $this->description = __( '360式圆形交互轮盘，展示核心功能', 'developer-starter' );
    }

    public function get_id() {
        return 'circle_wheel';
    }

    public function get_name() {
        return __( '圆形交互轮盘(小屏幕不显示)', 'developer-starter' );
    }

    public function get_fields() {
        return array(
            array( 'id' => 'wheel_title', 'type' => 'text', 'label' => __( '模块标题', 'developer-starter' ), 'default' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '启灵主题核心优势', 'Why Qiling Theme' ) : __( '启灵主题核心优势', 'developer-starter' ) ),
            array(
                'id' => 'wheel_subtitle',
                'type' => 'text',
                'label' => __( '模块副标题', 'developer-starter' ),
                'description' => __( '支持允许的HTML标签，例如 &lt;span style="color:red"&gt;强调文本&lt;/span&gt;', 'developer-starter' ),
                'default' => '',
            ),
            
            // --- 轮盘列表设置 ---
            array(
                'id' => 'wheel_items',
                'label' => __( '轮盘功能项 (建议8-10项效果最佳)', 'developer-starter' ),
                'type' => 'repeater',
                'fields' => array(
                    array( 'id' => 'ring_title_desc', 'label' => __( '圆环短标题 (常驻)', 'developer-starter' ), 'type' => 'text', 'desc' => __( '如：积分商城', 'developer-starter' ) ),
                    array( 'id' => 'hover_title_desc', 'label' => __( '悬停-标题', 'developer-starter' ), 'type' => 'text', 'desc' => __( '侧边显示的主标题，支持HTML', 'developer-starter' ) ),
                    array( 'id' => 'hover_desc', 'label' => __( '悬停-描述', 'developer-starter' ), 'type' => 'textarea', 'desc' => __( '侧边显示的 detailed description，支持HTML', 'developer-starter' ) ),
                    array( 
                        'id' => 'highlight', 
                        'label' => __( '默认高亮', 'developer-starter' ), 
                        'type' => 'select',
                        'options' => array(
                            'no' => __( '否', 'developer-starter' ),
                            'yes' => __( '是', 'developer-starter' ),
                        ),
                        'default' => 'no',
                        'desc' => __( '【必选一项】请将其中一项设置为“是”作为默认展示项。', 'developer-starter' ) 
                    ),
                ),
            ),

            // --- 样式设置 ---
            array(
                'id' => 'wheel_bg_type',
                'label' => __( '背景类型', 'developer-starter' ),
                'type' => 'select',
                'options' => array(
                    'color' => __( '纯色/渐变', 'developer-starter' ),
                    'image' => __( '背景图片', 'developer-starter' ),
                ),
                'default' => 'image',
            ),
            array(
                'id' => 'wheel_bg_color',
                'label' => __( '背景颜色', 'developer-starter' ),
                'type' => 'color',
                'dependency' => array( 'wheel_bg_type', '==', 'color' ),
                'default' => 'var(--color-neutral-900)',
            ),
            array(
                'id' => 'wheel_bg_image',
                'label' => __( '背景图片', 'developer-starter' ),
                'type' => 'image',
                'dependency' => array( 'wheel_bg_type', '==', 'image' ),
            ),
            array(
                'id' => 'wheel_bg_overlay',
                'label' => __( '背景遮罩浓度', 'developer-starter' ),
                'type' => 'text',
                'default' => '0.8',
                'desc' => __( '0-1之间，背景图模式下生效', 'developer-starter' ),
                'dependency' => array( 'wheel_bg_type', '==', 'image' ),
            ),

            // --- 间距设置 ---
            array(
                'id' => 'wheel_padding_top',
                'label' => __( '上边距', 'developer-starter' ),
                'type' => 'text',
                'default' => '100px',
            ),
            array(
                'id' => 'highlight_color',
                'label' => __( '高亮标题颜色', 'developer-starter' ),
                'type' => 'color',
                'default' => 'var(--qiling-color-4ffbdf)',
                'desc' => __( '侧边详情标题的高亮颜色', 'developer-starter' ),
            ),
            array(
                'id' => 'wheel_padding_bottom',
                'label' => __( '下边距', 'developer-starter' ),
                'type' => 'text',
                'default' => '100px',
            ),
        );
    }

    public function get_demo_data() {
        return array(
            'wheel_title' => __( '启灵主题核心功能', 'developer-starter' ),
            'wheel_subtitle' => 'Powerful Features of <span style="color:var(--qiling-color-4ffbdf)">Qi Ling Theme</span>',
            'wheel_bg_type' => 'color',
            'wheel_bg_color' => 'var(--color-neutral-900)',
            'wheel_items' => array(
                array( 'ring_title_desc' => __( '积分商城', 'developer-starter' ), 'hover_title_desc' => __( '强大的积分商城', 'developer-starter' ), 'hover_desc' => __( '内置完善的积分商城系统，支持实物、虚拟商品兑换，支持积分+现金混合支付。', 'developer-starter' ), 'highlight' => 'yes' ),
                array( 'ring_title_desc' => __( '营销互动', 'developer-starter' ), 'hover_title_desc' => __( '多种营销玩法', 'developer-starter' ), 'hover_desc' => __( '包含大转盘抽奖、每日签到、任务中心等多种用户互动营销工具，提升用户粘性。', 'developer-starter' ), 'highlight' => 'no' ),
                array( 'ring_title_desc' => __( '会员中心', 'developer-starter' ), 'hover_title_desc' => __( '精美用户中心', 'developer-starter' ), 'hover_desc' => __( '重新设计的用户中心界面，支持VIP会员体系、余额充值、推广返佣等功能。', 'developer-starter' ), 'highlight' => 'no' ),
                array( 'ring_title_desc' => __( '内容变现', 'developer-starter' ), 'hover_title_desc' => __( '内容付费阅读', 'developer-starter' ), 'hover_desc' => __( '支持文章部分内容隐藏、付费阅读、VIP免费阅读等多种内容变现模式。', 'developer-starter' ), 'highlight' => 'no' ),
                array( 'ring_title_desc' => __( '资源下载', 'developer-starter' ), 'hover_title_desc' => __( '专业资源下载', 'developer-starter' ), 'hover_desc' => __( '针对资源站优化的下载模块，支持多网盘地址、提取码复制、解压密码保护。', 'developer-starter' ), 'highlight' => 'no' ),
                array( 'ring_title_desc' => __( '社交登录', 'developer-starter' ), 'hover_title_desc' => __( '便捷社交登录', 'developer-starter' ), 'hover_desc' => __( '集成微信、QQ、微博等主流社交平台一键登录，降低用户注册门槛。', 'developer-starter' ), 'highlight' => 'no' ),
                array( 'ring_title_desc' => __( '响应式', 'developer-starter' ), 'hover_title_desc' => __( '全端响应式设计', 'developer-starter' ), 'hover_desc' => __( '完美适配PC、平板、手机等各种屏幕尺寸，提供一致的优质浏览体验。', 'developer-starter' ), 'highlight' => 'no' ),
                array( 'ring_title_desc' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( 'SEO优化', 'SEO' ) : __( 'SEO优化', 'developer-starter' ), 'hover_title_desc' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '极致SEO优化', 'SEO Ready' ) : __( '极致SEO优化', 'developer-starter' ), 'hover_desc' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '遵循SEO最佳实践，自动生成Mate标签、Sitemap，助力网站收录排名。', 'Built with search-friendly structure, metadata support, and sitemap-friendly output.' ) : __( '遵循SEO最佳实践，自动生成Mate标签、Sitemap，助力网站收录排名。', 'developer-starter' ), 'highlight' => 'no' ),
            ),
        );
    }

    public function render( $data = array() ) {
        // 数据获取
        $title = isset( $data['wheel_title'] ) ? $data['wheel_title'] : ( function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '启灵主题核心优势', 'Why Qiling Theme' ) : __( '启灵主题核心优势', 'developer-starter' ) );
        $subtitle = isset( $data['wheel_subtitle'] ) ? $data['wheel_subtitle'] : '';
        $items = isset( $data['wheel_items'] ) && is_array( $data['wheel_items'] ) ? $data['wheel_items'] : array();
        
        // 样式与背景
        $bg_type = isset( $data['wheel_bg_type'] ) ? $data['wheel_bg_type'] : 'image';
        $bg_color = isset( $data['wheel_bg_color'] ) ? $data['wheel_bg_color'] : 'var(--color-neutral-900)';
        $bg_image = isset( $data['wheel_bg_image'] ) ? $data['wheel_bg_image'] : '';
        $overlay = isset( $data['wheel_bg_overlay'] ) ? $data['wheel_bg_overlay'] : '0.8';
        
        $pt = isset( $data['wheel_padding_top'] ) ? $data['wheel_padding_top'] : '100px';
        $pb = isset( $data['wheel_padding_bottom'] ) ? $data['wheel_padding_bottom'] : '100px';
        $highlight_color = isset( $data['highlight_color'] ) ? $data['highlight_color'] : 'var(--qiling-color-4ffbdf)';

        $section_style = "padding-top: {$pt}; padding-bottom: {$pb};";
        if ( $bg_type === 'color' ) {
            $section_style .= "background: {$bg_color};";
        } elseif ( $bg_image ) {
            $section_style .= "background-image: url('{$bg_image}'); background-size: cover; background-position: center;";
        } else {
             // Fallback dark bg
             $section_style .= "background-color: var(--color-neutral-900);";
        }
        $section_style .= "--wheel-highlight-color: {$highlight_color};";
        
        // 生成唯一ID，用于JS/CSS隔离
        $unique_id = 'wheel-' . uniqid();
        
        if ( empty( $items ) ) {
            $items = $this->get_demo_data()['wheel_items'];
        }
        
        $item_count = count( $items );
        // 计算角度步长
        $step_angle = $item_count > 0 ? 360 / $item_count : 0;
        
        // 生成动态CSS
        $dynamic_css = "";
        foreach ( $items as $index => $item ) {
            $angle = $index * $step_angle;
            $counter_angle = -$angle;
            
            //Item Rotation
            $dynamic_css .= "#{$unique_id} .wheel-item[data-index='{$index}'] { transform: rotate({$angle}deg) translateY(-250px); } ";
            //Content Counter Rotation (Keep horizontal)
            $dynamic_css .= "#{$unique_id} .wheel-item[data-index='{$index}'] .wheel-item-content { transform: rotate({$counter_angle}deg); } ";
             //Active/Hover State
            $dynamic_css .= "#{$unique_id} .wheel-item[data-index='{$index}']:hover .wheel-item-content, #{$unique_id} .wheel-item[data-index='{$index}'].active .wheel-item-content { transform: rotate({$counter_angle}deg) scale(1.1); } ";
             // Ring Text Color on Active
            $dynamic_css .= "#{$unique_id} .wheel-item[data-index='{$index}'].active .ring-text, #{$unique_id} .wheel-item[data-index='{$index}']:hover .ring-text { color: {$highlight_color}; }";
        }
        ?>
        
        <style>
            <?php echo $dynamic_css; ?>
        </style>
        
        <section class="module module-circle-wheel" id="<?php echo esc_attr( $unique_id ); ?>" style="<?php echo esc_attr( $section_style ); ?>">
            <?php if ( $bg_type === 'image' ) : ?>
                <div class="wheel-overlay" style="opacity: <?php echo esc_attr( $overlay ); ?>;"></div>
            <?php endif; ?>
            
            <div class="container wheel-container">
                <!-- 顶部标题 -->
                <div class="wheel-header">
                    <?php if ( $title ) : ?>
                        <h2 class="wheel-main-title"><?php echo esc_html( $title ); ?></h2>
                    <?php endif; ?>
                    <?php if ( $subtitle ) : ?>
                        <div class="wheel-sub-title"><?php echo wp_kses_post( $subtitle ); ?></div>
                    <?php endif; ?>
                </div>

                <!-- 轮盘主体区域 -->
                <div class="wheel-wrapper">
                    <!-- 左侧/右侧 详情展示 (默认显示第一个或高亮项) -->
                    <!-- 为了实现左右布局，我们在中心放圆环，两侧放详情文字的容器 -->
                    <!-- 但根据360的设计，文字是根据当前选中的项显示的，通常设计为“默认左右两侧都有占位”或者“悬浮动态显示” -->
                    <!-- 这里采用：左右各一个内容容器，通过JS控制显示与隐藏 -->
                    
                    <div class="wheel-details-panel detail-left">
                        <!-- JS将动态填充内容 -->
                        <h3 class="detail-title"></h3>
                        <div class="detail-desc"></div>
                    </div>

                    <div class="wheel-center-stage">
                        <!-- 外圈圆环 -->
                        <div class="wheel-ring-border"></div>
                        <div class="wheel-ring-inner-border"></div>
                        
                        <!-- 旋转的圆环容器 -->
                        <div class="wheel-items-ring">
                            <?php foreach ( $items as $index => $item ) : 
                                $angle = $index * $step_angle;
                                $highlight = isset($item['highlight']) ? $item['highlight'] : 'no';
                                $is_active = ( $highlight === 'yes' ) ? 'active' : ( $index === 0 && $highlight !== 'no' ? 'active' : '' );
                                
                                $ring_title = isset($item['ring_title_desc']) ? $item['ring_title_desc'] : '';
                                $hover_title = isset($item['hover_title_desc']) ? $item['hover_title_desc'] : '';
                                $hover_desc = isset($item['hover_desc']) ? $item['hover_desc'] : '';
                            ?>
                                <div class="wheel-item <?php echo $is_active; ?>" 
                                     data-index="<?php echo $index; ?>"
                                     data-angle="<?php echo $angle; ?>"
                                     data-title="<?php echo esc_attr( $hover_title ); ?>"
                                     data-desc="<?php echo esc_attr( $hover_desc ); ?>">
                                    <div class="wheel-item-content">
                                        <span class="ring-text"><?php echo wp_kses_post( $ring_title ); ?></span>
                                        <span class="ring-dot"></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- 中心 Logo 或 图标 (已移除特定安全图标，改为纯装饰或品牌Logo位置) -->
                        <div class="wheel-center-core">
                            <!-- 如果需要放Logo，可以在这里放 img，目前留空仅展示光效 -->
                             <div class="core-center-point"></div>
                            <div class="core-pulse"></div>
                        </div>
                    </div>

                    <div class="wheel-details-panel detail-right">
                        <!-- JS将动态填充内容 -->
                        <h3 class="detail-title"></h3>
                        <div class="detail-desc"></div>
                    </div>
            </div>

            <!-- 内联JS处理交互 (简单逻辑直接内联，避免增加额外JS文件请求) -->
            <script>
            (function() {
                const container = document.getElementById('<?php echo $unique_id; ?>');
                if (!container) return;

                const items = container.querySelectorAll('.wheel-item');
                const leftTitle = container.querySelector('.detail-left .detail-title');
                const leftDesc = container.querySelector('.detail-left .detail-desc');
                const rightTitle = container.querySelector('.detail-right .detail-title');
                const rightDesc = container.querySelector('.detail-right .detail-desc');
                const leftPanel = container.querySelector('.detail-left');
                const rightPanel = container.querySelector('.detail-right');

                function updateDetails(item) {
                    const title = item.getAttribute('data-title');
                    const desc = item.getAttribute('data-desc');
                    const index = parseInt(item.getAttribute('data-index'));
                    const total = items.length;
                    
                    // Decode escaped attribute values before rendering them in detail panels.
                    const tempDiv = document.createElement('div');
                    tempDiv.innerHTML = title;
                    const decodedTitle = tempDiv.innerText;
                    
                    tempDiv.innerHTML = desc;
                    const decodedDesc = tempDiv.innerText;

                    // 判读左右 (0-180 右侧, 180-360 左侧)
                    
                    // 获取当前item的角度
                    const angleStr = item.getAttribute('data-angle');
                    const angle = parseFloat(angleStr) || 0;

                    // 规范化角度到 0-360
                    const normAngle = angle % 360;

                    // 判读左右 (0-180 右侧, 180-360 左侧)
                    // 注意：这取决于CSS的起始位置，这里假设0度是正上方
                    let isRight = (normAngle >= 0 && normAngle < 180);

                    if (isRight) {
                        rightTitle.innerHTML = title; // Use innerHTML to support span
                        rightDesc.innerHTML = desc;
                        rightPanel.style.opacity = '1';
                        rightPanel.style.transform = 'translateX(0)';
                        leftPanel.style.opacity = '0';
                        leftPanel.style.transform = 'translateX(20px)';
                    } else {
                        leftTitle.innerHTML = title;
                        leftDesc.innerHTML = desc;
                        leftPanel.style.opacity = '1';
                        leftPanel.style.transform = 'translateX(0)';
                        rightPanel.style.opacity = '0';
                        rightPanel.style.transform = 'translateX(-20px)';
                    }
                }

                // 初始化激活项
                const activeItem = container.querySelector('.wheel-item.active');
                if (activeItem) {
                    updateDetails(activeItem);
                }

                // 绑定悬停事件
                items.forEach(item => {
                    item.addEventListener('mouseenter', function() {
                        // 移除所有active
                        items.forEach(i => i.classList.remove('active'));
                        // 激活当前
                        this.classList.add('active');
                        updateDetails(this);
                    });
                });
            })();
            </script>
        </section>
        <?php
    }
}
