<?php
// Dashboard Widget Controller for admin
if (!defined('ABSPATH')) {
    exit;
}

use ArtPulse\Core\DashboardWidgetRegistry;

function artpulse_get_available_dashboard_widgets() {
    return [
        'ap_stats' => [
            'title' => 'Artwork Stats',
            'callback' => 'artpulse_widget_stats',
        ],
        'ap_favorites' => [
            'title' => 'Favorite Portfolios',
            'callback' => 'artpulse_widget_favorites',
        ],
        'ap_followers' => [
            'title' => 'Follower Insights',
            'callback' => 'artpulse_widget_followers',
        ],
    ];
}

add_action('admin_init', function () {
    register_setting('artpulse_widget_settings', 'artpulse_enabled_widgets');
});

add_action('artpulse_register_dashboard_widget', 'artpulse_register_dashboard_widgets');
function artpulse_register_dashboard_widgets() {
    $widgets = artpulse_get_available_dashboard_widgets();
    $enabled = get_option('artpulse_enabled_widgets', array_keys($widgets));

    foreach ($widgets as $id => $widget) {
        if (in_array($id, $enabled, true)) {
            DashboardWidgetRegistry::register(
                "artpulse_{$id}",
                $widget['title'],
                '',
                '',
                $widget['callback'],
                [ 'roles' => ['administrator'] ]
            );
        }
    }
}

function artpulse_widget_stats() {
    echo "<p>Total artworks synced: <strong>123</strong></p>";
}
function artpulse_widget_favorites() {
    echo "<p>Top favorited artwork this week: <em>“Sunrise Over Canvas”</em></p>";
}
function artpulse_widget_followers() {
    echo "<p>You gained 8 new followers this week.</p>";
}

/**
 * Optional helper to expose role keys in the editor.
 */
function artpulse_get_dashboard_roles(): array {
    return array_keys(wp_roles()->roles);
}
