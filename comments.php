<?php
/**
 * 评论模板
 * 
 * 处理：
 * - WordPress后台讨论设置（需登录才能评论等）
 * - 主题设置（完全禁用评论、蜜罐陷阱、用户名隐私）
 * - 密码保护文章
 * - 评论已关闭状态
 *
 * @package Developer_Starter
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// ========================================
// 前置检查
// ========================================

// 密码保护的文章不显示评论
if ( post_password_required() ) {
    return;
}

// 关闭评论时不向任何访客输出评论区，包括历史评论和登录提示。
$theme_disable_comments = developer_starter_get_option( 'disable_comments', '' );
if ( $theme_disable_comments || ! comments_open() ) {
    return;
}

// 获取WordPress讨论设置
$require_login = get_option( 'comment_registration' ); // 需要登录才能评论
$is_logged_in = is_user_logged_in();

// 主题蜜罐设置
$honeypot_enabled = developer_starter_get_option( 'comment_honeypot', '' );

$theme_options = function_exists( 'developer_starter_get_options_cache' ) ? developer_starter_get_options_cache() : array();
if ( ! is_array( $theme_options ) ) {
    $theme_options = array();
}
$comments_option_enabled = static function ( $key, $default = true ) use ( $theme_options ) {
    if ( array_key_exists( $key, $theme_options ) ) {
        return '1' === (string) $theme_options[ $key ];
    }
    return (bool) $default;
};
$comments_option_text = static function ( $key, $default ) use ( $theme_options ) {
    $value = isset( $theme_options[ $key ] ) ? trim( wp_strip_all_tags( (string) $theme_options[ $key ] ) ) : '';
    return '' !== $value ? $value : $default;
};
$comments_header_enabled = $comments_option_enabled( 'comments_header_enable', true );
$comments_count_enabled = $comments_option_enabled( 'comments_show_count', true );
$comments_empty_hint_enabled = $comments_option_enabled( 'comments_show_empty_hint', true );
$comments_logged_in_as_enabled = $comments_option_enabled( 'comments_show_logged_in_as', true );
$comments_section_title = $comments_option_text( 'comments_section_title', __( '读者评论', 'developer-starter' ) );
$comments_empty_hint_text = $comments_option_text( 'comments_empty_hint_text', __( '暂无评论，快来抢沙发吧！', 'developer-starter' ) );
$comments_closed_text = $comments_option_text( 'comments_closed_text', __( '评论已关闭', 'developer-starter' ) );
$comments_form_logged_in_title = $comments_option_text( 'comments_form_logged_in_title', __( '发表评论', 'developer-starter' ) );
$comments_form_guest_title = $comments_option_text( 'comments_form_guest_title', __( '参与讨论', 'developer-starter' ) );
$comments_textarea_placeholder = $comments_option_text( 'comments_textarea_placeholder', __( '写下你的评论...', 'developer-starter' ) );
$comments_submit_label = $comments_option_text( 'comments_submit_label', __( '发表评论', 'developer-starter' ) );
$comments_login_required_text = $comments_option_text( 'comments_login_required_text', __( '请先登录后发表评论', 'developer-starter' ) );
$comments_login_button_label = $comments_option_text( 'comments_login_button_label', __( '立即登录', 'developer-starter' ) );
$comments_avatar_size = isset( $theme_options['comments_avatar_size'] ) ? absint( $theme_options['comments_avatar_size'] ) : 48;
if ( $comments_avatar_size <= 0 ) {
    $comments_avatar_size = 48;
}
$comments_avatar_size = min( 96, max( 24, $comments_avatar_size ) );

// ========================================
// 评论区渲染
// ========================================
?>

<section id="comments" class="comments-section">
    
    <?php if ( have_comments() ) : ?>
        <?php if ( $comments_header_enabled ) : ?>
        <div class="comments-header">
            <div class="comments-title-wrap">
                <span class="comments-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                    </svg>
                </span>
                <div>
                    <h2 class="comments-title"><?php echo esc_html( $comments_section_title ); ?></h2>
                    <?php if ( $comments_count_enabled ) : ?>
                        <span class="comments-count"><?php printf( _n( '%s 条评论', '%s 条评论', get_comments_number(), 'developer-starter' ), number_format_i18n( get_comments_number() ) ); ?></span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="comments-list-wrap">
            <ol class="comment-list">
                <?php
                $GLOBALS['developer_starter_floor_counter'] = 0;

                wp_list_comments( array(
                    'style'       => 'ol',
                    'short_ping'  => true,
                    'avatar_size' => $comments_avatar_size,
                    'callback'    => 'developer_starter_comment_callback',
                ) );
                ?>
            </ol>
        </div>

        <?php if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) : ?>
            <nav class="comment-pagination">
                <div class="nav-prev"><?php previous_comments_link( __( '&larr; 较早的评论', 'developer-starter' ) ); ?></div>
                <div class="nav-next"><?php next_comments_link( __( '更新的评论 &rarr;', 'developer-starter' ) ); ?></div>
            </nav>
        <?php endif; ?>

    <?php else : ?>
        <?php if ( comments_open() && $comments_empty_hint_enabled ) : ?>
            <p class="no-comments-hint"><?php echo esc_html( $comments_empty_hint_text ); ?></p>
        <?php endif; ?>
    <?php endif; ?>

    <?php // 评论已关闭提示 ?>
    <?php if ( ! comments_open() && have_comments() ) : ?>
        <p class="comments-closed-notice"><?php echo esc_html( $comments_closed_text ); ?></p>
    <?php endif; ?>

    <?php // 评论表单区域 ?>
    <?php if ( comments_open() ) : ?>
        <?php
        $commenter              = wp_get_current_commenter();
        $required_name_email    = (bool) get_option( 'require_name_email' );
        $required_attributes    = $required_name_email ? ' required="required" aria-required="true"' : '';
        $author_placeholder     = $required_name_email ? __( '昵称 *', 'developer-starter' ) : __( '昵称', 'developer-starter' );
        $email_placeholder      = $required_name_email ? __( '邮箱 *', 'developer-starter' ) : __( '邮箱', 'developer-starter' );
        $custom_login_page      = developer_starter_get_option( 'login_page_id', '' );
        $login_url              = $custom_login_page ? get_permalink( $custom_login_page ) : '';

        if ( ! $login_url ) {
            $login_url = wp_login_url( get_permalink() );
        }

        $comment_fields = array(
            'author' => '<div class="form-row"><div class="form-field comment-form-author"><label class="screen-reader-text" for="author">' . esc_html__( '昵称', 'developer-starter' ) . '</label><input id="author" name="author" type="text" value="' . esc_attr( $commenter['comment_author'] ) . '" size="30" maxlength="245" autocomplete="name" placeholder="' . esc_attr( $author_placeholder ) . '"' . $required_attributes . ' /></div>',
            'email'  => '<div class="form-field comment-form-email"><label class="screen-reader-text" for="email">' . esc_html__( '邮箱', 'developer-starter' ) . '</label><input id="email" name="email" type="email" value="' . esc_attr( $commenter['comment_author_email'] ) . '" size="30" maxlength="100" autocomplete="email" placeholder="' . esc_attr( $email_placeholder ) . '"' . $required_attributes . ' /></div></div>',
        );

        if ( get_option( 'show_comments_cookies_opt_in' ) ) {
            $comment_fields['cookies'] = '<p class="comment-form-cookies-consent"><input id="wp-comment-cookies-consent" name="wp-comment-cookies-consent" type="checkbox" value="yes"' . ( empty( $commenter['comment_author_email'] ) ? '' : ' checked="checked"' ) . ' /><label for="wp-comment-cookies-consent">' . esc_html__( '在此浏览器中保存我的昵称和邮箱，方便下次评论。', 'developer-starter' ) . '</label></p>';
        }

        $honeypot_field = '';
        if ( $honeypot_enabled ) {
            $honeypot_field = '<div class="comment-honeypot" aria-hidden="true"><label for="website_url_hp">' . esc_html__( '网站地址', 'developer-starter' ) . '</label><input type="text" name="website_url_hp" id="website_url_hp" value="" autocomplete="off" tabindex="-1" /></div>';
        }

        $logged_in_as = '';
        if ( $is_logged_in && $comments_logged_in_as_enabled ) {
            $current_user = wp_get_current_user();
            $logged_in_as = '<div class="logged-user-info">' . get_avatar( get_current_user_id(), 36 ) . '<span class="user-name">' . esc_html( $current_user->display_name ) . '</span><a href="' . esc_url( wp_logout_url( get_permalink() ) ) . '" class="logout-link">' . esc_html__( '登出', 'developer-starter' ) . '</a></div>';
        }

        $must_log_in = '<div class="comment-login-required"><span class="login-required-icon" aria-hidden="true"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="11" width="18" height="11" rx="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg></span><p class="login-required-text">' . esc_html( $comments_login_required_text ) . '</p><a href="' . esc_url( $login_url ) . '" class="btn-login js-comment-login" data-login-url="' . esc_url( $login_url ) . '">' . esc_html( $comments_login_button_label ) . '</a></div>';

        comment_form(
            array(
                'class_container'     => 'comment-form-section comment-respond',
                'class_form'          => 'comment-form qiling-comment-form',
                'class_submit'        => 'btn-submit',
                'comment_field'       => '<div class="form-field comment-form-comment"><label class="screen-reader-text" for="comment">' . esc_html__( '评论内容', 'developer-starter' ) . '</label><textarea name="comment" id="comment" rows="3" maxlength="65525" placeholder="' . esc_attr( $comments_textarea_placeholder ) . '" required="required"></textarea></div>',
                'comment_notes_after' => $honeypot_field,
                'comment_notes_before' => '',
                'fields'              => $comment_fields,
                'format'              => 'html5',
                'id_form'             => 'commentform',
                'label_submit'        => $comments_submit_label,
                'logged_in_as'        => $logged_in_as,
                'must_log_in'         => $must_log_in,
                'submit_button'       => '<button name="%1$s" type="submit" id="%2$s" class="%3$s"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg><span>%4$s</span></button>',
                'submit_field'        => '<div class="form-actions">%1$s %2$s</div>',
                'title_reply'         => $is_logged_in ? $comments_form_logged_in_title : $comments_form_guest_title,
                'title_reply_before'  => '<div class="comment-form-header"><h3 class="form-title" id="reply-title">',
                'title_reply_after'   => '</h3></div>',
                'cancel_reply_before' => '<small class="cancel-reply">',
                'cancel_reply_after'  => '</small>',
                'cancel_reply_link'   => __( '取消回复', 'developer-starter' ),
            )
        );
        ?>
    <?php endif; ?>
    
</section>
