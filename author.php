<?php
/**
 * 作者归档页面模板 - 个人主页
 *
 * @package Developer_Starter
 * @since 2.10.2
 */

get_header();

// 获取当前作者信息
$author = get_queried_object();
if ( ! $author || ! isset( $author->ID ) ) {
    get_template_part( '404' );
    return;
}

$author_id = absint( $author->ID );
$display_name = $author->display_name;
$author_bio = get_the_author_meta( 'description', $author_id );
$avatar_url = get_avatar_url( $author_id, array( 'size' => 150 ) );
$author_url = get_author_posts_url( $author_id );

$author_theme_options = function_exists( 'developer_starter_get_options_cache' ) ? developer_starter_get_options_cache() : array();
if ( ! is_array( $author_theme_options ) ) {
    $author_theme_options = array();
}
$author_option_enabled = static function ( $key, $default = true ) use ( $author_theme_options ) {
    if ( array_key_exists( $key, $author_theme_options ) ) {
        return '1' === (string) $author_theme_options[ $key ];
    }
    return (bool) $default;
};
$author_option_text = static function ( $key, $default ) use ( $author_theme_options ) {
    $value = isset( $author_theme_options[ $key ] ) ? trim( wp_strip_all_tags( (string) $author_theme_options[ $key ] ) ) : '';
    return '' !== $value ? $value : $default;
};
$author_header_enabled = $author_option_enabled( 'author_page_header_enable', true );
$author_avatar_enabled = $author_option_enabled( 'author_page_show_avatar', true );
$author_bio_enabled = $author_option_enabled( 'author_page_show_bio', true );
$author_actions_enabled = $author_option_enabled( 'author_page_show_actions', true );
$author_social_enabled = $author_option_enabled( 'author_page_show_social', true );
$author_stats_enabled = $author_option_enabled( 'author_page_show_stats', true );
$author_posts_summary_enabled = $author_option_enabled( 'author_page_posts_summary_enable', true );
$author_empty_bio_text = $author_option_text( 'author_page_empty_bio_text', __( '这个人很懒，什么都没有留下...', 'developer-starter' ) );
$author_posts_title = $author_option_text( 'author_page_posts_title', __( 'TA 的文章', 'developer-starter' ) );
$author_empty_posts_text = $author_option_text( 'author_page_empty_posts_text', __( '该作者暂未发布任何文章', 'developer-starter' ) );
$author_posts_columns = isset( $author_theme_options['author_page_posts_columns'] ) ? absint( $author_theme_options['author_page_posts_columns'] ) : 3;
if ( ! in_array( $author_posts_columns, array( 2, 3, 4 ), true ) ) {
    $author_posts_columns = 3;
}
$author_stat_items_raw = array( 'posts', 'views', 'comments', 'joined' );
if ( array_key_exists( 'author_page_stat_items', $author_theme_options ) ) {
    $author_stat_items_raw = is_array( $author_theme_options['author_page_stat_items'] )
        ? $author_theme_options['author_page_stat_items']
        : array_filter( preg_split( '/[\s,]+/', (string) $author_theme_options['author_page_stat_items'] ) );
}
$author_stat_items_enabled = array();
foreach ( $author_stat_items_raw as $author_stat_item ) {
    $author_stat_item = sanitize_key( (string) $author_stat_item );
    if ( in_array( $author_stat_item, array( 'posts', 'views', 'comments', 'joined' ), true ) ) {
        $author_stat_items_enabled[ $author_stat_item ] = true;
    }
}
$author_grid_classes = sprintf( 'news-grid grid-cols-%d qiling-native-blog-grid qiling-native-blog-grid-author', $author_posts_columns );
$author_post_views_enabled = false;
if ( developer_starter_get_option( 'post_views_enable', '' ) ) {
    $author_post_views_enabled = true;
}

// 获取作者统计数据
$post_count = count_user_posts( $author_id, 'post' );
$total_views = function_exists( 'developer_starter_get_author_total_views' ) ? developer_starter_get_author_total_views( $author_id ) : 0;
$comment_count = function_exists( 'developer_starter_get_author_comment_count' ) ? developer_starter_get_author_comment_count( $author_id ) : 0;
$comments_feature_enabled = function_exists( 'developer_starter_comments_feature_enabled' ) ? developer_starter_comments_feature_enabled() : true;
$registered_date = ! empty( $author->user_registered ) ? date_i18n( get_option( 'date_format' ), strtotime( $author->user_registered ) ) : '';
$author_stat_items_visible = array();
if ( isset( $author_stat_items_enabled['posts'] ) ) {
    $author_stat_items_visible['posts'] = true;
}
if ( isset( $author_stat_items_enabled['views'] ) ) {
    $author_stat_items_visible['views'] = true;
}
if ( isset( $author_stat_items_enabled['comments'] ) && $comments_feature_enabled ) {
    $author_stat_items_visible['comments'] = true;
}
if ( isset( $author_stat_items_enabled['joined'] ) && $registered_date ) {
    $author_stat_items_visible['joined'] = true;
}
$format_number = function ( $number ) {
    return function_exists( 'developer_starter_format_number' ) ? developer_starter_format_number( $number ) : number_format_i18n( (int) $number );
};
$author_loop_settings = class_exists( '\Developer_Starter\Core\Blog_Visual_Manager' )
    ? \Developer_Starter\Core\Blog_Visual_Manager::get_native_loop_settings(
        array(
            'show_author'  => false,
            'grid_columns' => $author_posts_columns,
            'grid_classes' => $author_grid_classes,
            'card_classes' => 'news-card qiling-native-blog-card qiling-native-blog-card-author',
        )
    )
    : array(
        'grid_columns'      => $author_posts_columns,
        'grid_classes'      => $author_grid_classes,
        'card_classes'      => 'news-card qiling-native-blog-card qiling-native-blog-card-author',
        'show_thumb'        => true,
        'show_excerpt'      => true,
        'show_date'         => true,
        'show_author'       => false,
        'show_category'     => true,
        'show_reading_time' => false,
        'show_views'        => $author_post_views_enabled,
        'excerpt_length'    => 25,
        'thumb_height'      => 220,
        'thumb_fit'         => function_exists( 'developer_starter_get_thumbnail_display_mode' ) ? developer_starter_get_thumbnail_display_mode() : 'cover',
    );

// 检测 qibbs-community 插件是否激活
$qibbs_active = defined( 'QIBBS_VERSION' );
$is_following = false;
$is_self = false;

if ( $qibbs_active && is_user_logged_in() ) {
    $current_user_id = get_current_user_id();
    $is_self = ( $current_user_id === $author_id );
    
    // 检查是否已关注
    if ( class_exists( 'Qibbs_Follow_Service' ) ) {
        $follow_service = new Qibbs_Follow_Service();
        $is_following = $follow_service->is_following( $current_user_id, $author_id );
    }
}
?>

<div class="author-profile-page">
    <!-- 作者信息头部 -->
    <?php if ( $author_header_enabled ) : ?>
    <header class="author-profile-header">
        <div class="container">
            <div class="author-profile-main">
                <!-- 头像 -->
                <?php if ( $author_avatar_enabled ) : ?>
                <div class="author-profile-avatar">
                    <img src="<?php echo esc_url( $avatar_url ); ?>" alt="<?php echo esc_attr( $display_name ); ?>" loading="eager" decoding="async" fetchpriority="high" />
                </div>
                <?php endif; ?>
                
                <!-- 用户信息 -->
                <div class="author-profile-info">
                    <h1 class="author-profile-name"><?php echo esc_html( $display_name ); ?></h1>
                    <?php if ( $author_bio_enabled ) : ?>
                        <?php if ( $author_bio ) : ?>
                            <p class="author-profile-bio"><?php echo esc_html( $author_bio ); ?></p>
                        <?php else : ?>
                            <p class="author-profile-bio author-profile-bio-empty"><?php echo esc_html( $author_empty_bio_text ); ?></p>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
                
                <!-- 操作按钮（仅在 qibbs-community 插件激活时显示） -->
                <?php if ( $author_actions_enabled && $qibbs_active && ! $is_self && is_user_logged_in() ) : ?>
                    <div class="author-profile-actions">
                        <button class="author-follow-btn <?php echo $is_following ? 'is-following' : ''; ?>" 
                                data-user-id="<?php echo esc_attr( $author_id ); ?>"
                                data-nonce="<?php echo esc_attr( wp_create_nonce( 'qibbs_follow_nonce' ) ); ?>">
                            <?php if ( $is_following ) : ?>
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                <span><?php esc_html_e( '已关注', 'developer-starter' ); ?></span>
                            <?php else : ?>
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                                <span><?php esc_html_e( '关注', 'developer-starter' ); ?></span>
                            <?php endif; ?>
                        </button>
                        <a href="<?php echo esc_url( home_url( '/messages/?user=' . $author_id ) ); ?>" class="author-message-btn">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                            <span><?php esc_html_e( '私信', 'developer-starter' ); ?></span>
                        </a>
                    </div>
                <?php elseif ( $author_actions_enabled && $qibbs_active && ! $is_self && ! is_user_logged_in() ) : ?>
                    <div class="author-profile-actions">
                        <button class="author-follow-btn" onclick="if(typeof dsOpenLoginModal==='function'){dsOpenLoginModal();}else{window.location.href='<?php echo esc_url( wp_login_url( get_author_posts_url( $author_id ) ) ); ?>';}">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                            <span><?php esc_html_e( '关注', 'developer-starter' ); ?></span>
                        </button>
                        <button class="author-message-btn" onclick="if(typeof dsOpenLoginModal==='function'){dsOpenLoginModal();}else{window.location.href='<?php echo esc_url( wp_login_url( get_author_posts_url( $author_id ) ) ); ?>';}">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                            <span><?php esc_html_e( '私信', 'developer-starter' ); ?></span>
                        </button>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- 社交链接 -->
            <?php 
            $social_links = '';
            if ( $author_social_enabled && class_exists( '\Developer_Starter\Core\Post_Enhancer' ) ) {
                $social_links = \Developer_Starter\Core\Post_Enhancer::render_social_links( $author_id );
            }
            if ( $author_social_enabled && $social_links ) : 
            ?>
                <div class="author-profile-social">
                    <?php echo $social_links; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                </div>
            <?php endif; ?>
            
            <!-- 统计信息 -->
            <?php if ( $author_stats_enabled && ! empty( $author_stat_items_visible ) ) : ?>
            <div class="author-profile-stats">
                <?php if ( isset( $author_stat_items_visible['posts'] ) ) : ?>
                <div class="author-stat-item">
                    <span class="author-stat-value"><?php echo esc_html( $post_count ); ?></span>
                    <span class="author-stat-label"><?php esc_html_e( '文章', 'developer-starter' ); ?></span>
                </div>
                <?php endif; ?>
                <?php if ( isset( $author_stat_items_visible['views'] ) ) : ?>
                <div class="author-stat-item">
                    <span class="author-stat-value"><?php echo esc_html( $format_number( $total_views ) ); ?></span>
                    <span class="author-stat-label"><?php esc_html_e( '浏览', 'developer-starter' ); ?></span>
                </div>
                <?php endif; ?>
                <?php if ( isset( $author_stat_items_visible['comments'] ) ) : ?>
                <div class="author-stat-item">
                    <span class="author-stat-value"><?php echo esc_html( $comment_count ); ?></span>
                    <span class="author-stat-label"><?php esc_html_e( '评论', 'developer-starter' ); ?></span>
                </div>
                <?php endif; ?>
                <?php if ( isset( $author_stat_items_visible['joined'] ) ) : ?>
                <div class="author-stat-item">
                    <span class="author-stat-value"><?php echo esc_html( $registered_date ); ?></span>
                    <span class="author-stat-label"><?php esc_html_e( '加入', 'developer-starter' ); ?></span>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </header>
    <?php endif; ?>
    
    <!-- 文章列表 -->
    <section class="author-posts-section section-padding">
        <div class="container">
            <div class="author-posts-heading">
                <h2 class="author-posts-title">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                    <?php echo esc_html( $author_posts_title ); ?>
                </h2>
                <?php if ( $author_posts_summary_enabled ) : ?>
                <p class="author-posts-summary">
                    <?php
                    printf(
                        /* translators: %s: post count */
                        esc_html__( '共 %s 篇公开文章', 'developer-starter' ),
                        number_format_i18n( (int) $post_count )
                    );
                    ?>
                </p>
                <?php endif; ?>
            </div>
            
            <?php if ( have_posts() ) : ?>
                <?php get_template_part( 'template-parts/blog/post-loop', null, array( 'settings' => $author_loop_settings ) ); ?>
                <?php get_template_part( 'template-parts/blog/pagination' ); ?>
            <?php else : ?>
                <div class="author-posts-empty">
                    <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
                    <p><?php echo esc_html( $author_empty_posts_text ); ?></p>
                </div>
            <?php endif; ?>
        </div>
    </section>
</div>

<!-- 关注功能脚本（仅在 qibbs-community 激活时） -->
<?php if ( $author_header_enabled && $author_actions_enabled && $qibbs_active && is_user_logged_in() && ! $is_self ) : ?>
<script>
(function() {
    document.addEventListener('DOMContentLoaded', function() {
        var followBtn = document.querySelector('.author-follow-btn[data-user-id]');
        if (!followBtn) return;
        
        followBtn.addEventListener('click', function(e) {
            e.preventDefault();
            var btn = this;
            var userId = btn.dataset.userId;
            var nonce = btn.dataset.nonce;
            var isFollowing = btn.classList.contains('is-following');
            
            btn.disabled = true;
            
            var formData = new FormData();
            formData.append('action', isFollowing ? 'qibbs_unfollow' : 'qibbs_follow');
            formData.append('user_id', userId);
            formData.append('nonce', nonce);
            
            fetch(<?php echo wp_json_encode( esc_url_raw( admin_url( 'admin-ajax.php' ) ) ); ?>, {
                method: 'POST',
                body: formData
            })
            .then(function(response) { return response.json(); })
            .then(function(data) {
                if (data.success) {
                    if (isFollowing) {
                        btn.classList.remove('is-following');
                        btn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg><span><?php esc_html_e( '关注', 'developer-starter' ); ?></span>';
                    } else {
                        btn.classList.add('is-following');
                        btn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg><span><?php esc_html_e( '已关注', 'developer-starter' ); ?></span>';
                    }
                }
            })
            .finally(function() {
                btn.disabled = false;
            });
        });
    });
})();
</script>
<?php endif; ?>

<?php get_footer(); ?>
