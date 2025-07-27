<?php
if (!defined('ABSPATH')) { exit; }

use ArtPulse\Core\DashboardController;

function ap_reset_member_dashboard_layouts(): void {
    $users = get_users(['role' => 'member', 'fields' => ['ID']]);
    $count = 0;

    foreach ($users as $user) {
        $uid = (int) $user->ID;
        if (DashboardController::reset_user_dashboard_layout($uid)) {
            $count++;
        }
    }

    if (defined('WP_CLI') && WP_CLI) {
        \WP_CLI::success("Reset dashboard layouts for {$count} members.");
    } else {
        echo "✅ Reset dashboard layouts for {$count} members.\n";
    }
}

if (defined('WP_CLI') && WP_CLI) {
    \WP_CLI::add_command('ap reset-member-layouts', 'ap_reset_member_dashboard_layouts');
}
