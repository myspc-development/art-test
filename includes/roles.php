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

add_action('wp_dashboard_setup', function () {
    $current_user = wp_get_current_user();
    if (in_array('org_editor', (array) $current_user->roles, true)) {
        // Org editors have the capability to view analytics, but the full
        // metrics widget clutters their workflow. We remove it so only
        // managers handle performance reviews.
        remove_meta_box('artpulse_analytics_widget', 'dashboard', 'normal');
        ap_add_admin_notice(
            __('Analytics are available to organization managers only.', 'artpulse'),
            'info'
        );
    }
}, 99);
