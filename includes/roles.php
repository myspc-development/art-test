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
    $rules = [
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

    /**
     * Allow plugins to register additional visibility rules.
     *
     * @param array $rules Default visibility configuration.
     */
    return apply_filters('ap_dashboard_widget_visibility_rules', $rules);
}

function ap_dashboard_widget_visibility_filter(): void {
    $current_user = wp_get_current_user();
    $roles        = (array) $current_user->roles;

    global $ap_hidden_widgets;
    $ap_hidden_widgets = [];

    $rules = ap_get_dashboard_widget_visibility_rules();

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
                    if ($role === 'org_editor') {
                        if (!get_user_meta($current_user->ID, 'ap_dismiss_org_editor_notice', true)) {
                            update_user_meta($current_user->ID, 'ap_org_editor_notice_pending', $msg);
                        }
                    } else {
                        ap_add_admin_notice($msg, $type);
                    }
                }
            } elseif (in_array($role, (array) $exclude, true)) {
                $hide = true;
            }
        }

        if ($hide) {
            remove_meta_box($widget, 'dashboard', 'normal');
            $ap_hidden_widgets[$widget] = true;
        }
    }
}
add_action('wp_dashboard_setup', 'ap_dashboard_widget_visibility_filter', 99);

function ap_handle_org_editor_notice_dismiss(): void {
    if (isset($_GET['ap_dismiss_editor_notice'])) {
        update_user_meta(get_current_user_id(), 'ap_dismiss_org_editor_notice', 1);
        delete_user_meta(get_current_user_id(), 'ap_org_editor_notice_pending');
        wp_safe_redirect(remove_query_arg('ap_dismiss_editor_notice'));
        exit;
    }
}
add_action('admin_init', 'ap_handle_org_editor_notice_dismiss');

function ap_render_org_editor_notice(): void {
    $screen = function_exists('get_current_screen') ? get_current_screen() : null;
    if (!$screen || $screen->id !== 'dashboard') {
        return;
    }

    $user_id = get_current_user_id();
    $msg     = get_user_meta($user_id, 'ap_org_editor_notice_pending', true);
    if (!$msg || get_user_meta($user_id, 'ap_dismiss_org_editor_notice', true)) {
        return;
    }

    $dismiss = add_query_arg('ap_dismiss_editor_notice', '1');
    echo '<div class="notice notice-info is-dismissible"><p>' . esc_html($msg) .
        ' <a href="' . esc_url($dismiss) . '">' . esc_html__('Dismiss', 'artpulse') . '</a></p></div>';
    delete_user_meta($user_id, 'ap_org_editor_notice_pending');
}
add_action('admin_notices', 'ap_render_org_editor_notice');

function ap_dashboard_empty_state_notice(): void {
    $screen = function_exists('get_current_screen') ? get_current_screen() : null;
    if (!$screen || $screen->id !== 'dashboard') {
        return;
    }

    global $wp_meta_boxes;
    $count = 0;
    if (isset($wp_meta_boxes['dashboard'])) {
        foreach ($wp_meta_boxes['dashboard'] as $ctx) {
            foreach ($ctx as $priority) {
                $count += is_array($priority) ? count($priority) : 0;
            }
        }
    }

    if ($count === 0) {
        $url = apply_filters('ap_dashboard_empty_help_url', '');
        echo '<div class="notice notice-info"><p>' .
            esc_html__('No dashboard content available.', 'artpulse');
        if ($url) {
            echo ' <a href="' . esc_url($url) . '" target="_blank">' . esc_html__('Learn more', 'artpulse') . '</a>';
        }
        echo '</p></div>';
    }
}
add_action('admin_notices', 'ap_dashboard_empty_state_notice', 100);
