<?php
/**
 * FAQ Module - 常见问题
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Modules\Modules;

use Developer_Starter\Modules\Module_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class FAQ_Module extends Module_Base {

    public function __construct() {
        $this->category = 'homepage';
        $this->icon = 'dashicons-editor-help';
        $this->description = __( '常见问题解答', 'developer-starter' );
    }

    public function get_id() {
        return 'faq';
    }

    public function get_name() {
        return __( '常见问题', 'developer-starter' );
    }

    public function get_fields() {
        return array(
            array( 'id' => 'faq_title', 'type' => 'text', 'label' => __( '标题', 'developer-starter' ), 'default' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '常见问题', 'Frequently Asked Questions' ) : __( '常见问题', 'developer-starter' ) ),
            array(
                'id' => 'faq_title_size',
                'label' => __( '标题字体大小', 'developer-starter' ),
                'type' => 'text',
                'default' => '',
                'description' => __( '如 2rem 或 36px，留空使用默认', 'developer-starter' ),
            ),
            array(
                'id' => 'faq_title_color',
                'label' => __( '标题颜色', 'developer-starter' ),
                'type' => 'color',
                'default' => '',
                'description' => __( '留空使用默认颜色', 'developer-starter' ),
            ),
            array(
                'id' => 'faq_subtitle',
                'label' => __( '副标题', 'developer-starter' ),
                'type' => 'text',
                'default' => '',
            ),
            array(
                'id' => 'faq_subtitle_size',
                'label' => __( '副标题字体大小', 'developer-starter' ),
                'type' => 'text',
                'default' => '',
                'description' => __( '如 1.1rem 或 18px，留空使用默认', 'developer-starter' ),
            ),
            array(
                'id' => 'faq_subtitle_color',
                'label' => __( '副标题颜色', 'developer-starter' ),
                'type' => 'color',
                'default' => '',
                'description' => __( '留空使用默认颜色', 'developer-starter' ),
            ),
            
            array( 'id' => 'faq_items', 'type' => 'repeater', 'label' => __( '问题列表', 'developer-starter' ), 'fields' => array(
                array( 'id' => 'question', 'type' => 'text', 'label' => __( '问题', 'developer-starter' ) ),
                array( 'id' => 'answer', 'type' => 'textarea', 'label' => __( '解答', 'developer-starter' ) ),
            ) ),
            
            // Style Settings
            array(
                'id' => 'module_bg_color',
                'label' => __( '背景颜色', 'developer-starter' ),
                'type' => 'color',
                'desc' => __( '支持CSS颜色值或渐变代码', 'developer-starter' ),
                'default' => '',
            ),
            array( 'id' => 'faq_card_bg', 'label' => __( '问题卡片背景', 'developer-starter' ), 'type' => 'text', 'default' => '' ),
            array( 'id' => 'faq_card_border', 'label' => __( '问题卡片边框', 'developer-starter' ), 'type' => 'color', 'default' => '' ),
            array( 'id' => 'faq_card_hover_border', 'label' => __( '卡片悬停边框', 'developer-starter' ), 'type' => 'color', 'default' => '' ),
            array( 'id' => 'faq_question_color', 'label' => __( '问题文字颜色', 'developer-starter' ), 'type' => 'color', 'default' => '' ),
            array( 'id' => 'faq_answer_color', 'label' => __( '回答文字颜色', 'developer-starter' ), 'type' => 'color', 'default' => '' ),
            array( 'id' => 'faq_icon_bg', 'label' => __( '展开图标背景', 'developer-starter' ), 'type' => 'color', 'default' => '' ),
            array( 'id' => 'faq_icon_color', 'label' => __( '展开图标颜色', 'developer-starter' ), 'type' => 'color', 'default' => '' ),
            array(
                'id' => 'module_padding_top',
                'label' => __( '上边距 (如 60px)', 'developer-starter' ),
                'type' => 'text',
                'default' => '60px',
            ),
            array(
                'id' => 'module_padding_bottom',
                'label' => __( '下边距 (如 60px)', 'developer-starter' ),
                'type' => 'text',
                'default' => '60px',
            ),
        );
    }

    public function render( $data = array() ) {
        $title = isset( $data['faq_title'] ) && $data['faq_title'] !== ''
            ? $data['faq_title']
            : ( function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '常见问题', 'Frequently Asked Questions' ) : __( '常见问题', 'developer-starter' ) );
        $subtitle = isset( $data['faq_subtitle'] ) ? $data['faq_subtitle'] : '';
        $items = isset( $data['faq_items'] ) ? $data['faq_items'] : array();
        
        // Typography
        $title_size = isset( $data['faq_title_size'] ) ? $data['faq_title_size'] : '';
        $title_color = isset( $data['faq_title_color'] ) ? $data['faq_title_color'] : '';
        $subtitle_size = isset( $data['faq_subtitle_size'] ) ? $data['faq_subtitle_size'] : '';
        $subtitle_color = isset( $data['faq_subtitle_color'] ) ? $data['faq_subtitle_color'] : '';
        
        // Background & Spacing
        $bg_color = isset( $data['module_bg_color'] ) ? $data['module_bg_color'] : '';
        $pt = isset( $data['module_padding_top'] ) && $data['module_padding_top'] !== '' ? $data['module_padding_top'] : '60px';
        $pb = isset( $data['module_padding_bottom'] ) && $data['module_padding_bottom'] !== '' ? $data['module_padding_bottom'] : '60px';
        
        if ( empty( $items ) ) {
            $items = array(
                array(
                    'question' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '你们的服务范围是什么？', 'What services do you provide?' ) : __( '你们的服务范围是什么？', 'developer-starter' ),
                    'answer'   => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '我们提供全国范围内的服务，包括产品研发、技术咨询、解决方案定制等。', 'We provide product delivery, consulting, and tailored solutions for a wide range of business needs.' ) : __( '我们提供全国范围内的服务，包括产品研发、技术咨询、解决方案定制等。', 'developer-starter' ),
                ),
                array(
                    'question' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '如何与你们取得联系？', 'How can I contact you?' ) : __( '如何与你们取得联系？', 'developer-starter' ),
                    'answer'   => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '您可以通过页面底部的联系方式与我们取得联系，或直接拨打客服热线。', 'You can reach us through the contact details on the page or call the customer support line directly.' ) : __( '您可以通过页面底部的联系方式与我们取得联系，或直接拨打客服热线。', 'developer-starter' ),
                ),
                array(
                    'question' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '付款方式有哪些？', 'Which payment methods do you support?' ) : __( '付款方式有哪些？', 'developer-starter' ),
                    'answer'   => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '我们支持对公转账、支付宝、微信等多种付款方式。', 'We support common bank transfer and online payment methods depending on the service arrangement.' ) : __( '我们支持对公转账、支付宝、微信等多种付款方式。', 'developer-starter' ),
                ),
                array(
                    'question' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '售后服务如何保障？', 'How is after-sales support handled?' ) : __( '售后服务如何保障？', 'developer-starter' ),
                    'answer'   => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '我们提供7x24小时技术支持，并有完善的售后服务体系。', 'We provide responsive follow-up support and a structured after-sales service process.' ) : __( '我们提供7x24小时技术支持，并有完善的售后服务体系。', 'developer-starter' ),
                ),
            );
        }
        
        // Dynamic Styles
        $section_style = "padding-top: {$pt}; padding-bottom: {$pb};";
        
        if ( $bg_color ) {
            $section_style .= strpos( $bg_color, 'gradient' ) !== false ? "background: {$bg_color};" : "background-color: {$bg_color};";
        }
        foreach ( array( 'faq_card_bg' => '--faq-card-bg', 'faq_card_border' => '--faq-card-border', 'faq_card_hover_border' => '--faq-card-hover-border', 'faq_question_color' => '--faq-question', 'faq_answer_color' => '--faq-answer', 'faq_icon_bg' => '--faq-icon-bg', 'faq_icon_color' => '--faq-icon-color' ) as $field => $variable ) {
            if ( ! empty( $data[ $field ] ) ) $section_style .= $variable . ':' . $data[ $field ] . ';';
        }
        
        $title_style = '';
        if ( $title_size ) $title_style .= "font-size: {$title_size};";
        if ( $title_color ) $title_style .= "color: {$title_color};";
        
        $subtitle_style = '';
        if ( $subtitle_size ) $subtitle_style .= "font-size: {$subtitle_size};";
        if ( $subtitle_color ) $subtitle_style .= "color: {$subtitle_color};";
        ?>
        <section class="module module-faq" style="<?php echo esc_attr( $section_style ); ?>">
            <div class="container">
                <div class="section-header text-center">
                    <h2 class="section-title"<?php echo $title_style ? ' style="' . esc_attr( $title_style ) . '"' : ''; ?>><?php echo esc_html( $title ); ?></h2>
                    <?php if ( $subtitle ) : ?>
                        <p class="section-subtitle"<?php echo $subtitle_style ? ' style="' . esc_attr( $subtitle_style ) . '"' : ''; ?>><?php echo esc_html( $subtitle ); ?></p>
                    <?php endif; ?>
                </div>
                
                <div class="faq-list">
                    <?php foreach ( $items as $item ) : 
                        $question = isset( $item['question'] ) ? $item['question'] : '';
                        $answer = isset( $item['answer'] ) ? $item['answer'] : '';
                    ?>
                        <div class="faq-item">
                            <button type="button" class="faq-question" aria-expanded="false">
                                <span><?php echo esc_html( $question ); ?></span>
                                <span class="faq-icon">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                                </span>
                            </button>
                            <div class="faq-answer">
                                <div class="faq-answer-content">
                                    <?php echo wp_kses_post( $answer ); ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <?php
    }
}
