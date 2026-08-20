<?php
/**
 * Document head template part.
 *
 * @package Developer_Starter
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
    $dark_mode_config = function_exists( 'developer_starter_get_dark_mode_runtime_config' )
        ? developer_starter_get_dark_mode_runtime_config()
        : array( 'enabled' => (bool) developer_starter_get_option( 'darkmode_enable', '' ) );
    if ( ! empty( $dark_mode_config['enabled'] ) ) :
        ?>
        <script>
        window.qilingDarkModeConfig = <?php echo wp_json_encode( $dark_mode_config ); ?>;
        (function(config) {
            var root = document.documentElement;
            var storageKey = config.storageKey || 'qiling-theme-preference';
            var legacyStorageKey = config.legacyStorageKey || 'theme';

            function readStorage(key) {
                try {
                    return window.localStorage ? localStorage.getItem(key) : null;
                } catch (error) {
                    return null;
                }
            }

            function timeToMinutes(value, fallback) {
                var time = /^([01]?\d|2[0-3]):([0-5]\d)$/.exec(String(value || fallback || '00:00'));
                return time ? (parseInt(time[1], 10) * 60) + parseInt(time[2], 10) : 0;
            }

            function scheduleWantsDark() {
                var sunrise = timeToMinutes(config.sunriseTime, '06:00');
                var sunset = timeToMinutes(config.sunsetTime, '18:00');
                var now = new Date();
                var minutes = now.getHours() * 60 + now.getMinutes();
                if (sunrise === sunset) {
                    return false;
                }
                return sunset > sunrise ? (minutes >= sunset || minutes < sunrise) : (minutes >= sunset && minutes < sunrise);
            }

            function systemWantsDark() {
                return !!(window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches);
            }

            function autoWantsDark() {
                var mode = config.mode || 'system_schedule';
                if (mode === 'system') {
                    return systemWantsDark();
                }
                if (mode === 'schedule') {
                    return scheduleWantsDark();
                }
                return window.matchMedia ? systemWantsDark() : scheduleWantsDark();
            }

            var preference = readStorage(storageKey);
            var legacyTheme = readStorage(legacyStorageKey);
            var theme = 'light';
            var source = 'default';

            if (config.autoEnabled) {
                if (preference === 'dark' || preference === 'light') {
                    theme = preference;
                    source = 'manual';
                } else {
                    theme = autoWantsDark() ? 'dark' : 'light';
                    source = 'auto';
                }
            } else if (preference === 'dark' || preference === 'light') {
                theme = preference;
                source = 'manual';
            } else if (legacyTheme === 'dark' || legacyTheme === 'light') {
                theme = legacyTheme;
                source = 'legacy';
            }

            if (theme === 'dark') {
                root.classList.add('dark-mode');
                root.setAttribute('data-theme', 'dark');
            } else {
                root.classList.remove('dark-mode');
                root.setAttribute('data-theme', 'light');
            }
            root.setAttribute('data-theme-source', source);
            root.classList.toggle('qiling-dark-auto', !!config.autoEnabled);
            root.classList.toggle('qiling-dark-image-dim', !!config.imageDim);
        })(window.qilingDarkModeConfig || {});
        </script>
    <?php endif; ?>
    <?php wp_head(); ?>
</head>
