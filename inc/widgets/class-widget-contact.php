<?php
/**
 * Contact Widget
 *
 * @package Developer_Starter
 * @since 1.0.0
 */

namespace Developer_Starter\Widgets;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Widget_Contact extends \WP_Widget {

    public function __construct() {
        parent::__construct(
            'developer_starter_contact',
            __( '联系信息', 'developer-starter' ),
            array( 'description' => __( '显示企业联系信息', 'developer-starter' ) )
        );
    }

    public function widget( $args, $instance ) {
        $options = \developer_starter_get_options_cache();
        $qq      = ! empty( $options['company_qq'] ) ? (string) $options['company_qq'] : '';
        $qq_link = function_exists( 'developer_starter_get_qq_contact_link' ) ? \developer_starter_get_qq_contact_link( $qq ) : '';
        $wechat_qrcode = ! empty( $options['company_wechat_qrcode'] ) ? (string) $options['company_wechat_qrcode'] : '';
        
        echo $args['before_widget'];
        
        if ( ! empty( $instance['title'] ) ) {
            echo $args['before_title'] . esc_html( $instance['title'] ) . $args['after_title'];
        }
        ?>
        <div class="contact-widget-content">
            <?php if ( ! empty( $options['company_phone'] ) ) : ?>
                <div class="contact-widget-item">
                    <strong><?php esc_html_e( '电话:', 'developer-starter' ); ?></strong>
                    <a href="tel:<?php echo esc_attr( $options['company_phone'] ); ?>">
                        <?php echo esc_html( $options['company_phone'] ); ?>
                    </a>
                </div>
            <?php endif; ?>

            <?php if ( $qq ) : ?>
                <div class="contact-widget-item">
                    <strong><?php esc_html_e( 'QQ:', 'developer-starter' ); ?></strong>
                    <?php if ( $qq_link ) : ?>
                        <a href="<?php echo esc_url( $qq_link ); ?>" target="_blank" rel="noopener noreferrer">
                            <?php echo esc_html( $qq ); ?>
                        </a>
                    <?php else : ?>
                        <?php echo esc_html( $qq ); ?>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if ( $wechat_qrcode ) : ?>
                <div class="contact-widget-item">
                    <strong><?php esc_html_e( '微信:', 'developer-starter' ); ?></strong>
                    <div style="margin-top: 8px; display: inline-flex; flex-direction: column; gap: 6px; align-items: flex-start;">
                        <img src="<?php echo esc_url( $wechat_qrcode ); ?>" alt="<?php esc_attr_e( '微信二维码', 'developer-starter' ); ?>" loading="lazy" decoding="async" style="width: 96px; height: 96px; object-fit: cover; border-radius: 10px;" />
                        <span style="font-size: 0.85rem; color: inherit;"><?php esc_html_e( '扫码添加微信', 'developer-starter' ); ?></span>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ( ! empty( $options['company_email'] ) ) : ?>
                <div class="contact-widget-item">
                    <strong><?php esc_html_e( '邮箱:', 'developer-starter' ); ?></strong>
                    <a href="mailto:<?php echo esc_attr( $options['company_email'] ); ?>">
                        <?php echo esc_html( $options['company_email'] ); ?>
                    </a>
                </div>
            <?php endif; ?>

            <?php if ( ! empty( $options['company_address'] ) ) : ?>
                <div class="contact-widget-item">
                    <strong><?php esc_html_e( '地址:', 'developer-starter' ); ?></strong>
                    <?php echo esc_html( $options['company_address'] ); ?>
                </div>
            <?php endif; ?>
        </div>
        <?php
        echo $args['after_widget'];
    }

    public function form( $instance ) {
        $title = ! empty( $instance['title'] ) ? $instance['title'] : __( '联系我们', 'developer-starter' );
        ?>
        <p>
            <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php esc_html_e( '标题:', 'developer-starter' ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
        </p>
        <p><small><?php esc_html_e( '联系信息从主题设置中自动获取', 'developer-starter' ); ?></small></p>
        <?php
    }

    public function update( $new_instance, $old_instance ) {
        $instance = array();
        $instance['title'] = sanitize_text_field( $new_instance['title'] );
        return $instance;
    }
}

add_action( 'widgets_init', function() {
    register_widget( '\Developer_Starter\Widgets\Widget_Contact' );
});
