<?php
/**
 * Shortcodes for rendering common user dashboard widgets.
 */

if (!defined('ABSPATH')) {
    exit;
}

use ArtPulse\Core\ShortcodeRegistry;
use ArtPulse\Core\RoleResolver;
use ArtPulse\Core\DashboardPresets;
use ArtPulse\Core\WidgetRegistry;

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

function user_dashboard_shortcode(): string {
    if (!is_user_logged_in()) {
        return '';
    }
    wp_enqueue_style('ap-react-dashboard');
    wp_enqueue_script('ap-react-vendor');
    wp_enqueue_script('ap-react-dashboard');

    $user_id = get_current_user_id();
    $role    = function_exists('ap_get_effective_role') ? ap_get_effective_role() : RoleResolver::resolve($user_id);
    $widgets = DashboardPresets::get_preset_for_role($role);
    if (empty($widgets)) {
        $widgets = ['empty_dashboard'];
    }

    ob_start();
    echo '<div class="ap-dashboard-fallback" data-role="' . esc_attr($role) . '">';
    foreach ($widgets as $slug) {
        echo WidgetRegistry::render($slug, ['user_id' => $user_id]);
    }
    echo '</div>';
    return ob_get_clean();
}
ShortcodeRegistry::register('user_dashboard', 'User Dashboard', 'user_dashboard_shortcode');

