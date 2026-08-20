<?php
/**
 * Video Portal Hero Module - 视频门户首屏Banner
 * 
 * @package Developer_Starter
 */

namespace Developer_Starter\Modules\Modules;

use Developer_Starter\Modules\Module_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Video_Portal_Hero_Module extends Module_Base {

    public $allow_full_width;

    public function __construct() {
        $this->category = 'hero'; // 归类为首屏
        $this->icon = 'dashicons-video-alt3';
        $this->description = __( '适用于影视站的高端首屏Banner，支持动态背景切换、右侧播放列表和底部多栏导航。', 'developer-starter' );
        // 允许 full width
        $this->allow_full_width = true;
    }

    public function get_id() {
        return 'qiling_video_portal_hero';
    }

    public function get_name() {
        return __( '视频门户Banner', 'developer-starter' );
    }

    public function get_fields() {
        return array(
            // --- 幻灯片设置 ---
            array(
                'id' => 'slides_info',
                'label' => __( '幻灯片内容', 'developer-starter' ),
                'type' => 'info',
                'description' => __( '添加Banner轮播内容。建议添加 5-8 个项目以达到最佳显示效果。', 'developer-starter' )
            ),
            array( 
                'id' => 'autoplay', 
                'label' => __( '自动轮播', 'developer-starter' ), 
                'type' => 'select', 
                'options' => array( 'yes' => __( '开启', 'developer-starter' ), 'no' => __( '关闭', 'developer-starter' ) ), 
                'default' => 'yes',
                'description' => __( '开启后，幻灯片将每隔5秒自动切换。鼠标悬停时暂停。', 'developer-starter' )
            ),
            array(
                'id' => 'slides',
                'label' => __( '幻灯片列表', 'developer-starter' ),
                'type' => 'repeater',
                'fields' => array(
                    // 大图背景
                    array( 'id' => 'bg_image', 'label' => __( '背景大图 (1920x650)', 'developer-starter' ), 'type' => 'image', 'description' => __( '用于全屏展示的高清背景图', 'developer-starter' ) ),
                    // 小图封面
                    array( 'id' => 'poster_image', 'label' => __( '列表封面 (小图)', 'developer-starter' ), 'type' => 'image', 'description' => __( '右侧列表中显示的竖版或横版小图', 'developer-starter' ) ),
                    
                    // 标题区
                    array( 'id' => 'title', 'label' => __( '影视标题', 'developer-starter' ), 'type' => 'text', 'default' => __( '剧名', 'developer-starter' ) ),
                    array( 'id' => 'title_color', 'label' => __( '影视标题文字颜色', 'developer-starter' ), 'type' => 'color', 'default' => '', 'description' => __( '仅用于纯文字标题；留空时使用视频门户默认标题颜色。', 'developer-starter' ) ),
                    array( 'id' => 'title_image', 'label' => __( '标题图片 (可选)', 'developer-starter' ), 'type' => 'image', 'description' => __( '上传图片标题将替换纯文本标题', 'developer-starter' ) ),
                    
                    // 信息区
                    array( 'id' => 'subtitle', 'label' => __( '更新状态', 'developer-starter' ), 'type' => 'text', 'default' => __( '更新至20集', 'developer-starter' ), 'description' => __( '显示在标题下方', 'developer-starter' ) ),
                    array( 'id' => 'tags', 'label' => __( '类型标签', 'developer-starter' ), 'type' => 'text', 'default' => __( '剧情 / 爱情', 'developer-starter' ), 'description' => __( '用斜杠 / 分隔', 'developer-starter' ) ),
                    array( 'id' => 'desc', 'label' => __( '剧情简介 (可选)', 'developer-starter' ), 'type' => 'textarea', 'description' => __( '建议不超过30字', 'developer-starter' ) ),
                    
                    // 链接与角标
                    array( 'id' => 'link', 'label' => __( '播放链接', 'developer-starter' ), 'type' => 'text', 'default' => '#' ),
                    array( 'id' => 'btn_text', 'label' => __( '按钮文字', 'developer-starter' ), 'type' => 'text', 'default' => __( '立即播放', 'developer-starter' ) ),
                    array( 'id' => 'badge', 'label' => __( '封面角标', 'developer-starter' ), 'type' => 'text', 'default' => '', 'description' => __( '显示在右侧缩略图右上角的标签，例如：4K、VIP、独播、更新至12集', 'developer-starter' ) ),
                    array( 'id' => 'badge_bg_color', 'label' => __( '封面角标背景颜色', 'developer-starter' ), 'type' => 'color', 'default' => '', 'description' => __( '留空时使用视频门户默认角标颜色。', 'developer-starter' ) ),
                ),
                'default_items' => array(
                    array( 'title' => __( '示例剧集 1', 'developer-starter' ), 'subtitle' => __( '更新至第1集', 'developer-starter' ), 'btn_text' => __( '立即播放', 'developer-starter' ) ),
                    array( 'title' => __( '示例剧集 2', 'developer-starter' ), 'subtitle' => __( '全集', 'developer-starter' ), 'btn_text' => __( '立即播放', 'developer-starter' ) ),
                )
            ),

            // --- 搜索入口设置 ---
            array(
                'id'          => 'search_info',
                'label'       => __( '影视搜索入口', 'developer-starter' ),
                'type'        => 'info',
                'description' => __( '搜索框显示在影片信息与右侧缩略图导航之间。旧页面默认关闭，不会改变现有布局。', 'developer-starter' ),
            ),
            array(
                'id'      => 'content_source',
                'label'   => __( 'Banner 内容来源', 'developer-starter' ),
                'type'    => 'select',
                'options' => array( 'manual' => __( '手工幻灯片', 'developer-starter' ) ),
                'default' => 'manual',
            ),
            array(
                'id'      => 'show_search',
                'label'   => __( '显示影视搜索框', 'developer-starter' ),
                'type'    => 'select',
                'options' => array( 'no' => __( '关闭', 'developer-starter' ), 'yes' => __( '开启', 'developer-starter' ) ),
                'default' => 'no',
            ),
            array(
                'id'      => 'search_mode',
                'label'   => __( '搜索模式', 'developer-starter' ),
                'type'    => 'select',
                'options' => array_merge(
                    array( 'inherit' => __( '跟随主题设置', 'developer-starter' ) ),
                    function_exists( 'developer_starter_get_search_mode_choices' )
                        ? developer_starter_get_search_mode_choices()
                        : array( 'all' => __( '综合搜索', 'developer-starter' ), 'post' => __( '文章搜索', 'developer-starter' ) )
                ),
                'default' => 'inherit',
            ),
            array(
                'id'      => 'search_placeholder',
                'label'   => __( '搜索框占位文字', 'developer-starter' ),
                'type'    => 'text',
                'default' => __( '搜索影片、演员或导演', 'developer-starter' ),
            ),
            array(
                'id'      => 'search_button_text',
                'label'   => __( '搜索按钮文字', 'developer-starter' ),
                'type'    => 'text',
                'default' => __( '搜索', 'developer-starter' ),
            ),
            array(
                'id'      => 'show_hot_keywords',
                'label'   => __( '显示热门关键词', 'developer-starter' ),
                'type'    => 'select',
                'options' => array( 'no' => __( '关闭', 'developer-starter' ), 'yes' => __( '开启', 'developer-starter' ) ),
                'default' => 'no',
            ),
            array(
                'id'          => 'hot_keywords',
                'label'       => __( '热门关键词', 'developer-starter' ),
                'type'        => 'text',
                'default'     => '',
                'description' => __( '使用半角或全角逗号分隔；留空时不输出热门词区域。', 'developer-starter' ),
            ),
            array(
                'id'      => 'search_style',
                'label'   => __( '搜索框样式', 'developer-starter' ),
                'type'    => 'select',
                'options' => array(
                    'glass'   => __( '深色通透', 'developer-starter' ),
                    'solid'   => __( '明亮实体', 'developer-starter' ),
                    'compact' => __( '紧凑模式', 'developer-starter' ),
                    'cinema'  => __( '影院搜索台', 'developer-starter' ),
                ),
                'default' => 'glass',
            ),

            // --- 底部栏设置 ---
            array(
                'id' => 'bottom_bar_info',
                'label' => __( '底部导航条设置', 'developer-starter' ),
                'type' => 'info',
                'description' => __( '设置Banner底部的三栏信息条。', 'developer-starter' )
            ),
            
            // 左侧：分类
            array(
                'id' => 'bottom_cats',
                'label' => __( '左侧：分类导航', 'developer-starter' ),
                'type' => 'repeater',
                'fields' => array(
                    array( 'id' => 'text', 'label' => __( '名称', 'developer-starter' ), 'type' => 'text' ),
                    array( 'id' => 'link', 'label' => __( '链接', 'developer-starter' ), 'type' => 'text' ),
                    array( 'id' => 'badge', 'label' => __( '角标文字(可选)', 'developer-starter' ), 'type' => 'text' ),
                    array( 'id' => 'badge_color', 'label' => __( '角标颜色', 'developer-starter' ), 'type' => 'select', 'options' => array( 'red' => __( '红色', 'developer-starter' ), 'orange' => __( '橙色', 'developer-starter' ), 'blue' => __( '蓝色', 'developer-starter' ) ), 'default' => 'red' ),
                    array( 'id' => 'badge_bg_color', 'label' => __( '角标自定义背景颜色', 'developer-starter' ), 'type' => 'color', 'default' => '', 'description' => __( '填写后优先于红/橙/蓝预设。', 'developer-starter' ) ),
                )
            ),
            
            // 中间：功能图标
            array(
                'id' => 'bottom_icons',
                'label' => __( '中间：功能入口', 'developer-starter' ),
                'type' => 'repeater',
                'fields' => array(
                    array( 'id' => 'text', 'label' => __( '名称', 'developer-starter' ), 'type' => 'text' ),
                    array( 'id' => 'icon_img', 'label' => __( '图标图片', 'developer-starter' ), 'type' => 'image', 'description' => __( '建议 24x24 png透明图标', 'developer-starter' ) ),
                    array( 'id' => 'link', 'label' => __( '链接', 'developer-starter' ), 'type' => 'text' ),
                )
            ),
            
            // 右侧：热搜
            array(
                'id' => 'bottom_trends',
                'label' => __( '右侧：热点推荐', 'developer-starter' ),
                'type' => 'repeater',
                'fields' => array(
                    array( 'id' => 'text', 'label' => __( '标题', 'developer-starter' ), 'type' => 'text' ),
                    array( 'id' => 'link', 'label' => __( '链接', 'developer-starter' ), 'type' => 'text' ),
                    array( 'id' => 'info', 'label' => __( '状态(可选)', 'developer-starter' ), 'type' => 'text', 'description' => __( '显示在标题右侧的辅助文字，例如：更新中、HD、8.9分', 'developer-starter' ) ),
                )
            ),

            // --- 样式设置 ---
            array(
                'id' => 'height_desktop',
                'label' => __( 'PC端高度 (px)', 'developer-starter' ),
                'type' => 'number',
                'default' => '650'
            ),
            array(
                'id' => 'height_mobile',
                'label' => __( '移动端高度 (px)', 'developer-starter' ),
                'type' => 'number',
                'default' => '400'
            ),
            $this->get_button_border_color_field( 'play_btn_border_color', __( '播放按钮边框颜色', 'developer-starter' ) ),
            $this->get_button_border_color_field( 'play_btn_hover_border_color', __( '播放按钮悬停边框颜色', 'developer-starter' ), __( '留空时跟随播放按钮边框颜色。', 'developer-starter' ) ),
        );
    }

    public function render( $data = array() ) {
        $clean_css_color_value = static function ( $value ) {
            $value = is_string( $value ) ? trim( wp_strip_all_tags( $value ) ) : '';
            if ( '' === $value || preg_match( '/[;<>{}]/', $value ) ) {
                return '';
            }

            $hex_color = sanitize_hex_color( $value );
            if ( $hex_color ) {
                return $hex_color;
            }

            if ( preg_match( '/^(rgba?|hsla?)\(\s*[0-9\.\s,%]+\s*\)$/i', $value ) ) {
                return $value;
            }

            if ( preg_match( '/^(?:rgba?|hsla?)\(\s*var\(--[a-z0-9_-]+\)(?:\s*,\s*[0-9\.\s%]+)*\s*\)$/i', $value ) ) {
                return $value;
            }

            if ( preg_match( '/^var\(--[a-z0-9_-]+\)$/i', $value ) ) {
                return $value;
            }

            return '';
        };

        $slides = isset( $data['slides'] ) ? $data['slides'] : array();
        
        if ( empty( $slides ) ) return;

        $height_pc = isset( $data['height_desktop'] ) ? $data['height_desktop'] : '650';
        $height_mb = isset( $data['height_mobile'] ) ? $data['height_mobile'] : '400';
        $autoplay = isset( $data['autoplay'] ) ? $data['autoplay'] : 'yes';
        $play_btn_border = isset( $data['play_btn_border_color'] ) ? $clean_css_color_value( $data['play_btn_border_color'] ) : '';
        $play_btn_hover_border = isset( $data['play_btn_hover_border_color'] ) ? $clean_css_color_value( $data['play_btn_hover_border_color'] ) : '';
        
        $module_uid = 'qvph-' . uniqid();
        $module_style = '--qvph-height-pc: ' . esc_attr( $height_pc ) . 'px; --qvph-height-mb: ' . esc_attr( $height_mb ) . 'px;';
        if ( $play_btn_border !== '' ) {
            $module_style .= '--qvph-play-btn-border:' . esc_attr( $play_btn_border ) . ';';
        }
        if ( $play_btn_hover_border !== '' ) {
            $module_style .= '--qvph-play-btn-hover-border:' . esc_attr( $play_btn_hover_border ) . ';';
        }
        
        // 底部栏数据
        $bottom_cats = isset( $data['bottom_cats'] ) ? $data['bottom_cats'] : array();
        $bottom_icons = isset( $data['bottom_icons'] ) ? $data['bottom_icons'] : array();
        $bottom_trends = isset( $data['bottom_trends'] ) ? $data['bottom_trends'] : array();
        $show_search = isset( $data['show_search'] ) && 'yes' === (string) $data['show_search'];
        $search_mode_setting = isset( $data['search_mode'] ) ? sanitize_key( (string) $data['search_mode'] ) : 'inherit';
        $search_mode = function_exists( 'developer_starter_resolve_search_mode' ) ? developer_starter_resolve_search_mode( $search_mode_setting ) : 'all';
        $search_placeholder = isset( $data['search_placeholder'] ) ? sanitize_text_field( (string) $data['search_placeholder'] ) : __( '搜索影片、演员或导演', 'developer-starter' );
        $search_button_text = isset( $data['search_button_text'] ) ? sanitize_text_field( (string) $data['search_button_text'] ) : __( '搜索', 'developer-starter' );
        $search_placeholder = '' !== $search_placeholder ? $search_placeholder : __( '搜索影片、演员或导演', 'developer-starter' );
        $search_button_text = '' !== $search_button_text ? $search_button_text : __( '搜索', 'developer-starter' );
        $show_hot_keywords = $show_search && isset( $data['show_hot_keywords'] ) && 'yes' === (string) $data['show_hot_keywords'];
        $search_style = isset( $data['search_style'] ) ? sanitize_key( (string) $data['search_style'] ) : 'glass';
        if ( ! in_array( $search_style, array( 'glass', 'solid', 'compact', 'cinema' ), true ) ) {
            $search_style = 'glass';
        }
        $hot_keywords_raw = isset( $data['hot_keywords'] ) ? sanitize_text_field( (string) $data['hot_keywords'] ) : '';
        $hot_keywords = preg_split( '/[,，\r\n]+/u', $hot_keywords_raw );
        $hot_keywords = array_values( array_unique( array_filter( array_map( 'sanitize_text_field', array_map( 'trim', (array) $hot_keywords ) ) ) ) );
        $hot_keywords = array_slice( $hot_keywords, 0, 10 );
        $search_action = function_exists( 'developer_starter_get_search_form_action_url' ) ? developer_starter_get_search_form_action_url() : home_url( '/' );
        $search_use_rewrite = function_exists( 'developer_starter_get_option' ) && developer_starter_get_option( 'search_rewrite', '' );
        $search_input_id = $module_uid . '-search-input';
        
        ?>
        <section class="module qiling-video-portal-hero<?php echo $show_search ? ' qvph-has-search' : ''; ?>" id="<?php echo esc_attr( $module_uid ); ?>" style="<?php echo esc_attr( $module_style ); ?>">
            
            <!-- 1. 背景层容器 -->
            <div class="qvph-bg-container">
                <?php foreach ( $slides as $index => $slide ) : 
                    $bg = ! empty( $slide['bg_image'] ) ? $slide['bg_image'] : '';
                    $active_class = $index === 0 ? 'active' : '';
                ?>
                    <div class="qvph-bg-item <?php echo $active_class; ?>" data-index="<?php echo $index; ?>">
                        <?php if ( $bg ) : ?>
                            <!-- 懒加载处理：第一张图直接加载，后面的懒加载 -->
                            <?php if ( $index === 0 ) : ?>
                                <img src="<?php echo esc_url( $bg ); ?>" alt="bg" class="qvph-bg-img">
                            <?php else : ?>
                                <img data-src="<?php echo esc_url( $bg ); ?>" alt="bg" class="qvph-bg-img lazy-bg">
                            <?php endif; ?>
                        <?php endif; ?>
                        <div class="qvph-bg-mask"></div> <!-- 渐变遮罩 -->
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- 2. 主内容：左侧影片信息、右侧海报导航、底部搜索 -->
            <div class="container qvph-content-container">
                <div class="qvph-layout">
                    <div class="qvph-info-wrapper">
                        <?php foreach ( $slides as $index => $slide ) : 
                            $active_class = $index === 0 ? 'active' : '';
                            $title = $slide['title'];
                            $title_color = isset( $slide['title_color'] ) ? $clean_css_color_value( $slide['title_color'] ) : '';
                            $title_style = $title_color !== '' ? 'color:' . $title_color . ';' : '';
                            $title_img = isset( $slide['title_image'] ) ? $slide['title_image'] : '';
                            $subtitle = isset( $slide['subtitle'] ) ? $slide['subtitle'] : '';
                            $tags = isset( $slide['tags'] ) ? $slide['tags'] : '';
                            $desc = isset( $slide['desc'] ) ? $slide['desc'] : '';
                            $link = isset( $slide['link'] ) ? $slide['link'] : '#';
                            $btn_text = ! empty( $slide['btn_text'] ) ? $slide['btn_text'] : __( '立即播放', 'developer-starter' );
                        ?>
                            <div class="qvph-info-item <?php echo $active_class; ?>" data-index="<?php echo $index; ?>">
                                <!-- 标题 -->
                                <?php if ( $title_img ) : ?>
                                    <img src="<?php echo esc_url( $title_img ); ?>" alt="<?php echo esc_attr( $title ); ?>" class="qvph-title-img">
                                <?php else : ?>
                                    <h2 class="qvph-title-text" style="<?php echo esc_attr( $title_style ); ?>"><?php echo esc_html( $title ); ?></h2>
                                <?php endif; ?>

                                <!-- 标签 -->
                                <div class="qvph-meta-row">
                                    <?php if ( $tags ) : ?><span class="qvph-tag"><?php echo esc_html( $tags ); ?></span><?php endif; ?>
                                    <?php if ( $tags && $subtitle ) : ?><span class="qvph-sep">|</span><?php endif; ?>
                                    <?php if ( $subtitle ) : ?><span class="qvph-subtitle"><?php echo esc_html( $subtitle ); ?></span><?php endif; ?>
                                </div>

                                <!-- 简介 -->
                                <?php if ( $desc ) : ?>
                                    <p class="qvph-desc"><?php echo esc_html( $desc ); ?></p>
                                <?php endif; ?>

                                <!-- 按钮 -->
                                <a href="<?php echo esc_url( $link ); ?>" class="qvph-play-btn">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>
                                    <?php echo esc_html( $btn_text ); ?>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- 右侧缩略图导航 -->
                    <div class="qvph-nav-list">
                        <?php foreach ( $slides as $index => $slide ) : 
                            $poster = isset( $slide['poster_image'] ) ? $slide['poster_image'] : '';
                            $title = $slide['title'];
                            $badge = isset( $slide['badge'] ) ? $slide['badge'] : '';
                            $badge_bg_color = isset( $slide['badge_bg_color'] ) ? $clean_css_color_value( $slide['badge_bg_color'] ) : '';
                            $badge_style = $badge_bg_color !== '' ? 'background:' . $badge_bg_color . ';' : '';
                            $active_class = $index === 0 ? 'active' : '';
                        ?>
                            <div class="qvph-nav-item <?php echo $active_class; ?>" data-index="<?php echo $index; ?>">
                                <div class="qvph-nav-thumb">
                                    <?php if ( $poster ) : ?>
                                        <img src="<?php echo esc_url( $poster ); ?>" alt="<?php echo esc_attr( $title ); ?>">
                                    <?php endif; ?>
                                    <div class="qvph-nav-overlay"></div>
                                    <?php if ( $badge ) : ?>
                                        <span class="qvph-nav-badge" style="<?php echo esc_attr( $badge_style ); ?>"><?php echo esc_html( $badge ); ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="qvph-nav-title"><?php echo esc_html( $title ); ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                </div>

                <?php if ( $show_search ) : ?>
                    <div class="qvph-search-stage">
                        <div class="qvph-search qvph-search--<?php echo esc_attr( $search_style ); ?>">
                            <form role="search" method="get" class="qvph-search-form qiling-search-enhanced" data-qiling-search-form="1" action="<?php echo esc_url( $search_action ); ?>"<?php if ( $search_use_rewrite ) : ?> onsubmit="return dsSearchRedirect(this);"<?php endif; ?>>
                                <label class="screen-reader-text" for="<?php echo esc_attr( $search_input_id ); ?>"><?php esc_html_e( '搜索影视内容', 'developer-starter' ); ?></label>
                                <div class="qvph-search-box">
                                    <svg class="qvph-search-icon" viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"></circle><path d="m20 20-4-4"></path></svg>
                                    <input id="<?php echo esc_attr( $search_input_id ); ?>" type="search" name="s" class="qvph-search-input" placeholder="<?php echo esc_attr( $search_placeholder ); ?>" autocomplete="off" data-qiling-search-input="1" />
                                    <input type="hidden" name="qiling_search_mode" value="<?php echo esc_attr( $search_mode ); ?>" />
                                    <button type="submit" class="qvph-search-submit"><?php echo esc_html( $search_button_text ); ?></button>
                                </div>
                            </form>
                            <?php if ( $show_hot_keywords && ! empty( $hot_keywords ) ) : ?>
                                <div class="qvph-search-hot" aria-label="<?php esc_attr_e( '热门搜索', 'developer-starter' ); ?>">
                                    <span class="qvph-search-hot-label"><?php esc_html_e( '热门：', 'developer-starter' ); ?></span>
                                    <?php foreach ( $hot_keywords as $hot_keyword ) : ?>
                                        <?php
                                        $hot_url = function_exists( 'developer_starter_get_search_pretty_url' )
                                            ? developer_starter_get_search_pretty_url( $hot_keyword, array( 'qiling_search_mode' => $search_mode ) )
                                            : add_query_arg( array( 's' => $hot_keyword, 'qiling_search_mode' => $search_mode ), home_url( '/' ) );
                                        ?>
                                        <a href="<?php echo esc_url( $hot_url ); ?>"><?php echo esc_html( $hot_keyword ); ?></a>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <?php if ( ! empty( $bottom_cats ) || ! empty( $bottom_icons ) || ! empty( $bottom_trends ) ) : ?>
            <!-- 3. 底部信息条 -->
            <div class="qvph-bottom-bar">
                <div class="container qvph-bottom-inner<?php echo empty( $bottom_icons ) ? ' qvph-bottom-inner--no-icons' : ''; ?>">
                    
                    <!-- 左侧：分类 -->
                    <?php if ( ! empty( $bottom_cats ) ) : ?><div class="qvph-col qvph-col-cats">
                        <?php foreach ( $bottom_cats as $item ) : ?>
                            <a href="<?php echo esc_url( $item['link'] ); ?>" class="qvph-cat-link">
                                <?php echo esc_html( $item['text'] ); ?>
                                <?php if ( ! empty( $item['badge'] ) ) : 
                                    $badge_preset = isset( $item['badge_color'] ) ? sanitize_key( (string) $item['badge_color'] ) : 'red';
                                    if ( ! in_array( $badge_preset, array( 'red', 'orange', 'blue' ), true ) ) {
                                        $badge_preset = 'red';
                                    }
                                    $badge_bg_color = isset( $item['badge_bg_color'] ) ? $clean_css_color_value( $item['badge_bg_color'] ) : '';
                                    $badge_style = $badge_bg_color !== '' ? 'background:' . $badge_bg_color . ';' : '';
                                ?>
                                    <span class="qvph-badge-dot badge-<?php echo esc_attr( $badge_preset ); ?>" style="<?php echo esc_attr( $badge_style ); ?>"><?php echo esc_html( $item['badge'] ); ?></span>
                                <?php endif; ?>
                            </a>
                        <?php endforeach; ?>
                    </div><?php endif; ?>

                    <!-- 中间：功能 -->
                    <?php if ( ! empty( $bottom_icons ) ) : ?><div class="qvph-col qvph-col-icons">
                        <?php foreach ( $bottom_icons as $item ) : ?>
                            <a href="<?php echo esc_url( $item['link'] ); ?>" class="qvph-icon-link">
                                <?php if ( ! empty( $item['icon_img'] ) ) : ?>
                                    <img src="<?php echo esc_url( $item['icon_img'] ); ?>" alt="">
                                <?php endif; ?>
                                <span><?php echo esc_html( $item['text'] ); ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div><?php endif; ?>

                    <!-- 右侧：热搜 -->
                    <?php if ( ! empty( $bottom_trends ) ) : ?><div class="qvph-col qvph-col-trends">
                         <?php foreach ( $bottom_trends as $item ) : 
                            $info = ! empty( $item['info'] ) ? $item['info'] : '';
                         ?>
                            <a href="<?php echo esc_url( $item['link'] ); ?>" class="qvph-trend-link">
                                <span class="qvph-trend-text"><?php echo esc_html( $item['text'] ); ?></span>
                                <?php if ( $info ) : ?>
                                    <span class="qvph-trend-info"><?php echo esc_html( $info ); ?></span>
                                <?php endif; ?>
                            </a>
                        <?php endforeach; ?>
                    </div><?php endif; ?>

                </div>
            </div>
            <?php endif; ?>

            
            
            <script>
            (function () {
            function boot() {
                var module = document.getElementById(<?php echo wp_json_encode( $module_uid ); ?>);
                if (!module || module.dataset.videoPortalHeroInitialized) {
                    return;
                }
                module.dataset.videoPortalHeroInitialized = 'true';

                var backgrounds = Array.prototype.slice.call(module.querySelectorAll('.qvph-bg-item'));
                var infos = Array.prototype.slice.call(module.querySelectorAll('.qvph-info-item'));
                var navs = Array.prototype.slice.call(module.querySelectorAll('.qvph-nav-item'));
                var navList = module.querySelector('.qvph-nav-list');
                var interactionArea = module.querySelector('.qvph-layout');
                var autoplay = <?php echo wp_json_encode( $autoplay === 'yes' ); ?>;
                var intervalTime = 5000;
                var autoTimer = null;
                var currentIndex = 0;
                var totalSlides = navs.length;

                function getByIndex(items, index) {
                    return items.find(function (item) {
                        return String(item.getAttribute('data-index')) === String(index);
                    }) || null;
                }

                function animateScroll(container, targetScrollLeft, duration) {
                    var start = container.scrollLeft;
                    var change = targetScrollLeft - start;
                    var startTime = null;

                    function easeInOutQuad(progress) {
                        return progress < 0.5
                            ? 2 * progress * progress
                            : 1 - Math.pow(-2 * progress + 2, 2) / 2;
                    }

                    function step(timestamp) {
                        if (!module.isConnected) return;
                        if (startTime === null) {
                            startTime = timestamp;
                        }

                        var elapsed = timestamp - startTime;
                        var progress = duration > 0 ? Math.min(elapsed / duration, 1) : 1;
                        var eased = easeInOutQuad(progress);

                        container.scrollLeft = start + (change * eased);

                        if (progress < 1) {
                            window.requestAnimationFrame(step);
                        }
                    }

                    window.requestAnimationFrame(step);
                }

                function loadBg(index) {
                    var wrapper = getByIndex(backgrounds, index);
                    if (!wrapper) {
                        return;
                    }

                    var image = wrapper.querySelector('img.lazy-bg');
                    if (image && image.getAttribute('data-src')) {
                        image.setAttribute('src', image.getAttribute('data-src'));
                        image.removeAttribute('data-src');
                        image.classList.remove('lazy-bg');
                    }
                }

                function scrollNavIntoView(index) {
                    if (!navList) {
                        return;
                    }

                    var navItem = getByIndex(navs, index);
                    if (!navItem) {
                        return;
                    }

                    var containerRect = navList.getBoundingClientRect();
                    var itemRect = navItem.getBoundingClientRect();

                    if (itemRect.left < containerRect.left) {
                        animateScroll(navList, navList.scrollLeft - (containerRect.left - itemRect.left), 200);
                    } else if (itemRect.right > containerRect.right) {
                        animateScroll(navList, navList.scrollLeft + (itemRect.right - containerRect.right), 200);
                    }
                }

                function switchSlide(index) {
                    currentIndex = index;

                    navs.forEach(function (item) {
                        item.classList.toggle('active', String(item.getAttribute('data-index')) === String(index));
                    });

                    backgrounds.forEach(function (item) {
                        item.classList.toggle('active', String(item.getAttribute('data-index')) === String(index));
                    });

                    infos.forEach(function (item) {
                        item.classList.toggle('active', String(item.getAttribute('data-index')) === String(index));
                    });

                    loadBg(index);
                    scrollNavIntoView(index);
                }

                function stopAutoPlay() {
                    if (autoTimer) {
                        clearInterval(autoTimer);
                        autoTimer = null;
                    }
                }

                function startAutoPlay() {
                    if (!autoplay || totalSlides <= 1) {
                        return;
                    }

                    stopAutoPlay();
                    autoTimer = window.setInterval(function () {
                        if (!module.isConnected) {
                            stopAutoPlay();
                            return;
                        }

                        var nextIndex = (currentIndex + 1) % totalSlides;
                        switchSlide(nextIndex);
                    }, intervalTime);
                }

                navs.forEach(function (nav) {
                    nav.addEventListener('mouseenter', function () {
                        var index = parseInt(nav.getAttribute('data-index'), 10) || 0;
                        switchSlide(index);
                        stopAutoPlay();
                    });

                    nav.addEventListener('click', function () {
                        var index = parseInt(nav.getAttribute('data-index'), 10) || 0;
                        switchSlide(index);
                    });
                });

                if (interactionArea) {
                    interactionArea.addEventListener('mouseenter', stopAutoPlay);
                    interactionArea.addEventListener('mouseleave', startAutoPlay);
                }

                window.setTimeout(function () {
                    if (!module.isConnected) return;
                    loadBg(1);
                    startAutoPlay();
                }, 2000);
            }
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', boot, { once: true });
            } else {
                boot();
            }
            })();
            </script>

        </section>
        <?php
    }
}
