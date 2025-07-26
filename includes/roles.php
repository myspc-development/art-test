<?php
if (!defined('ABSPATH')) { exit; }

function ap_register_roles() {
    add_role('org_manager', 'Organization Manager', [
        'read' => true,
        'edit_dashboard_widgets' => true,
        'view_analytics' => true,
    ]);

    add_role('org_editor', 'Organization Editor', [
        'read' => true,
        'edit_dashboard_widgets' => false,
        // Editors can technically view analytics but we hide the widget by
        // default so managers remain responsible for performance insights.
        'view_analytics' => true,
    ]);

    add_role('org_viewer', 'Organization Viewer', [
        'read' => true,
        'edit_dashboard_widgets' => false,
        // Viewers never see analytics so the widget is never registered.
        'view_analytics' => false,
    ]);
}

register_activation_hook(ARTPULSE_PLUGIN_FILE, 'ap_register_roles');

add_action('init', function () {
    if (!get_role('org_manager') || !get_role('org_editor') || !get_role('org_viewer')) {
        ap_register_roles();
    }
});

function ap_add_admin_notice(string $message, string $type = 'success'): void {
    $notices = get_transient('ap_admin_notices');
    if (!is_array($notices)) {
        $notices = [];
    }
    $notices[] = ['message' => wp_kses_post($message), 'type' => $type];
    set_transient('ap_admin_notices', $notices, MINUTE_IN_SECONDS);
}

add_action('admin_notices', function () {
    $notices = get_transient('ap_admin_notices');
    if (empty($notices)) {
        return;
    }
    foreach ($notices as $notice) {
        printf('<div class="notice notice-%s"><p>%s</p></div>', esc_attr($notice['type']), $notice['message']);
    }
    delete_transient('ap_admin_notices');
});

/**
 * Filter dashboard widgets based on role capabilities.
 *
 * Org editors technically have the `view_analytics` capability but the
 * analytics widget is removed so managers remain responsible for metrics.
 * Viewers lack the capability entirely. This helper centralizes the logic
 * so tests can verify widget visibility per role.
 */
function ap_dashboard_widget_visibility_filter(): void {
    $current_user = wp_get_current_user();
    $roles        = (array) $current_user->roles;

    if (in_array('org_editor', $roles, true)) {
        remove_meta_box('artpulse_analytics_widget', 'dashboard', 'normal');
        ap_add_admin_notice(
            __('Analytics are available to organization managers only.', 'artpulse'),
            'info'
        );
        return;
    }

    if (!current_user_can('view_analytics')) {
        remove_meta_box('artpulse_analytics_widget', 'dashboard', 'normal');
    }
}
add_action('wp_dashboard_setup', 'ap_dashboard_widget_visibility_filter', 99);
