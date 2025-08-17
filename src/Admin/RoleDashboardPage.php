<?php
namespace ArtPulse\Admin;

use ArtPulse\Frontend\ShortcodeRoleDashboard;
use ArtPulse\Core\DashboardController;

/**
 * Registers a hidden wp-admin page for the role-based dashboard.
 */
class RoleDashboardPage
{
    public static function register(): void
    {
        add_action('admin_menu', [self::class, 'add_page']);
    }

    public static function add_page(): void
    {
        add_submenu_page(
            null,
            __('Role Dashboard', 'artpulse'),
            __('Role Dashboard', 'artpulse'),
            'read',
            'dashboard-role',
            [self::class, 'render']
        );
    }

    public static function render(): void
    {
        $role = DashboardController::get_role(get_current_user_id());
        ShortcodeRoleDashboard::enqueue_assets($role);
        \ap_render_dashboard();
    }
}
