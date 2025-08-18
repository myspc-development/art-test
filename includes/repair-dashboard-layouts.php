<?php
if (!defined('ABSPATH')) {
    exit;
}

use ArtPulse\Admin\UserLayoutManager;

/**
 * Repopulate missing dashboard layouts for existing users.
 *
 * This iterates over all users and assigns the default layout for their
 * primary role when the `ap_dashboard_layout` meta is empty. Fallbacks to a
 * minimal layout containing the `my-events` widget if no role configuration is
 * available.
 */
function ap_repair_dashboard_layouts(): void {
    $users    = get_users(['fields' => ['ID']]);
    $repaired = 0;

    foreach ($users as $user) {
        $uid    = (int) $user->ID;
        $layout = get_user_meta($uid, UserLayoutManager::META_KEY, true);
        if (is_array($layout) && !empty($layout)) {
            continue;
        }

        $role   = UserLayoutManager::get_primary_role($uid);
        $result = UserLayoutManager::get_role_layout($role);
        $layout = $result['layout'] ?? [];
        if (empty($layout)) {
            $layout = [ ['id' => 'my-events', 'visible' => true] ];
        }

        UserLayoutManager::save_user_layout($uid, $layout);
        $repaired++;
    }

    if (defined('WP_CLI') && WP_CLI) {
        \WP_CLI::success("Repaired dashboard layouts for {$repaired} users.");
    } else {
        echo "✅ Repaired dashboard layouts for {$repaired} users.\n";
    }
}

if (defined('WP_CLI') && WP_CLI) {
    \WP_CLI::add_command('ap repair-dashboard-layouts', 'ap_repair_dashboard_layouts');
}
