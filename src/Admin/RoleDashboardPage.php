<?php
namespace ArtPulse\Admin;

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
        \ap_render_dashboard();
    }
}
