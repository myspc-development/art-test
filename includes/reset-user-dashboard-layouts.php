<?php
if (!defined('ABSPATH')) { exit; }

use ArtPulse\Core\DashboardController;

/**
 * Reset dashboard layouts for all users grouped by role.
 */
function ap_reset_user_dashboard_layouts(): void {
    $roles = ['member', 'artist', 'organization'];
    $total = 0;

    foreach ($roles as $role) {
        $users = get_users(['role' => $role, 'fields' => ['ID']]);
        foreach ($users as $user) {
            $uid = (int) $user->ID;
            if (DashboardController::reset_user_dashboard_layout($uid)) {
                $total++;
            }
        }
    }

    if (defined('WP_CLI') && WP_CLI) {
        \WP_CLI::success("Reset dashboard layouts for {$total} users.");
    } else {
        echo "✅ Reset dashboard layouts for {$total} users.\n";
    }
}

if (defined('WP_CLI') && WP_CLI) {
    \WP_CLI::add_command('ap reset-user-layouts', 'ap_reset_user_dashboard_layouts');
}
