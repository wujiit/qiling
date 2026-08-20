<?php
/**
 * Social Links Widget
 *
 * @package Developer_Starter
 * @since 1.0.0
 */

namespace Developer_Starter\Widgets;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Widget_Social extends \WP_Widget {

    public function __construct() {
        parent::__construct(
            'developer_starter_social',
            __( '社交链接', 'developer-starter' ),
            array( 'description' => __( '显示社交媒体链接', 'developer-starter' ) )
        );
    }

    public function widget( $args, $instance ) {
        $options = \developer_starter_get_options_cache();
        
        echo $args['before_widget'];
        
        if ( ! empty( $instance['title'] ) ) {
            echo $args['before_title'] . esc_html( $instance['title'] ) . $args['after_title'];
        }
        ?>
        <div class="social-widget-content">
            <?php if ( ! empty( $options['weibo_url'] ) ) : ?>
                <a href="<?php echo esc_url( $options['weibo_url'] ); ?>" target="_blank" rel="noopener" class="social-link social-weibo">
                    <?php esc_html_e( '微博', 'developer-starter' ); ?>
                </a>
            <?php endif; ?>

            <?php if ( ! empty( $options['wechat_qrcode'] ) ) : ?>
                <div class="social-wechat">
                    <span><?php esc_html_e( '微信公众号', 'developer-starter' ); ?></span>
                    <img src="<?php echo esc_url( $options['wechat_qrcode'] ); ?>" alt="<?php esc_attr_e( '微信二维码', 'developer-starter' ); ?>" />
                </div>
            <?php endif; ?>
        </div>
        <?php
        echo $args['after_widget'];
    }

    public function form( $instance ) {
        $title = ! empty( $instance['title'] ) ? $instance['title'] : __( '关注我们', 'developer-starter' );
        ?>
        <p>
            <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php esc_html_e( '标题:', 'developer-starter' ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
        </p>
        <?php
    }

    public function update( $new_instance, $old_instance ) {
        $instance = array();
        $instance['title'] = sanitize_text_field( $new_instance['title'] );
        return $instance;
    }
}

add_action( 'widgets_init', function() {
    register_widget( '\Developer_Starter\Widgets\Widget_Social' );
});
