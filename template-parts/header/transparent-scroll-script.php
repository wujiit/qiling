<?php
/**
 * Transparent header scroll behavior.
 *
 * @package Developer_Starter
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$args = wp_parse_args(
    is_array( $args ) ? $args : array(),
    array(
        'enable_transparent_header' => false,
    )
);

if ( empty( $args['enable_transparent_header'] ) ) {
    return;
}
?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var header = document.getElementById('masthead');
    if (!header) {
        return;
    }

    var scrolled = false;

    function updateHeaderOffsetVar() {
        var rectHeight = header.getBoundingClientRect ? header.getBoundingClientRect().height : 0;
        var measuredHeight = Math.max(header.offsetHeight || 0, rectHeight || 0);
        if (measuredHeight <= 0) {
            return;
        }

        document.documentElement.style.setProperty('--qiling-header-offset', Math.round(measuredHeight) + 'px');
    }

    function checkScroll() {
        if (window.scrollY > 100) {
            if (!scrolled) {
                header.classList.add('header-scrolled');
                scrolled = true;
                updateHeaderOffsetVar();
            }
        } else {
            if (scrolled) {
                header.classList.remove('header-scrolled');
                scrolled = false;
                updateHeaderOffsetVar();
            }
        }
    }

    window.addEventListener('scroll', checkScroll);
    window.addEventListener('resize', updateHeaderOffsetVar);
    window.addEventListener('orientationchange', updateHeaderOffsetVar);
    window.addEventListener('load', updateHeaderOffsetVar);
    updateHeaderOffsetVar();
    checkScroll();
});
</script>
