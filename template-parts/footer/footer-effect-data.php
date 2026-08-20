<?php
/**
 * Footer effect boot data.
 *
 * @package Developer_Starter
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$args = wp_parse_args(
    is_array( $args ) ? $args : array(),
    array(
        'effect_enabled' => false,
        'effect_type'    => 'particles',
    )
);

if ( empty( $args['effect_enabled'] ) ) {
    return;
}
?>
<script>
window.footerEffectType = '<?php echo esc_js( (string) $args['effect_type'] ); ?>';
</script>
