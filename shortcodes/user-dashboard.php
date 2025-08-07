<?php
/**
 * Shortcodes for rendering common user dashboard widgets.
 */

if (!defined('ABSPATH')) {
    exit;
}

use ArtPulse\Core\ShortcodeRegistry;
use ArtPulse\Core\DashboardController;
use ArtPulse\Core\DashboardWidgetRegistry;
use ArtPulse\Core\RoleResolver;

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

    $user_id = get_current_user_id();
    $role    = RoleResolver::resolve($user_id);
    $layout  = DashboardController::get_user_dashboard_layout($user_id);

    $widgets = [];
    foreach ($layout as $entry) {
        $id = $entry['id'] ?? '';
        if ($id && DashboardWidgetRegistry::user_can_see($id, $user_id)) {
            $widgets[] = $id;
        }
    }

    if (empty($widgets)) {
        $widgets = ['widget_placeholder'];
    }

    ob_start();
    echo '<div class="ap-dashboard-fallback" data-role="' . esc_attr($role) . '">';
    foreach ($widgets as $id) {
        ap_render_widget($id, $user_id);
    }
    echo '</div>';
    return ob_get_clean();
}
ShortcodeRegistry::register('user_dashboard', 'User Dashboard', 'user_dashboard_shortcode');
