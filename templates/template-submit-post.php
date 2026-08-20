<?php
/**
 * Template Name: 用户投稿
 * Template Post Type: page
 *
 * @package Developer_Starter
 */

// 检查投稿功能是否启用
$submit_post_enable = developer_starter_get_option( 'submit_post_enable', '' );

// 加载投稿页面专用样式
add_action( 'wp_enqueue_scripts', function() {
    wp_enqueue_style(
        'developer-starter-submit-post',
        DEVELOPER_STARTER_ASSETS . '/css/submit-post.css',
        array( 'developer-starter-main' ),
        developer_starter_get_assets_version()
    );
}, 20 );

get_header();

// 检查用户是否登录
$is_logged_in = is_user_logged_in();

// 获取设置
$disabled_message = developer_starter_get_option( 'submit_post_disabled_message', __( '投稿功能暂时关闭，请稍后再试。', 'developer-starter' ) );
$success_message = developer_starter_get_option( 'submit_post_success_message', __( '投稿成功！请等待管理员审核。', 'developer-starter' ) );
$allow_tags = developer_starter_get_option( 'submit_post_allow_tags', '1' );
$max_tags = developer_starter_get_option( 'submit_post_max_tags', '5' );
$allowed_categories = developer_starter_get_option( 'submit_post_categories', array() );

// 获取可选分类
$cat_args = array(
    'hide_empty' => false,
    'orderby'    => 'name',
    'order'      => 'ASC',
);

// 如果限制了分类，只获取指定分类
if ( ! empty( $allowed_categories ) && is_array( $allowed_categories ) ) {
    $cat_args['include'] = $allowed_categories;
}

$categories = get_categories( $cat_args );
?>

<div class="submit-post-page">
    <div class="container">
        
        <?php if ( ! $submit_post_enable ) : ?>
        <!-- 功能关闭提示 -->
        <div class="submit-post-disabled" data-aos="fade-up">
            <div class="disabled-icon">
                <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="15" y1="9" x2="9" y2="15"/>
                    <line x1="9" y1="9" x2="15" y2="15"/>
                </svg>
            </div>
            <h2><?php esc_html_e( '暂不支持投稿', 'developer-starter' ); ?></h2>
            <p><?php echo esc_html( $disabled_message ); ?></p>
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="btn-primary"><?php esc_html_e( '返回首页', 'developer-starter' ); ?></a>
        </div>
        
        <?php elseif ( ! $is_logged_in ) : ?>
        <!-- 登录提示 -->
        <div class="submit-post-login-required" data-aos="fade-up">
            <div class="login-icon">
                <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/>
                    <circle cx="12" cy="7" r="4"/>
                </svg>
            </div>
            <h2><?php esc_html_e( '请先登录', 'developer-starter' ); ?></h2>
            <p><?php esc_html_e( '您需要登录后才能投稿文章。', 'developer-starter' ); ?></p>
            <?php
            $login_page_id = developer_starter_get_option( 'login_page_id', '' );
            $login_url = $login_page_id ? get_permalink( $login_page_id ) : wp_login_url( get_permalink() );
            $header_login_enable = developer_starter_get_option( 'header_login_enable', '' );
            
            // 如果顶部登录按钮开启，则尝试调用弹窗登录
            $login_btn_atts = '';
            $login_btn_class = 'btn-primary';
            
            if ( $header_login_enable ) {
                $login_btn_class .= ' trigger-login-modal';
            }
            ?>
            <a href="<?php echo esc_url( $login_url ); ?>" class="<?php echo esc_attr( $login_btn_class ); ?>"><?php esc_html_e( '立即登录', 'developer-starter' ); ?></a>
            
            <?php if ( $header_login_enable ) : ?>
            <script>
            document.addEventListener('DOMContentLoaded', function() {
                var triggerBtn = document.querySelector('.trigger-login-modal');
                var headerLoginBtn = document.getElementById('header-login-toggle');
                
                if (triggerBtn && headerLoginBtn) {
                    triggerBtn.addEventListener('click', function(e) {
                        e.preventDefault();
                        headerLoginBtn.click();
                    });
                }
            });
            </script>
            <?php endif; ?>
        </div>
        
    <?php else : ?>
        <!-- 投稿表单 -->
        <?php
        // 检查是否是编辑模式
        $edit_post_id = isset( $_GET['post_id'] ) ? absint( wp_unslash( $_GET['post_id'] ) ) : 0;
        $post_to_edit = null;
        $form_title = __( '投稿文章', 'developer-starter' );
        $submit_btn_text = __( '提交投稿', 'developer-starter' );
        
        if ( $edit_post_id ) {
            $post_to_edit = get_post( $edit_post_id );
            // 验证权限：文章存在且作者是当前用户
            if ( $post_to_edit && $post_to_edit->post_author == get_current_user_id() ) {
                $form_title = __( '编辑文章', 'developer-starter' );
                $submit_btn_text = __( '更新投稿', 'developer-starter' );
            } else {
                $post_to_edit = null; // 权限验证失败，重置
            }
        }
        ?>
        <div class="submit-post-form-wrapper" data-aos="fade-up">
            <div class="form-header">
                <h1 class="form-title">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 20h9"/>
                        <path d="M16.5 3.5a2.121 2.121 0 013 3L7 19l-4 1 1-4L16.5 3.5z"/>
                    </svg>
                    <?php echo esc_html( $form_title ); ?>
                </h1>
                <p class="form-desc"><?php esc_html_e( '请填写以下信息，提交后将由管理员审核。', 'developer-starter' ); ?></p>
            </div>
            
            <form id="submit-post-form" class="submit-post-form">
                <?php wp_nonce_field( 'developer_starter_submit_post', 'submit_post_nonce' ); ?>
                <?php if ( $post_to_edit ) : ?>
                    <input type="hidden" name="post_id" value="<?php echo esc_attr( $post_to_edit->ID ); ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="post-title">
                        <?php esc_html_e( '文章标题', 'developer-starter' ); ?> <span class="required">*</span>
                    </label>
                    <input type="text" 
                           id="post-title" 
                           name="post_title" 
                           placeholder="<?php esc_attr_e( '请输入文章标题', 'developer-starter' ); ?>" 
                           maxlength="100"
                           value="<?php echo $post_to_edit ? esc_attr( $post_to_edit->post_title ) : ''; ?>"
                           required />
                    <span class="char-count"><span id="title-char-count"><?php echo $post_to_edit ? mb_strlen( $post_to_edit->post_title ) : '0'; ?></span>/100</span>
                </div>
                
                <div class="form-group">
                    <label for="post-content">
                        <?php esc_html_e( '文章内容', 'developer-starter' ); ?> <span class="required">*</span>
                    </label>
                    <div class="editor-wrapper">
                        <?php
                        $content = $post_to_edit ? $post_to_edit->post_content : '';
                        wp_editor( $content, 'post_content', array(
                            'textarea_name' => 'post_content',
                            'textarea_rows' => 15,
                            'media_buttons' => true,
                            'teeny'         => false,
                            'quicktags'     => true,
                            'tinymce'       => array(
                                'toolbar1' => 'formatselect,bold,italic,underline,strikethrough,|,bullist,numlist,|,blockquote,|,alignleft,aligncenter,alignright,|,link,unlink,|,wp_more,|,fullscreen',
                                'toolbar2' => '',
                            ),
                        ) );
                        ?>
                    </div>
                </div>
                
                <?php if ( ! empty( $categories ) ) : 
                    $current_cat = 0;
                    if ( $post_to_edit ) {
                        $cats = get_the_category( $post_to_edit->ID );
                        if ( ! empty( $cats ) ) {
                            $current_cat = $cats[0]->term_id;
                        }
                    }
                ?>
                <div class="form-group">
                    <label for="post-category">
                        <?php esc_html_e( '文章分类', 'developer-starter' ); ?> <span class="required">*</span>
                    </label>
                    <select id="post-category" name="post_category" required>
                        <option value=""><?php esc_html_e( '请选择分类', 'developer-starter' ); ?></option>
                        <?php foreach ( $categories as $cat ) : ?>
                        <option value="<?php echo esc_attr( $cat->term_id ); ?>" <?php selected( $current_cat, $cat->term_id ); ?>>
                            <?php echo esc_html( $cat->name ); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>
                
                <?php if ( $allow_tags ) : 
                    $current_tags_str = '';
                    if ( $post_to_edit ) {
                        $tags_obj = get_the_tags( $post_to_edit->ID );
                        if ( $tags_obj ) {
                            $tag_names = array();
                            foreach ( $tags_obj as $tag ) {
                                $tag_names[] = $tag->name;
                            }
                            $current_tags_str = implode( ', ', $tag_names );
                        }
                    }
                ?>
                <div class="form-group">
                    <label for="post-tags">
                        <?php esc_html_e( '文章标签', 'developer-starter' ); ?>
                        <span class="label-hint">(<?php printf( esc_html__( '可选，用逗号分隔，最多%d个', 'developer-starter' ), esc_html( $max_tags ) ); ?>)</span>
                    </label>
                    <input type="text" 
                           id="post-tags" 
                           name="post_tags" 
                           placeholder="<?php esc_attr_e( '如：WordPress, 教程, 主题开发', 'developer-starter' ); ?>"
                           value="<?php echo esc_attr( $current_tags_str ); ?>"
                           data-max-tags="<?php echo esc_attr( $max_tags ); ?>" />
                    <div class="tags-preview" id="tags-preview">
                        <?php 
                        if ( $current_tags_str ) {
                            $tags_arr = explode( ',', $current_tags_str );
                            foreach ( $tags_arr as $tag ) {
                                echo '<span class="tag-item">' . esc_html( trim( $tag ) ) . '</span>';
                            }
                        }
                        ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php 
                /**
                 * 投稿表单额外字段钩子
                 * 允许插件在提交按钮前添加自定义字段
                 * 
                 * @param WP_Post|null $post_to_edit 编辑模式下的文章对象，新建时为null
                 */
                do_action( 'qiling_submit_post_extra_fields', $post_to_edit );
                ?>
                
                <div class="form-group form-actions">
                    <button type="submit" class="btn-submit" id="submit-btn">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="22" y1="2" x2="11" y2="13"/>
                            <polygon points="22 2 15 22 11 13 2 9 22 2"/>
                        </svg>
                        <span><?php echo esc_html( $submit_btn_text ); ?></span>
                    </button>
                    <?php if ( $post_to_edit ) : ?>
                    <a href="javascript:history.back()" class="btn-cancel" style="margin-left: var(--qiling-space-10); padding: var(--qiling-space-10) var(--qiling-space-20); text-decoration: none; color: var(--color-text-muted);"><?php esc_html_e( '取消', 'developer-starter' ); ?></a>
                    <?php endif; ?>
                </div>
                
                <div id="submit-message" class="submit-message" style="display: none;"></div>
            </form>
        </div>
        <?php endif; ?>
        
    </div>
</div>

<?php if ( $submit_post_enable && $is_logged_in ) : ?>
<script>
(function() {
    var form = document.getElementById('submit-post-form');
    var submitBtn = document.getElementById('submit-btn');
    var messageDiv = document.getElementById('submit-message');
    var titleInput = document.getElementById('post-title');
    var titleCharCount = document.getElementById('title-char-count');
    var tagsInput = document.getElementById('post-tags');
    var tagsPreview = document.getElementById('tags-preview');
    var ajaxUrl = <?php echo wp_json_encode( esc_url_raw( admin_url( 'admin-ajax.php' ) ) ); ?>;
    var successMessage = '<?php echo esc_js( $success_message ); ?>';
    var maxTags = <?php echo intval( $max_tags ); ?>;
    
    // 标题字数统计
    if (titleInput && titleCharCount) {
        titleInput.addEventListener('input', function() {
            titleCharCount.textContent = this.value.length;
        });
    }
    
    // 标签预览
    if (tagsInput && tagsPreview) {
        tagsInput.addEventListener('input', function() {
            var tags = this.value.split(',').map(function(t) { return t.trim(); }).filter(function(t) { return t; });
            if (tags.length > maxTags) {
                tags = tags.slice(0, maxTags);
                this.value = tags.join(', ');
            }
            
            tagsPreview.innerHTML = '';
            tags.forEach(function(tag) {
                var span = document.createElement('span');
                span.className = 'tag-item';
                span.textContent = tag;
                tagsPreview.appendChild(span);
            });
        });
    }
    
    // 显示消息
    function showMessage(type, text) {
        messageDiv.className = 'submit-message ' + type;
        messageDiv.textContent = text;
        messageDiv.style.display = 'block';
        
        // 滚动到消息位置
        messageDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
    
    // 表单提交
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // 获取编辑器内容
            var content = '';
            if (typeof tinyMCE !== 'undefined' && tinyMCE.get('post_content')) {
                content = tinyMCE.get('post_content').getContent();
            } else {
                var contentTextarea = document.getElementById('post_content');
                if (contentTextarea) {
                    content = contentTextarea.value;
                }
            }
            
            var title = titleInput ? titleInput.value.trim() : '';
            var category = document.getElementById('post-category');
            var categoryValue = category ? category.value : '';
            var tags = tagsInput ? tagsInput.value.trim() : '';
            var postIdInput = document.querySelector('input[name="post_id"]');
            var postId = postIdInput ? postIdInput.value : '';
            
            // 验证
            if (!title) {
                showMessage('error', '<?php echo esc_js( __( '请输入文章标题', 'developer-starter' ) ); ?>');
                return;
            }
            
            if (!content || content === '<p><br></p>' || content === '<p></p>') {
                showMessage('error', '<?php echo esc_js( __( '请输入文章内容', 'developer-starter' ) ); ?>');
                return;
            }
            
            if (category && !categoryValue) {
                showMessage('error', '<?php echo esc_js( __( '请选择文章分类', 'developer-starter' ) ); ?>');
                return;
            }
            
            // 禁用按钮
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<svg class="spin" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10" stroke-dasharray="32" stroke-dashoffset="32"/></svg><span><?php echo esc_js( __( '提交中...', 'developer-starter' ) ); ?></span>';
            
            // 发送请求
            var formData = new FormData(form);
            formData.append('action', 'developer_starter_submit_post');
            formData.set('post_content', content); // Ensure content from editor is used
            
            var submitSuccess = false; // 标记提交是否成功
            
            fetch(ajaxUrl, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            })
            .then(function(response) { return response.json(); })
            .then(function(data) {
                if (data.success) {
                    submitSuccess = true;
                    showMessage('success', data.data.message || successMessage);
                    // 跳转到个人中心投稿管理页面
                    if (data.data.redirect_url) {
                         setTimeout(function() {
                             window.location.href = data.data.redirect_url;
                         }, 1500);
                    }
                } else {
                    showMessage('error', data.data.message || '<?php echo esc_js( __( '提交失败，请重试', 'developer-starter' ) ); ?>');
                }
            })
            .catch(function(error) {
                showMessage('error', '<?php echo esc_js( __( '网络错误，请重试', 'developer-starter' ) ); ?>');
                console.error('Submit error:', error);
            })
            .finally(function() {
                if (!submitSuccess) { // 成功时不恢复按钮，防止重复提交
                    submitBtn.disabled = false;
                    var btnText = postId ? '<?php echo esc_js( __( '更新投稿', 'developer-starter' ) ); ?>' : '<?php echo esc_js( __( '提交投稿', 'developer-starter' ) ); ?>';
                    submitBtn.innerHTML = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg><span>' + btnText + '</span>';
                }
            });
        });
    }
})();
</script>
<?php endif; ?>

<?php
get_footer();
