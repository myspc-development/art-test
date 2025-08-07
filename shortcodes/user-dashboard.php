<?php
/**
 * Shortcodes for rendering common user dashboard widgets.
 */

if (!defined('ABSPATH')) {
    exit;
}

use ArtPulse\Core\ShortcodeRegistry;

function ap_user_events_shortcode(): string {
    return do_shortcode('[ap_widget id="my-events"]');
}
ShortcodeRegistry::register('ap_user_events', 'User Events', 'ap_user_events_shortcode');

function ap_user_follows_shortcode(): string {
    return do_shortcode('[ap_widget id="my-follows"]');
}
ShortcodeRegistry::register('ap_user_follows', 'User Follows', 'ap_user_follows_shortcode');

function ap_user_analytics_shortcode(): string {
    return do_shortcode('[ap_widget id="artpulse_analytics_widget"]');
}
ShortcodeRegistry::register('ap_user_analytics', 'User Analytics', 'ap_user_analytics_shortcode');
