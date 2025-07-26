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
        'view_analytics' => true,
    ]);

    add_role('org_viewer', 'Organization Viewer', [
        'read' => true,
        'edit_dashboard_widgets' => false,
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
        // Editors see a simplified dashboard. Hide analytics reserved for managers.
        remove_meta_box('artpulse_analytics_widget', 'dashboard', 'normal');
        ap_add_admin_notice(
            __('Analytics are available to organization managers only.', 'artpulse'),
            'info'
        );
    }
}, 99);
