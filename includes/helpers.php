<?php
declare(strict_types=1);

function ap_get_ui_mode(): string {
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

/**
 * Fetch and parse an RSS feed.
 *
 * Wraps WordPress fetch_feed() with basic error handling.
 *
 * @param string $url Feed URL.
 * @return array|SimplePie
 */
function ap_get_feed(string $url): array|SimplePie {
    include_once ABSPATH . WPINC . '/feed.php';

    $feed = fetch_feed($url);
    if (is_wp_error($feed)) {
        return [];
    }

    return $feed;
}

function ap_template_context(array $args = [], array $defaults = []): array {
    return wp_parse_args($args, $defaults);
}

function ap_safe_include(string $relative_template, string $fallback_path): void {
    $template = locate_template($relative_template);
    if (!$template) {
        $template = $fallback_path;
    }
    if ($template && file_exists($template)) {
        include $template;
    } else {
        error_log("ArtPulse: Missing template → $relative_template or fallback.");
    }
}
