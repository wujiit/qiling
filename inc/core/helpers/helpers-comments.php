<?php
/**
 * Comment helpers split from functions.php.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 自定义评论回调函数
 * 提前定义以确保在 comments.php 加载前可用
 */
if ( ! function_exists( 'developer_starter_comment_callback' ) ) {
    $GLOBALS['developer_starter_floor_counter'] = 0;

    function developer_starter_comment_callback( $comment, $args, $depth ) {
        $GLOBALS['comment'] = $comment;

        $avatar_size = isset( $args['avatar_size'] ) ? absint( $args['avatar_size'] ) : 48;
        if ( $avatar_size <= 0 ) {
            $avatar_size = 48;
        }
        $avatar_size = min( 96, max( 24, $avatar_size ) );
        $is_top_level = ( 1 === (int) $depth && 0 === (int) $comment->comment_parent );
        $floor_number = 0;

        if ( $is_top_level ) {
            $GLOBALS['developer_starter_floor_counter']++;
            $floor_number = $GLOBALS['developer_starter_floor_counter'];
        }

        static $comment_speech_enabled = null;
        if ( null === $comment_speech_enabled ) {
            $comment_speech_enabled = class_exists( '\Developer_Starter\Core\Post_Enhancer' )
                ? \Developer_Starter\Core\Post_Enhancer::is_comment_speech_enabled()
                : ( is_singular( 'post' ) && '1' === (string) developer_starter_get_option( 'comment_speech_enable', '' ) );
        }
        ?>
        <li id="comment-<?php comment_ID(); ?>" <?php comment_class( 'comment-item' ); ?>>
            <article class="comment-body">
                <div class="comment-avatar">
                    <?php echo get_avatar( $comment, $avatar_size ); ?>
                </div>
                <div class="comment-content">
                    <div class="comment-meta">
                        <span class="comment-author"><?php echo esc_html( get_comment_author() ); ?></span>
                        <?php if ( $is_top_level ) : ?>
                            <span class="comment-floor">
                                <?php
                                /* translators: %d: Floor number */
                                printf( esc_html__( '%d楼', 'developer-starter' ), $floor_number );
                                ?>
                            </span>
                        <?php endif; ?>
                        <span class="comment-date"><?php echo esc_html( get_comment_date() ); ?></span>
                        <?php
                        $comment_ip_location_enable = developer_starter_get_option( 'comment_ip_location_enable', '' );
                        $comment_ip_location        = '';
                        if ( $comment_ip_location_enable && function_exists( 'developer_starter_get_comment_ip_location' ) ) {
                            $comment_ip_location = developer_starter_get_comment_ip_location( (int) $comment->user_id, $comment );
                        }
                        ?>
                        <?php if ( $comment_ip_location ) : ?>
                            <span class="comment-ip-location"><?php printf( esc_html__( '地区：%s', 'developer-starter' ), esc_html( $comment_ip_location ) ); ?></span>
                        <?php endif; ?>
                        <?php if ( $comment->comment_approved == '0' ) : ?>
                            <span class="comment-awaiting"><?php esc_html_e( '待审核', 'developer-starter' ); ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="comment-text">
                        <?php comment_text(); ?>
                    </div>
                    <div class="comment-actions">
                        <?php
                        comment_reply_link(
                            array_merge(
                                $args,
                                array(
                                    'depth'     => $depth,
                                    'max_depth' => $args['max_depth'],
                                )
                            )
                        );
                        ?>
                        <?php if ( $comment_speech_enabled ) : ?>
                            <button type="button" class="qiling-comment-speech-trigger" data-comment-id="<?php echo esc_attr( (int) $comment->comment_ID ); ?>" aria-label="<?php esc_attr_e( '朗读这条评论', 'developer-starter' ); ?>">
                                <svg viewBox="0 0 24 24" width="14" height="14" aria-hidden="true" focusable="false"><path d="M4 9v6h4l5 4V5L8 9H4z" fill="currentColor"></path><path d="M16 9.5c.8.7 1.2 1.5 1.2 2.5s-.4 1.8-1.2 2.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"></path><path d="M18.5 7c1.4 1.3 2.1 3 2.1 5s-.7 3.7-2.1 5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"></path></svg>
                                <span><?php esc_html_e( '朗读', 'developer-starter' ); ?></span>
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </article>
        <?php
    }
}

if ( ! function_exists( 'developer_starter_mask_username' ) ) {
    /**
     * 用户名脱敏处理。
     *
     * @param string $name 用户名。
     * @return string
     */
    function developer_starter_mask_username( $name ) {
        $name = trim( $name );
        if ( empty( $name ) ) {
            return $name;
        }

        $len = mb_strlen( $name, 'UTF-8' );
        if ( $len <= 1 ) {
            return $name;
        }

        $first = mb_substr( $name, 0, 1, 'UTF-8' );
        $stars = str_repeat( '*', min( $len - 1, 3 ) );

        return $first . $stars;
    }
}

if ( ! function_exists( 'developer_starter_filter_comment_author' ) ) {
    /**
     * 过滤评论作者名（全局脱敏）。
     *
     * @param string $author 作者名。
     * @param int    $comment_id 评论 ID。
     * @param mixed  $comment 评论对象。
     * @return string
     */
    function developer_starter_filter_comment_author( $author, $comment_id, $comment ) {
        unset( $comment_id, $comment );

        $privacy_enabled = developer_starter_get_option( 'comment_username_privacy', '' );
        if ( $privacy_enabled && ! empty( $author ) ) {
            return developer_starter_mask_username( $author );
        }
        return $author;
    }
}
add_filter( 'get_comment_author', 'developer_starter_filter_comment_author', 10, 3 );

if ( ! function_exists( 'developer_starter_filter_reply_link' ) ) {
    /**
     * 过滤评论回复链接中的作者名。
     *
     * @param string $link 回复链接 HTML。
     * @param array  $args 参数。
     * @param mixed  $comment 评论对象。
     * @param mixed  $post 文章对象。
     * @return string
     */
    function developer_starter_filter_reply_link( $link, $args, $comment, $post ) {
        unset( $args, $post );

        $privacy_enabled = developer_starter_get_option( 'comment_username_privacy', '' );
        if ( $privacy_enabled ) {
            $original_author = get_comment_author( $comment );
            unset( $original_author );
        }
        return $link;
    }
}
add_filter( 'comment_reply_link', 'developer_starter_filter_reply_link', 10, 4 );

if ( ! function_exists( 'developer_starter_filter_reply_title' ) ) {
    /**
     * 过滤评论回复标题中的作者名。
     *
     * @param array $defaults 表单默认值。
     * @return array
     */
    function developer_starter_filter_reply_title( $defaults ) {
        $privacy_enabled = developer_starter_get_option( 'comment_username_privacy', '' );
        if ( $privacy_enabled ) {
            $defaults['title_reply_to'] = __( '回复 %s', 'developer-starter' );
        }
        return $defaults;
    }
}
add_filter( 'comment_form_defaults', 'developer_starter_filter_reply_title', 10, 1 );
