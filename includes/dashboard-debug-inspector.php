<?php
if (!defined('ABSPATH')) { exit; }

use ArtPulse\Core\DashboardController;
use ArtPulse\Core\DashboardWidgetRegistry;

/**
 * Output debugging information for administrators when viewing wp-admin dashboard.
 */
add_action('load-index.php', function () {
    if (!current_user_can('manage_options')) {
        return;
    }
    $user_id = get_current_user_id();
    $role    = DashboardController::get_role($user_id);
    $can     = current_user_can('view_artpulse_dashboard');
    $widgets = array_keys(DashboardWidgetRegistry::get_widgets($role, $user_id));

    // Log to debug.log for troubleshooting.
    error_log(sprintf(
        'ap dashboard inspector user=%d role=%s can_view=%s widgets=%s',
        $user_id,
        $role,
        $can ? 'yes' : 'no',
        implode(',', $widgets)
    ));

    // Display an admin notice with the same information.
    add_action('admin_notices', function () use ($role, $can, $widgets) {
        echo '<div class="notice notice-info"><p>' . sprintf(
            esc_html__('Dashboard inspector – role: %1$s, can_view: %2$s, widgets: %3$s', 'artpulse'),
            esc_html($role),
            esc_html($can ? 'yes' : 'no'),
            esc_html(implode(',', $widgets))
        ) . '</p></div>';
    });
});
