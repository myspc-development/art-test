<?php
/**
 * Dashboard widget helpers and AJAX handlers.
 */

if (!defined('ABSPATH')) {
    exit;
}

use ArtPulse\Core\DashboardWidgetRegistry;

function ap_get_all_widget_definitions(): array
{
    return DashboardWidgetRegistry::get_definitions();
}


add_action('wp_ajax_ap_save_dashboard_widget_config', 'ap_save_dashboard_widget_config');

function ap_save_dashboard_widget_config(): void
{
    check_ajax_referer('ap_dashboard_widget_config', 'nonce');
    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => __('Permission denied', 'artpulse')]);
    }

    $raw = $_POST['config'] ?? [];
    $sanitized = [];
    foreach ($raw as $role => $widgets) {
        $role_key = sanitize_key($role);
        $ordered = [];
        foreach ((array) $widgets as $w) {
            $ordered[] = sanitize_key($w);
        }
        $sanitized[$role_key] = $ordered;
    }

    update_option('ap_dashboard_widget_config', $sanitized);
    wp_send_json_success(['saved' => true]);
}
