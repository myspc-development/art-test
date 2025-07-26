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
/**
 * Determine widget visibility for the current user and display any notices.
 *
 * This helper checks capability requirements and per-role exclusions. It can be
 * extended via the `ap_dashboard_widget_visibility_rules` filter to support new
 * sub-roles in the future.
 */
function ap_get_dashboard_widget_visibility_rules(): array {
    return [
        'artpulse_analytics_widget' => [
            'capability'    => 'view_analytics',
            'exclude_roles' => [
                'org_editor' => [
                    'notice' => __('Analytics are available to organization managers only.', 'artpulse'),
                    'type'   => 'info',
                ],
                'org_viewer',
            ],
        ],
    ];
}

function ap_dashboard_widget_visibility_filter(): void {
    $current_user = wp_get_current_user();
    $roles        = (array) $current_user->roles;

    $rules = apply_filters('ap_dashboard_widget_visibility_rules', ap_get_dashboard_widget_visibility_rules());

    foreach ($rules as $widget => $config) {
        $cap          = $config['capability']    ?? null;
        $exclude      = $config['exclude_roles'] ?? [];
        $notice_roles = is_array($exclude) ? $exclude : [];

        $hide = false;
        if ($cap && !current_user_can($cap)) {
            $hide = true;
        }

        foreach ($roles as $role) {
            if (isset($notice_roles[$role])) {
                $hide = true;
                $msg  = $notice_roles[$role]['notice'] ?? '';
                $type = $notice_roles[$role]['type'] ?? 'info';
                if ($msg) {
                    ap_add_admin_notice($msg, $type);
                }
            } elseif (in_array($role, (array) $exclude, true)) {
                $hide = true;
            }
        }

        if ($hide) {
            remove_meta_box($widget, 'dashboard', 'normal');
        }
    }
}
add_action('wp_dashboard_setup', 'ap_dashboard_widget_visibility_filter', 99);
