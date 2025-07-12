<?php
function ap_get_ui_mode() {
    if (isset($_GET['ui_mode'])) {
        return sanitize_text_field($_GET['ui_mode']);
    }
    return get_option('ap_ui_mode', 'salient');
}

/**
 * Simple object cache wrapper for expensive queries.
 */
function ap_cache_get(string $key, callable $callback, int $expires = HOUR_IN_SECONDS) {
    $group = 'artpulse_queries';
    $value = wp_cache_get($key, $group);
    if (false === $value) {
        $value = $callback();
        wp_cache_set($key, $value, $group, $expires);
    }
    return $value;
}
