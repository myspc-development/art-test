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

