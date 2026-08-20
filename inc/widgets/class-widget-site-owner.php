<?php

namespace Developer_Starter\Widgets;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Widget_Site_Owner extends \WP_Widget {

    public function __construct() {
        parent::__construct(
            'developer_starter_site_owner',
            __( '站长信息', 'developer-starter' ),
            array( 'description' => __( '展示站长头像、简介、社交媒体和网站统计', 'developer-starter' ) )
        );
    }

    public function widget( $args, $instance ) {
        $defaults = array(
            'title' => __( '站长信息', 'developer-starter' ),
            'user_id' => 0,
            'show_avatar' => '1',
            'show_name' => '1',
            'show_bio' => '1',
            'show_social' => '1',
            'show_stats' => '1',
            'show_stats_posts' => '1',
            'show_stats_comments' => '1',
            'show_stats_categories' => '1',
            'show_stats_tags' => '1',
        );
        $instance = wp_parse_args( $instance, $defaults );
        $user_id = (int) $instance['user_id'];

        if ( ! $user_id ) {
            $admins = get_users( array( 'role' => 'administrator', 'number' => 1 ) );
            if ( ! empty( $admins ) ) {
                $user_id = (int) $admins[0]->ID;
            }
        }

        if ( ! $user_id ) {
            return;
        }

        $user = get_userdata( $user_id );
        if ( ! $user ) {
            return;
        }

        $comments_feature_enabled = function_exists( '\developer_starter_comments_feature_enabled' ) ? \developer_starter_comments_feature_enabled() : true;
        $avatar_html = get_avatar( $user_id, 96, '', '', array( 'class' => 'site-owner-avatar' ) );
        $bio = get_the_author_meta( 'description', $user_id );

        $social_html = '';
        if ( class_exists( '\Developer_Starter\Core\Post_Enhancer' ) ) {
            $social_html = \Developer_Starter\Core\Post_Enhancer::render_social_links( $user_id );
        }

        $stats = array();
        if ( ! empty( $instance['show_stats_posts'] ) ) {
            $post_count = wp_count_posts( 'post' );
            $stats[] = array(
                'label' => __( '文章', 'developer-starter' ),
                'value' => isset( $post_count->publish ) ? (int) $post_count->publish : 0,
            );
        }
        if ( ! empty( $instance['show_stats_comments'] ) && $comments_feature_enabled ) {
            $comment_count = wp_count_comments();
            $stats[] = array(
                'label' => __( '评论', 'developer-starter' ),
                'value' => isset( $comment_count->approved ) ? (int) $comment_count->approved : 0,
            );
        }
        if ( ! empty( $instance['show_stats_categories'] ) ) {
            $category_count = wp_count_terms( array( 'taxonomy' => 'category' ) );
            $stats[] = array(
                'label' => __( '分类', 'developer-starter' ),
                'value' => is_wp_error( $category_count ) ? 0 : (int) $category_count,
            );
        }
        if ( ! empty( $instance['show_stats_tags'] ) ) {
            $tag_count = wp_count_terms( array( 'taxonomy' => 'post_tag' ) );
            $stats[] = array(
                'label' => __( '标签', 'developer-starter' ),
                'value' => is_wp_error( $tag_count ) ? 0 : (int) $tag_count,
            );
        }

        echo $args['before_widget'];

        if ( ! empty( $instance['title'] ) ) {
            echo $args['before_title'] . esc_html( $instance['title'] ) . $args['after_title'];
        }
        ?>
        <div class="site-owner-widget">
            <?php if ( ! empty( $instance['show_avatar'] ) || ! empty( $instance['show_name'] ) || ( ! empty( $instance['show_bio'] ) && $bio ) ) : ?>
                <div class="site-owner-header">
                    <?php if ( ! empty( $instance['show_avatar'] ) ) : ?>
                        <div class="site-owner-avatar-wrap">
                            <?php echo $avatar_html; ?>
                        </div>
                    <?php endif; ?>
                    <div class="site-owner-meta">
                        <?php if ( ! empty( $instance['show_name'] ) ) : ?>
                            <div class="site-owner-name"><?php echo esc_html( $user->display_name ); ?></div>
                        <?php endif; ?>
                        <?php if ( ! empty( $instance['show_bio'] ) && $bio ) : ?>
                            <div class="site-owner-bio"><?php echo esc_html( $bio ); ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ( ! empty( $instance['show_social'] ) && $social_html ) : ?>
                <div class="site-owner-social">
                    <?php echo $social_html; ?>
                </div>
            <?php endif; ?>

            <?php if ( ! empty( $instance['show_stats'] ) && ! empty( $stats ) ) : ?>
                <div class="site-owner-stats">
                    <?php foreach ( $stats as $stat ) : ?>
                        <div class="site-owner-stat">
                            <div class="site-owner-stat-number"><?php echo number_format_i18n( $stat['value'] ); ?></div>
                            <div class="site-owner-stat-label"><?php echo esc_html( $stat['label'] ); ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        <?php
        echo $args['after_widget'];
    }

    public function form( $instance ) {
        $defaults = array(
            'title' => __( '站长信息', 'developer-starter' ),
            'user_id' => 0,
            'show_avatar' => '1',
            'show_name' => '1',
            'show_bio' => '1',
            'show_social' => '1',
            'show_stats' => '1',
            'show_stats_posts' => '1',
            'show_stats_comments' => '1',
            'show_stats_categories' => '1',
            'show_stats_tags' => '1',
        );
        $instance = wp_parse_args( $instance, $defaults );
        $users = get_users( array( 'role__in' => array( 'administrator', 'editor' ) ) );
        ?>
        <p>
            <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php esc_html_e( '标题:', 'developer-starter' ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $instance['title'] ); ?>">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id( 'user_id' ); ?>"><?php esc_html_e( '站长用户:', 'developer-starter' ); ?></label>
            <select class="widefat" id="<?php echo $this->get_field_id( 'user_id' ); ?>" name="<?php echo $this->get_field_name( 'user_id' ); ?>">
                <option value="0"><?php esc_html_e( '自动选择管理员', 'developer-starter' ); ?></option>
                <?php foreach ( $users as $user ) : ?>
                    <option value="<?php echo esc_attr( $user->ID ); ?>" <?php selected( $instance['user_id'], $user->ID ); ?>>
                        <?php echo esc_html( $user->display_name ); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>
        <p>
            <input type="checkbox" id="<?php echo $this->get_field_id( 'show_avatar' ); ?>" name="<?php echo $this->get_field_name( 'show_avatar' ); ?>" value="1" <?php checked( $instance['show_avatar'], '1' ); ?>>
            <label for="<?php echo $this->get_field_id( 'show_avatar' ); ?>"><?php esc_html_e( '显示头像', 'developer-starter' ); ?></label>
        </p>
        <p>
            <input type="checkbox" id="<?php echo $this->get_field_id( 'show_name' ); ?>" name="<?php echo $this->get_field_name( 'show_name' ); ?>" value="1" <?php checked( $instance['show_name'], '1' ); ?>>
            <label for="<?php echo $this->get_field_id( 'show_name' ); ?>"><?php esc_html_e( '显示管理员名称', 'developer-starter' ); ?></label>
        </p>
        <p>
            <input type="checkbox" id="<?php echo $this->get_field_id( 'show_bio' ); ?>" name="<?php echo $this->get_field_name( 'show_bio' ); ?>" value="1" <?php checked( $instance['show_bio'], '1' ); ?>>
            <label for="<?php echo $this->get_field_id( 'show_bio' ); ?>"><?php esc_html_e( '显示简介', 'developer-starter' ); ?></label>
        </p>
        <p>
            <input type="checkbox" id="<?php echo $this->get_field_id( 'show_social' ); ?>" name="<?php echo $this->get_field_name( 'show_social' ); ?>" value="1" <?php checked( $instance['show_social'], '1' ); ?>>
            <label for="<?php echo $this->get_field_id( 'show_social' ); ?>"><?php esc_html_e( '显示社交媒体', 'developer-starter' ); ?></label>
        </p>
        <p>
            <input type="checkbox" id="<?php echo $this->get_field_id( 'show_stats' ); ?>" name="<?php echo $this->get_field_name( 'show_stats' ); ?>" value="1" <?php checked( $instance['show_stats'], '1' ); ?>>
            <label for="<?php echo $this->get_field_id( 'show_stats' ); ?>"><?php esc_html_e( '显示网站统计', 'developer-starter' ); ?></label>
        </p>
        <p>
            <input type="checkbox" id="<?php echo $this->get_field_id( 'show_stats_posts' ); ?>" name="<?php echo $this->get_field_name( 'show_stats_posts' ); ?>" value="1" <?php checked( $instance['show_stats_posts'], '1' ); ?>>
            <label for="<?php echo $this->get_field_id( 'show_stats_posts' ); ?>"><?php esc_html_e( '统计文章', 'developer-starter' ); ?></label>
        </p>
        <p>
            <input type="checkbox" id="<?php echo $this->get_field_id( 'show_stats_comments' ); ?>" name="<?php echo $this->get_field_name( 'show_stats_comments' ); ?>" value="1" <?php checked( $instance['show_stats_comments'], '1' ); ?>>
            <label for="<?php echo $this->get_field_id( 'show_stats_comments' ); ?>"><?php esc_html_e( '统计评论', 'developer-starter' ); ?></label>
        </p>
        <p>
            <input type="checkbox" id="<?php echo $this->get_field_id( 'show_stats_categories' ); ?>" name="<?php echo $this->get_field_name( 'show_stats_categories' ); ?>" value="1" <?php checked( $instance['show_stats_categories'], '1' ); ?>>
            <label for="<?php echo $this->get_field_id( 'show_stats_categories' ); ?>"><?php esc_html_e( '统计分类', 'developer-starter' ); ?></label>
        </p>
        <p>
            <input type="checkbox" id="<?php echo $this->get_field_id( 'show_stats_tags' ); ?>" name="<?php echo $this->get_field_name( 'show_stats_tags' ); ?>" value="1" <?php checked( $instance['show_stats_tags'], '1' ); ?>>
            <label for="<?php echo $this->get_field_id( 'show_stats_tags' ); ?>"><?php esc_html_e( '统计标签', 'developer-starter' ); ?></label>
        </p>
        <?php
    }

    public function update( $new_instance, $old_instance ) {
        $instance = array();
        $instance['title'] = sanitize_text_field( $new_instance['title'] );
        $instance['user_id'] = isset( $new_instance['user_id'] ) ? (int) $new_instance['user_id'] : 0;
        $instance['show_avatar'] = ! empty( $new_instance['show_avatar'] ) ? '1' : '';
        $instance['show_name'] = ! empty( $new_instance['show_name'] ) ? '1' : '';
        $instance['show_bio'] = ! empty( $new_instance['show_bio'] ) ? '1' : '';
        $instance['show_social'] = ! empty( $new_instance['show_social'] ) ? '1' : '';
        $instance['show_stats'] = ! empty( $new_instance['show_stats'] ) ? '1' : '';
        $instance['show_stats_posts'] = ! empty( $new_instance['show_stats_posts'] ) ? '1' : '';
        $instance['show_stats_comments'] = ! empty( $new_instance['show_stats_comments'] ) ? '1' : '';
        $instance['show_stats_categories'] = ! empty( $new_instance['show_stats_categories'] ) ? '1' : '';
        $instance['show_stats_tags'] = ! empty( $new_instance['show_stats_tags'] ) ? '1' : '';
        return $instance;
    }
}

add_action( 'widgets_init', function() {
    register_widget( '\Developer_Starter\Widgets\Widget_Site_Owner' );
});
