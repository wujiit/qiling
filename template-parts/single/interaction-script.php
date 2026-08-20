<?php
/**
 * Single post like/favorite inline behaviour.
 *
 * @package Developer_Starter
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$args = wp_parse_args(
    is_array( $args ) ? $args : array(),
    array(
        'post_like_enable'     => false,
        'post_favorite_enable' => false,
    )
);

if ( ! $args['post_like_enable'] && ! $args['post_favorite_enable'] ) {
    return;
}
?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var buttons = document.querySelectorAll('.ds-interaction-btn');
    if (!buttons.length) {
        return;
    }
    buttons.forEach(function(btn) {
        btn.addEventListener('click', function() {
            var formData = new FormData();
            formData.append('action', 'ds_toggle_post_interaction');
            formData.append('post_id', btn.dataset.postId || '');
            formData.append('interaction_type', btn.dataset.type || '');
            formData.append('nonce', btn.dataset.nonce || '');

            fetch(<?php echo wp_json_encode( esc_url_raw( admin_url( 'admin-ajax.php' ) ) ); ?>, {
                method: 'POST',
                body: formData
            })
            .then(function(response) { return response.text(); })
            .then(function(text) {
                var data = null;
                var normalized = text.replace(/^\uFEFF/, '').trim();
                if (normalized) {
                    try {
                        data = JSON.parse(normalized);
                    } catch (err) {
                        alert('<?php echo esc_js( __( '响应解析失败：', 'developer-starter' ) ); ?>' + normalized);
                        return;
                    }
                }
                if (!data || !data.success) {
                    var message = data && data.data && data.data.message ? data.data.message : '<?php echo esc_js( __( '操作失败', 'developer-starter' ) ); ?>';
                    alert(message);
                    return;
                }
                var countEl = btn.querySelector('.ds-interaction-count');
                if (countEl) {
                    countEl.textContent = data.data.count;
                }
                if (data.data.active) {
                    btn.classList.add('is-active');
                } else {
                    btn.classList.remove('is-active');
                }
            })
            .catch(function() {
                alert('<?php echo esc_js( __( '网络错误，请稍后再试', 'developer-starter' ) ); ?>');
            });
        });
    });
});
</script>
