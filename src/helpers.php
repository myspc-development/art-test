<?php
/**
 * Helper function for dashboard layout permissions.
 */
function ap_user_can_edit_layout(string $role): bool
{
    return current_user_can($role) || current_user_can('manage_options');
}

/**
 * Fetch and parse an RSS feed.
 *
 * Wraps WordPress fetch_feed() with basic error handling.
 *
 * @param string $url Feed URL.
 * @return array|SimplePie
 */
function ap_get_feed($url) {
    include_once ABSPATH . WPINC . '/feed.php';

    $feed = fetch_feed($url);
    if (is_wp_error($feed)) {
        return [];
    }

    return $feed;
}
